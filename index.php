<?php 

// session_start(); // iniciando a sessão
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    $page = new Page();
    $page->setTpl("index");
});

$app->get('/admin', function() {
    // Vefica se o usuário está logado.
    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("index");
});

$app->get('/admin/login', function() {
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("login");
});

$app->post('/admin/login', function() {
    // Login do usuário
    User::login($_POST["login"], $_POST["password"]);
    // Redirecionando o usuário
    header("Location: /admin");
    exit;
});