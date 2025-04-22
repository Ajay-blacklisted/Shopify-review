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
$json_str = file_get_contents('php://input');
file_put_contents("uninstall.txt",$json_str);
$json_obj = json_decode($json_str,true);
$domain_name = $json_obj['myshopify_domain'];
$user_email = $json_obj['email'];
$delete = "Delete FROM installs WHERE `store`='$domain_name'";
$db->query($delete);
$delete1 = "Delete FROM store_information WHERE `store`='$domain_name'";
$db->query($delete1);
$delete2 = "Delete FROM trial_used WHERE `store`='$domain_name'";
$db->query($delete2);

return http_response_code(200);die;  
?>