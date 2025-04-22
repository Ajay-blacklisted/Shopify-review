<?php
error_reporting(1);
header("Access-Control-Allow-Origin: *"); 
header("Content-Security-Policy: Frame-Ancestors https://$_GET[shop] https://admin.shopify.com;");
header("X-Frame-Options: sameorigin");
header("X-XSS-Protection: 1; mode=block");
require_once __DIR__ . '/vendor/autoload.php';
// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = new Mysqli($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], $_ENV['MYSQL_DB']);
$api_key = $_ENV['SHOPIFY_APIKEY'];
$scopes = $_ENV['SHOPIFY_SCOPES'];
$secret_key = $_ENV['SHOPIFY_SECRET'];
$app_base_url = $_ENV['APP_BASE_URL'];
$api_version = $_ENV['API_VERSION'];
$query = $_GET;
if (!isset($query['code'], $query['hmac'], $query['shop'], $query['state'], $query['timestamp'])) {
    exit;
}
$hmac = $query['hmac'];
unset($query['hmac']);
$params = [];
foreach ($query as $key => $val) {
    $params[] = "$key=$val";
}
asort($params);
$params = implode('&', $params);
$calculated_hmac = hash_hmac('sha256', $params, $secret_key);
$store = $query['shop'];
if ($hmac === $calculated_hmac) {
    // Step 1: Exchange code for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://{$store}/admin/oauth/access_token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    $postData = [
        'client_id' => $api_key,
        'client_secret' => $secret_key,
        'code' => $query['code']
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    
    // Decode response to get access token
    $data = json_decode($response, true);
    $access_token = $data['access_token'];
    // Save the access token to the database
    $db->query("UPDATE installs SET `access_token` = '$access_token' WHERE `store` = '$store'");
    
    $sql2 = "SELECT * FROM trial_used WHERE shop ='$store'";
    $resultt = $db->query($sql2);
    $roww = mysqli_fetch_assoc($resultt);
    $storename = $roww['shop'];
    if ($storename) {
       $trial = "30";
    } else {
        $trial = "30";
    }
     // Setup URLs and GraphQL query

// Return URL for the subscription confirmation
$urls = "https://{$store}/admin/apps/{$api_key}/projects/Shopify-review/basic_plan_selected.php?shop={$store}";

// cURL initialization
$ch = curl_init();

// GraphQL query and variables
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
                        "interval" => "EVERY_30_DAYS" // Correct casing
                    ]
                ]
            ]
        ]
    ]
];

// Shopify Admin API endpoint
curl_setopt($ch, CURLOPT_URL, "https://{$store}/admin/api/2025-01/graphql.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

// Set the POST fields (GraphQL payload)
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));

// Set the headers
$headers = [
    'Content-Type: application/json',
    "X-Shopify-Access-Token: $access_token"
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute the cURL request
$result = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Decode and process the response
    $responseData = json_decode($result, true);

    if (isset($responseData['errors'])) {
        echo "GraphQL Errors:\n";
        print_r($responseData['errors']);
    } elseif (isset($responseData['data']['appSubscriptionCreate']['userErrors']) 
              && !empty($responseData['data']['appSubscriptionCreate']['userErrors'])) {
        //echo "User Errors:\n";
        print_r($responseData['data']['appSubscriptionCreate']['userErrors']);
    } else {
       // echo "Subscription Created Successfully:\n";
        //print_r($responseData['data']['appSubscriptionCreate']);
    }
}

    // Close the cURL session
    curl_close($ch);
    
    // Extract billing details
    if (isset($responseData['data']['appSubscriptionCreate']['userErrors']) && !empty($responseData['data']['appSubscriptionCreate']['userErrors'])) {
        print_r($responseData['data']['appSubscriptionCreate']['userErrors']);
    } else {
        $appSubscription = $responseData['data']['appSubscriptionCreate']['appSubscription'];
        $billingapi = $responseData['data']['appSubscriptionCreate']['confirmationUrl'];
         $billing_Id = $appSubscription['id'];
        $api_client_id = '';
       // $createdd = $appSubscription['createdAt'];
        $actstatus = '';
        
        // Parse the creation date
      //  $created = explode("T", $createdd);
        $billdate = '';
    }

    
    if ($storename) {
    $update = $db->query("UPDATE trial_used SET trial = '0' WHERE shop = '$store'");    
    }else{
    $db->query("INSERT into `trial_used` (shop,trial) VALUES ('".$store."','".$trial."')"); 
    }

    $nonce = $query['state'];

    if ($select = $db->prepare("SELECT id FROM installs WHERE store = ?")) {
        $select->bind_param('s', $store);
        $select->execute();
        $select->bind_result($id);
        $select->fetch();
        $select->close();
        $db->query("UPDATE installs SET access_token = '$access_token', status_activation = '$actstatus' , recurring_id = '$billing_Id', client_id = '$api_client_id', install_date = '$billdate'  WHERE id = '$id'");
        header("Location: {$billingapi}");
    }  
}else{
    echo 'no';
}