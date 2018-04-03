<?php
/**
 * ----------------------------------------------------------------
 * Morrisons Bespoke App - Genric HTML Partials
 * ----------------------------------------------------------------
 * @desc - Various page sections printed using functions
 */

/**
 * printHead()
 * @desc - Prints out the page head with inputted title
 * @param {String} $title       - Page title
 * @param {String} $body        - ID to be placed on body
 */
function printHead($title, $body){
    ?>
    <!DOCTYPE html>
    <!--[if lte IE 6]><html class="preIE7 preIE8 preIE9"><![endif]-->
    <!--[if IE 7]><html class="preIE8 preIE9"><![endif]-->
    <!--[if IE 8]><html class="preIE9"><![endif]-->
    <!--[if gte IE 9]><!--><html><!--<![endif]-->
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,initial-scale=1">

        <meta name="author" content="Zonal Marketing Technologies">
        <meta name="description" content="Morrisons app bespoke pages">
        <meta name="keywords" content="morrisons,app,zonal,loyalty">

        <title><?php echo $title; ?></title>
        
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,900" rel="stylesheet">
        <link href="css/main.css?v=1" rel="stylesheet" type="text/css">

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.19.1/TweenMax.min.js"></script>
        <script src="/js/<?php echo $body;?>.js?v=1" type="text/javascript"></script>

    </head>
    <body id="<?php echo $body; ?>">
    <div id="wrapper">
    <?php
}

/**
 * printFoot()
 * @desc - Prints out the page foot
 */
function printFoot(){
    ?>
    </div>
    </body>
    </html>
    <?php
}

?>