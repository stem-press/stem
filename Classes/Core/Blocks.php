<?php

namespace ILab\Stem\Core;

use ILab\Stem\UI\Block;

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

    private function loadBlocks() {
        $blocksArray = arrayPath($this->ui->config,'blocks', []);
        foreach($blocksArray as $blockClass) {
            /** @var Block $block */
            $block = new $blockClass($this->context, $this->ui);
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

    private function blockCategories($block_categories, $post) {
        $slugs = [];
        foreach($this->blocks as $key => $block) {
            if (!key_exists($block->categorySlug(), $slugs)) {
                $slugs[] = $block->categorySlug();
                $block_categories[] = [
                    'title' => $block->category(),
                    'slug' => $block->categorySlug()
                ];
            }
        }

        return $block_categories;
    }

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

    private function renderBlock($block, $blockData) {
//        acf_get_field
        $data = [];
        if (isset($blockData['data'])) {
            $data = $this->normalizeData($blockData['data']);
        }

        echo $block->render(['block' => $data]);
    }

}