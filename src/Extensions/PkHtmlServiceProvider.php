<?php
namespace PkExtensions;
use Illuminate\Html\FormBuilder;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

class PkHtmlServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register() {
		$this->registerPkFormBuilder();
		$this->registerPkHtmlBuilder();
    $this->app->alias('pkform', 'PkExtensions\PkFormBuilder');
    $this->app->alias('pkhtml', 'PkExtensions\PkHtmlBuilder');
  }
	protected function registerPkHtmlBuilder() {
		$this->app->singleton('pkhtml', function($app) {
			$html = new PkHtmlBuilder($app['pkhtml'], $app['url']);
      return $html;
		});
  }
	protected function registerPkFormBuilder() {
		$this->app->singleton('pkform', function($app) {
			//$form = new PkFormBuilder($app['html'], $app['url'], $app['session.store']->getToken());
			$form = new PkFormBuilder($app['pkhtml'], $app['url'], $app['session.store']->getToken());
			return $form->setSessionStore($app['session.store']);
		});
	}
	public function provides() {
		return ['pkform','pkhtml'];
	}
}
