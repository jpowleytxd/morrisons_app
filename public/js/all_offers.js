/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - All Offers Page Functions
 * ----------------------------------------------------------------
 */

// ----------------------------------------------------------------
// ------------------------Global Variables------------------------
// ----------------------------------------------------------------
var qrLink = "https://chart.apis.google.com/chart?cht=qr&chs=500x500&chl=";
var heartActive = false;

// ----------------------------------------------------------------
// ------------------------On Ready Function-----------------------
// ----------------------------------------------------------------

$(document).ready(function(){
    // Detect click on a tile action button
    $('.tile_outer').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        // Determine which action needs to occur
        var action = $(this).data('action')
        if(action === "offer"){
            // Call offer function
            openOffer($(this));
        } else if(action === "voucher"){
            // Call voucher function
            openVoucher($(this));
        }
    });

    // Detect click on the page buttons
    $('.all_offers_button').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        // Determine which action needs to occur
        var overlay = $(this).data('overlay');
        if(overlay === "offer"){
            // Close offer overlay
            closeAnimation($('#offer_overlay'));
        } else if(overlay === "voucher"){
            // Close voucher overlay
            closeAnimation($('#voucher_overlay'));
        }
    });

    // Detect click on heart icon
    $('.offer_heart').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        // Check if like currently being processed
        if(heartActive === false){
            // Call function
            likeOffer();
        }
    });
});

// ----------------------------------------------------------------
// --------------------------Page Functions------------------------
// ----------------------------------------------------------------

/**
 * openAnimation()
 * @desc - Sliding in from bottom animation
 * @param {Object} object       - Object to be animated in
 */
function openAnimation(object){
    // Restrict the body
    $('body').css({
        "overflow": "hidden"
    });

    // Execute animation
    TweenMax.to($(object), 0.5, {
        top: '0',
        ease: 'ease-in'
    });
}

/**
 * closeAnimation()
 * @desc - Sliding out to the bottom animation
 * @param {Object} object       - Object to be animated out
 */
function closeAnimation(object){
    TweenMax.to($(object), 0.5, {
        top: '100vh',
        ease: 'ease-in',
        onComplete: function(){
            $('body').css({
                "overflow": "auto"
            });
        }
    });
}

/**
 * openOffer()
 * @desc - Submits the hidden form to the individual offer page
 * @param {Object} tile     - Tile related to the action
 */
function openOffer(tile){
    // Push required data into the offer overlay
    $('.offer_title').html($(tile).data('title'));
    $('.offer_heart').data("id", $(tile).data('id'));
    
    // GET liked status of the offer
    var liked = $(tile).data('liked');
    if(liked === "true" || liked === true){
        $('.offer_heart').addClass('liked');
    } else{
        $('.offer_heart').removeClass('liked');
    }

    // Push in background
    $('.offer_pane').css({
        "background-image": "url('" + $(tile).data('background') + "')"
    });

    // Animate overlay
    openAnimation($('#offer_overlay'));
}

/**
 * likeOffer()
 * @desc - Using AJAX to store offer ID against a user
 */
function likeOffer(){
    // GET details required
    var offerID = $('.offer_heart').data('id');
    var offerStatus;
    if($('.offer_heart').hasClass('liked')){
        offerStatus = "REMOVE";
    } else{
        offerStatus = "APPLY";
    }

    // Set heartActive
    heartActive = true;

    var dataToSend = {
        "offer_id": offerID,
        "status": offerStatus,
        "customer": cardnumber
    }
    dataToSend = JSON.stringify(dataToSend);

    // Use AJAX for functionality
    $.ajax({
        type: "POST",
        url: "../processes/like_offer.php",
        data: {
            values: dataToSend
        },
        error: function(){}, 
        success: function(data){
            console.log(data);
            
            // Reset heartActive
            heartActive = false;
            
            // Parse the response
            if(data === "success"){
                // Adjust the heart views
                if(offerStatus === "APPLY"){
                    // Add class to big heart
                    $('.offer_heart').addClass('liked');

                    // Adjust liked data attribute on parent tile
                    $('#offer_' + offerID).data('liked', "true");

                    // Add class to checked corners
                    $('#offer_' + offerID + ' #checked_corner').removeClass('hidden');
                    $('#offer_' + offerID + ' #unchecked_corner').addClass('hidden');
                } else{
                    // Remove class to big heart
                    $('.offer_heart').removeClass('liked');

                    // Adjust liked data attribute on parent tile
                    $('#offer_' + offerID).data('liked', "false");

                    // Add class to checked corners
                    $('#offer_' + offerID + ' #unchecked_corner').removeClass('hidden');
                    $('#offer_' + offerID + ' #checked_corner').addClass('hidden');
                }
            } else{
                // Failing response -> 
            }
        }
    })
}

/**
 * openVoucher()
 * @desc - Opens the voucher overlay
 * @param {Object} tile     - Tile related to the action
 */
function openVoucher(tile){
    // GET details required
    var code = $(tile).data('code');
    var title = $(tile).data('title');

    // Push values into QR page
    $('.voucher_image').attr('src', qrLink + code);
    $('.voucher_code').html(code);
    $('.voucher_title').html(title);

    // Animate overlay
    openAnimation($('#voucher_overlay'));
}