<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//  Admin
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

$app->get('/admin/logout', function() {
   User::logout();
   header("Location: /admin/login");
   exit;
});


$app->get("/admin/forgot", function(){
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){

    $user = User::getForgot($_POST["email"]);

    header("Location: /admin/forgot/sent");
    exit;
});

$app->get("/admin/forgot/sent", function(){
    // Aula 106 - Muito desatualizada não completei ela.
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot-sent");
});


?>