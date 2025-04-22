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

?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="https://sdks.shopifycdn.com/polaris/latest/polaris.css" />
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
	<div class="client_section hiddenclass" >
		<div class="Polaris-SkeletonPage__Page custom-tab-css" role="status" aria-label="Page loading">
			<div class="contentdata">
				<h1 class="content-head">Smart Reviews App Intructions</h1>
				<div class="content-center-box">
					<p style="font-size: 18px;margin-top: 15px;"><strong>Please follow the following steps to setup and configure the smart reviews App</strong></p>
					<h2 class="head-two-box">Add reviews to your product pages</h2>
					<p>Copy the following code to snippet to your clipboard:</p>
					<input type="text" class="content-tag" value='<div class="smart_review"></div>' disabled="">
					<p style="background: #bbeaf9;padding: 15px;">Paste the snippet in your <strong>Product template</strong> file where you want your reviews to appear. Once added save the theme.</p>
				</div>
			</div>
		</div>	
	</div>
	<style>
    /*instructions */
    .contentdata {
      background: white !important;
      box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 12px -2px, rgba(0, 0, 0, 0.3) 0px 3px 7px -3px !important;
      border-radius: 5px !important;
      max-width: 700px;
      margin:25px auto;
      font-family: calibri;
    }
    .contentdata .content-head {
      border-bottom:solid 2px #eaeaeb;
      text-transform: uppercase;
      text-align: center;
      margin: 0;padding: 18px 0;border-radius: 5px 5px 0px 0;
      font-size: 25px;
    }
    .contentdata .content-center-box{padding:0 20px 30px;font-size: 17px;}
    .contentdata .head-two-box{color: #3b787c;margin-bottom: 15px;font-size: 18px;}
    
     .contentdata .content-center-box input.content-tag{
     	width: 100%;
			padding: 12px;
			font-size: 17px;
			margin: 10px 0 20px;
			background: #eeeded;
			border: 1px solid #aaa8a8;
			border-radius: 5px;
     }

    @media(max-width: 1024px){
     .contentdata  .content-center-box input.content-tag {
      width: 100%;
    }

  </style>
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