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
$store = $_GET['shop'];
$nonce = bin2hex(random_bytes(10)); // 20-character random string
$api_key = $_ENV['SHOPIFY_APIKEY'];
$app_base_url = $_ENV['APP_BASE_URL'];
$api_version = $_ENV['API_VERSION'];
$redirect_uri = urlencode($_ENV['SHOPIFY_REDIRECT_URI']);

$cst_query = $_GET; 
$store = $cst_query['shop'];

$select2 = $db->query("SELECT * FROM installs WHERE store = '$store'");
$user2 = $select2->fetch_object();
$access_token2 = $user2->access_token;
$billing_Id = $user2->recurring_id;

// GraphQL query to get billing status
$graphql_query = json_encode([
    'query' => "query {
        recurringApplicationCharge(id: \"$billing_Id\") {
            status
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
    "X-Shopify-Access-Token: $access_token2"
]);

$response = curl_exec($ch);
curl_close($ch);

$response_data = json_decode($response, true);
$bill_status = $response_data['data']['recurringApplicationCharge']['status'] ?? null;

if ($bill_status === "ACCEPTED" && empty($status_activation)) {
    // GraphQL mutation to activate billing
    $graphql_mutation = json_encode([
        'query' => "mutation {
            activateRecurringApplicationCharge(id: \"$billing_Id\") {
                recurringApplicationCharge {
                    id
                    status
                    billingOn
                }
            }
        }"
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://$store/admin/api/$api_version/graphql.json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $graphql_mutation);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $access_token2"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);
    $act_status = $response_data['data']['activateRecurringApplicationCharge']['recurringApplicationCharge']['status'] ?? null;
    $billing_on = $response_data['data']['activateRecurringApplicationCharge']['recurringApplicationCharge']['billingOn'] ?? null;

    if ($act_status === "ACTIVE") {
        $db->query("UPDATE installs SET status_activation = '$act_status',bill_on_date = '$billing_on'' WHERE store = '$store'");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="https://sdks.shopifycdn.com/polaris/latest/polaris.css" />
<style>
	.hiddenclass{display:none;}
	div#app ._1Llag{background:#06062b99!important;}
	#editproduct .hideinter .inter_size_chart_ed {display:none;}
</style>
</head>
<body>
	<div style="--top-bar-background:#00848e; --top-bar-color:#f9fafb; --top-bar-background-lighter:#1d9ba4; width: 70%;margin: 100px auto;">
		<div class="Polaris-Card">
			<div class="Polaris-CalloutCard__Container">
				<div class="Polaris-Card__Section">
					<div class="Polaris-CalloutCard">
						<div class="Polaris-CalloutCard__Content">
							<div class="Polaris-CalloutCard__Title">
								<h2 class="Polaris-Heading">Congratulations!!</h2>
							</div>
							<div class="Polaris-TextContainer">
								<p>You have successfully subscribed to our Basic Plan.</p>
							</div>
							<div class="Polaris-CalloutCard__Buttons"><button class="Polaris-Button" onclick="window.top.location.href='https://<?php echo $_GET['shop'];?>/admin/apps/<?php echo $api_key; ?>/projects/Shopify-review/main.php?shop=<?php echo $_GET['shop'];?>'" data-polaris-unstyled="true"><span class="Polaris-Button__Content"><span class="Polaris-Button__Text">BACK TO HOME</span></span></button></div>
						</div><img src="https://cdn.shopify.com/s/assets/admin/checkout/settings-customizecart-705f57c725ac05be5a34ec20c05b94298cb8afd10aac7bd9c7ad02030f48cfa0.svg" alt="" class="Polaris-CalloutCard__Image">
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
