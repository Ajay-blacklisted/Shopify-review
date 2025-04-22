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

$store = $_POST['store'];
$reviewid = $_POST['reviewid'];
$enable = $_POST['enable'];
$productId = $_POST['productId'];

$select = $db->query("SELECT access_token FROM installs WHERE store = '$store'");
$user = $select->fetch_object();
echo $access_token = $user->access_token;

die();

$update = $db->query("UPDATE review_details SET enable = '$enable' WHERE id = '$reviewid'"); 

$ch = curl_init();

$query = [
    "query" => "mutation {
        productUpdate(input: {
            id: \"gid://shopify/Product/$productId\",
            metafields: [{
                namespace: \"prapp-pub-reviews\",
                key: \"review-data\",
                value: \"{\\\"rating\\\": 5, \\\"comment\\\": \\\"Great product!\\\"}\",
                type: \"json\"
            }]
        }) {
            product {
                id
                metafields(first: 10) {
                    edges {
                        node {
                            namespace
                            key
                            value
                        }
                    }
                }
            }
        }
    }"
];

curl_setopt($ch, CURLOPT_URL, "https://{$store}/admin/api/2025-01/graphql.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));

$headers = [
    'Content-Type: application/json',
    "X-Shopify-Access-Token: $access_token"
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    echo "Response Code: $httpCode\n";
    echo "Response: $result\n";
}

curl_close($ch);


echo '1';

?>