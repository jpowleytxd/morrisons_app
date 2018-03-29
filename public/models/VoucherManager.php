<?php

/**
 * ----------------------------------------------------------------
 * Voucher Manager Model
 * ----------------------------------------------------------------
 * @desc        - This is the model that interacts with the 
 *                Voucher Manager API.
 * ----------------------------------------------------------------
 * @requires
 *      - BASE_URL                          -> The live/UAT Voucher Manager URL
 *      - API_USER                          -> User credentials for Voucher Manager
 *      - API_PASSWORD                      -> User password for Voucher Manager
 * ----------------------------------------------------------------
 */

class VoucherManager{
    // Class variables
    private $environment;
    private $baseURL;
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
		$config = $this->loadInConfig();

        // Store required variables
        $this->environment = $config['ENVIRONMENT'];
        $this->baseURL = $config['VOUCHER']['API_BASE'];
        $this->apiUser = $config['VOUCHER']['API_USER'];
        $this->apiPassword = $config['VOUCHER']['API_PASS'];
    }

    /**
     * getVoucher(
     * @desc - Gets a new voucher code for the passed Campaign ID
     * @param {String} $campaign        - Campaign ID to get voucher for
     * @return {Object}
     */
    public function getVoucher($campaign){
        // Generate datetime for creation
        $logDate = date("Y-m-d H:i:s");

        // Check that campaign is set
        if(isset($campaign) && !empty($campaign)){
            // Build URL and CURL
            $voucherURL = $this->baseURL . "?Username=" . $this->apiUser . "&Password=" . $this->apiPassword . "&CampaignID=" . $campaign;

            // Setup Loyalty cURL request
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $voucherURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                  "cache-control: no-cache",
                  "postman-token: 26a980df-0e8c-5715-26d9-fff2a3d2c669"
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
                // Server connected -> Check voucher code exists
                if(array_key_exists('VoucherCode', $response) && isset($response['VoucherCode']) && !empty($response['VoucherCode'])){
                    // GET code and return 
                    return array("SUCCESS" => true,
                                 "VOUCHER" => $response['VoucherCode']);
                } else{
                    // An error occurred return details
                    return array("SUCCESS" => false,
                                "MESSAGE" => $response['Message'], 
                                "DATETIME" => $logDate,
                                "FILE" => "models/VoucherManager.php", 
                                "METHOD" => "getVoucher",
                                "CODE" => $code);
                }
            } else{
                // An error occurred return details
                return array("SUCCESS" => false,
                             "MESSAGE" => $error, 
                             "DATETIME" => $logDate,
                             "FILE" => "models/VoucherManager.php", 
                             "METHOD" => "getVoucher",
                             "CODE" => $code);
            }
        } else{
            // Campaign ID has not been set
            return array("SUCCESS" => false,
                         "MESSAGE" => "Campaign ID has not been set",
                         "DATETIME" => $logDate,
                         "FILE" => "models/VoucherManager.php", 
                         "METHOD" => "getVoucher",
                         "CODE" => "NA");
        }
    }
}

?>