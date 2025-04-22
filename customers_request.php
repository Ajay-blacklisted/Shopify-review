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
echo $hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$webhook_payload = file_get_contents('php://input');
$verified = verify_webhook($webhook_payload, $hmac_header);
$webhook_payload_obj = json_decode($webhook_payload, true);
$shop_id = $webhook_payload_obj['shop_id'];
$domain_shop = $webhook_payload_obj['shop_domain'];
$cID = $webhook_payload_obj['customer']['id'];
$eEmail = $webhook_payload_obj['customer']['email'];
$getdetail = "SELECT * from installs WHERE store ='".$domain_shop."'";
$result = $db->query($getdetail);
$row = mysqli_fetch_assoc($result);
$access_token = $row["access_token"];
$ch = curl_init("https://{$store}/admin/api/{$api_version}/shop.json?access_token={$access_token}&fields=id,name,country_code,currency,email");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$shopdata = curl_exec($ch);
curl_close($ch);

$get_shopdetails = json_decode($shopdata, true);
$country_code = $get_shopdetails['shop']['country_code'];
$currency = $get_shopdetails['shop']['currency'];
$storeemail = $get_shopdetails['shop']['email'];
$storename = $get_shopdetails['shop']['name'];
$getcustomer = "SELECT * from store_information WHERE store ='".$domain_shop."'";
$result1 = $db->query($getcustomer);
$row1 = mysqli_fetch_assoc($result1);
$customer_email = $row1["customer_email"];
$domainid = $row1["domainid"];
if (!empty($row1)) {
	$htmldata = '<p>Hello '.$storename.',</p>
			<br><br>
			<p>Please find the data of customer (<i>Email - '.$customer_email.'</i>) that you had requested.</p>
			<br>
			<p><b>Customer Email:</b> '.$customer_email.'</p>
			<p><b>Customer Domain Id No:</b> '.$domainid.'</p>
			<br><br>
			<p>We have use only mentioned data in our Salonist Shopify app.</p>
			<br>
			<p>Thanks & Regards</p>
			<p>Review TEAM.</p>';

			// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$to = $customer_email;
$subject = "Review - Customer Data Request";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: Review <blacklisted.web14@gmail.com>' . "\r\n";

mail($to,$subject,$htmldata,$headers);
return http_response_code(200);
}
else{
	$htmldata = '<p>Hello '.$storename.',</p>
				<br><br>
				<p>The data of the customer (<i>Email - '.$customer_email.'</i>) that you had requested, is not founded in our system.</p>
				<br>
				<br>
				<p>Thanks & Regards</p>
				<p>Review TEAM.</p>';
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$to = $customer_email;
$subject = "Review - Customer Data Request";

// More headers
$headers .= 'From: Review <blacklisted.web14@gmail.com>' . "\r\n";

mail($to,$subject,$htmldata,$headers);
return http_response_code(200);
}
?>