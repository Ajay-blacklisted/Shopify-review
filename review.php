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

<div class="smart-review_container-fluid">
	<div class="smart-review-row  reviews-list">
		<div class="sr-col-md-12">
			<div class="review-item filter-item-sr">
				<div class="smart-review-row d-flex">
					<div class="sr-col-md-2">
						<div class="review-count">
							<div class="total-count">
								<span class="total"><?php if(is_nan($average)) { echo '0'; }  else { echo $average; }?></span>
								<span class="smart-review smart-star"></span>
							</div>
							<div class="total-message">Based on <?php echo $reviewcount; ?> reviews</div>
						</div>
					</div>
					<div class="sr-col-md-8">
						<div class="rating_widget">
							<ul class="smart_rating_filer">
								<li>
									<div class="smart-rev-widget " data-id="5">
										<div class="smart_star_graph smart-rat-wid">
											<span class="sr-rating-value">5</span>
											<span class="smart-review smart-star"></span>
										</div>
										<div class="smart-pro-bar smart-rat-wid" style="">
											<progress id="sr-status-bar" class="sr-status-bar-5" value="<?php echo $five; ?>" max="<?php echo $reviewcount; ?>"></progress>
										</div>
										<div class="smart-digit smart-rat-wid">
											<span class="rating5"><?php if(empty($five)) { echo '0'; } else { echo $five; } ?></span>
										</div>
									</div>
								</li>
								<li>
									<div class="smart-rev-widget" data-id="4">
										<div class="smart_star_graph smart-rat-wid">
											<span class="sr-rating-value">4</span>
											<span class="smart-review smart-star"></span>
										</div>
										<div class="smart-pro-bar smart-rat-wid" style="">
											<progress id="sr-status-bar" class="sr-status-bar-4" value="<?php echo $four; ?>" max="<?php echo $reviewcount; ?>"></progress>
										</div>
										<div class="smart-digit smart-rat-wid">
											<span class="rating4"><?php if(empty($four)) { echo '0'; } else { echo $four; } ?></span>
										</div>
									</div>
								</li>
								<li>
									<div class="smart-rev-widget" data-id="3">
										<div class="smart_star_graph smart-rat-wid">
											<span class="sr-rating-value">3</span>
											<span class="smart-review smart-star"></span>
										</div>
										<div class="smart-pro-bar smart-rat-wid" style="">
											<progress id="sr-status-bar" class="sr-status-bar-3" value="<?php echo $three; ?>" max="<?php echo $reviewcount; ?>"></progress>
										</div>
										<div class="smart-digit smart-rat-wid">
											<span class="rating3"><?php if(empty($three)) { echo '0'; } else { echo $three; } ?></span>
										</div>
									</div>
								</li>
								<li>
									<div class="smart-rev-widget" data-id="2">
										<div class="smart_star_graph smart-rat-wid">
											<span class="sr-rating-value">2</span>
											<span class="smart-review smart-star"></span>
										</div>
										<div class="smart-pro-bar smart-rat-wid" style="">
											<progress id="sr-status-bar" class="sr-status-bar-2" value="<?php echo $two; ?>" max="<?php echo $reviewcount; ?>"></progress>
										</div>
										<div class="smart-digit smart-rat-wid">
											<span class="rating2"><?php if(empty($two)) { echo '0'; } else { echo $two; } ?></span>
										</div>
									</div>
								</li>
								<li>
									<div class="smart-rev-widget" data-id="1">
										<div class="smart_star_graph smart-rat-wid">
											<span class="sr-rating-value">1</span>
											<span class="smart-review smart-star"></span>
										</div>
										<div class="smart-pro-bar smart-rat-wid" style="">
											<progress id="sr-status-bar" class="sr-status-bar-1" value="<?php echo $one; ?>" max="<?php echo $reviewcount; ?>"></progress>
										</div>
										<div class="smart-digit smart-rat-wid">
											<span class="rating1"><?php if(empty($one)) { echo '0'; } else { echo $one; } ?></span>
										</div>
									</div>
								</li>
								<li>
									<div class="smart-rev-widget all-smart-rev smart-rating" data-id="0" style="display:none;">
										<p>See all reviews </p>
									</div>
								</li>
							</ul>
						</div>
					</div>
					<div class="sr-col-md-2 text-center">
						<button class="smart-btn smart-btn-defult reviewButton">Write a Review</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="smart-review-row  reviews-list smartreview" style="display: none;">
		<div class="sr-col-md-12">
			<div class="review-item reviewsForm">
				<div class="smart-review-row">
					<div class="sr-col-md-12">
						<form method="post" id="reviewForm" enctype="multipart/form-data" action="">
							<div class="sr-form-group">
								<label for="firstName">First Name </label>
								<input type="text" name="firstName" class="smart-form-control" id="firstName" placeholder="Enter Your First Name..">
							</div>

							<div class="sr-form-group">
								<label for="lastName">Last Name </label>
								<input type="text" name="lastName" class="smart-form-control" id="lastName" placeholder="Enter Your Last Name..">
							</div>

							<div class="sr-form-group">
								<label for="email">Email</label>
								<input type="email" name="email" class="smart-form-control" id="email" placeholder="Enter Your email address..">
							</div>

							<div class="sr-form-group">
								<label for="email">Rating Stars:</label>
								<div class="smart-star-rating">
									<span class="smart-star smart-star-0" data-rating="1" id="review1"></span>
									<span class="smart-star smart-star-0" data-rating="2" id="review2"></span>
									<span class="smart-star smart-star-0" data-rating="3" id="review3"></span>
									<span class="smart-star smart-star-0" data-rating="4" id="review4"></span>
									<span class="smart-star smart-star-0" data-rating="5" id="review5"></span>
									<input type="hidden" name="ratings" class="rating-value" value="5" id="rating-value"></div>
								</div>
								<div class="sr-form-group">
									<label for="reviewTitle">Review Title</label>
									<input type="text" name="review_title" class="smart-form-control" id="reviewTitle" placeholder="Give your review a title">
								</div>
								<div class="sr-form-group">
									<label for="reviewFeedback">Your Feedback..</label>
									<textarea class="smart-form-control" name="reviewFeedback" placeholder="Add your comments here.." required="required"></textarea>
								</div>
								<div class="sr-form-group">
									<label for="reviewImage">Upload product image</label>
									<input type="file" class="smart-form-control" name="proudct_image">
								</div>
								<div class="sr-form-group">
									<button class="smart-btn smart-btn-defult smart-submit" id="submitReviewBtn">Submit Review</button>
								</div>

								<input type="hidden" name="productid" value="<?php echo $productid; ?>">
								<input type="hidden" name="store" value="<?php echo $store; ?>">
							</form>
						</div>
						<div class="sr-col-md-12">
							<div class="success-message" style="display: none;">Successfully submit the review</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="smart-review-row  reviews-list reviews">
			<div class="sr-col-md-12">
				<div class="review-item smart-itemcontain">
					<div id="sr-msnry" class="SmartReivewsView">
						<?php 
						if(empty($reviewcount)){
							?>
							<p style="text-align:center">Be the first to post a review</p>
							<?php 
						}
						else{
							foreach ($reviewlists as $key => $value) {								
								?>
								<div class="main-review">
									<div class="review-img">
										<?php 
										if(empty($value['img'])){}
											else{
												?>
												<img src="https://blacklistedagency.com/projects/Shopify-review/uploads/<?php echo $value['img']; ?>">
												<?php
											}
											?>
										</div>
										<div class="review-content">
											<?php
											if(empty($value['title'])){}
												else{
													echo '<h2>'.$value['title'].'</h2>';
												}
												if(empty($value['dsc'])){}
													else{
														echo '<p>'.$value['dsc'].'</p>';
													}
													?>
												</div>
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
											</div>
											<?php 
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="smart-review-row  reviews-list smartreview-pagination" style="display:none">
						<div class="sr-col-md-12">
							<div class="review-item smart-paging" id="smart-paging"></div>
						</div>
					</div>
				</div>
