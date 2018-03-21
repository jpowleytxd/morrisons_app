<?php

/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - My Rewards Page
 * ----------------------------------------------------------------
 * Routed to from iOrder rear menu
 * Requires:    - Cardnumber
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

// Include required functions and models
require_once("partials/generic.php");
require_once("models/dbConn.php");
require_once("models/Loyalty.php");

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

$loyaltyPoints;
$perc250;
$perc500;
$perc750;

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
        // Check if standard request or if json has been passed
        $json;
        if(array_key_exists('json', $_POST)){
            // Decode and check the POST
            $json = base64_decode($_POST['request']);
            $json = json_decode($json, true);
        } else{
            // Decode and check the POST
            $json = json_decode($_POST['request'], true);
        }

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
    // Data has been recieved -> GET Loyalty details
    $loyaltyObject = new Loyalty;
    $loyaltyObject->setDefaultIDs();
    $loyaltyResponse = $loyaltyObject->getLoyaltyAccount($loyaltyCardnumber);

    // Set values from the Loyalty response
    $loyaltyPoints = $loyaltyResponse['POINTS'];

    // Calculate percentages
    $perc250 = intval($loyaltyPoints) / 250;
    $perc500 = intval($loyaltyPoints) / 500;
    $perc750 = intval($loyaltyPoints) / 750;

    if($perc250 > 1){$perc250 = 1;}
    if($perc500 > 1){$perc500 = 1;}
    if($perc750 > 1){$perc750 = 1;}
} else{
    // No data
    // POST is NOT set -> fallback
    goToFallback('no_post');
}

?>

<?php printHead('My Rewards', "my_rewards"); ?>

<section>
    <header>
        <div class="upper_title">You currently have...</div>
        <div class="points_balance"><?php echo $loyaltyPoints; ?> points</div>
        <div class="lower_title">Here's what you can get with your points</div>
    </header>
    <div class="points_tile_container">
        <div class="points_tile">
            <div class="tile_left">
                <div class="total_points">250 Points</div>
                <div class="points_reward">Redeem a FREE soft drink</div>
            </div>
            <div class="tile_right"> 
                <div class="progress" id="progress250"></div>
                <img src="https://www.fillmurray.com/30/30" alt="" class="tile_logo">
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="points_tile">
            <div class="tile_left">
                <div class="total_points">500 Points</div>
                <div class="points_reward">Redeem a FREE hot drink</div>
            </div>
            <div class="tile_right">
                <div class="progress" id="progress500"></div>
                <img src="https://www.fillmurray.com/30/30" alt="" class="tile_logo">
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="points_tile">
            <div class="tile_left">
                <div class="total_points">750 Points</div>
                <div class="points_reward">Redeem a FREE snack</div>
            </div>
            <div class="tile_right">
                <div class="progress" id="progress750"></div>
                <img src="https://www.fillmurray.com/30/30" alt="" class="tile_logo">
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</section>
<script src="https://cdn.rawgit.com/kimmobrunfeldt/progressbar.js/0.5.6/dist/progressbar.js"></script>
<script>
// Load all progress bars
window.onload = function onLoad() {
    // Initiate 250 Points loader
    var circle250 = new ProgressBar.Circle('#progress250', {
        color: '#00563F',
        trailColor: 'rgba(255, 193, 13, 0.5)',
        duration: 3000,
        easing: 'bounce',
        strokeWidth: 12,
        trailWidth: 12
    });
    circle250.animate(<?php echo $perc250; ?>);

    // Initiate 500 Points loader
    var circle500 = new ProgressBar.Circle('#progress500', {
        color: '#00563F',
        trailColor: 'rgba(255, 193, 13, 0.5)',
        duration: 3000,
        easing: 'bounce',
        strokeWidth: 12,
        trailWidth: 12
    });
    circle500.animate(<?php echo $perc500; ?>);

    // Initiate 750 Points loader
    var circle750 = new ProgressBar.Circle('#progress750', {
        color: '#00563F',
        trailColor: 'rgba(255, 193, 13, 0.5)',
        duration: 3000,
        easing: 'bounce',
        strokeWidth: 12,
        trailWidth: 12
    });
    circle750.animate(<?php echo $perc750; ?>);
};
</script>
<?php printFoot(); ?>