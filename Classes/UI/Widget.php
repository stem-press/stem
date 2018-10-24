<?php

namespace ILab\Stem\UI;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\UI;
use ILab\Stem\UI\Utilities\WPWidgetWrapper;

abstract class Widget {
    /** @var WPWidgetWrapper The underlying WordPress widget */
    protected $wpWidget;

    protected $context;
    protected $ui;

    protected $template;
    protected $formTemplate;

    public function __construct(Context $context, UI $ui) {
        $this->context = $context;
        $this->ui = $ui;

        $this->template = 'widgets/'.$this->id();
        $this->formTemplate = 'widgets/'.$this->id().'-form';

        $this->wpWidget = new WPWidgetWrapper($this);
    }

    abstract public function id();
    abstract public function name();

    public function render($data) {
        return $this->ui->render($this->template, $data);
    }

    public function renderForm($data) {
        $data['widget'] = $this;

        return $this->ui->render($this->formTemplate, $data);
    }

    public function processData($data) {
        return $data;
    }

    public function fieldID($field) {
        return $this->wpWidget->get_field_id($field);
    }

    public function fieldName($field) {
        return $this->wpWidget->get_field_name($field);
    }

}