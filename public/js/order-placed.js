/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - Order Placed Page Functions
 * ----------------------------------------------------------------
 */

// ----------------------------------------------------------------
// ------------------------On Ready Function-----------------------
// ----------------------------------------------------------------

$(document).ready(function(){
    // Detect click on place another order button
    $('#place_order').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        var type = deviceType.toLowerCase();

        // Search for substring -> iphone
        if((type.indexOf("iphone") >= 0)){
            // iPhone callback needed
            alert('iPhone callback');
        } else if((type.indexOf("android") >= 0)){
            // Android callback needed
            alert('Android callback');
        }
    });

    // Detect click on view my rewards
    $('#my_rewards_button').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        // Submit form and leave page
        $('#post_details').submit();
    });
});