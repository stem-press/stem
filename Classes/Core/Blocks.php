<?php

namespace ILab\Stem\Core;

use ILab\Stem\UI\Block;

class Blocks {
    /*** @var Block[] */
    private $blocks = [];
    private $blockData = [];
    private $context = null;
    private $ui = null;

    private $acb_block;
    private $acb_is_updating_fields = false;

    public function __construct(Context $context, UI $ui) {
        $this->context = $context;
        $this->ui = $ui;

        if (!function_exists('register_block_type')) {
            return;
        }

        $this->loadBlocks();

        $this->configureACF();

        add_action('save_post', [$this, 'savePost'], 10, 1);
        add_filter('block_categories', [$this, 'blockCategories'], 5, 2 );
        add_action('init', [$this, 'initBlocks']);
        add_action('admin_notices', [$this, 'renderScripts'], 0);
    }

    private function loadBlocks() {
        $blocksArray = arrayPath($this->ui->config,'blocks', []);
        foreach($blocksArray as $blockClass) {
            /** @var Block $block */
            $block = new $blockClass($this->context, $this->ui);
            $this->blocks[$block->slug()] = $block;
            $this->blockData[$block->slug()] = $block->data();
        }



        $groups = acf_get_field_groups();
        foreach($groups as $group) {
            if (!isset($group['location'])) {
                continue;
            }

            if (count($group['location'])==0) {
                continue;
            }

            $location = $group['location'][0][0];
            if ($location['param'] == 'block_name') {
               $value = $location['value'];
               if (isset($this->blockData[$value])) {
                   $this->blockData[$value]['ID'] = $group['ID'];
               }
            }
        }
    }

    private function configureACF() {
        // Add a new location to where ACF can display custom fields
        add_filter('acf/location/rule_types', function ( $choices ) {
            $choices['Gutenberg']['block_name'] = 'Block Name';
            return $choices;
        }, 10, 1);

        // Return the registered block titles as values to match against
        add_filter('acf/location/rule_values/block_name', function ( $choices, $data ) {
            foreach($this->blocks as $key => $block) {
                $choices[$key] = $block->title();
            }

            return $choices;
        }, 10, 2);

        // Check to see if the current block is a match
        add_filter('acf/location/rule_match/block_name', function ( $match, $rule, $options ) {
            if (empty($rule['value'])) {
                return true;
            }

            return ($rule['operator'] === '==') === ($this->getCurrentBlockData('name') == $rule['value']);
        }, 10, 3);

        add_filter('acf/location/screen', function ($screen, $field_group) {
            if (!isset($field_group['block_name']) || !$field_group['block_name']) {
                return $screen;
            }

            if (!isset($screen['post_id']) || !$screen['post_id']) {
                $screen['post_id'] = $_REQUEST['post'] ?: $_REQUEST['post_id'] ?: $_REQUEST['attributes']['post_id'];
            }

            return $screen;
        }, 1, 2);

        add_filter('acf/load_field', [$this, 'loadField'], 10, 1);
    }

    private function getCurrentBlockData($key = null) {
        if (empty($key)) {
            return null;
        }


        if ($key === 'name') {
            $key = 'block_name';
        }

        if (empty($this->acb_block) || !isset($this->acb_block[$key])) {
            return null;
        }

        return $this->acb_block[$key];
    }

    public function savePost($post_id) {
        if (!isset($_POST['acf_blocks']) || empty($_POST['acf_blocks'])) {
            return;
        }

        foreach ($_POST['acf_blocks'] as $block_id => $fields) {
            foreach ($fields as $field_key => $meta_value) {
                $field = get_field_object($field_key);

                delete_post_meta($post_id, $field['name']);

                update_field($field_key, $meta_value, $post_id);
            }
        }

        foreach ($_POST['acf_blocks'] as $block_id => $fields) {
            foreach ($fields as $field_key => $meta_value) {
                $field = get_field_object($field_key);

                if (isset($field['sub_fields'])) {
                    continue;
                }

                add_post_meta($post_id, $field['name'], $meta_value, false);
            }
        }
    }

    public function loadField($field) {
        if ($this->acb_is_updating_fields) {
            return $field;
        }

        if (!$this->getCurrentBlockData('block_id') || !$this->getCurrentBlockData('post_id')) {
            return $field;
        }

        $value = $this->getCurrentBlockData('acf_fields');

        if ($value) {
            if (isset($field['sub_fields'])) {
                $this->acb_is_updating_fields = true;

                acf_save_post( $this->getCurrentBlockData('post_id'), $value );

                $this->acb_is_updating_fields = false;
            }

            if (isset($value[$field['key']])) {
                $value = $value[$field['key']];
            }

            $field['value'] = $value;
        }

        return $field;
    }

    public function initBlocks() {
        if (!function_exists('register_block_type')) {
            return;
        }

        foreach ($this->blocks as $key => $block) {
            register_block_type( 'acf/' . $key, array(
                'attributes'      => array(
                    'post_id' => array(
                        'type'    => 'number',
                        'default' => 0,
                    ),
                    'block_id' => array(
                        'type'    => 'string',
                        'default' => '',
                    ),
                    'block_name' => array(
                        'type'    => 'string',
                        'default' => $key,
                    ),
                    'acf_fields' => array(
                        'type'    => 'object',
                        'default' => [],
                    ),
                    'acf_field_group' => array(
                        'type'    => 'number',
                        'default' => 0,
                    ),
                ),
                'render_callback' => function ($attributes) use ($block) {
                    global $post;

                    $output = '';

                    if ($post instanceof \WP_Post && $post->ID) {
                        $attributes['post_id'] = $post->ID;
                    } else if ($attributes['post_id']) {
                        setup_postdata($post = get_post($attributes['post_id']));
                    }

                    $cache_active = acf_is_cache_active();

                    if ($cache_active) {
                        acf_disable_cache();
                    }

                    if (isset($_REQUEST['attributes']) && isset($_REQUEST['attributes']['acf_fields'])) {
                        $attributes['acf_fields'] = $_REQUEST['attributes']['acf_fields'];
                    }

                    $this->acb_block = $attributes;

                    if (isset($_GET['context']) && ($_GET['context'] === 'edit')) {
                        ob_start();
                        $fields = acf_get_fields($attributes['acf_field_group']);
                        acf_render_fields($fields, $attributes['post_id']);
                        $output .= ob_get_contents();
                        ob_end_clean();
                    } else {
                        $values = [];

                        $fields = get_field_objects($post->ID);

                        if (isset($attributes['acf_fields'])) {
                            foreach($attributes['acf_fields'] as $fieldKey => $fieldValue) {
                                $field = get_field_object($fieldKey, $attributes['post_id']);
                                $values[$field['name']] = $fieldValue;
                            }
                        }

                        $output .= $block->render($attributes['post_id'], $values);
                    }

                    reset_rows();
                    wp_reset_postdata();
                    $this->acb_block = null;

                    if ($cache_active) {
                        acf_enable_cache();
                    }

                    return $output;
                },
            ) );
        }
    }

    public function blockCategories($block_categories, $post) {
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

    public function renderScripts() {
        ?>
        <script>
            (function ($) {
                if (!window.wp || !window.wp.blocks || !window.wp.editor) {
                    return;
                }

                var groupElements = {},
                    fieldGroups = {},
                    fieldGroupForms = {},
                    field_groups = <?php echo json_encode(array_values($this->blockData)); ?>;

                console.log('ACF Field Groups:', field_groups);

                function loadACF(callback) {
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: $.param({
                            action: 'gutenberg_match_acf',
                            post_id: <?php echo json_encode(get_the_ID()); ?>,
                        }),
                        success: function (field_groups) {
                            callback(field_groups.sort(function (a, b) {
                                return a.menu_order - b.menu_order;
                            }), true);
                        }
                    });
                }

                field_groups.forEach(function (group) {
                    var slug = 'acf/' + group.block_name;

                    console.log('Register block', slug, group);

                    wp.blocks.registerBlockType(slug, {
                        title: group.title,
                        description: group.description,
                        icon: group.block_icon || 'feedback',
                        category: group.block_category_slug || 'widgets',
                        supports: {
                            html: false,
                        },
                        getEditWrapperProps: function( attributes ) {
                            return attributes;
                        },
                        edit: function (block) {
                            // console.log('edit', block);
                            var el = wp.element.createElement;

                            block.attributes.post_id = <?php echo json_encode(intval(get_the_ID())); ?>;
                            var block_id = block.attributes.block_id = block.attributes.block_id || block.clientId;

                            var remote = el('form', {
                                className: 'acf-block-group-content',
                                'data-block-id': block_id,
                            }, [
                                el(wp.components.ServerSideRender, {
                                    block: slug,
                                    attributes: {
                                        acf_fields: block.attributes.acf_fields,
                                        acf_field_group: group.ID,
                                        post_id: <?php echo json_encode(intval(get_the_ID())); ?>,
                                        block_id: block_id,
                                    },
                                })
                            ]);

                            var children = [];

                            if (group.style === 'default') {
                                children.push(el('div', { className: 'acf-block-group-heading' }, [
                                    el('span', {
                                        className: 'dashicons dashicons-' + (group.block_icon || 'feedback'),
                                    }),
                                    ' ',
                                    group.title
                                ]));
                            }

                            var selector = 'form[data-block-id="' + block_id + '"]';

                            if ($(selector).length < 1) {
                                $(document).on('acb_save_fields', function () {
                                    block.setAttributes({
                                        acf_fields: acf.serialize($(selector))['acf'],
                                    });
                                });
                            }
                            // setTimeout(function () {
                            //   acf.do_action('ready', $('[data-block-id="' + block_id + '"]'));
                            // }, 500);

                            children.push(remote);

                            return el('div', {
                                className: 'acf-block-group-wrapper',
                            }, children);
                        },
                        save: function (block) {
                            // console.log('SAVE', block);

                            return null;
                        },
                    })
                });

                wp.apiFetch.use(function (options, next) {
                    if (options.path && /block-renderer\/acf/.test(options.path)) {
                        var res = next(options);

                        res.then(function () {
                            setTimeout(function () {
                                $('[data-block-id]').each(function () {
                                    acf.do_action('ready', $(this));
                                });
                            }, 500);
                        });

                        return res;
                    }

                    // Publish: method === PUT
                    // Autosave: method === POST

                    if ((options.method === 'PUT' || options.method === 'POST') && options.data && options.data.content) {
                        $(document).trigger('acb_save_fields');

                        /*
                        return new Promise(function (resolve, reject) {
                          var interval = setInterval(function () {
                            if ($('#editor .components-placeholder').length < 1) {
                              doRequest();
                            }
                          }, 150);

                          var doRequest = function () {
                            clearInterval(interval);
                            next(options).then(resolve).catch(reject);
                          };

                          setTimeout(doRequest, 1500);
                        });
                        */

                        return next(options);
                    }

                    if (options.method !== 'POST' || !(options.body instanceof FormData)) {
                        return next(options);
                    }

                    $('.acf-block-group-content').each(function () {
                        var form = this;

                        // var data = $.param(acf.serialize($(this)));
                        // console.log(data);

                        (new FormData(this)).forEach(function (val, key) {
                            // var val = data[key];
                            console.log('Saving ACF field', key, val);

                            options.body.append(key, val);

                            key = key.replace(/^acf/, 'acf_blocks[' + $(form).data('block-id') + ']');
                            console.log('Saving ACF field', key, val);
                            options.body.append(key, val);
                        });
                    });

                    return next(options);
                });
            })(jQuery);
        </script>
        <style>
            .acf-block-group-wrapper {
                overflow: auto;
            }
            .acf-block-group-heading {
                background-color: #EEE;
                padding: 0.25em 0.5em;
            }
            .acf-block-group-heading > .dashicons {
                vertical-align: text-top;
            }
            .acf-block-group-content {
            }
        </style>
        <?php
    }
}