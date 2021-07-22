<?php

namespace Stem\Core;

use Carbon\Carbon;
use Stem\UI\Block;

/**
 * Manages the user defined blocks
 */
class Blocks {
    /** @var Block[]  */
    private $blocks = [];

    /** @var Context|null  */
    private $context = null;

    /** @var UI|null  */
    private $ui = null;

    public function __construct(Context $context, UI $ui) {
        $this->context = $context;
        $this->ui = $ui;

        add_action('init', function(){
            if (!function_exists('register_block_type') || !function_exists('acf_register_block')) {
                return;
            }

            /**  */
            add_filter('block_categories_all', function($block_categories, $editorContext) {
                return $this->blockCategories($block_categories, $editorContext);
            }, 5, 2 );

            $this->loadBlocks();
            $this->enqueueBlockAssets();
        });
    }

    private function enqueueBlockAssets() {
    	add_action('enqueue_block_editor_assets', function() {
    		$cssFiles = apply_filters('heavymetal/ui/gutenberg/enqueue/css', []);
    		$cssFiles = array_merge($cssFiles, arrayPath($this->ui->config,'gutenberg/enqueue/css', []));
    		foreach($cssFiles as $css) {
			    if (!filter_var($css, FILTER_VALIDATE_URL)) {
				    $cssUrl = $this->ui->cssPath.$css;
			    } else {
				    $cssUrl = $css;
			    }

			    wp_enqueue_style($css, $cssUrl, [], $this->context->currentBuild);
		    }


		    $jsFiles = apply_filters('heavymetal/ui/gutenberg/enqueue/js', []);
		    $jsFiles = array_merge($jsFiles, arrayPath($this->ui->config,'gutenberg/enqueue/js', []));
		    foreach($jsFiles as $js) {
			    if (!filter_var($js, FILTER_VALIDATE_URL)) {
				    $jsUrl = $this->ui->jsPath.$js;
			    } else {
				    $jsUrl = $js;
			    }

			    wp_enqueue_script($js, $jsUrl, ['jquery'], $this->context->currentBuild, true);
		    }
	    });
    }

    /**
     * Loads the blocks from the config
     * @throws \Exception
     */
    private function loadBlocks() {
	    $blocksArray = apply_filters('heavymetal/ui/gutenberg/blocks', []);
	    $blocksArray = array_merge($blocksArray, arrayPath($this->ui->config,'gutenberg/blocks', []));
        foreach($blocksArray as $blockDataorClass) {
            /** @var Block $block */
            $block = null;

            if (is_array($blockDataorClass)) {
                if (isset($blockDataorClass['class'])) {
                    $blockClass = $blockDataorClass['class'];
                    $block = new $blockClass($this->context, $this->ui, $blockDataorClass);
                } else {
                    $block = new Block($this->context, $this->ui, $blockDataorClass);
                }
            } else {
                /** @var Block $block */
                $block = new $blockDataorClass($this->context, $this->ui, []);
            }

            $this->blocks[$block->name()] = $block;

            acf_register_block([
                'name' => $block->name(),
                'title' => $block->title(),
                'description' => $block->description(),
                'render_callback' => function($blockData, $content, $is_preview, $post_id) use ($block) {
                    $this->renderBlock($block, $blockData, $is_preview, $post_id);
                },
                'category' => $block->categorySlug(),
                'icon' => $block->icon(),
                'keywords' => $block->keywords(),
	            'mode' => $block->mode(),
                'supports' => $block->supports(),
            ]);

            $block->registerFields();
        }
    }

    /**
     * Returns the user defined block categories
     *
     * @param $block_categories
     * @param $post
     * @return array
     * @throws \Exception
     */
    private function blockCategories($block_categories, $post) {
        $slugs = [];
        foreach($block_categories as $existing) {
            $slugs[] = $existing['slug'];
        }

        foreach($this->blocks as $key => $block) {
            if (!in_array($block->categorySlug(), $slugs)) {
                $slugs[] = $block->categorySlug();
                $block_categories[] = [
                    'title' => $block->category(),
                    'slug' => $block->categorySlug()
                ];
            }
        }

        return $block_categories;
    }

    private function processFieldsData($fields, $blockData, $block) {
	    $data = [];
	    foreach($fields as $field) {
		    $name = $field['name'];
		    $type = $field['type'];
		    if (!isset($blockData[$name])) {
		        $previewName = "field_".$block->name()."_".$name;
                $value = (isset($blockData[$previewName])) ? $blockData[$previewName] : null;
            } else {
                $value = $blockData[$name];
            }

		    if ($field['type'] == 'true_false') {
		        if (!isset($blockData[$name])) {
                    $value = $field['default_value'];
		        }
            } else {
                if(empty($value) && isset($field['default_value'])) {
                    $value = $field['default_value'];
                }
            }

		    if ($type == 'repeater') {
			    $repeaterData = get_field($name);
			    $value = [];
			    if (!empty($repeaterData)) {
				    foreach($repeaterData as $repeaterDatum) {
				        $valueData = $this->processFieldsData($field['sub_fields'], $repeaterDatum, $block);
				        if (!empty($field['value_class']) && class_exists($field['value_class'])) {
				            $value[] = new $field['value_class']($valueData);
                        } else {
                            $value[] = $valueData;
                        }
				    }
			    }
		    } else if (!empty($value)) {
			    if (in_array($type, ['image', 'file', 'post_object', 'page'])) {
				    $value = ($value instanceof \WP_Post) ? Context::current()->modelForPost($value) : Context::current()->modelForPostID($value);
			    } else if (($field['type'] == 'date_picker') || ($field['type'] == 'date_time_picker')) {
				    try {
					    $value = Carbon::parse($value, Context::timezone());
				    } catch (\Exception $ex) {
					    $value = Carbon::createFromFormat('d/m/Y', $value, Context::timezone());
				    }
			    } else if ($type == 'oembed') {
				    if (filter_var($value, FILTER_VALIDATE_URL)) {
					    $value = wp_oembed_get($value);
				    }
			    } else if ($type == 'gallery') {
			        $images = [];
			        foreach($value as $imageId) {
			            if (is_numeric($imageId)) {
			                $images[] = Context::current()->modelForPostID($imageId);
                        }
                    }

			        $value = $images;
                }
		    }

		    $data[camelCaseString($name)] = $value;
	    }


	    return $data;
    }

    /**
     * Maps the ACF field keys to their names for use in rendering the views
     *
     * @param Block $block
     * @param $blockData
     * @return array|object
     */
    private function processData($block, $blockData) {
    	$fields = $block->getFields();
    	return $this->processFieldsData($fields['fields'], $blockData, $block);
    }

    /**
     * Renders the block
     *
     * @param $block
     * @param $blockData
     * @param bool $is_preview
     * @param null|int $post_id
     */
    private function renderBlock($block, $blockData, $is_preview = false, $post_id = null) {
        $data = [];
        if (isset($blockData['data'])) {
            $data = $this->processData($block, $blockData['data']);
        }

        echo $block->render([
            'is_preview' => $is_preview,
            'post_id' => $post_id,
            'block' => $data
        ]);
    }

}
