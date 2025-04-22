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
$secret_key = $_ENV['SHOPIFY_SECRET'];
function verify_webhook($webhook_payload, $hmac_header)
{
  $calculated_hmac = base64_encode(hash_hmac('sha256', $webhook_payload, $secret_key, true));
  return hash_equals($hmac_header, $calculated_hmac);
}
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$webhook_payload = file_get_contents('php://input');
$verified = verify_webhook($webhook_payload, $hmac_header);
$webhook_payload_obj = json_decode($webhook_payload, true);
$shop_id = $webhook_payload_obj['shop_id'];
$domain_shop = $webhook_payload_obj['shop_domain'];
//$customer_id = $webhook_payload_obj['customer']['id'];
$delete = "DELETE FROM store_information WHERE store = '".$domain_shop."'";
$result = $db->query($delete);
file_put_contents("cusredact.txt",$delete);
return http_response_code(200);
?>