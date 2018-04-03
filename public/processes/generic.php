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

?>