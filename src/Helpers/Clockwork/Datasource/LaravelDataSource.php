<?php



namespace LARAVEL\Helpers\Clockwork\Datasource;

use Clockwork\DataSource\DataSource;
use Clockwork\Helpers\Serializer;
use Clockwork\Request\{Log, Request};

use LARAVEL\Core\Container;
use LARAVEL\Core\Routing\LARAVELRouter;
use LARAVEL\DatabaseCore\Eloquent\Model;
use Pecee\Http\Response;

// Data source for Laravel framework, provides application log, request and response information
class LaravelDataSource extends DataSource
{
	// Laravel application instance
	protected $app;

	// Laravel response instance
	protected $response;

	// Whether we should collect log messages
	protected $collectLog = true;

	// Whether we should collect routes
	protected $collectRoutes = false;

	// Only collect routes from following list of namespaces (collect all if empty)
	protected $routesOnlyNamespaces = [];

	// Clockwork log instance
	protected $log;

	// Create a new data source, takes Laravel application instance and additional options as an arguments
	public function __construct(Container $app, $collectLog = true, $collectRoutes = false, $routesOnlyNamespaces = true)
	{
		$this->app = $app;

		$this->collectLog           = $collectLog;
		$this->collectRoutes        = $collectRoutes;
		$this->routesOnlyNamespaces = $routesOnlyNamespaces;

		$this->log = new Log;
	}

	// Adds request, response information, middleware, routes, session data, user and log entries to the request
	public function resolve(Request $request)
	{
		$request->method         = $this->getRequestMethod();
		$request->url            = $this->getRequestUrl();
		$request->uri            = $this->getRequestUri();
		$request->controller     = $this->getController();
		$request->headers        = $this->getRequestHeaders();
		$request->responseStatus = $this->getResponseStatus();
		$request->middleware     = $this->getMiddleware();
		$request->routes         = $this->getRoutes();
		$request->sessionData    = $this->getSessionData();

		$this->resolveAuthenticatedUser($request);

		$request->log()->merge($this->log);

		return $request;
	}

	// Reset the data source to an empty state, clearing any collected data
	public function reset()
	{
		$this->log = new Log;
	}

	// Set Laravel application instance for the current request
	public function setApplication(Container $app)
	{
		$this->app = $app;
		return $this;
	}

	// Set Laravel response instance for the current request
	public function setResponse(Response $response)
	{
		$this->response = $response;
		return $this;
	}

	// Listen for the log events
	public function listenToEvents()
	{
		if (! $this->collectLog) return;

		if (class_exists(\Illuminate\Log\Events\MessageLogged::class)) {
			// Laravel 5.4
			$this->app['events']->listen(\Illuminate\Log\Events\MessageLogged::class, function ($event) {
				$this->log->log($event->level, $event->message, $event->context);
			});
		} else {

			// Laravel 5.0 to 5.3
			$this->app['events']->listen('illuminate.log', function ($level, $message, $context) {
				$this->log->log($level, $message, $context);
			});
		}
	}

	// Get a textual representation of the current route's controller
	protected function getController()
	{
		$router = LARAVELRouter::router();

		$route = $router->getCurrentProcessingRoute();
		$controller = $route ? $route->getCallback() : null;


		if (\is_string($controller)) {
			$controller = $route->getNamespace() . '\\' . explode('@', $controller)[0];
		} else if ($controller instanceof \Closure) {
			$controller = 'anonymous function';
		} elseif (is_object($controller)) {
			$controller = 'instance of ' . get_class($controller);
		} elseif (is_array($controller) && count($controller) == 2) {
			if (is_object($controller[0])) {
				$controller = get_class($controller[0]) . '->' . $controller[1];
			} else {
				$controller = $controller[0] . '::' . $controller[1];
			}
		} elseif (! is_string($controller)) {
			$controller = null;
		}

		return $controller;
	}

	// Get the request headers
	protected function getRequestHeaders()
	{
		return $this->app['request']->headers->all();
	}

	// Get the request method
	protected function getRequestMethod()
	{
		return $this->app['request']->getMethod();
	}

	// Get the request URL
	protected function getRequestUrl()
	{
		return $this->app['request']->fullUrl();
	}

	// Get the request URI
	protected function getRequestUri()
	{
		return $this->app['request']->getRequestUri();
	}

	// Get the response status code
	protected function getResponseStatus()
	{
		return \http_response_code();
	}

	// Get an array of middleware for the matched route
	protected function getMiddleware()
	{
		$route = LARAVELRouter::router()->getCurrentProcessingRoute();

		if (! $route) return;

		return method_exists($route, 'getMiddlewares') ? $route->getMiddlewares() : $route->getMiddlewares();
	}

	// Get an array of application routes
	protected function getRoutes()
	{
		if (! $this->collectRoutes) return [];

		return array_values(array_filter(array_map(function ($route) {
			$action = $route->getActionName() ?: 'anonymous function';
			$namespace = strpos($action, '\\') !== false ? explode('\\', $action)[0] : null;

			if (count($this->routesOnlyNamespaces) && ! in_array($namespace, $this->routesOnlyNamespaces)) return;

			return [
				'method'     => implode(', ', $route->methods()),
				'uri'        => $route->uri(),
				'name'       => $route->getName(),
				'action'     => $action,
				'middleware' => $route->middleware(),
				'before'     => method_exists($route, 'beforeFilters') ? implode(', ', array_keys($route->beforeFilters())) : '',
				'after'      => method_exists($route, 'afterFilters') ? implode(', ', array_keys($route->afterFilters())) : ''
			];
		}, $this->app['router']->getRoutes()->getRoutes())));
	}

	// Get the session data (normalized with removed passwords)
	protected function getSessionData()
	{
		if (! isset($this->app['session'])) return [];

		return $this->removePasswords((new Serializer)->normalizeEach($this->app['session']->storage()));
	}

	// Add authenticated user data to the request
	protected function resolveAuthenticatedUser(Request $request)
	{
		if (! isset($this->app['auth'])) return;
		if (! ($user = $this->app['auth']->user())) return;

		if ($user instanceof Model) {
			// retrieve attributes in this awkward way to make sure we don't trigger exceptions with Eloquent strict mode on
			$keyName = method_exists($user, 'getAuthIdentifierName') ? $user->getAuthIdentifierName() : $user->getKeyName();
			$user = $user->getAttributes();

			$userId = $user[$keyName] ?? null;
			$userEmail = $user['email'] ?? $userId;
			$userName = $user['name'] ?? null;
		} else {
			$userId = $user->getAuthIdentifier();
			$userEmail = $user->email ?? $userId;
			$userName = $user->name ?? null;
		}

		$request->setAuthenticatedUser($userEmail, $userId, [
			'email' => $userEmail,
			'name'  => $userName
		]);
	}
}
