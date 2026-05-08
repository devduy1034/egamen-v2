<?php
if (PHP_VERSION_ID < 80200 ) {
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
    }
    $err = 'Source dropped support for PHP <8.2 and you are running '.PHP_VERSION.', please upgrade PHP'.PHP_EOL;

}
require_once __DIR__ . '/composer/autoload_real.php';

return ComposerAutoloaderInit84d69aeae6a307ef68c66c78ef89b954::getLoader();
?>