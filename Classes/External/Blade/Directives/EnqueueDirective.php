<?php

namespace ILab\Stem\External\Blade\Directives;

use ILab\Stem\Core\ViewDirective;

/**
 * Class EnqueueDirective.
 *
 * Adds an `@enqueue` directive to Blade templates.  Usage:
 *
 * ```
 * @enqueue('js','name.of.script.js')
 * ```
 *
 * First argument must either be 'js' or 'css'
 */
class EnqueueDirective extends ViewDirective
{
    public function execute($args)
    {
        if (count($args) > 2) {
            $type = $args[0];
            $resource = $args[1];

            $dep = [];

            if (count($args) >= 3) {
                $dep = is_array($args[2]) ? $args[2] : [$args[2]];
            }

            for ($i = 0; $i < count($dep); $i++) {
                $dep[$i] = "'{$dep[$i]}'";
            }

            if ($type == 'js') {
                $dep[] = "'jquery'";
            }

            $deps = '['.implode(',', $dep).']';

            if (($type == 'js') || ($type == 'script')) {
                return "<?php wp_enqueue_script('$resource', '{$this->context->ui->script($resource)}', $deps, false, true); ?>";
            } elseif (($type == 'css') || ($type == 'style')) {
                return "<?php wp_enqueue_style('$resource'', '{$this->context->ui->css($resource)}', $deps); ?>";
            }
        }
    }
}
