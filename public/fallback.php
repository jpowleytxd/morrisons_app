<?php
/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - Fallback Page
 * ----------------------------------------------------------------
 */

// Include required functions and models
require_once("partials/generic.php");

 ?>

<?php printHead('Timed Out', "fallback"); ?>

<section>
    <header>
        <img src="/media/market_street_header.png" alt="Market Street" class="header_image">
    </header>
    <h1>Session Timed Out</h1>
    <h2>Please return to the page via the app menu</h2>
</section>

<?php printFoot(); ?>