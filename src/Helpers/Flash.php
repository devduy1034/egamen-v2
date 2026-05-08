<?php



namespace LARAVEL\Helpers;

use LARAVEL\Core\Singleton;

class Flash
{

	use Singleton;
	function __construct()
	{
		$this->init();
	}

	function init()
	{
		if ((function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE) || !session_id()) {
			session_start();
		}
	}

	public function set($key, $value)
	{
		if (!empty($key) && !empty($value)) {
			$_SESSION['flash'][$key] = $value;
		}
	}

	public function get($key)
	{
		$data = (!empty($_SESSION['flash'][$key])) ? $_SESSION['flash'][$key] : null;
		unset($_SESSION['flash'][$key]);
		return $data;
	}

	public function has($key)
	{
		if (!empty($_SESSION['flash'][$key])) {
			return true;
		} else {
			return false;
		}
	}

	public function getMessages($type = '')
	{
		$str = '';

		if (!empty($type)) {
			$message = $this->get('message');

			if (!empty($message)) {
				$result = json_decode(base64_decode($message), true);
				if (!empty($result)) {
					if (!empty($result['status'])) {
						if ($result['status'] == 'danger') {
							$class = 'danger';
						}
					}

					if (!empty($result['messages'])) {
						$str = $this->messagesHtml($result['messages'], $class, $type);
					}
				}
			}
		}

		return $str;
	}

	private function messagesHtml($messages = array(), $class = '', $type = '')
	{
		$str = '';

		if (!empty($messages) && !empty($class) && !empty($type)) {
			if ($type == 'admin') {
				$str .= '<div class="bg-gradient-mess mb-4 card bg-gradient-' . $class . '">';
				$str .= '<div class="card-header">';
				$str .= '<h3 class="card-title">Thông báo</h3>';
				$str .= '<div class="card-tools">';
				$str .= '<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapsemess" aria-expanded="false" aria-controls="collapsemess"><i class="fas fa-minus"></i></button>';
				$str .= '</div>';
				$str .= '</div>';
				$str .= '<div class="collapse show" id="collapsemess"><div class="card-body">';
				foreach ($messages as $v) {
					$str .= '<p class="mb-1">- ' . $v . '</p>';
				}
				$str .= '</div></div>';
				$str .= '</div>';
			} else if ($type == 'frontend') {
				$str .= '<div class="alert alert-' . $class . '">';
				foreach ($messages as $v) {
					$str .= '<p class="mb-1">- ' . $v . '</p>';
				}
				$str .= '</div>';
			}
		}

		return $str;
	}
}
