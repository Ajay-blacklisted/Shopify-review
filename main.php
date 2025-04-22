<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *"); 
header("Content-Security-Policy: Frame-Ancestors https://$_GET[shop] https://admin.shopify.com;");
header("X-Frame-Options: ALLOWALL");
header("X-XSS-Protection: 1; mode=block");

require_once __DIR__ . '/vendor/autoload.php';
// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = new Mysqli($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $_ENV['MYSQL_DB']);
$api_key = $_ENV['SHOPIFY_APIKEY'];
$scopes = $_ENV['SHOPIFY_SCOPES'];
$app_base_url = $_ENV['APP_BASE_URL'];
$api_version = $_ENV['API_VERSION'];

$cst_query = $_GET;
$store = $cst_query['shop'];
$select = $db->query("SELECT * FROM installs WHERE store = '$store'");
$user = $select->fetch_object();
$access_token = $user->access_token;
$store_id =  $user->id;

$select = $db->query("SELECT * FROM review_details WHERE store = '$store'");

$reviewlists = array();
$ratinglists = array();
$total_review = 0;
$enable_review = 0;
$disable_review = 0;
while ($reviewlist = $select->fetch_row()) {
	$total_review++;
	if($reviewlist[9] == 1){
		$enable_review++;
	}
	else{
		$disable_review++;
	}
	$postJson['reviewid']= $reviewlist[0];
	$postJson['customerid']= $reviewlist[2];
	$postJson['productid']= $reviewlist[3];
	$postJson['title']= $reviewlist[5];
	$postJson['dsc']= $reviewlist[6];
	$postJson['rating']=$reviewlist[7];
	$postJson['img']= $reviewlist[8];
	$postJson['enable']= $reviewlist[9];
	$reviewlists[]=$postJson;
}

$reviewlists = array_reverse($reviewlists);

?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="https://sdks.shopifycdn.com/polaris/latest/polaris.css" />
	<link href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" rel="stylesheet"/> 
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/> 
	<link rel="stylesheet" href="<?php echo $app_base_url;?>css/custom.css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"/>
	<link href="<?php echo $app_base_url;?>css/jquery.dataTables.min.css" rel="stylesheet"/>
	<script src="https://unpkg.com/@shopify/app-bridge@3"></script>
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
			<input type="hidden" name="shopname" id="shopname" value="<?php echo $store; ?>" />

			<div class="row">
				<div class="col-md-4">
					<div class="review-content">
						<h3>Total Review</h3>
						<p><?php echo $total_review; ?></p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="review-content">
						<h3>Publish Review</h3>
						<p><?php echo $enable_review; ?></p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="review-content">
						<h3>Unpublish Review</h3>
						<p><?php echo $disable_review; ?></p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<h1 class="recenet_review">Recent Reviews</h1>
				</div>
			</div>

			<?php 
			$i=0;
			foreach ($reviewlists as $key => $value) {	

				$reviewid = $value['reviewid'];
				$i++;
				$reply = $db->query("SELECT * FROM review_reply WHERE review_id = '$value[reviewid]'");
				$review_reply = $reply->fetch_object();
				$reply = $review_reply->reply;
				?>
				<div class="row">
					<div class="col-md-12">
						<div class="recent-review-content <?php if($value['enable'] == '0'){ echo 'unapprove'; } else { echo 'approve'; } ?>">
							<?php 

							if(empty($value['title'])){	}
								else{
									$productid = $value['productid'];
                                    $graphql_query = [
                                        'query' => "query {
                                            product(id: \"gid://shopify/Product/$productid\") {
                                                title
                                                handle
                                            }
                                        }"
                                    ];
                                    
                                    // cURL request
                                    $curl = curl_init();
                                    curl_setopt_array($curl, [
                                        CURLOPT_URL => "https://$store/admin/api/$api_version/graphql.json",
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_POST => true,
                                        CURLOPT_POSTFIELDS => json_encode($graphql_query), // Correctly encode the query only once
                                        CURLOPT_HTTPHEADER => [
                                            "Content-Type: application/json",
                                            "X-Shopify-Access-Token: $access_token"
                                        ],
                                    ]);
                                    
                                    $response = curl_exec($curl);
                                    curl_close($curl);
                                    
                                    // Decode the response
                                    $result = json_decode($response, true);
                                    
                                    // Access title and handle
                                    if (isset($result['data']['product'])) {
                                        $title = $result['data']['product']['title'];
                                        $handle = $result['data']['product']['handle'];
                                    } else {
                                        // Handle error
                                        $error_message = $result['errors'][0]['message'] ?? 'Unknown error';
                                        echo "Error: $error_message";
                                    }
                                    // Access title and handle
                                    $title = $result['data']['product']['title'];
                                    $handle = $result['data']['product']['handle'];


									$customer = $db->query("SELECT * FROM customer_data WHERE id = '$value[customerid]'");
									$user = $customer->fetch_object();
									$first_name = $user->first_name;

									echo ''.$first_name.' about <small class=text-info><a href=https://'.$store.'/products/'.$handle.' target=_blank>'.$title.'</a></small>';
								}

								if($value['rating'] == '5'){
									?>
									<div class="smart-star-rating">
										<span class="smart-star"></span>
										<span class="smart-star"></span>
										<span class="smart-star"></span>
										<span class="smart-star"></span>
										<span class="smart-star"></span>
									</div>	
								<?php }
								else if($value['rating'] == '4'){
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
								else if($value['rating'] == '3'){
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
								else if($value['rating'] == '2'){
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
								else{
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
								echo $value['dsc'];
								if($value['enable'] == '0'){}
									else{
										
										if(!empty($reply))
											echo '<p class=your_reply>Your Reply</p>';
										echo $reply;
									}
									?>

								</div>
							</div>
						</div>

						<input type="checkbox" name="status" class="rStatus" checked="" value="<?php echo $reviewid; ?>" hidden="">
						<div class="btn-group approve_unapprove" tabindex="<?php echo $value['enable']; ?>" style="display: flex;" data-id="<?php echo $reviewid; ?>" data-product-id="<?php echo $productid; ?>">
							<?php 
							if($value['enable'] == '0'){
								?>
								<a is="0" class="btn slider active btn-danger">Unpublish</a>
								<a is="1" class="btn slider btn-default">Publish</a>
								<?php
							}
							else{
								?>
								<a is="0" class="btn slider btn-default">Unpublish</a>
								<a is="1" class="btn slider active btn-success">Publish</a>
								<?php 
								if(!empty($reply)){ ?>
									<button type="button" data-toggle="dropdown" data-review-id="<?php echo $reviewid; ?>" class="dropdown-toggle btn btn-primary btn-small">Reply</button>
									<ul class="dropdown-menu">
										<li><a href="#" data-toggle="<?php echo $reviewid; ?>" class="edit-reply">Edit</a></li>
										<li><a href="#" data-toggle="<?php echo $reviewid; ?>" class="delete-reply">Delete</a></li>
									</ul>
								<?php	}
								else{
									?>
									<button type="button" data-toggle="modal" data-review-id="<?php echo $reviewid; ?>" class=" btn btn-primary btn-small review-reply">Reply</button>
									<?php
								}
								?>
								<?php
							}
							?>	
						</div>
					<?php } ?>
				</div>	
				<div id="myModal" class="modal reply-Reviews-popup">
					<div class="modal-content">
						<div class="modal-header">
							<h3 class="modal-title">Your reply</h3>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">×</span>
							</button>
						</div>
						<div class="modal-body">
							<form method="post" id="commentreview" enctype="multipart/form-data" action="">
								<div class="sr-form-group">
									<label for="reviewFeedback">Your Feedback..</label>
									<textarea class="smart-form-control" name="reviewFeedback" placeholder="Add your comments here.." required="required"></textarea>
								</div>
								<div class="sr-form-group">
									<button class="smart-btn smart-btn-defult smart-submit">Submit Review</button>
								</div>	
								<input type="hidden" name="reviewid" id="reviewid" value="<?php echo $reviewid; ?>">			
							</form>
						</div>
					</div>
				</div>

			</div>
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> 
			<script src="<?php echo $app_base_url;?>js/jquery-3.3.1.js"></script>
			<script type="text/javascript">
				$(document).ready(function() {
					$(".loader").fadeOut(2500);

					$('.slider').click(function(){
						var reviewid =  $(this).parent().attr('data-id');
						var enable = $(this).attr('is');
						var productid =  $(this).parent().attr('data-product-id');
						var store = '<?php echo $store; ?>';
						var values = {
							'reviewid': reviewid,
							'enable': enable,
							'store': store,
							'productId': productid
						};
						var baseUrl = '<?php echo $app_base_url; ?>';
						$.ajax({
							type: "POST",
							url: baseUrl+"publish-review.php",
							data:  values,
						}).done(function (data) {
							window.location.reload();
						});
					});

					$('.review-reply').click(function(){
						var reviewid =  $(this).attr('data-review-id');
						$('#reviewid').val(reviewid);
						var modal = document.getElementById("myModal");
						modal.style.display = "block";
						window.onclick = function(event) {
							if (event.target == modal) {
								modal.style.display = "none";
							}
						}
					});

					$("#commentreview").submit(function (event) {
						event.preventDefault();
						var baseUrl = '<?php echo $app_base_url; ?>';
						$.ajax({
							type: "POST",
							url: baseUrl+"reply_review.php",
							data:  new FormData(this),
							contentType: false,
							cache: false,
							processData:false,
						}).done(function (data) {
							console.log(data);
						});
					});

					$('.edit-reply').click(function(){
						var reviewid =  $(this).attr('data-toggle');
						var values = {
							'reviewid': reviewid
						};
						var baseUrl = '<?php echo $app_base_url; ?>';
						$.ajax({
							type: "POST",
							url: baseUrl+"edit-review.php",
							data:  values,
						}).done(function (data) {							
							$('.modal-body').html(data);
							var modal = document.getElementById("myModal");
							modal.style.display = "block";
							window.onclick = function(event) {
								if (event.target == modal) {
									modal.style.display = "none";
								}
							}
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