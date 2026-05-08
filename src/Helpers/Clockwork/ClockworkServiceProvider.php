<?php



namespace LARAVEL\Helpers\Clockwork;

use Clockwork\Clockwork;
use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\DataSource\LaravelEventsDataSource;
use Clockwork\Request\Request;
use Clockwork\Storage\StorageInterface;

use Illuminate\Support\ServiceProvider;
use LARAVEL\Core\Routing\LARAVELRouter;
use LARAVEL\Helpers\Clockwork\Datasource\EloquentDataSource;
use LARAVEL\Helpers\Clockwork\Datasource\LaravelDataSource;

class ClockworkServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app['clockwork.support']
			->configureSerializer()
			->configureShouldCollect()
			->configureShouldRecord();

		// $this->app['clockwork.support']->handleArtisanEvents();
		// $this->app['clockwork.support']->handleOctaneEvents();

		// If Clockwork is disabled, return before registering middleware or routes
		if (! $this->app['clockwork.support']->isEnabled()) return;

		// $this->registerRoutes();

		// register the Clockwork Web UI routes
		// if ($this->app['clockwork.support']->isWebEnabled()) {
		// 	$this->registerWebRoutes();
		// }
	}

	public function register()
	{
		$this->registerConfiguration();
		$this->registerClockwork();
		// $this->registerCommands();
		$this->registerDataSources();
		$this->registerAliases();

		$this->app->make('clockwork.request'); // instantiate the request to have id and time available as early as possible

		if ($this->app['clockwork.support']->getConfig('register_helpers', true)) {
			require __DIR__ . '/helpers.php';
		}
	}

	// Register the configuration file
	protected function registerConfiguration()
	{
		$this->publishes([__DIR__ . '/config/clockwork.php' => config_path('clockwork.php')], 'clockwork');
		$this->mergeConfigFrom(__DIR__ . '/config/clockwork.php', 'clockwork');
	}

	// Register core Clockwork components
	protected function registerClockwork()
	{
		$this->app->singleton('clockwork', function ($app) {
			return (new Clockwork)
				// ->authenticator($app['clockwork.authenticator'])
				->request($app['clockwork.request'])
				->storage($app['clockwork.storage']);
		});

		$this->app->singleton('clockwork.authenticator', function ($app) {
			return $app['clockwork.support']->makeAuthenticator();
		});

		$this->app->singleton('clockwork.request', function ($app) {
			return new Request;
		});

		$this->app->singleton('clockwork.storage', function ($app) {
			return $app['clockwork.support']->makeStorage();
		});

		$this->app->singleton('clockwork.support', function ($app) {
			return new ClockworkSupport($app);
		});
	}

	// Register the artisan commands
	protected function registerCommands()
	{
		// $this->commands([
		// 	ClockworkCleanCommand::class
		// ]);
	}

	// Register Clockwork data sources
	protected function registerDataSources()
	{
		// $this->app->singleton('clockwork.cache', function ($app) {
		// 	return (new LaravelCacheDataSource(
		// 		$app['events'],
		// 		$app['clockwork.support']->getConfig('features.cache.collect_queries'),
		// 		$app['clockwork.support']->getConfig('features.cache.collect_values')
		// 	));
		// });

		$this->app->singleton('clockwork.eloquent', function ($app) {
			$dataSource = (new EloquentDataSource(
				$app['capsule']->getDatabaseManager(),
				$app['events'],
				$app['clockwork.support']->getConfig('features.database.collect_queries'),
				$app['clockwork.support']->getConfig('features.database.slow_threshold'),
				$app['clockwork.support']->getConfig('features.database.slow_only'),
				$app['clockwork.support']->getConfig('features.database.detect_duplicate_queries'),
				$app['clockwork.support']->getConfig('features.database.collect_models_actions'),
				$app['clockwork.support']->getConfig('features.database.collect_models_retrieved')
			));

			// // if we are collecting queue jobs, filter out queries caused by the database queue implementation
			// if ($app['clockwork.support']->isCollectingQueueJobs()) {
			// 	$dataSource->addFilter(function ($query, $trace) {
			// 		return ! $trace->first(StackFilter::make()->isClass(\Illuminate\Queue\DatabaseQueue::class));
			// 	}, 'early');
			// }

			// if ($app->runningUnitTests()) {
			// 	$dataSource->addFilter(function ($query, $trace) {
			// 		return ! $trace->first(StackFilter::make()->isClass([
			// 			\Illuminate\Database\Migrations\Migrator::class,
			// 			\Illuminate\Database\Console\Migrations\MigrateCommand::class
			// 		]));
			// 	});
			// }

			return $dataSource;
		});

		$this->app->singleton('clockwork.events', function ($app) {
			return (new LaravelEventsDataSource(
				$app['events'],
				$app['clockwork.support']->getConfig('features.events.ignored_events', [])
			));
		});

		// $this->app->singleton('clockwork.http-requests', function ($app) {
		// 	return new LaravelHttpClientDataSource(
		// 		$app['events'],
		// 		$app['clockwork.support']->getConfig('features.http_requests.collect_data'),
		// 		$app['clockwork.support']->getConfig('features.http_requests.collect_raw_data')
		// 	);
		// });

		$this->app->singleton('clockwork.laravel', function ($app) {
			return (new LaravelDataSource(
				$app,
				$app['clockwork.support']->isFeatureEnabled('log'),
				$app['clockwork.support']->isFeatureEnabled('routes'),
				$app['clockwork.support']->getConfig('features.routes.only_namespaces', [])
			));
		});

		// $this->app->singleton('clockwork.notifications', function ($app) {
		// 	return new LaravelNotificationsDataSource($app['events']);
		// });

		// $this->app->singleton('clockwork.queue', function ($app) {
		// 	return (new LaravelQueueDataSource($app['queue']->connection()));
		// });

		// $this->app->singleton('clockwork.redis', function ($app) {
		// 	$dataSource = new LaravelRedisDataSource($app['events']);

		// 	// if we are collecting queue jobs, filter out commands executed by the redis queue implementation
		// 	if ($app['clockwork.support']->isCollectingQueueJobs()) {
		// 		$dataSource->addFilter(function ($query, $trace) {
		// 			return ! $trace->first(StackFilter::make()->isClass([
		// 				\Illuminate\Queue\RedisQueue::class,
		// 				\Laravel\Horizon\Repositories\RedisJobRepository::class,
		// 				\Laravel\Horizon\Repositories\RedisTagRepository::class,
		// 				\Laravel\Horizon\Repositories\RedisMetricsRepository::class
		// 			]));
		// 		});
		// 	}

		// 	return $dataSource;
		// });

		// $this->app->singleton('clockwork.swift', function ($app) {
		// 	return new SwiftDataSource($app['mailer']->getSwiftMailer());
		// });

		// $this->app->singleton('clockwork.twig', function ($app) {
		// 	return new TwigDataSource($app['twig']);
		// });

		// $this->app->singleton('clockwork.views', function ($app) {
		// 	return new LaravelViewsDataSource(
		// 		$app['events'],
		// 		$app['clockwork.support']->getConfig('features.views.collect_data')
		// 	);
		// });

		// $this->app->singleton('clockwork.xdebug', function ($app) {
		// 	return new XdebugDataSource;
		// });
	}

	// Set up aliases for all Clockwork parts so they can be resolved by type-hinting
	protected function registerAliases()
	{
		$this->app->alias('clockwork', Clockwork::class);

		$this->app->alias('clockwork.authenticator', AuthenticatorInterface::class);
		$this->app->alias('clockwork.storage', StorageInterface::class);
		$this->app->alias('clockwork.support', ClockworkSupport::class);

		// $this->app->alias('clockwork.cache', LaravelCacheDataSource::class);
		$this->app->alias('clockwork.eloquent', EloquentDataSource::class);
		$this->app->alias('clockwork.events', LaravelEventsDataSource::class);
		$this->app->alias('clockwork.laravel', LaravelDataSource::class);
		// $this->app->alias('clockwork.notifications', LaravelNotificationsDataSource::class);
		// $this->app->alias('clockwork.queue', LaravelQueueDataSource::class);
		// $this->app->alias('clockwork.redis', LaravelRedisDataSource::class);
		// $this->app->alias('clockwork.swift', SwiftDataSource::class);
		// $this->app->alias('clockwork.xdebug', XdebugDataSource::class);
	}

	// Register event listeners
	protected function registerEventListeners()
	{
		
	}

	// Register middleware
	// protected function registerMiddleware()
	// {
	// 	$eventHandler = new EventHandler();

	// 	// Add event that fires when a route is rendered
	// 	$eventHandler->register(EventHandler::EVENT_RENDER_MIDDLEWARES, function (EventArgument $argument) {

	// 	});

	// 	LARAVELRouter::addEventHandler($eventHandler);
	// }

	protected function registerWebRoutes()
	{
		$this->app['clockwork.support']->webPaths()->each(function ($path) {
			LARAVELRouter::get("{$path}", 'LARAVEL\Helpers\Clockwork\ClockworkController@webRedirect');
			LARAVELRouter::get("{$path}/app", 'LARAVEL\Helpers\Clockwork\ClockworkController@webIndex');
			LARAVELRouter::get("{$path}/{path}", 'LARAVEL\Helpers\Clockwork\ClockworkController@webAsset')
				->where(['path' => '.+']);
		});
	}

	public function provides()
	{
		return ['clockwork'];
	}
}
