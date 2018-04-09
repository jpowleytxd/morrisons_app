<?php

/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - All Offers Page
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
require_once("processes/generic.php");
require_once("models/dbConn.php");
require_once("models/VoucherManager.php");

// Connect to database
$pdo = new dbConn();

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

$customerFound = false;
$customer;

$offerStack = [];
$voucherStack = [];

$voucherCampaign = "10000696";
$cakeVoucher = array("TITLE" => "Free slice of cake",
                     "BACKGROUND" => "/media/tiles/reward_cake.jpg");

$hotDrinkVoucher = array("TITLE" => "Free hot drink",
                         "BACKGROUND" => "/media/tiles/hot_drink.jpg", 
                         "CODE" => "MOREPSRWH");

// ----------------------------------------------------------------
// ----------------------------------------------------------------
// ------------------------Process Functions-----------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------

/**
 * getAllOffers()
 * @desc - GETS all offers from the database
 * @return {array[]}
 */
function getAllOffers(){
    global $pdo;

    // No customer found -> GET all offers 
    $selectSQL = "SELECT * 
    FROM app_offers";

    $selectQuery = $pdo->prepare($selectSQL);

    // Execute query
    $result = $selectQuery->execute();

    // Check results
    if($result){
        // GET rows
        return $selectQuery->fetchAll(PDO::FETCH_ASSOC);
    } else{
        // No offers found -> fallback
        processFallback();
    }
}

/**
 * createCustomer()
 * @desc - Creates a customer
 * @param {String} $cardnumber      - Cardnumber to be stored against the customer
 * @param {String} $cakeVoucher     - Cake voucher to be stored against the customer
 * @return {boolean}
 */
function createCustomer($cardnumber, $cakeVoucher){
    global $pdo;

    // Create INSERT statement
    $insertSQL = "INSERT INTO app_customers (cust_cardnumber, cust_cake_voucher) 
                  VALUES (:cardnumber, :cakeVoucher)";
    
    $insertQuery = $pdo->prepare($insertSQL);
    $insertQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);
    $insertQuery->bindParam(":cakeVoucher", $cakeVoucher , PDO::PARAM_STR);

    // Execute query
    $result = $insertQuery->execute();
    return $result;
}

/**
 * updateCustomer()
 * @desc - Updates a customer
 * @param {String} $cardnumber      - Cardnumber to be looked up
 * @param {String} $cakeVoucher     - Cake voucher to be stored against the customer
 * @return {boolean}
 */
function updateCustomer($cardnumber, $cakeVoucher){
    global $pdo;

    // Create INSERT statement
    $updateSQL = "UPDATE app_customers
                  SET cust_cake_voucher = :cakeVoucher
                  WHERE cust_cardnumber = :cardnumber";
    
    $updateQuery = $pdo->prepare($updateSQL);
    $updateQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);
    $updateQuery->bindParam(":cakeVoucher", $cakeVoucher , PDO::PARAM_STR);

    // Execute query
    $result = $updateQuery->execute();
    return $result;
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
                loyaltyFallback();
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
    // Data has been recieved -> GET All Offers

    // GET users offers from the database
    $selectSQL = "SELECT * 
                  FROM app_customers 
                  WHERE cust_cardnumber = :cardnumber";
    
    $selectQuery = $pdo->prepare($selectSQL);
    $selectQuery->bindParam(":cardnumber", $loyaltyCardnumber , PDO::PARAM_STR);

    // Execute query
    $result = $selectQuery->execute();

    // Check to see if a customer was found
    if($result){
        // Check to see if if empty
        $customer = $selectQuery->fetch();

        // Check a customer was found
        if($customer !== false){
            $customerFound = true;
            $custOffers = $customer['cust_offers'];
        
            // Check offers have been set
            if(isset($custOffers) && !empty($custOffers)){
                $custOffers = json_decode($custOffers);

                // Build SELECT
                $inString = "";
                foreach($custOffers as $key => $id){
                    // Check if first offer
                    if($key === 0){
                        $inString = $id;
                    } else{
                        $inString = $inString . ", " . $id;
                    }
                }

                $selectSQL = "SELECT * 
                            FROM app_offers 
                            WHERE offer_id 
                            IN ({$inString})";
                
                $selectQuery = $pdo->prepare($selectSQL);

                // Execute query
                $result = $selectQuery->execute();

                // Check results
                if($result){
                    // GET rows -> Push into array with like key
                    foreach($selectQuery->fetchAll(PDO::FETCH_ASSOC) as $offer){
                        $offer['offer_liked'] = true;
                        array_push($offerStack, $offer);
                    }

                    // GET all offers that aren't liked
                    $selectSQL = "SELECT * 
                                FROM app_offers 
                                WHERE offer_id 
                                NOT IN ({$inString})";

                    $selectQuery = $pdo->prepare($selectSQL);

                    // Execute query
                    $result = $selectQuery->execute();

                    if($result){
                        // Push each offer to array
                        foreach($selectQuery->fetchAll(PDO::FETCH_ASSOC) as $offer){
                            array_push($offerStack, $offer);
                        }
                    }
                } else{
                    // No customer offer preferences found -> GET all offers
                    $offerStack = getAllOffers();
                }
            } else{
                // No customer offer preferences found -> GET all offers
                $offerStack = getAllOffers();
            }
        } else{
            // No customer found -> GET all offers from database
            $offerStack = getAllOffers();
        }
    } else{
        // GET all offers from the database
        $offerStack = getAllOffers();
    }
} else{
    // No data
    // POST is NOT set -> fallback
    processFallback();
}

// Check offers have been stacked
if(isset($offerStack) && !empty($offerStack)){
    // Offers have been set -> Prepare repeating voucher
    array_push($voucherStack, $hotDrinkVoucher);

    // Search for user in DB
    if($customerFound === true){
        // GET customer voucher
        $cakeVoucher['CODE'] = $customer['cust_cake_voucher'];
        
        // Check cake voucher is set
        if(!isset($cakeVoucher['CODE']) || empty($cakeVoucher['CODE'])){
            // Cake voucher is not set -> GET voucher
            $voucherManager = new VoucherManager();
            $voucher = $voucherManager->getVoucher($voucherCampaign);

            // Parse response 
            if($voucher['SUCCESS']){
                // GET voucher and push to stack
                $cakeVoucher['CODE'] = $voucher['VOUCHER'];
                array_push($voucherStack, $cakeVoucher);

                // UPDATE customer
                updateCustomer($loyaltyCardnumber, $cakeVoucher['CODE']);
            } else{
                // Voucher failed -> fallback
                processFallback();
            }
        } else{
            // Voucher already assigned
            array_push($voucherStack, $cakeVoucher);
        }
    } else{
        // Customer not found -> GET voucher
        $voucherManager = new VoucherManager();
        $voucher = $voucherManager->getVoucher($voucherCampaign);

        // Parse response 
        if($voucher['SUCCESS']){
            // GET voucher and push to stack
            $cakeVoucher['CODE'] = $voucher['VOUCHER'];
            array_push($voucherStack, $cakeVoucher);

            // Create customer
            createCustomer($loyaltyCardnumber, $cakeVoucher['CODE']);
        } else{
            // Voucher failed -> fallback
            processFallback();
        }
    }
} else{
    // No offers available -> fallback
    processFallback();
}

?>

<?php printHead('All Offers', "all_offers"); ?>

<section>
    <header>
        <img src="/media/market_street_header.png" alt="Market Street" class="header_image">
    </header>

    <?php foreach($voucherStack as $voucher){ ?>
        <div id="voucher_<?php echo $voucher['CODE']; ?>" class="tile_outer" style="background-image: url('<?php echo $voucher['BACKGROUND']; ?>');" data-action="voucher" data-code="<?php echo $voucher['CODE']; ?>" data-title="<?php echo $voucher['TITLE']; ?>">
            <div class="tile_bar">
                <div class="tile_title"><?php echo $voucher['TITLE']; ?></div>
                <div class="tile_action">
                    <img src="/media/right_arrow.png" class="action_icon">
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    <?php } ?>

    <?php foreach($offerStack as $offer){ ?>
        <div id="offer_<?php echo $offer['offer_id']; ?>" class="tile_outer" style="background-image: url('<?php echo $offer['offer_tile_img']; ?>');" data-action="offer" data-id="<?php echo $offer['offer_id']; ?>" data-title="<?php echo $offer['offer_title']; ?>" data-background="<?php echo $offer['offer_lrg_img']; ?>" data-liked="<?php if(array_key_exists('offer_liked', $offer) && $offer['offer_liked']){ echo 'true'; } else{ echo 'false'; } ?>">
            <div class="like_corner">
                <?php if(array_key_exists('offer_liked', $offer) && $offer['offer_liked']){ ?>
                    <img src="/media/corners_checked.png" id="checked_corner">
                    <img src="/media/corners_unchecked.png" id="unchecked_corner" class="hidden">
                <?php } else{ ?>
                    <img src="/media/corners_checked.png" id="checked_corner" class="hidden">
                    <img src="/media/corners_unchecked.png" id="unchecked_corner">
                <?php } ?>
            </div>
            <div class="tile_bar">
                <div class="tile_title"><?php echo $offer['offer_title']; ?></div>
                <div class="tile_action">
                    <img src="/media/right_arrow.png" class="action_icon">
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    <?php } ?>
</section>

<section class="overlay_outer yellow_background" id="voucher_overlay">
    <div class="voucher_pane">
        <div class="voucher_title"></div>
        <img src class="voucher_image">
        <div class="voucher_code"></div>
        <div class="voucher_text">To redeem your reward, the cashier will need to scan this code</div>
    </div>
    <footer>
        <a class="green_button button all_offers_button" data-overlay="voucher">View All Offers</a>
    </footer>
</section>

<section class="overlay_outer yellow_background" id="offer_overlay">
    <div class="offer_pane">
        <div class="offer_heart" data-id></div>
        <div class="offer_title"></div>
    </div>
    <footer>
        <a class="green_button button all_offers_button" data-overlay="offer">View All Offers</a>
    </footer>
</section>

<script>
    const cardnumber = "<?php echo $loyaltyCardnumber; ?>";
</script>

<?php printFoot(); ?>