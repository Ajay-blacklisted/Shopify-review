<?php
error_reporting(1);
header("Access-Control-Allow-Origin: *"); 
header("Content-Security-Policy: Frame-Ancestors https://$_GET[shop] https://admin.shopify.com;");
header("X-Frame-Options: ALLOWALL");
header("X-XSS-Protection: 1; mode=block");
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = new Mysqli($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $_ENV['MYSQL_DB']);
$api_key = $_ENV['SHOPIFY_APIKEY'];
$app_base_url = $_ENV['APP_BASE_URL'];
$api_version = $_ENV['API_VERSION'];

//$id = '31';
$id = $_POST['reviewid'];

$html .= '<div class="modal-body"><div class="row"><div></div><div class="col-md-12 text-center"><button data-href='.$id.' class="btn mr-3 btn-success btn-small yesdeleteReview" onclick="myFunction()">Yes</button><button data-href='.$id.' class="btn btn-danger btn-small nodeleteReview" onclick="myFunction1()">No</button></div></div></div>';
echo $html;
?>