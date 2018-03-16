<?php

/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - Order Placed Page
 * ----------------------------------------------------------------
 * Routed to from iOrder after a successful order
 * Requires:    - Loyalty Cardnumber
 *              - Basket Spend
 */

// Include required functions and models
include "partials/generic.php";
include "models/dbConn.php";
include "models/Loyalty.php";

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
            <div class="points_value">400 points</div>
            <div class="lower_title">
                <p>Thanks for you order</p>
                <p>Enjoy!</p>
            </div>
        </div>
    </div>
    <div class="points_disclaimer">Your order has been placed and will be with you shortly.</div>
</section>
<footer>
    <a href="" class="green_button button">View My Rewards</a>
    <a href="" class="green_button button mt10">Place Another Order</a>
</footer>

<?php printFoot(); ?>

