<?php

namespace ILab\Stem\UI;

use ILab\Stem\Core\Context;

/**
 * Class ShortCode.
 *
 * Represents a dashboard widget on the WordPress dashboard.
 */
abstract class ShortCode
{
    protected $context;
    protected $config;

    /**
     * ShortCode constructor.
     *
     * @param Context $context
     * @param array $config
     */
    public function __construct(Context $context, $config = [])
    {
        $this->context = $context;
        $this->config = $config;
    }

    /**
     * Registers the UI for the shortcode via Shortcake plugin.  If Shortcake isn't installed, this will not be called.
     * Additionally, if you have the UI defined in your config for the shortcode, this won't be called either.
     * @param string $shortCode The shortcode's name as defined in ui.php configuration.
     */
    public function registerUI($shortCode)
    {
    }

    /**
     * Renders the shortcode.
     * @return string
     */
    abstract public function render($attrs = [], $content = null);
}
