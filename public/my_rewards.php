<?php

/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - My Rewards Page
 * ----------------------------------------------------------------
 * Routed to from iOrder rear menu
 * Requires:    - Cardnumber
 */

// Include required functions and models
include "partials/generic.php";
include "models/dbConn.php";
include "models/Loyalty.php";

?>

<?php printHead('My Rewards', "my_rewards"); ?>

<section>
    <header>
        <div class="upper_title">You currently have...</div>
        <div class="points_balance">1500 points</div>
        <div class="lower_title">Here's what you can get with your points</div>
    </header>
    <div class="points_tile_container">
        <div class="points_tile">
            <div class="tile_left">
                <div class="total_points">250 Points</div>
                <div class="points_reward">Redeem a FREE soft drink</div>
            </div>
            <div class="tile_right"> 
                <div id="myItem1" class="ldBar" style="width:50%; height:50%;margin:auto" data-value="35" data-preset="circle"></div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="points_tile">
            <div class="tile_left">
                <div class="total_points">500 Points</div>
                <div class="points_reward">Redeem a FREE hot drink</div>
            </div>
            <div class="tile_right">
                
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="points_tile">
            <div class="tile_left">
                <div class="total_points">750 Points</div>
                <div class="points_reward">Redeem a FREE snack</div>
            </div>
            <div class="tile_right">
                
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</section>

<script>
  var bar1 = new ldBar("#myItem1");
  var bar2 = document.getElementById('myItem1').ldBar;
  bar1.set(60);
</script>

<?php printFoot(); ?>