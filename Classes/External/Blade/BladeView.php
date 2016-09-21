<?php
namespace ILab\Stem\External\Blade;

use duncan3dc\Laravel\Blade;
use duncan3dc\Laravel\BladeInstance;
use ILab\Stem\Core\Context;
use ILab\Stem\Core\UI;
use ILab\Stem\Core\View;
use ILab\Stem\Models\Theme;
use ILab\Stem\Utilities\ArgumentParser;

/**
 * Class BladeView
 *
 * Class for rendering laravel blade views
 *
 * @package ILab\Stem\External\Blade
 */
class BladeView extends View {

	private $blade = null;

	public function __construct(Context $context=null, UI $ui=null, $viewName=null) {
		parent::__construct($context, $ui, $viewName);

		$viewPath = $context->rootPath.'/views/';
		$cache = $ui->setting('options/views/cache');

		$this->blade = new BladeInstance($viewPath, $cache);
		$this->registerDirectives();
	}

	public function render($data) {
		return $this->blade->render($this->viewName, $data);
	}

	public static function renderView(Context $context, UI $ui, $view, $data) {
		$view=new BladeView($context, $ui, $view);
		return $view->render($data);
	}

	public static function viewExists(UI $ui, $view) {
		$exists = file_exists($ui->viewPath.$view.'.blade.php');

		if (!$exists) {
			return file_exists($ui->viewPath.$view.'.html.blade.php');
		}

		return $exists;
	}

	protected function registerDirectives() {
		$this->blade->directive('enqueue', [$this, 'enqueue']);
		$this->blade->directive('header', [$this, 'header']);
		$this->blade->directive('footer', [$this, 'footer']);
	}

	public function enqueue($expression) {
		$expression = trim($expression, '()');

		$args = ArgumentParser::Parse($expression);

		if (count($args)>2) {
			$type = $args[0];
			$resource = $args[1];

			$dep = [];

			if (count($args)>=3)
				$dep = is_array($args[2]) ? $args[2] : [$args[2]];

			for($i = 0; $i<count($dep); $i++)
				$dep[$i] = "'{$dep[$i]}'";

			if ($type == 'js')
				$dep[] = "'jquery'";

			$deps = '['.implode(',', $dep).']';

			if (($type == 'js') || ($type == 'script')) {
				return "<?php wp_enqueue_script('$resource', '{$this->context->ui->script($resource)}', $deps, false, true); ?>";
			} else if (($type == 'css') || ($type == 'style')) {
				return "<?php wp_enqueue_style('$resource'', '{$this->context->ui->css($resource)}', $deps); ?>";
			}
		}

		return null;
	}

	public function header($expression) {
		return "<?php echo ILab\\Stem\\Core\\Context::current()->ui->header(); ?>";
	}

	public function footer($expression) {
		return "<?php echo ILab\\Stem\\Core\\Context::current()->ui->footer(); ?>";
	}
}
