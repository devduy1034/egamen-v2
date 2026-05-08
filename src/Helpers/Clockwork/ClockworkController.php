<?php



namespace LARAVEL\Helpers\Clockwork;

use Clockwork\Clockwork;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Routing\LARAVELRouter;

// Clockwork api and app controller
class ClockworkController extends Controller
{
	// Authantication endpoint
	public function authenticate(Clockwork $clockwork, ClockworkSupport $clockworkSupport, Request $request)
	{
		$this->ensureClockworkIsEnabled($clockworkSupport);

		$token = $clockwork->authenticator()->attempt(
			$request->only(['username', 'password'])
		);

		return new JsonResponse(['token' => $token], $token ? 200 : 403);
	}

	// Metadata retrieving endpoint
	public function getData(Request $request, $id = null, $direction = null, $count = null)
	{
		$clockworkSupport = app('clockwork.support');
		$this->ensureClockworkIsEnabled($clockworkSupport);

		return $clockworkSupport->getData(
			$id,
			$direction,
			$count,
			$request->only(['only', 'except'])
		);
	}

	// Extended metadata retrieving endpoint
	public function getExtendedData(Request $request, $id = null)
	{
		$clockworkSupport = app('clockwork.support');
		$this->ensureClockworkIsEnabled($clockworkSupport);

		return $clockworkSupport->getExtendedData(
			$id,
			$request->only(['only', 'except'])
		);
	}

	// Metadata updating endpoint
	public function updateData(Request $request, $id = null)
	{
		$clockworkSupport = app('clockwork.support');

		$this->ensureClockworkIsEnabled($clockworkSupport);

		return $clockworkSupport->updateData($id, $request->json()->all());
	}

	// App index
	public function webIndex()
	{
		$clockworkSupport = app('clockwork.support');

		$this->ensureClockworkIsEnabled($clockworkSupport);

		return $clockworkSupport->getWebAsset('index.html');
	}

	// App assets serving
	public function webAsset($path)
	{
		$clockworkSupport = app('clockwork.support');
		$this->ensureClockworkIsEnabled($clockworkSupport);

		return $clockworkSupport->getWebAsset($path);
	}

	// App redirect (/clockwork -> /clockwork/app)
	public function webRedirect(Request $request)
	{
		$clockworkSupport = app('clockwork.support');
		$this->ensureClockworkIsEnabled($clockworkSupport);

		return new RedirectResponse('/' . $request->path() . '/app');
	}

	// Ensure Clockwork is still enabled at this point and stop Telescope recording if present
	protected function ensureClockworkIsEnabled()
	{
		$clockworkSupport = app('clockwork.support');
		if (! $clockworkSupport->isEnabled()) {
			view('error.notfound');
			response()->httpCode(404);
		}
	}
}
