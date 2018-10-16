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

    abstract public function description();
    abstract public function icon();
    abstract public function keywords();

    abstract public function title();
    public function name() {
        return sanitize_title($this->title());
    }

    abstract public function category();
    public function categorySlug() {
        return sanitize_title($this->category());
    }

    public function registerFields() {

    }

    public function render($data) {
        echo $this->ui->render($this->template, $data);
    }
}