<?php

/**
 * ----------------------------------------------------------------
 * Loyalty Model
 * ----------------------------------------------------------------
 * @desc        - This is the model that interacts with the 
 *                Loyalty API.
 * ----------------------------------------------------------------
 * @requires
 *      - BASE_URL                          -> The live/UAT Loyalty URL
 *      - API_USER                          -> User credentials for Loyalty
 *      - API_PASSWORD                      -> User password for Loyalty
 *      - PLATFORM_SERVICES_COMPANY_ID      -> The initial Loyalty API key to use
 *      - (Optional) DEFAULT_PROGRAM_ID     -> The initial Loyalty Program ID
 * ----------------------------------------------------------------
 */

class Loyalty{
    // Class variables
    var $environment;
    var $baseURL;
    var $platformServicesCompanyID = "";
    var $programID = "";
    private $apiUser;
    private $apiPassword;

    /**
     * loadInConfig()
     * @desc - Loads the config file
     * @return {Object[]}
     */
    private function loadInConfig(){
        // Find config in directories
        $config;
        if(file_exists('../config.json')){
			$config = file_get_contents('../config.json');
		} else{
			$config = file_get_contents('../../config.json');
		}
        $config = json_decode($config, true);
        return $config;
    }

    /**
     * __construct()
     * @desc - Constructs the Loyalty object 
     */
    public function __construct(){
        // Load in config
		$config = loadInConfig();

        // Store required variables
        $this->environment = $jsonImport['ENVIRONMENT'];
        $this->baseURL = $jsonImport['LOYALTY']['API_BASE'];
        $this->apiUser = $jsonImport['LOYALTY']['API_USER'];
        $this->apiPassword = $jsonImport['LOYALTY']['API_PASS'];
    }

    /**
     * setDefaultIDs()
     * @desc - Sets the default value (provided in config json) for:
     *       - Platform Services Company ID
     *       - Program ID
     */
    public function setDefaultIDs(){
        // Load in config
		$config = loadInConfig();

        // Store required variables
        $this->platformServicesCompanyID = $jsonImport['LOYALTY']['PLATFORM_SERVICES_COMPANY_ID'];
        $this->programID = $jsonImport['LOYALTY']['DEFAULT_PROGRAM_ID'];
    }

        /**
     * setCompanyID()
     * @desc - Sets the Loyalty object's company ID
     * @param {String} $input       - String to to set as the company ID
     */
    public function setCompanyID($id){
        $this->platformServicesCompanyID = $id;
    }

    /**
     * setProgramID()
     * @desc - Sets the Loyalty object's program ID
     * @param {String} $input       - String to be set as a program ID
     */
    public function setProgramID($id){
        $this->programID = $id;
     }

    /**
     * getCompanyLoyaltyAccount()
     * @desc - Gets the customers Loyalty account details and balances,
     *         Uses the platform services company ID
     * @param {String} $cardnumber     - Loyalty cardnumber
     * @return {Object[]}
     */
    public function getCompanyLoyaltyAccount($cardnumber){
        // Generate datetime for creation
        $logDate = date("Y-m-d H:i:s");

        // Check that $platformServicesCompanyID has been set
        if(isset($this->platformServicesCompanyID) && !empty($this->platformServicesCompanyID)){
            // Check cardnumber is set, not empty and    valid
            if(isset($cardnumber) && !empty($cardnumber) && is_numeric($cardnumber)){
                intval($cardnumber);

                // Build URL, authorization and CURL
                $loyaltyURL = $this->baseURL . "/api/companies/{$this->platformServicesCompanyID}/accounts/{$cardnumber}";
                $authorization = base64_encode($this->apiUser . ":" . $this->apiPassword);

                // Setup Loyalty cURL request
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $loyaltyURL,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Basic " . $authorization,
                        "cache-control: no-cache",
                        "postman-token: 9ea13a43-07a6-f090-0527-a8a5b3c0a953"
                    ),
                ));

                // Send cURL request
                $response = curl_exec($curl);
                $response = json_decode($response, true);

                // get cURL info
                $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $error = curl_error($curl);
                curl_close($curl);

                // Check the response
                if($code === 200){
                    // SUCCESS code -> GET the appropriate details
                    // Check the card is active
                    if($response['Card']['Status'] === "Active"){
                        // Card is active
                        // Search for the correct balance type theen RETURN
                        foreach($response['Balances'] as $type){
                            if($type['Type'] === "Currency"){
                                return array("SUCCESS" => true,
                                             "BALANCE" => $type['Balance'], 
                                             "CARD_STATUS" => "ACTIVE");
                                break;
                            }
                        }
                    } else if($response['Card']['Status'] === "Inventory"){
                        // Card is in inventory
                        return array("SUCCESS" => false,
                                     "MESSAGE" => "Loyalty card is in inventory (" . $cardnumber . ")", 
                                     "CARD_STATUS" => "INVENTORY", 
                                     "DATETIME" => $logDate,
                                     "FILE" => "models/LoyaltyModel.php", 
                                     "METHOD" => "getLoyaltyAccount",
                                     "CODE" => $code);
                    } else{
                        // Card status is not active -> Return error
                        return array("SUCCESS" => false,
                                     "MESSAGE" => "Loyalty card not active (" . $cardnumber . ")", 
                                     "DATETIME" => $logDate,
                                     "FILE" => "models/LoyaltyModel.php", 
                                     "METHOD" => "getLoyaltyAccount",
                                     "CODE" => $code);
                    }
                } else{
                    // An error occurred return details
                    return array("SUCCESS" => false,
                                 "MESSAGE" => $error, 
                                 "DATETIME" => $logDate,
                                 "FILE" => "models/LoyaltyModel.php", 
                                 "METHOD" => "getCompanyLoyaltyAccount",
                                 "CODE" => $code);
                }
            } else{
                // Cardnumber is not set or is invalid
                return array("SUCCESS" => false,
                             "MESSAGE" => "Cardnumber is not set OR not numeric.",
                             "DATETIME" => $logDate,
                             "FILE" => "models/LoyaltyModel.php", 
                             "METHOD" => "getCompanyLoyaltyAccount",
                             "CODE" => "NA");
            }
        } else{
            // $platformServicesCompanyID hasn't been set.
            return array("SUCCESS" => false,
                         "MESSAGE" => "Loyalty platformServicesCompanyID has not been set.",
                         "DATETIME" => $logDate,
                         "FILE" => "models/LoyaltyModel.php", 
                         "METHOD" => "getCompanyLoyaltyAccount",
                         "CODE" => "NA");
        }
    }
      
    /**
     * getLoyaltyAccount()
     * @desc - Gets the customers Loyalty account details and balances,
     *         Uses the program ID
     * @param {String} $cardnumber     - Loyalty cardnumber
     * @return {Object[]}
     */
    public function getLoyaltyAccount($cardnumber){
        // Generate datetime for creation
        $logDate = date("Y-m-d H:i:s");
        
        // Check that $programID has been set
        if(isset($this->programID) && !empty($this->programID)){
            // Check cardnumber is set, not empty and    valid
            if(isset($cardnumber) && !empty($cardnumber) && is_numeric($cardnumber)){
                intval($cardnumber);

                // Build URL, authorization and CURL
                $loyaltyURL = $this->baseURL . "/api/programs/{$this->programID}/accounts/{$cardnumber}";
                $authorization = base64_encode($this->apiUser . ":" . $this->apiPassword);

                // Setup Loyalty cURL request
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $loyaltyURL,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Basic " . $authorization,
                        "cache-control: no-cache",
                        "postman-token: 9ea13a43-07a6-f090-0527-a8a5b3c0a953"
                    ),
                ));

                
                // Send cURL request
                $response = curl_exec($curl);
                $response = json_decode($response, true);
                
                // get cURL info
                $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $error = curl_error($curl);
                curl_close($curl);

                // Check the response
                if($code === 200){
                    // SUCCESS code -> GET the appropriate details
                    // Check the card is active
                    if($response['Card']['Status'] === "Active"){
                        // Card is active
                        // Search for the correct balance type theen RETURN
                        foreach($response['Balances'] as $type){
                            if($type['Type'] === "Currency"){
                                return array("SUCCESS" => true,
                                             "BALANCE" => $type['Balance'],
                                             "CARD_STATUS" => "ACTIVE");
                                break;
                            }
                        }
                    } else if($response['Card']['Status'] === "Inventory"){
                        // Card is in inventory
                        return array("SUCCESS" => false,
                                     "MESSAGE" => "Loyalty card is in inventory (" . $cardnumber . ")", 
                                     "CARD_STATUS" => "INVENTORY", 
                                     "DATETIME" => $logDate,
                                     "FILE" => "models/LoyaltyModel.php", 
                                     "METHOD" => "getLoyaltyAccount",
                                     "CODE" => $code);
                    } else{
                        // Card status is not active -> Return error
                        return array("SUCCESS" => false,
                                     "MESSAGE" => "Loyalty card not active (" . $cardnumber . ")", 
                                     "DATETIME" => $logDate,
                                     "FILE" => "models/LoyaltyModel.php", 
                                     "METHOD" => "getLoyaltyAccount",
                                     "CODE" => $code);
                    }
                } else{
                    // An error occurred return details
                    return array("SUCCESS" => false,
                                 "MESSAGE" => $error, 
                                 "DATETIME" => $logDate,
                                 "FILE" => "models/LoyaltyModel.php", 
                                 "METHOD" => "getLoyaltyAccount",
                                 "CODE" => $code);
                }
            } else{
                // Cardnumber is not set or is invalid
                return array("SUCCESS" => false,
                             "MESSAGE" => "Cardnumber is not set OR not numeric.",
                             "DATETIME" => $logDate,
                             "FILE" => "models/LoyaltyModel.php", 
                             "METHOD" => "getLoyaltyAccount",
                             "CODE" => "NA");
            }
        } else{
            // $programID hasn't been set.
            return array("SUCCESS" => false,
                         "MESSAGE" => "Loyalty programID has not been set.",
                         "DATETIME" => $logDate,
                         "FILE" => "models/LoyaltyModel.php", 
                         "METHOD" => "getLoyaltyAccount",
                         "CODE" => "NA");
        }
    }

    /**
     * creditLoyaltyAccountCurrency()
     * @desc - Credits a Loyalty account with a supplied value
     * @param {int} $topupAmount        - Topup amount as pence integer
     * @param {String} $cardnumber      - Customer's Loyalty cardnumber
     * @return {Object[]}
     */
    public function creditLoyaltyAccountCurrency($topupAmount, $cardnumber){
        // Generate datetime for creation
        $logDate = date("Y-m-d H:i:s");
        
        // Check that $programID has been set
        if(isset($this->programID) && !empty($this->programID)){
            // Check that $topupAmount has been set
            if(isset($topupAmount) && !empty($topupAmount)){
                // Check that $cardnumber has been set
                if(isset($cardnumber) && !empty($cardnumber)){
                    $loyaltyAmount = intval($topupAmount) / 100;

                    // Build URL, authorization and CURL
                    $loyaltyURL = $this->baseURL . "/api/programs/{$this->programID}/accounts/{$cardnumber}/credits";
                    $authorization = base64_encode($this->apiUser . ":" . $this->apiPassword);

                    // Topup card with transaction amount vie the Loyalty Balance Credit API
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $loyaltyURL,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => "{\"Amount\":\"{$loyaltyAmount}\",\"Type\":\"Currency\",\"Currency\":\"GBP\"}",
                        CURLOPT_HTTPHEADER => array(
                            "authorization: Basic " . $authorization,
                            "cache-control: no-cache",
                            "content-type: application/json",
                            "postman-token: 32b9685f-b9b4-d887-e2c6-924373729d96"
                        ),
                    ));

                    // Send cURL request
                    $storedResponse = curl_exec($curl);
                    $response = json_decode($storedResponse, true);

                    // get cURL info
                    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $error = curl_error($curl);
                    curl_close($curl);

                    // Check the response
                    if($code === 200){
                        // SUCCESS code -> GET the appropriate details
                        return array("SUCCESS" => true,
                                     "BALANCE" => $response['Balance']);
                    } else{
                        // An error occurred return details
                        return array("SUCCESS" => false,
                                    "MESSAGE" => $error, 
                                    "DATETIME" => $logDate,
                                    "FILE" => "models/LoyaltyModel.php", 
                                    "METHOD" => "creditLoyaltyAccountCurrency",
                                    "CODE" => $code, 
                                    "RESPONSE" => $storedResponse);
                    }

                } else{
                    // $cardnumber hasn't been set.
                    return array("SUCCESS" => false,
                                 "MESSAGE" => "cardnumber has not been set.",
                                 "DATETIME" => $logDate,
                                 "FILE" => "models/LoyaltyModel.php", 
                                 "METHOD" => "creditLoyaltyAccountCurrency",
                                 "CODE" => "NA");
                }
            } else{
                // $topupAmount hasn't been set.
                return array("SUCCESS" => false,
                             "MESSAGE" => "topupAmount has not been set.",
                             "DATETIME" => $logDate,
                             "FILE" => "models/LoyaltyModel.php", 
                             "METHOD" => "creditLoyaltyAccountCurrency",
                             "CODE" => "NA");
            }
        } else{
            // $programID hasn't been set.
            return array("SUCCESS" => false,
                         "MESSAGE" => "Loyalty programID has not been set.",
                         "DATETIME" => $logDate,
                         "FILE" => "models/LoyaltyModel.php", 
                         "METHOD" => "creditLoyaltyAccountCurrency",
                         "CODE" => "NA");
        }
    }

    /**
     * registerLoyaltyCard()
     * @desc - Registers a Loyalty card to the current program. Requires:
     *              - Platform Services Company ID
     *              - Program ID
     *              - Cardnumber
     *              - Email
     *              - FirstName
     *              - LastName
     * @param {Object[]} $userDetails        - User details to be registered
     * @param {String} $cardnumber           - Customer's Loyalty cardnumber
     * @return {Object[]}
     */
    public function registerLoyaltyCard($userDetails, $cardnumber){
        // Generate datetime for creation
        $logDate = date("Y-m-d H:i:s");

        // Check that $platformServicesCompanyID has been set
        if(isset($this->platformServicesCompanyID) && !empty($this->platformServicesCompanyID)){
            // Check that $programID has been set
            if(isset($this->programID) && !empty($this->programID)){
                // Check cardnumber is set
                if(isset($cardnumber) && !empty($cardnumber)){
                    // Check userdetails['email_input'] is set
                    if(isset($userDetails['email_input']) && !empty($userDetails['email_input'])){
                        // Check userdetails['first_name_input'] is set
                        if(isset($userDetails['first_name_input']) && !empty($userDetails['first_name_input'])){
                            // Check userdetails['last_name_input'] is set
                            if(isset($userDetails['last_name_input']) && !empty($userDetails['last_name_input'])){
                                // Prep Loyalty credentials
                                $loyaltyURL = $this->baseURL . "/api/companies/" . trim($this->platformServicesCompanyID) . "/programs/" . trim($this->programID) . "/accounts/" . trim($cardnumber) . "/register";
                                $authorization = base64_encode($this->apiUser . ":" . $this->apiPassword);

                                // Prep data for Loyalty Registration
                                $apiFirstName = (array_key_exists('first_name_input', $userDetails) && isset($userDetails['first_name_input']) && !empty($userDetails['first_name_input'])) ? $userDetails['first_name_input'] : "";
                                $apiLastName = (array_key_exists('last_name_input', $userDetails) && isset($userDetails['last_name_input']) && !empty($userDetails['last_name_input'])) ? $userDetails['last_name_input'] : "";
                                $apiEmail = (array_key_exists('email_input', $userDetails) && isset($userDetails['email_input']) && !empty($userDetails['email_input'])) ? $userDetails['email_input'] : "";
                                $apiVenue = (array_key_exists('venue_selector', $userDetails) && isset($userDetails['venue_selector']) && !empty($userDetails['venue_selector'])) ? $userDetails['venue_selector'] : "";
                                $apiMobile = (array_key_exists('mobile_input', $userDetails) && isset($userDetails['mobile_input']) && !empty($userDetails['mobile_input'])) ? $userDetails['mobile_input'] : "";
                                $apiMarketingOptIn = (array_key_exists('marketing_opt_in', $userDetails) && isset($userDetails['marketing_opt_in']) && !empty($userDetails['marketing_opt_in'])) ? $userDetails['marketing_opt_in'] : "";
                                $apiPromo = (array_key_exists('promo_input', $userDetails) && isset($userDetails['promo_input']) && !empty($userDetails['promo_input'])) ? $userDetails['promo_input'] : "";
                                $apiAddressLine1 = (array_key_exists('address_1_input', $userDetails) && isset($userDetails['address_1_input']) && !empty($userDetails['address_1_input'])) ? $userDetails['address_1_input'] : "";
                                $apiAddressLine2 = (array_key_exists('address_2_input', $userDetails) && isset($userDetails['address_2_input']) && !empty($userDetails['address_2_input'])) ? $userDetails['address_2_input'] : "";
                                $apiAddressLine3 = (array_key_exists('address_3_input', $userDetails) && isset($userDetails['address_3_input']) && !empty($userDetails['address_3_input'])) ? $userDetails['address_3_input'] : "";
                                $apiCity = (array_key_exists('city_input', $userDetails) && isset($userDetails['city_input']) && !empty($userDetails['city_input'])) ? $userDetails['city_input'] : "";
                                $apiPostcode = (array_key_exists('postcode_input', $userDetails) && isset($userDetails['postcode_input']) && !empty($userDetails['postcode_input'])) ? $userDetails['postcode_input'] : "";

                                // Determine customs
                                $apiEmailOptIn = "false";
                                $apiMobileOptIn = "false";
                                $apiCustom1 = "Marketing (Opted OUT)";
                                if(($apiMarketingOptIn === 1) || ($apiMarketingOptIn === "1")){
                                    $apiEmailOptIn = "true";
                                    $apiMobileOptIn = "true";
                                    $apiCustom1 = "Marketing (Opted IN)";
                                }
                                $apiCustom2 = "Venue ID (" . $apiVenue . ")";
                                
                                // Setup Postfields
                                $postfields = "{\"FirstName\": \"{$apiFirstName}\", \"LastName\": \"{$apiLastName}\", \"Email\": \"{$apiEmail}\", \"MobilePhone\": \"{$apiMobile}\", \"OptInEmail\": {$apiEmailOptIn}, \"OptInMobilePhone\": {$apiMobileOptIn}, \"CustomInput1\": \"{$apiCustom1}\", \"CustomInput2\": \"{$apiCustom2}\", \"Address1\": \"{$apiAddressLine1}\", \"Address2\": \"{$apiAddressLine2}\", \"Address3\": \"{$apiAddressLine3}\", \"City\": \"{$apiCity}\", \"Postcode\": \"{$apiPostcode}\"}";

                                // Setup CURL for execution
                                $curl = curl_init();
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => $loyaltyURL,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "POST",
                                    CURLOPT_POSTFIELDS => $postfields,
                                    CURLOPT_HTTPHEADER => array(
                                        "authorization: Basic " . $authorization,
                                        "cache-control: no-cache",
                                        "content-type: application/json", # For some reason if this is on it returns html (for errors), if it's off it returns JSON (errors)
                                        "postman-token: a50d27c4-2599-c6cd-c554-a48f43902820"
                                    )
                                ));
                                
                                // Send cURL request
                                $storedResponse = curl_exec($curl);
                                $response = json_decode($storedResponse, true);
                                
                                // get cURL info
                                $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                                $error = curl_error($curl);
                                curl_close($curl);
                                
                                // Check CURL response
                                if(($code == 200) && (array_key_exists("Card", $response)) && (isset($response['Card']['Status']))){
                                    // Check card status is active
                                    if($response['Card']['Status'] === "Active"){
                                        // Card is active -> GET card balances
                                        $currencyBalance = 0;
                                        $pointsBalance = 0;
                                        $visits = 0;
                                        foreach($response['Balances'] as $element){
                                            if($element['Type'] === "Currency"){
                                                // Currency value
                                                $currencyBalance = $element['Balance'];
                                            } else if($element['Type'] === "Points"){
                                                // Points value
                                                $pointsBalance = $element['Balance'];
                                            } else if($element['Type'] === "Visits"){
                                                // Visits
                                                $visits = $element['Balance'];
                                            }
                                        }
                                        // Build and return
                                        return array("SUCCESS" => true,
                                                     "CURRENCY_BALANCE" => $currencyBalance, 
                                                     "POINTS_BALANCE" => $pointsBalance, 
                                                     "VISITS" => $visits, 
                                                     "CODE" => $code);
                                    } else{
                                        // Card isn't set to active
                                        return array("SUCCESS" => false,
                                                     "MESSAGE" => "Card isn't set to active.",
                                                     "DATETIME" => $logDate,
                                                     "FILE" => "models/LoyaltyModel.php", 
                                                     "METHOD" => "registerLoyaltyCard",
                                                     "CODE" => $code);
                                    }
                                } else if($code == 400){
                                    // Email / Mobile Already registered
                                    return array("SUCCESS" => false,
                                                 "MESSAGE" => "Email or Mobile already registered",
                                                 "DATETIME" => $logDate,
                                                 "FILE" => "models/LoyaltyModel.php", 
                                                 "METHOD" => "registerLoyaltyCard",
                                                 "CODE" => $code);
                                } else if($code == 404){
                                    // Loyalty URL not found
                                    return array("SUCCESS" => false,
                                                 "MESSAGE" => "Loyalty URL (Cardnumber) not found",
                                                 "DATETIME" => $logDate,
                                                 "FILE" => "models/LoyaltyModel.php", 
                                                 "METHOD" => "registerLoyaltyCard",
                                                 "CODE" => $code);
                                } else if($error){
                                    // Loyalty CURL error
                                    return array("SUCCESS" => false,
                                                 "MESSAGE" => implode($error),
                                                 "DATETIME" => $logDate,
                                                 "FILE" => "models/LoyaltyModel.php", 
                                                 "METHOD" => "registerLoyaltyCard",
                                                 "CODE" => $code);
                                } else{
                                    // Unknown Error
                                    return array("SUCCESS" => false,
                                                 "MESSAGE" => "Unknown Loyalty Enrol cURL Error",
                                                 "DATETIME" => $logDate,
                                                 "FILE" => "models/LoyaltyModel.php", 
                                                 "METHOD" => "registerLoyaltyCard",
                                                 "CODE" => $code);
                                }
                            } else{
                                // $userdetails['last_name_input'] hasn't been set.
                                return array("SUCCESS" => false,
                                             "MESSAGE" => "Last Name has not been set.",
                                             "DATETIME" => $logDate,
                                             "FILE" => "models/LoyaltyModel.php", 
                                             "METHOD" => "registerLoyaltyCard",
                                             "CODE" => "NA");
                            }
                        } else{
                            // $userdetails['first_name_input'] hasn't been set.
                            return array("SUCCESS" => false,
                                         "MESSAGE" => "First Name has not been set.",
                                         "DATETIME" => $logDate,
                                         "FILE" => "models/LoyaltyModel.php", 
                                         "METHOD" => "registerLoyaltyCard",
                                         "CODE" => "NA");
                        }
                    } else{
                        // $userdetails['email_input'] hasn't been set.
                        return array("SUCCESS" => false,
                                     "MESSAGE" => "Email has not been set.",
                                     "DATETIME" => $logDate,
                                     "FILE" => "models/LoyaltyModel.php", 
                                     "METHOD" => "registerLoyaltyCard",
                                     "CODE" => "NA");
                    }
                } else{
                    // $cardnumber hasn't been set.
                    return array("SUCCESS" => false,
                                 "MESSAGE" => "Cardnumber has not been set.",
                                 "DATETIME" => $logDate,
                                 "FILE" => "models/LoyaltyModel.php", 
                                 "METHOD" => "registerLoyaltyCard",
                                 "CODE" => "NA");
                }
            } else{
                // $programID hasn't been set.
                return array("SUCCESS" => false,
                             "MESSAGE" => "Loyalty programID has not been set.",
                             "DATETIME" => $logDate,
                             "FILE" => "models/LoyaltyModel.php", 
                             "METHOD" => "registerLoyaltyCard",
                             "CODE" => "NA");
            }
        } else{
            // $platformServicesCompanyID hasn't been set.
            return array("SUCCESS" => false,
                         "MESSAGE" => "Loyalty platformServicesCompanyID has not been set.",
                         "DATETIME" => $logDate,
                         "FILE" => "models/LoyaltyModel.php", 
                         "METHOD" => "registerLoyaltyCard",
                         "CODE" => "NA");
        }
    }
}

?>