<?php
namespace ILab\Stem\External\Blade;

use duncan3dc\Laravel\BladeInstance;
use ILab\Stem\Core\Context;
use ILab\Stem\Core\UI;
use ILab\Stem\Core\View;
use ILab\Stem\Models\Theme;

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
}
