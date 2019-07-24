<?php
session_start(); // Iniciando as sessões no programa
require_once("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

// Arquivos de rotas modularizados
require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");


$app->run();