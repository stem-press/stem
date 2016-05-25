<?php
namespace ILab\Stem\External\Blade;

use duncan3dc\Laravel\BladeInstance;
use ILab\Stem\Core\Context;
use ILab\Stem\Core\View;

/**
 * Class BladeView
 *
 * Class for rendering laravel blade views
 *
 * @package ILab\Stem\External\Blade
 */
class BladeView extends View {

	private $blade = null;

	public function __construct(Context $context=null, $viewName=null) {
		parent::__construct($context, $viewName);

		$viewPath = $context->rootPath.'/views/';
		$cache = $context->setting('options/views/cache');

		$this->blade = new BladeInstance($viewPath, $cache);
	}

	public function render($data) {
		if ($data==null)
			$data=[];

		if (!isset($data['context']))
			$data['context']=$this->context;

		return $this->blade->render($this->viewName, $data);
	}

	public static function renderView(Context $context, $view, $data) {
		$view=new BladeView($context, $view);
		return $view->render($data);
	}

	public static function viewExists(Context $context, $view) {
		$exists = file_exists($context->viewPath.$view.'.blade.php');

		if (!$exists) {
			return file_exists($context->viewPath.$view.'.html.blade.php');
		}

		return $exists;
	}
}
