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
		$this->publishes([
			__DIR__.'/../../config/connection.php' => config_path('consumers-mq/connection.php'),
			__DIR__.'/../../config/constants.php' => config_path('consumers-mq/constants.php'),
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		\App::bind('ConnectMQ', function() {
			$configuration = \Config::get('consumers-mq.connection');
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
