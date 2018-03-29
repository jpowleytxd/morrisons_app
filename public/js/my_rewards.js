/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - My Rewards Page Functions
 * ----------------------------------------------------------------
 */

// ----------------------------------------------------------------
// ------------------------On Ready Function-----------------------
// ----------------------------------------------------------------

$(document).ready(function(){
    // Detect click on main page
    $('.points_tile').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();

        // GET points from this tile 
        var tilePoints = parseInt($(this).data('points'));

        // Check enough points have been earned
        if(points >= tilePoints){
            // Get title from this tile
            var title = $(this).data('title');
            openCard(title);
        }
    });

    // Detect click on the page buttons
    $('.all_offers_button').on('click', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        // Close voucher overlay
        closeAnimation($('#voucher_overlay'));
    });
});

// ----------------------------------------------------------------
// -----------------------Window Load Function---------------------
// ----------------------------------------------------------------

window.onload = function onLoad() {
    // Initiate 250 Points loader
    var circle250 = new ProgressBar.Circle('#progress250', {
        color: '#00563F',
        trailColor: 'rgba(255, 193, 13, 0.5)',
        duration: 3000,
        easing: 'bounce',
        strokeWidth: 12,
        trailWidth: 12
    });

    // Initiate 500 Points loader
    var circle500 = new ProgressBar.Circle('#progress500', {
        color: '#00563F',
        trailColor: 'rgba(255, 193, 13, 0.5)',
        duration: 3000,
        easing: 'bounce',
        strokeWidth: 12,
        trailWidth: 12
    });

    // Initiate 750 Points loader
    var circle750 = new ProgressBar.Circle('#progress750', {
        color: '#00563F',
        trailColor: 'rgba(255, 193, 13, 0.5)',
        duration: 3000,
        easing: 'bounce',
        strokeWidth: 12,
        trailWidth: 12
    });

    // Animate after delays
    circle250.animate(perc250);
    setTimeout(function(){
        circle500.animate(perc500);
    }, 250);
    setTimeout(function(){
        circle750.animate(perc750);
    }, 500);
};

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
 * openCard()
 * @desc - Opens up the card QR page
 * @param {String} title        - Title for page
 */
function openCard(title){
    // Push value into page
    $('.voucher_title').html(title);

    openAnimation($('#voucher_overlay'));
}