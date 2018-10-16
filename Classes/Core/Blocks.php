<?php

namespace ILab\Stem\Core;

use ILab\Stem\UI\Block;

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

            add_filter('block_categories', function($block_categories, $post) {
                return $this->blockCategories($block_categories, $post);
            }, 5, 2 );

            $this->loadBlocks();
        });
    }

    /**
     * Loads the blocks from the config
     * @throws \Exception
     */
    private function loadBlocks() {
        $blocksArray = arrayPath($this->ui->config,'blocks', []);
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
                'render_callback' => function($blockData) use ($block) {
                    $this->renderBlock($block, $blockData);
                },
                'category' => $block->categorySlug(),
                'icon' => $block->icon(),
                'keywords' => $block->keywords()
            ]);
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

    /**
     * Maps the ACF field keys to their names for use in rendering the views
     *
     * @param $blockData
     * @return array|object
     */
    private function normalizeData($blockData) {
        $data = [];

        if (empty($blockData)) {
            return $data;
        }

        foreach($blockData as $key => $value) {
            if (is_array($value) && (strpos($key, 'field_') === false)) {
                $data[] = $this->normalizeData($value);
            } else {
                $field = acf_get_field($key);
                $fieldName = $field['name'];

                if ($field['type']=='repeater') {
                    $data[$fieldName] = $this->normalizeData($value);
                } else {
                    if (in_array($field['type'], ['image', 'post_object', 'page_link'])) {
                        if (is_numeric($value)) {
                            $data[$fieldName] = $this->context->modelForPostID($value);
                        } else if (is_array($value) && isset($value['ID'])) {
                            $data[$fieldName] = $this->context->modelForPostID($value);
                        } else if ($value instanceof \WP_Post) {
                            $data[$field['name']] = $this->context->modelForPost($value);
                        }
                    } else {
                        $data[$field['name']] = $value;
                    }
                }
            }
        }

        return (object)$data;
    }

    /**
     * Renders the block
     *
     * @param $block
     * @param $blockData
     */
    private function renderBlock($block, $blockData) {
        $data = [];
        if (isset($blockData['data'])) {
            $data = $this->normalizeData($blockData['data']);
        }

        echo $block->render(['block' => $data]);
    }

}