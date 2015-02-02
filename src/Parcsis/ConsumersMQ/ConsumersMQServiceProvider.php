<?php

namespace Parcsis\ConsumersMQ;

use Illuminate\Support\ServiceProvider;

class ConsumersMQServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('parcsis/consumers-mq');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		/*\App::bind('Connect', function() {
			$configuration = \Config::get('consumers-mq::connection');
			return new Connection($configuration);
		});*/

		\App::singleton('ConnectMQ', function() {
			$configuration = \Config::get('consumers-mq::connection');
			return new Connection($configuration);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
