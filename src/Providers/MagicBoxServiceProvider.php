<?php

namespace Fuzz\MagicBox\Providers;


use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Support\ServiceProvider;


class MagicBoxServiceProvider extends ServiceProvider
{
	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher $events
	 *
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);

		$config_file = realpath(__DIR__ . '/../config/magicbox.php');

		$this->publishes(
			[
				$config_file => config_path('magicbox.php'),
			]
		);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// pass
	}
}