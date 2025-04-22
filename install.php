<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *"); 
header("Content-Security-Policy: Frame-Ancestors https://$_GET[shop] https://admin.shopify.com;");
header("X-Frame-Options: ALLOWALL");
header("X-XSS-Protection: 1; mode=block");
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = new Mysqli($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $_ENV['MYSQL_DB']);
$store = $_GET['shop'];
$nonce = bin2hex(random_bytes(10));
$api_key = $_ENV['SHOPIFY_APIKEY'];
$scopes = $_ENV['SHOPIFY_SCOPES'];
$app_base_url = $_ENV['APP_BASE_URL'];
$api_version = $_ENV['API_VERSION'];
$redirect_uri = urlencode($_ENV['SHOPIFY_REDIRECT_URI']);
$sql = "SELECT * FROM installs WHERE store = '$store'";
$result = $db->query($sql);
if (!$result) {
    exit;
}

if ($row = $result->fetch_assoc()) {
    $lastid = $row['id'];
    $storename = $row['store'];
    $recurring_id = $row['recurring_id'];
    $client_id = $row['client_id'];
} else {
    if ($query = $db->prepare('INSERT INTO installs SET store = ?, nonce = ?, access_token = ""')) {
        $query->bind_param('ss', $store, $nonce);
        $query->execute();
        $query->close();
        $url = "https://{$store}/admin/oauth/authorize?client_id={$api_key}&scope={$scopes}&redirect_uri={$redirect_uri}&state={$nonce}";
        header("Location: {$url}");
    }
}

if (is_null($client_id) && is_null($recurring_id)) {
    $delete = $db->prepare("DELETE FROM installs WHERE store = ?");
    $delete->bind_param('s', $storename);
    $delete->execute();
}

if (($storename != '') && ($recurring_id !== NULL)) {
    $select2 = $db->query("SELECT access_token, recurring_id FROM installs WHERE store = '$store'");
    $user2 = $select2->fetch_object();
    $access_token = $user2->access_token;
    $billing_Id = $user2->recurring_id;
    $ch = curl_init("https://{$store}/admin/api/{$api_version}/shop.json?access_token={$access_token}&fields=id,name,country_code,currency");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $shopdata = curl_exec($ch);
    curl_close($ch);
    $get_shopdetails = json_decode($shopdata, true);
    $country_code = $get_shopdetails['shop']['country_code'];
    $currency = $get_shopdetails['shop']['currency'];
    if (empty($billing_Id)) {
        $rurls = "https://{$store}/admin/apps/{$api_key}/projects/Shopify-review/charge_declined.php?shop={$store}";
    }
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
        "X-Shopify-Access-Token: $access_token"
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $response_data = json_decode($response, true);
    $bill_status = $response_data['data']['recurringApplicationCharge']['status'] ?? null;

   if ($bill_status === "ACCEPTED" && empty($status_activation)) {
        $sql2 = "SELECT * FROM trial_used WHERE shop ='$store'";
        $resultt = $db->query($sql2);
        $roww = mysqli_fetch_assoc($resultt);
        $storename = $roww['shop'];
        if ($storename) {
            $trial = "7";
        } else {
            $trial = "7";
        }
        
    $urls = "https://{$store}/admin/apps/{$api_key}/projects/Shopify-review/basic_plan_selected.php?shop={$store}";
    curl_setopt($ch, CURLOPT_URL, "https://{$store}/admin/api/2025-01/graphql.json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
   $query = [
    "query" => "mutation AppSubscriptionCreate(
                    \$name: String!, 
                    \$lineItems: [AppSubscriptionLineItemInput!]!, 
                    \$returnUrl: URL!,
                    \$test: Boolean) { 
                        appSubscriptionCreate(
                            name: \$name, 
                            returnUrl: \$returnUrl, 
                            lineItems: \$lineItems,
                            test: \$test
                        ) { 
                            userErrors { 
                                field 
                                message 
                            } 
                            confirmationUrl 
                            appSubscription { 
                                id 
                            } 
                        } 
                    }",
    "variables" => [
        "name" => "Basic Plan",
        "returnUrl" => $urls,
         "test" => true,
        "lineItems" => [
            [
                "plan" => [
                    "appRecurringPricingDetails" => [
                        "price" => [
                            "amount" => 19.0,
                            "currencyCode" => "USD"
                        ],
                        "interval" => "EVERY_30_DAYS"
                    ]
                ]
            ]
        ]
    ]
];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
    
    $headers = [
        'Content-Type: application/json',
        "X-Shopify-Access-Token: $access_token"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
      
        if (curl_errno($ch)) {
           echo 'cURL Error: ' . curl_error($ch);
        } else {
            $responseData = json_decode($result, true);    
            if (isset($responseData['errors'])) {
                echo "GraphQL Errors:\n";
                print_r($responseData['errors']);
            } elseif (isset($responseData['data']['appSubscriptionCreate']['userErrors']) 
                      && !empty($responseData['data']['appSubscriptionCreate']['userErrors'])) {
                print_r($responseData['data']['appSubscriptionCreate']['userErrors']);
            } else {
            $appSubscription = $responseData['data']['appSubscriptionCreate']['appSubscription'];
            $billingapi = $responseData['data']['appSubscriptionCreate']['confirmationUrl'];
            $billing_Id = $appSubscription['id'];
            }
            $db->query("UPDATE installs SET app_enabled = 'disable', recurring_id = '$billing_Id' WHERE store = '$store'");
        }
    } else {
        if (isset($_GET['shop']) && isset($api_key)) {
            $shop = filter_var($_GET['shop'], FILTER_SANITIZE_URL);
            if (filter_var('https://' . $shop, FILTER_VALIDATE_URL)) {
                echo '<script type="text/javascript">window.top.location.href = "https://' . $_GET['shop'] . '/admin/apps/' . $api_key . '/projects/Shopify-review/main.php?shop=' . $_GET['shop'] . '"; </script>';
                //    $url = "https://$shop/admin/apps/$api_key/projects/Shopify-review/main.php?shop=$shop";
                  // header("Location: $url");
                } else {
                    die("Invalid shop parameter.");
                }
            } else {
                die("Required parameters are missing.");
            }
    }
}
?>