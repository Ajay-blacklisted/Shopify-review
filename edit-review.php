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

//$id = '30';
$id = $_POST['reviewid'];
$select = $db->query("SELECT * FROM review_reply WHERE review_id = '$id'");
$reply = $select->fetch_object();
$admin_reply = $reply->reply;

$html .= '<form method="post" id="commentreview" enctype="multipart/form-data" action="">
<div class="sr-form-group">
<label for="reviewFeedback">Your Feedback..</label>
<textarea class="smart-form-control" name="reviewFeedback" placeholder="Add your comments here.." required="required">'.$admin_reply.'</textarea>
</div>
<div class="sr-form-group">
<button class="smart-btn smart-btn-defult smart-submit">Submit Review</button>
</div>	
<input type="hidden" name="reviewid" id="reviewid" value="'.$id.'">			
</form>';
echo $html;
?>