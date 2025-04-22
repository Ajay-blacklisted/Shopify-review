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
$select = $db->query("SELECT * FROM review_details WHERE id = '$id'");
$user = $select->fetch_object();
$customer_id = $user->customer_id;
$product_id = $user->product_id;
$store = $user->store;
$review_title = $user->review_title;
$review_dsc = $user->review_dsc;
$rating = $user->rating;
$product_img = $user->product_img;

$select = $db->query("SELECT * FROM installs WHERE store = '$store'");
$user = $select->fetch_object();
$access_token = $user->access_token;


$select = $db->query("SELECT * FROM customer_data WHERE id = '$customer_id'");
$user = $select->fetch_object();
$email = $user->email;
$first_name = $user->first_name;
$last_name = $user->last_name;

$graphql_query = json_encode([
    'query' => "query {
        product(id: \"gid://shopify/Product/$product_id\") {
            title
            handle
        }
    }"
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$store/admin/api/$api_version/graphql.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $graphql_query);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-Shopify-Access-Token: $access_token"
]);

$response = curl_exec($ch);
curl_close($ch);

$response_data = json_decode($response, true);
$title = $response_data['data']['product']['title'] ?? null;
$handle = $response_data['data']['product']['handle'] ?? null;


if(empty($product_img)){
	$html .= '<div class="row"><div class="col-md-3"><label>Proudct Title :</label></div><div class="col-md-9"><a href="https://'.$store.'/products/'.$handle.'" target="_blank">'.$title.'</a></div></div><div class="row"><div class="col-md-3"><label>Reviewer Name :</label></div><div class="col-md-9">'.$first_name.' '.$last_name.'</div></div><div class="row"><div class="col-md-3"><label>Reviewer Email :</label></div><div class="col-md-9">'.$email.'</div></div><div class="row"><div class="col-md-3"><label>Title:</label></div><div class="col-md-9">'.$review_title.'</div></div><div class="row"><div class="col-md-3"><label>Description :</label></div><div class="col-md-9">'.$review_dsc.'</div></div>';
}
else{
	$html .= '<div class="row"><div class="col-md-3"><label>Proudct Title :</label></div><div class="col-md-9"><a href="https://'.$store.'/products/'.$handle.'" target="_blank">'.$title.'</a></div></div><div class="row"><div class="col-md-3"><label>Reviewer Name :</label></div><div class="col-md-9">'.$first_name.' '.$last_name.'</div></div><div class="row"><div class="col-md-3"><label>Reviewer Email :</label></div><div class="col-md-9">'.$email.'</div></div><div class="row"><div class="col-md-3"><label>Title:</label></div><div class="col-md-9">'.$review_title.'</div></div><div class="row"><div class="col-md-3"><label>Description :</label></div><div class="col-md-9">'.$review_dsc.'</div></div><div class="col-md-3"><label>Review Image</label><div class="col-md-9"><img src="https://blacklistedagency.com/projects/Shopify-review/uploads/'.$product_img.'"></div></div>';
}
echo $html;
?>