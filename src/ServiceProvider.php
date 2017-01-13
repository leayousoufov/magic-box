<?php

namespace Fuzz\MagicBox;


use Illuminate\Support\ServiceProvider as BaseServiceProvider;


class ServiceProvider extends BaseServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// pass
	}

	/**
	 * Register any other events for your application.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([$this->configPath() => config_path('magicbox.php')]);
	}

	protected function configPath()
	{
		return realpath(__DIR__ . '/../config/magicbox.php');
	}
}