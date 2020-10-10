<?php

set_include_path("./src");
session_name("Metardata");
session_start();

use Metardata\App\Controllers\FrontController;
use Metardata\Framework\Http\Request;
use Metardata\Framework\Http\Response;

require_once("vendor/autoload.php");
require_once("config.php");
require_once("/users/21500894/private/smtp.php");

$request = new Request($_GET, $_POST, $_SERVER, $_FILES, $_SESSION);
$response = new Response();
$front_controller = new FrontController($request, $response);
$front_controller->execute();

?>
