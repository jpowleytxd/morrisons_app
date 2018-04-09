<?php

/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - Generic Process Functions
 * ----------------------------------------------------------------
 */

/**
 * processFallback()
 * @desc - Processes any fallback requests
 */
function processFallback(){
    header("Location: fallback.php");
    die();
}

/**
 * LoyaltyFallback()
 * @desc - Processes loyalty fallback requests
 */
function LoyaltyFallback(){
    header("Location: loyalty_fallback.php");
    die();
}

?>