<?php 

require __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Core\Router;

Router::start();

?>