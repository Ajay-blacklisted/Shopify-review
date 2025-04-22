<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *"); 
header("Content-Security-Policy: Frame-Ancestors $_POST[storename] https://admin.shopify.com;");
header("X-Frame-Options: ALLOWALL");
header("X-XSS-Protection: 1; mode=block");
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = new Mysqli($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $_ENV['MYSQL_DB']);
$api_key = $_ENV['SHOPIFY_APIKEY'];
$app_base_url = $_ENV['APP_BASE_URL'];
$api_version = $_ENV['API_VERSION'];

$store = $_POST['storename'];
$productid = $_POST['productid'];
$star_size = $_POST['star_size'];
$star_color = $_POST['star_color'];
$select = $db->query("SELECT access_token FROM installs WHERE store = '$store'");
$user = $select->fetch_object();
$access_token = $user->access_token;
$select = $db->query("SELECT * FROM review_details WHERE store = '$store' AND product_id = '$productid' AND enable = '1'");

$reviewlists = array();
$ratinglists = array();
$average = 0;
$reviewcount = 0;
while ($reviewlist = $select->fetch_row()) {
	$postJson['title']= $reviewlist[5];
	$postJson['dsc']= $reviewlist[6];
	$postJson['rating']=$reviewlist[7];
	$postJson['img']= $reviewlist[8];
	$reviewlists[]=$postJson;
}

foreach ($reviewlists as $key => $value) {
	$ratinglists[]=$value['rating'];
}

if(!empty($ratinglists)){
    $ratinglist = array_count_values($ratinglists);
    $five = $ratinglist['5'];
    $four = $ratinglist[4] ?? 0;
    $three = $ratinglist[3] ?? 0;
    $two=$ratinglist[2] ?? 0;
    $one=$ratinglist[1] ?? 0;
    
    $reviewcount = count($reviewlists);
    $average = (($five*5)+($four*4)+($three*3)+($two*2)+($one*1))/$reviewcount; 
}
?>

<style>
.smart-star, .smart-star-0 {
    color: <?php echo $star_color; ?>;
}
.smart-star:before {content: "\f005"; font: normal normal normal <?php echo $star_size; ?>px/1 FontAwesome; }
.smart-star-0:before {content: "\f006";font: normal normal normal <?php echo $star_size; ?>px/1 FontAwesome;}
progress::-moz-progress-bar {background: <?php echo $star_color; ?>;}
progress::-webkit-progress-value {background: <?php echo $star_color; ?>;}
progress {background: <?php echo $star_color; ?>;}
</style>
<link href='https://blacklistedagency.com/projects/Shopify-review/css/custom.css' rel='stylesheet'>
<?php 
if($average == '5'){
	?>
	<div class="smart-star-rating">
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star"></span>
	</div>	
<?php }
else if($average == '4'){
	?>
	<div class="smart-star-rating">
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star-0"></span>
	</div>	
	<?php
} 
else if($average == '3'){
	?>
	<div class="smart-star-rating">
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
	</div>	
	<?php
} 	
else if($average == '2'){
	?>
	<div class="smart-star-rating">
		<span class="smart-star"></span>
		<span class="smart-star"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
	</div>	
	<?php
} 
else if($average == '1'){
	?>
	<div class="smart-star-rating">
		<span class="smart-star"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
	</div>	
	<?php
} 
else{
	?>
	<div class="smart-star-rating">
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
		<span class="smart-star-0"></span>
	</div>	
	<?php
} ?>