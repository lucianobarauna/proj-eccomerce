<?php
session_start(); // Iniciando as sessões no programa
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    $page = new Page();
    $page->setTpl("index");
});

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

//  Users
$app->get('/admin/users', function() {
    User::verifyLogin();

    $users = User::listAll();

    $page = new PageAdmin();

    $page->setTpl("users", array(
        "users"=>$users
    ));
});

$app->get('/admin/users/create', function() {
    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("users-create");
});

$app->get('/admin/users/:iduser/delete', function($iduser) {
    User::verifyLogin();

    $user =  new User();

    $user->get((int)$iduser);

    $user->delete();

    header('Location: /admin/users');
    exit;
});

$app->get('/admin/users/:iduser', function($iduser) {
    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $page = new PageAdmin();

    $page->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});

$app->post('/admin/users/create', function() {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST['inadmin'])) ? 1 : 0; // se o inadmin estiver marcado é 1 se não é 0

    $_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
        "cost"=>12
    ]);

    $user->setData($_POST);

    $user->save();

    header("Location: /admin/users");

    exit;

});

$app->post('/admin/users/:iduser', function($iduser) {
    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST['inadmin'])) ? 1 : 0; // se o inadmin estiver marcado é 1 se não é 0

    $user->get((int)$iduser);

    $user->setData($_POST);

    $user->update();

    header("Location: /admin/users");
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

$app->get("/admin/categories", function(){

    User::verifyLogin();

    $categories = Category::listAll();

    $page = new PageAdmin();

    $page->setTpl("categories", [
        'categories' => $categories
    ]);
});

$app->get("/admin/categories/create", function(){

    User::verifyLogin();

    $categories = Category::listAll();

    $page = new PageAdmin();

    $page->setTpl("categories-create");
});

$app->post("/admin/categories/create", function(){

    User::verifyLogin();

    $category = new Category();

    $category->setData($_POST);

    $category->save();

    header('Location: /admin/categories');
    exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $category->delete();

    header('Location: /admin/categories');
    exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory); // fazendo casting para inteiro.

    $page = new PageAdmin();

    $page->setTpl("categories-update", [
        'category'=>$category->getValues()
    ]);
});

$app->post("/admin/categories/:idcategory", function($idcategory){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory); // fazendo casting para inteiro.

    $category->setData($_POST);

    $category->save();

    header('Location: /admin/categories');
    exit;
});

$app->get("/categories/:idcategory", function($idcategory){

    $category = new Category();

    $category->get((int)$idcategory);

    $page = new Page();

    $page->setTpl("category", [
        "category"=>$category->getValues(),
        "products"=> []
    ]);

});


$app->run();