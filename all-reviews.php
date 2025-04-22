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
$access_token = $user->access_token;
$store_id =  $user->id;

$select = $db->query("SELECT * FROM review_details WHERE store = '$store'");

$reviewlists = array();
$ratinglists = array();
while ($reviewlist = $select->fetch_row()) {
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
	<script src="https://unpkg.com/@shopify/app-bridge@2"></script>
	<script>
		var AppBridge = window['app-bridge'];
		var createApp = AppBridge.createApp;
		var app = createApp({
			apiKey: '<?php echo$$api_key; ?>',
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
	<div class="client_section hiddenclass">
		<div class="Polaris-SkeletonPage__Page custom-tab-css" role="status" aria-label="Page loading">
			<input type="hidden" name="shopname" id="shopname" value="<?php echo $store; ?>" />
		</div>	
		<div class="backend-card">
			<div class="backend-reviews_table">
				<table class="table reviews_table" id="reviews_table" role="grid" aria-describedby="DataTables_Table_0_info" style="width: 1033px;">
					<thead>
						<tr role="row">
							<th class="reviews-th" rowspan="1" colspan="1" style="width: 65px;">Sr. No</th>
							<th class="reviews-th" rowspan="1" colspan="1" style="width: 120px;">Rating</th>
							<th class="reviews-th" rowspan="1" colspan="1" style="width: 105px;">Review</th>
							<th class="reviews-th" rowspan="1" colspan="1" style="width: 150px;">Customer Details</th>
							<th class="reviews-th" rowspan="1" colspan="1" style="width: 55px;">Publish</th>
							<th class="reviews-th" rowspan="1" colspan="1" style="width: 140px;">Action</th>
						</tr>
					</thead>
					<tbody class="reviews_table-sortable">
						<?php 
						$i=0;
						foreach ($reviewlists as $key => $value) {	

							$reviewid = $value['reviewid'];
							$i++;							
							?>
							<tr role="row">
								<td><?php echo $i; ?></td>
								<td>
									<?php 
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
									} ?>
								</td>
								<?php
								if(empty($value['title'])){}
									else{

										$productid = $value['productid'];

										$clienttheme1 = new Client();
										$shopifyresponse = $clienttheme1->request('GET','https://'.$store.'/admin/api/'.$api_version.'/products/'.$productid.'.json', [
											'headers' => ['X-Shopify-Access-Token' => $access_token]
										]);
										$getProductDatashopify = json_decode($shopifyresponse->getBody()->getContents(), true);
										$title = $getProductDatashopify['product']['title'];
										$handle = $getProductDatashopify['product']['handle'];

										echo '<td>'.$value['title'].'<br><small class=text-info><a href=https://'.$store.'/products/'.$handle.' target=_blank>'.$title.'</a></small></td>';
									}
									?>
									<td>
										<?php 
										$customer = $db->query("SELECT * FROM customer_data WHERE id = '$value[customerid]'");
										$user = $customer->fetch_object();
										$first_name = $user->first_name;
										echo $first_name;
										?>
									</td>
									<td>
										<input type="checkbox" name="status" class="rStatus" checked="" value="<?php echo $reviewid; ?>" hidden="">
										<div class="btn-group" tabindex="<?php echo $value['enable']; ?>" style="display: flex;" data-id="<?php echo $reviewid; ?>">
											<?php 
											if($value['enable'] == '0'){
												?>
												<a is="0" class="btn slider active btn-danger">No</a>
												<a is="1" class="btn slider btn-default">Yes</a>
												<?php
											}
											else{
												?>
												<a is="0" class="btn slider btn-default">No</a>
												<a is="1" class="btn slider active btn-success">Yes</a>
												<?php
											}
											?>											
										</div>
									</td>
									<td>
										<div class="btn-group" role="group">
											<button  data-href="<?php echo $reviewid; ?>" class="btn btn-sm viewReviews" title="View"><i class="fa fa-eye" aria-hidden="true"></i></button>
											<button data-href="<?php echo $reviewid; ?>" class="btn btn-sm deleteReview" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>
										</div>
									</td>
								</tr>
							<?php 	}	?>
						</tbody>
					</table>
				</div>
			</div>	
			<div id="myModal" class="modal View-Reviews-popup">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title">View Reviews</h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>	

			<div id="myModal1" class="modal View-Reviews-popup">
				<div class="modal-content" style="max-width: 400px">
					<div class="modal-header">
						<h3 class="modal-title">Delete Reviews</h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>	
		</div>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script src="<?php echo $app_base_url;?>js/jquery-3.3.1.js"></script>
	<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 
	<script type="text/javascript">
		$(document).ready(function() {
			var table = $('#reviews_table').DataTable({
			}); 
			$(".loader").fadeOut(2500);
			$('.slider').click(function(){
				var reviewid =  $(this).parent().attr('data-id');
				var enable = $(this).attr('is');
				var store = '<?php echo $store; ?>';
				var values = {
					'reviewid': reviewid,
					'enable': enable,
					'store': store
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

			$('.deleteReview').click(function(){
				var reviewid =  $(this).attr('data-href');
				var values = {
					'reviewid': reviewid
				};
				var baseUrl = '<?php echo $app_base_url; ?>';
				$.ajax({
					type: "POST",
					url: baseUrl+"delete_review.php",
					data:  values,
				}).done(function (data) {
					$('.modal-body').html(data);
					var modal = document.getElementById("myModal1");
					modal.style.display = "block";
					$('.close').click(function(){
						modal.style.display = "none";
					});
				});
			});	

			$('.viewReviews').click(function(){
				var reviewid =  $(this).attr('data-href');
				var values = {
					'reviewid': reviewid
				};
				var baseUrl = '<?php echo $app_base_url; ?>';
				$.ajax({
					type: "POST",
					url: baseUrl+"view-review.php",
					data:  values,
				}).done(function (data) {
					$('.modal-body').html(data);
					var modal = document.getElementById("myModal");
					modal.style.display = "block";
					$('.close').click(function(){
						modal.style.display = "none";
					});
				});
			});	

		});

		function myFunction() {		
			var reviewid =  $(this).attr('data-href');
			var values = {
				'reviewid': reviewid
			};
			var baseUrl = '<?php echo $app_base_url; ?>';
			$.ajax({
				type: "POST",
				url: baseUrl+"delete-review.php",
				data:  values,
			}).done(function (data) {
				var modal = document.getElementById("myModal1");
				modal.style.display = "none";
			});
		}
		function myFunction1() {		
			var modal = document.getElementById("myModal1");
			modal.style.display = "none";
		}
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