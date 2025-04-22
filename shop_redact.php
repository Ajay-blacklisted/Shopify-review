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

if ($verified) {

$delete_charges = "SELECT * from installs WHERE store ='".$domain_shop."'";
$result = $db->query($delete_charges);
$roww = mysqli_fetch_assoc($result);
$access_token = $roww["access_token"];
$delete1 = "DELETE FROM review_details WHERE store = '".$domain_shop."'";
$result1 = $db->query($delete1);
$delete2 = "DELETE FROM customer_data WHERE store = '".$domain_shop."'";
$result2 = $db->query($delete2);
$delete3 = "DELETE FROM trial_used WHERE shop = '".$domain_shop."'";
$result3 = $db->query($delete3);
$delete4 = "DELETE FROM review_reply WHERE store = '".$domain_shop."'";
$result4 = $db->query($delete4);
$delete5 = "DELETE FROM installs WHERE store = '".$domain_shop."'";
$result5 = $db->query($delete5);

return http_response_code(200);
} else {
  http_response_code(401);
}
?>
