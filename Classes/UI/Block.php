<?php

namespace ILab\Stem\UI;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\UI;

abstract class Block {
    protected $context = null;
    protected $ui = null;
    protected $template = null;

    public function __construct(Context $context, UI $ui, $template = null) {
        $this->context = $context;
        $this->ui = $ui;

        $this->template = $template;

        if (empty($this->template)) {
            $this->template = 'blocks/'.class_basename($this);
        }
    }

    abstract public function category();
    abstract public function title();
    abstract public function description();
    abstract public function icon();


    public function slug() {
        return sanitize_title($this->title());
    }

    public function categorySlug() {
        return sanitize_title($this->category());
    }

    public function registerFields() {

    }

    public function render($post_id, $fields) {
        $post = (empty($post_id)) ? null : $this->context->modelForPostID($post_id);
        return $this->ui->render($this->template, array_merge(['post' => $post], $fields));
    }

    public function data() {
        return [
            'title' => $this->title(),
            'description' => $this->description(),
            'block_icon' => $this->icon(),
            'block_category_slug' => $this->categorySlug(),
            'block_name' => $this->slug()
        ];
    }
}