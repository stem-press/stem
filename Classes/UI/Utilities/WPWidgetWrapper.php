<?php

namespace Stem\UI\Utilities;

use Stem\UI\Widget;

class WPWidgetWrapper extends \WP_Widget {
    /** @var Widget The wrapped widget */
    protected $widget;

    public function __construct(Widget $widget, array $widget_options = [], array $control_options = []) {
        parent::__construct($widget->id(), $widget->name(), $widget_options, $control_options);

        $this->widget = $widget;

        add_action('widgets_init', function(){
            register_widget($this);
        });
    }

    public function widget($args, $instance) {
        echo $this->widget->render(array_merge($args, $instance));
    }

    public function form($instance) {
        echo $this->widget->renderForm($instance);
    }

    public function update($new_instance, $old_instance) {
        return $this->widget->processData($new_instance);
    }

}