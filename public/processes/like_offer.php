<?php

/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - Like Offer Process
 * ----------------------------------------------------------------
 * Routed to from All Offers Page via AJAX
 * Requires:    - customer
 *              - offer_id
 *              - status
 */

 // Set infinite timeout
ini_set('max_execution_time', 0);
date_default_timezone_set("Europe/London");

// Include required functions and models
require_once("../models/dbConn.php");

// Connect to database
$pdo = new dbConn();

// ----------------------------------------------------------------
// ------------------------Global Variables------------------------
// ----------------------------------------------------------------
$cardnumber;
$status;
$offerID;

// ----------------------------------------------------------------
// ----------------------------------------------------------------
// ------------------------Process Functions-----------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------

/**
 * validateSet()
 * @desc - Checks that the value is set (Does not equal NULL OR EMPTY)
 * @param {String} $value       - Value to be checked
 * @return {boolean}
 */
function validateSet($value){
    // Check value
    if(isset($value) && !empty($value)){
        return true;
    } else{
        return false;
    }
}

/**
 * validateNumeric()
 * @desc - Checks that the value is numeric
 * @param {int} $value      - The value to be checked
 * @return {boolean}
 */
function validateNumeric($value){
    // Trim the value
    $value = trim($value);
    // Check value is set and not empty
    if(validateSet($value)){
        // Check value is numeric
        if(is_numeric($value)){
            // Value is numeric
            return true;
        } else{
            // Value is not numeric
            return false;
        }
    } else{
        // Value is not set or is empty
        return false;
    }
}

/**
 * applyOffer()
 * @desc - Applied an offer to a customer
 * @return {String}
 */
function applyOffer(){
    global $pdo, $cardnumber, $status, $offerID;

    // Check to see if customer is in the DB
    $selectSQL = "SELECT *
                  FROM app_customers 
                  WHERE cust_cardnumber = :cardnumber";
    
    $selectQuery = $pdo->prepare($selectSQL);
    $selectQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);

    // Execute query
    $result = $selectQuery->execute();

    // Check result to see if customer was found
    if($result){
        // Customer was found -> GET offer IDs
        $favOffers = $selectQuery->fetch();

        // Check customer was returned
        if($favOffers !== false){
            $favOffers = $favOffers['cust_offers'];

            // Decode offers and check new offer doesn't exist already
            $offerID = intval($offerID);
            $favOffers = json_decode($favOffers);
            if(!in_array($offerID, $favOffers)){
                // Offer not against customer already -> Add offer
                array_push($favOffers, $offerID);

                // Encode as JSON and UPDATE customer
                $favOffers = json_encode($favOffers);
                $updateSQL = "UPDATE app_customers 
                            SET cust_offers = :offers 
                            WHERE cust_cardnumber = :cardnumber";

                $updateQuery = $pdo->prepare($updateSQL);
                $updateQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);
                $updateQuery->bindParam(":offers", $favOffers , PDO::PARAM_STR);

                // Execute query
                $result = $updateQuery->execute();

                // Check result
                if($result){
                    // Update succeeded
                    return 'success';
                } else{
                    // Update failed
                    return 'update failed';
                }
            } else{
                // Offer already against customer
                return 'offer already against customer';
            }
        } else{
            // No customer found -> Create customer
            $offers = "[" . $offerID . "]";

            $insertSQL = "INSERT INTO app_customers (cust_cardnumber, cust_offers) 
                        VALUES (:cardnumber, :offers)";

            $insertQuery = $pdo->prepare($insertSQL);
            $insertQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);
            $insertQuery->bindParam(":offers", $offers , PDO::PARAM_STR);

            // Execute query
            $result = $insertQuery->execute();

            // Check customer was inserted
            if($result){
                // Insert was successful
                return "success";
            } else{
                // Insert failed
                return "insert failed";
            }
        }
    } else{
        // No customer has been found -> Create customer
        $offers = "[" . $offerID . "]";

        $insertSQL = "INSERT INTO app_customers (cust_cardnumber, cust_offers) 
                      VALUES (:cardnumber, :offers)";

        $insertQuery = $pdo->prepare($insertSQL);
        $insertQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);
        $insertQuery->bindParam(":offers", $offers , PDO::PARAM_STR);

        // Execute query
        $result = $insertQuery->execute();

        // Check customer was inserted
        if($result){
            // Insert was successful
            return "success";
        } else{
            // Insert failed
            return "insert failed";
        }
    }
}

/**
 * removeOffer()
 * @desc - Removes an offer from the customer's priority
 * @return {String}
 */
function removeOffer(){
    global $pdo, $cardnumber, $status, $offerID;

    // Check to see if customer is in the DB
    $selectSQL = "SELECT *
                  FROM app_customers 
                  WHERE cust_cardnumber = :cardnumber";
    
    $selectQuery = $pdo->prepare($selectSQL);
    $selectQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);

    // Execute query
    $result = $selectQuery->execute();

    // Check result to see if customer was found
    if($result){
        // Customer was found -> GET offer IDs
        $favOffers = $selectQuery->fetch();

        // Check customer was returned
        if($favOffers !== false){
            $favOffers = $favOffers['cust_offers'];

            // Decode offers and check new offer doesn't exist already
            $offerID = intval($offerID);
            $favOffers = json_decode($favOffers);
            
            // Search array for offerID
            $index = array_search($offerID, $favOffers);
            if($index !== false){
                // Offer ID found -> Remove from array
                $favOffers[$index] = null;
                
                // Loop through array and re-index
                $newOffers = [];
                foreach($favOffers as $offer){
                    // Check not equal to null
                    if(validateSet($offer)){
                        array_push($newOffers, $offer);
                    }
                }

                // Encode as JSON and UPDATE customer
                $newOffers = json_encode($newOffers);

                $updateSQL = "UPDATE app_customers 
                            SET cust_offers = :offers 
                            WHERE cust_cardnumber = :cardnumber";

                $updateQuery = $pdo->prepare($updateSQL);
                $updateQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);
                $updateQuery->bindParam(":offers", $newOffers , PDO::PARAM_STR);

                // Execute query
                $result = $updateQuery->execute();

                // Check result
                if($result){
                    // Update succeeded
                    return 'success';
                } else{
                    // Update failed
                    return 'update failed';
                }
            } else{
                // Offer IF not found -> No change
                return 'success';
            }
        } else{
            // No customer found -> Create customer
            $insertSQL = "INSERT INTO app_customers (cust_cardnumber) 
                      VALUES (:cardnumber)";

            $insertQuery = $pdo->prepare($insertSQL);
            $insertQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);

            // Execute query
            $result = $insertQuery->execute();

            // Check customer was inserted
            if($result){
                // Insert was successful
                return "success";
            } else{
                // Insert failed
                return "insert failed";
            }
        }
    } else{
        // No customer has been found -> Create customer
        $insertSQL = "INSERT INTO app_customers (cust_cardnumber) 
                      VALUES (:cardnumber)";

        $insertQuery = $pdo->prepare($insertSQL);
        $insertQuery->bindParam(":cardnumber", $cardnumber , PDO::PARAM_STR);

        // Execute query
        $result = $insertQuery->execute();

        // Check customer was inserted
        if($result){
            // Insert was successful
            return "success";
        } else{
            // Insert failed
            return "insert failed";
        }
    }
}

// ----------------------------------------------------------------
// ----------------------------------------------------------------
// ---------------------Start Main Process Here--------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------

$_POST = json_decode($_POST['values'], true);

// Check POST
if(isset($_POST) && !empty($_POST)){
    // GET data from POST
    $cardnumber = (array_key_exists('customer', $_POST) && !empty($_POST['customer'])) ? $_POST['customer'] : null;
    $status = (array_key_exists('status', $_POST) && !empty($_POST['status'])) ? $_POST['status'] : null;
    $offerID = (array_key_exists('offer_id', $_POST) && !empty($_POST['offer_id'])) ? $_POST['offer_id'] : null;

    // Check all are set
    if(isset($cardnumber) && !empty($cardnumber) && isset($status) && !empty($status) && isset($offerID) && !empty($offerID) && validateNumeric($offerID)){
        // Parse status for which process
        if($status === "APPLY"){
            // Offer needs to be applied to the user
            echo applyOffer();
        } else {
            // Offer needs to be removed from the user
            echo removeOffer();
        }
    } else{
        // Required value not set
        echo 'required value missing';
    }
} else{
    // No POST
    echo "no post";
}