<?php

/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - Order Placed Page
 * ----------------------------------------------------------------
 * Routed to from iOrder after a successful order
 * Requires:    - Loyalty Cardnumber
 *              - Basket Spend
 */

/**
 * ----------------------------------------------------------------
 * Model For POST
 * ----------------------------------------------------------------
 * $_POST['request']    -> ['bundleIdentifier']
 *                      -> ['version']
 *                      -> ['platform']
 *                      -> ['method']
 *                      -> ['siteId']
 *                      -> ['user']     -> ['emailAddress']
 *                                      -> ['loyaltyCardNumber']
 *                                      -> ['firstName']
 *                                      -> ['lastName']
 *                                      -> ['deviceIdentifier']
 *                      -> ['basketSpend']
 * ----------------------------------------------------------------
 */

// Set infinite timeout
ini_set('max_execution_time', 0);
date_default_timezone_set("Europe/London");

// Include required functions and models
require_once("partials/generic.php");

// ----------------------------------------------------------------
// ------------------------Global Variables------------------------
// ----------------------------------------------------------------
$config;
$environment;

// From POST
$bundleIdentifier;
$version;
$platform;
$method;
$siteId;
$emailAddress;
$loyaltyCardnumber;
$firstName;
$lastName;
$deviceIdentifier;
$basketSpend;

$dataRecieved = false;

$POINTS_MULTIPLIER = 5;
$loyaltyPoints = 0;
$returnJSON;

// ----------------------------------------------------------------
// ----------------------------------------------------------------
// ------------------------Process Functions-----------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------

/**
 * processFallback()
 * @desc - Processes any fallback requests
 */
function processFallback(){
    echo 'Process fallback';
}

// ----------------------------------------------------------------
// ----------------------------------------------------------------
// ---------------------Start Main Process Here--------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------

// GET configuration settings
$config = file_get_contents('../config.json');
$config = json_decode($config, true);
$environment = $config['ENVIRONMENT'];

// Check which environment is to be used -> allows by-passing
if($environment === "DEVELOPMENT"){
    // INSERT development data into variables
    $bundleIdentifier = $config['DEVELOPMENT_POST']['BUNDLE_IDENTIFIER'];
    $version = $config['DEVELOPMENT_POST']['VERSION'];
    $platform = $config['DEVELOPMENT_POST']['PLATFORM'];
    $method = $config['DEVELOPMENT_POST']['METHOD'];
    $siteId = $config['DEVELOPMENT_POST']['AZTEC_ID'];
    $emailAddress = $config['DEVELOPMENT_POST']['EMAIL_ADDRESS'];
    $loyaltyCardnumber = $config['DEVELOPMENT_POST']['LOYALTY_CARDNUMBER'];
    $firstName = $config['DEVELOPMENT_POST']['FIRSTNAME'];
    $lastName = $config['DEVELOPMENT_POST']['LASTNAME'];
    $deviceIdentifier = $config['DEVELOPMENT_POST']['DEVICE_IDENTIFIER'];
    $basketSpend = $config['DEVELOPMENT_POST']['BASKET_SPEND'];

    $dataRecieved = true;
} else{
    // Using UAT / Live environment -> Check POST is set and NOT empty
    if(isset($_POST) && !empty($_POST)){
        // Decode and chewckthe POST
        $json = json_decode($_POST['request'], true);

        // Check if POST has minimum required user data
        if(array_key_exists("user", $json['request'])){
            if((array_key_exists("emailAddress", $json['request']['user'])) && (array_key_exists("loyaltyCardNumber", $json['request']['user']))){
                // Store POST data in globals
                // Format => (IF STATEMENT) ? [TRUE ACTION] : [FALSE ACTION];
                $bundleIdentifier = (array_key_exists("bundleIdentifier", $json['request']) && isset($json['request']['bundleIdentifier']) && !empty($json['request']['bundleIdentifier'])) ? $json['request']['bundleIdentifier'] : "NOT_SET";
                $version = (array_key_exists("version", $json['request']) && isset($json['request']['version']) && !empty($json['request']['version'])) ? $json['request']['version'] : "NOT_SET";
                $platform = (array_key_exists("platform", $json['request']) && isset($json['request']['platform']) && !empty($json['request']['platform'])) ? $json['request']['platform'] : "NOT_SET";
                $method = (array_key_exists("method", $json['request']) && isset($json['request']['method']) && !empty($json['request']['method'])) ? $json['request']['method'] : "NOT_SET";
                $siteId = (array_key_exists("siteId", $json['request']) && isset($json['request']['siteId']) && !empty($json['request']['siteId'])) ? $json['request']['siteId'] : "NOT_SET";
                $emailAddress = (array_key_exists("emailAddress", $json['request']['user']) && isset($json['request']['user']['emailAddress']) && !empty($json['request']['user']['emailAddress'])) ? $json['request']['user']['emailAddress'] : "NOT_SET";
                $loyaltyCardnumber = (array_key_exists("loyaltyCardNumber", $json['request']['user']) && !empty($json['request']['user']['loyaltyCardNumber'])) ? $json['request']['user']['loyaltyCardNumber'] : "NOT_SET";
                $firstName = (array_key_exists("firstName", $json['request']['user']) && isset($json['request']['user']['firstName']) && !empty($json['request']['user']['firstName'])) ? $json['request']['user']['firstName'] : "NOT_SET";
                $lastName = (array_key_exists("lastName", $json['request']['user']) && isset($json['request']['user']['lastName']) && !empty($json['request']['user']['lastName'])) ? $json['request']['user']['lastName'] : "NOT_SET";
                $deviceIdentifier = (array_key_exists("deviceIdentifier", $json['request']['user']) && isset($json['request']['user']['deviceIdentifier']) && !empty($json['request']['user']['deviceIdentifier'])) ? $json['request']['user']['deviceIdentifier'] : "NOT_SET";
                $basketSpend = (array_key_exists("basketSpend", $json['request']) && isset($json['request']['basketSpend']) && !empty($json['request']['basketSpend'])) ? $json['request']['basketSpend'] : "NOT_SET";

                $dataRecieved = true;
            } else{
                // POST does not contain email OR cardnumber
                processFallback();
            }
        } else{
            // POST does not contain user data
            processFallback();
        }
    } else{
        // POST is NOT set -> fallback
        processFallback();
    }
}

// Check data has been recieved
if($dataRecieved){
    // Data has been recieved -> convert basket spend into Loyalty points
    $loyaltyPoints = floor(floatval($basketSpend)) * $POINTS_MULTIPLIER;

    // Build a JSON string for passing
    $returnJSON = array("bundleIdentifier" => $bundleIdentifier,
                        "version" => $version,
                        "platform" => $platform,
                        "method" => $method,
                        "siteId" => $siteId,
                        "emailAddress" => $emailAddress,
                        "loyaltyCardnumber" => $loyaltyCardnumber,
                        "firstName" => $firstName,
                        "lastName" => $lastName,
                        "deviceIdentifier" => $deviceIdentifier);
    
    $returnJSON = json_encode($returnJSON);
    $returnJSON = base64_encode($returnJSON);
} else{
    // No data
    // POST is NOT set -> fallback
    goToFallback('no_post');
}

// ----------------------------------------------------------------
// ----------------------Construct HTML Page-----------------------
// ----------------------------------------------------------------
?>

<?php printHead('Your Points', "order_placed"); ?>

<section class="food_background">
    <img src="http://www.fillmurray.com/300/150" alt="Morrison" id="order_logo">
    <div class="point_circle_outer">
        <div class="point_circle_inner">
            <div class="upper_title">You have received...</div>
            <div class="points_value"><?php echo intval($loyaltyPoints); ?> points</div>
            <div class="lower_title">
                <p>Thanks for you order</p>
                <p>Enjoy!</p>
            </div>
        </div>
    </div>
    <div class="points_disclaimer">Your order has been placed and will be with you shortly.</div>
</section>
<footer>
    <a class="green_button button mb10" id="my_rewards_button">View My Rewards</a>
    <a class="green_button button mb10" id="place_order">Place Another Order</a>
</footer>
<form action="my_rewards.php" method="POST" id="post_details">
    <input type="hidden" name="base64" value="<?php echo $returnJSON;  ?>">
</form>
<script>
    const deviceType = "<?php echo $platform; ?>";

    // Detect click on place another order button
    $('#place_order').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        var type = deviceType.toLowerCase();

        // Search for substring -> iphone
        if((type.indexOf("iphone") >= 0)){
            // iPhone callback needed
            alert('iPhone callback');
        } else if((type.indexOf("android") >= 0)){
            // Android callback needed
            alert('Android callback');
        }
    });

    // Detect click on view my rewards
    $('#my_rewards_button').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        // Submit form and leave page
        $('#post_details').submit();
    });
</script>

<?php printFoot(); ?>

