<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = new mysqli(
    $_ENV['MYSQL_HOST'],
    $_ENV['MYSQL_USER'],
    $_ENV['MYSQL_PASS'],
    $_ENV['MYSQL_DB']
);

if ($db->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $db->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);
$store = $data['store_name'];
// $parsed_url = parse_url($storeName);
// $store = $parsed_url['host'];
$firstName = $data['review']['author'];
$productId = $data['product_id'];
$email = $data['review']['email'];
$review = $data['review']['title'];
$reviewFeedback = $data['review']['body'];
$rating = $data['review']['rating'];
$today = date("Y-m-d");
$enable = '0';

$select = $db->query("SELECT * FROM installs WHERE store = '$store'");
$store_details = $select->fetch_object();
$storeid = $store_details->id;


$select = $db->query("SELECT * FROM customer_data WHERE store = '$store' AND email	= '$email'");
$customer = $select->fetch_object();
$customerid = $customer->id;

if(empty($customerid)){

	if ($query = $db->prepare('INSERT INTO customer_data SET store_id = ?, store = ?, first_name = ?,  email = ?')) {
		$query->bind_param('ssss', $storeid, $store, $firstName,$email);
		$query->execute();
		$query->close();
	}

	$select = $db->query("SELECT * FROM customer_data WHERE store = '$store' AND email	= '$email'");
	$customer = $select->fetch_object();
	$customerid = $customer->id;

}   

$queryreview = $db->prepare('INSERT INTO review_details SET store_id = ?, customer_id = ?, product_id	=?, store = ?, review_title = ?, review_dsc = ?, rating = ?, enable = ?, date_create = ?');
$queryreview->bind_param('sssssssss', $storeid,$customerid,$productId,$store,$review,$reviewFeedback,$rating,$enable,$today);

if ($queryreview->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to save review."]);
}

$queryreview->close();
?>
