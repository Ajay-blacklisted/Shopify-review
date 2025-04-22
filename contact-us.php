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

$cst_query = $_GET;
$store = $cst_query['shop'];
$select = $db->query("SELECT * FROM installs WHERE store = '$store'");
$user = $select->fetch_object();
$store = $user->store;
$access_token = $user->access_token;

$ch = curl_init("https://{$store}/admin/api/{$api_version}/shop.json?access_token={$access_token}&fields=id,name,country_code,currency,email");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$shopdata = curl_exec($ch);
curl_close($ch);

$get_shopdetails = json_decode($shopdata, true);
$country_code = $get_shopdetails['shop']['country_code'];
$currency = $get_shopdetails['shop']['currency'];
$email = $get_shopdetails['shop']['email'];
    
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="https://sdks.shopifycdn.com/polaris/latest/polaris.css" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/> 
	<link rel="stylesheet" href="<?php echo $app_base_url;?>css/custom.css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"/>
	<script src="https://unpkg.com/@shopify/app-bridge@2"></script>
	<script>
		var AppBridge = window['app-bridge'];
		var createApp = AppBridge.createApp;
		var app = createApp({
			apiKey: '<?php echo $api_key; ?>',
			shopOrigin: '<?php echo $_GET['shop']; ?>',
		});
		var actions = AppBridge.actions;
		var ResourcePicker = actions.ResourcePicker;
		var Modal = actions.Modal;
		var Button = actions.Button;
		var Toast = actions.Toast;
		var ButtonGroup = actions.ButtonGroup;
		var shopname = '<?php echo $_GET['shop']; ?>';
	</script>
</head>
<body>
	<section class="content-contact-us">
		<div class="container-fluid">
			<div class="row justify-content-center">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title text-center">Contact Us</h4>
						</div>
						<div class="card-body">
							<form method="post" id="query_submit" enctype="multipart/form-data" action="">
								<div class="form-group">
									<label>Please complete the form in as many details as possible so our team can work efficiently on the ticket. Please describe the issue with details and page URL where our developers can check the issue on front end.</label>
								</div>
								<div class="form-group">
									<label>Name</label>
									<input type="type" class="form-control" name="contact_name" value="<?php echo $store; ?>">
								</div>
								<div class="form-group">
									<label>Email</label>
									<input type="type" class="form-control" name="contact_email" value="<?php echo $email; ?>">
								</div>
								<div class="form-group">
									<label for="name">Query</label>
									<textarea name="query" class="form-control"></textarea>

								</div>

								<div class="form-group">
									<button type="submit" class="btn btn-primary" name="send" value="submit">Send</button>
								</div>
							</form>
							<p style=" background: #444; color: #fff; font-size: 13px; padding: 10px; box-sizing: border-box; float: left; letter-spacing: 0.5px; width: 100%; line-height: 24px; "><b><u>Note:</u></b> Our support team works in Indian time zone Mon- Fri 9am to 6pm. We have limited support outside business hours. We resolve over 90% issues in less than 2 hrs during normal business hours and 95% issues within 24 hrs.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<script src="<?php echo $app_base_url;?>js/jquery-3.3.1.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 
	<script type="text/javascript">
		$(document).ready(function() {

			$(".loader").fadeOut(2500);

			$("#query_submit").submit(function (event) {
				event.preventDefault();
				var baseUrl = '<?php echo $app_base_url; ?>';
				$.ajax({
					type: "POST",
					url: baseUrl+"query_submit.php",
					data:  new FormData(this),
					contentType: false,
					cache: false,
					processData:false,
				}).done(function (data) {
					console.log(data);
				});
			});
		});
	</script>   
	<script>
		(function() {
			var loadScript = function(url, callback) {
				var script = document.createElement("script");
				script.type = "text/javascript";
				if (script.readyState) {
					script.onreadystatechange = function() {
						if (script.readyState == "loaded" || script.readyState == "complete") {
							script.onreadystatechange = null;
							callback();
						}
					};
				} else {
					script.onload = function() {
						callback();
					};
				}
				script.src = url;
				document.getElementsByTagName("head")[0].appendChild(script);
			};
			var myAppJavaScript = function($) {
				$('ul.client_sude_menu li a').click(function(e) {
					e.preventDefault();
					$('ul.client_sude_menu li').removeClass('sd_active');
					$(this).parent().addClass('sd_active');
					dataid = $(this).attr('data-id');
					$("#" + dataid).show().siblings().hide();
				});   
			};
			if ((typeof jQuery === 'undefined') || (parseFloat(jQuery.fn.jquery) < 1.7)) {
				loadScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', function() {
					jQuery191 = jQuery.noConflict(true);
					myAppJavaScript(jQuery191);
				});
			} else {
				myAppJavaScript(jQuery);
			} 
		})(); 


	</script>
</body>
</html> 