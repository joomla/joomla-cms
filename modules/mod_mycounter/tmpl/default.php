<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<script type="text/javascript" src="modules/mod_mycounter/tmpl/jquery.min.js"></script>
<p></p>
<style type="text/css">
    #countercontainer{width:100%;}
    #counter{width:225px;height:55px;margin:0 auto;}
    .counterdigit{float:left;background:url("<?php echo JURI::base()."modules/mod_mycounter/tmpl/img/filmstrip.png";?>") 0px 0px no-repeat;width:27px;height:52px}
    .counterseperator{float:left;background:url("<?php echo JURI::base()."modules/mod_mycounter/tmpl/img/comma.png";?>") 0px 35px no-repeat;width:8px;height:52px}
    #counter_copyright{text-align: center; margin:0 auto;}
</style>

<div id="counter_container">
    <div id="counter">
        <!--<li id="d10"></li>
        <div class="counterseperator"></div>
        <div class="counterdigit" id="d9"></div>
        <div class="counterdigit" id="d8"></div>-->
        <div class="counterdigit" id="d7"></div>
        <div class="counterseperator"></div>
        <div class="counterdigit" id="d5"></div>
        <div class="counterdigit" id="d4"></div>
        <div class="counterdigit" id="d3"></div>
        <div class="counterseperator"></div>
        <div class="counterdigit" id="d2"></div>
        <div class="counterdigit" id="d1"></div>
        <div class="counterdigit" id="d0"></div>
    </div>
    <div id="counter_copyright">
        <p style="font-size: smaller;">© 2011 by <a href="http://gpiutmd.iut.ac.ir/">GPIUTMD</a></p>
    </div>
</div>

<script type="text/javascript">
// Array to hold each digit's starting background-position Y value
var initialPos = [0, -312, -624, -936, -1248, -1560, -1872, -2184, -2496, -2808];
// Amination frames
var animationFrames = 5;
// Frame shift
var frameShift = 52;
// Starting number
<?php
    $db    = JFactory::getDbo();
    $query= $db->getQuery(true);
    $query->clear();
    $query->select('SUM(hits) AS count_hits');
    $query->from('#__content');
    $query->where('state = 1');
    $db->setQuery($query);
    $hits = $db->loadResult();
?>;
var theNumber = <?php echo $hits ?>;
// Increment
var increment = <?php echo $params->get( 'increment', '1' ); ?>;
// Pace of counting in milliseconds
var pace = <?php echo $params->get( 'interval', '1000000' ); ?>;

// Initializing variables
var digitsOld = [], digitsNew = [], subStart, subEnd, x, y;

// Function that controls counting
function doCount(){
    var x = theNumber.toString();
    theNumber += increment;
    var y = theNumber.toString();
    digitCheck(x,y);
}

// This checks the old count value vs. new value, to determine how many digits
// have changed and need to be animated.
function digitCheck(x,y){
    var digitsOld = splitToArray(x),
    digitsNew = splitToArray(y);
    for (var i = 0, c = digitsNew.length; i < c; i++){
        if (digitsNew[i] != digitsOld[i]){
            animateDigit(i, digitsOld[i], digitsNew[i]);
        }
    }
}

// Animation function
function animateDigit(n, oldDigit, newDigit){
    // I want three different animations speeds based on the digit,
    // because the pace and increment is so high. If it was counting
    // slower, just one speed would do.
    // 1: Changes so fast is just like a blur
    // 2: You can see complete animation, barely
    // 3: Nice and slow
    var speed;
    switch (n){
        case 0:
            speed = pace/8;
            break;
        case 1:
            speed = pace/4;
            break;
        default:
            speed = pace/2;
            break;
    }
    // Cap on slowest animation can go
    speed = (speed > 100) ? 100 : speed;
    // Get the initial Y value of background position to begin animation
    var pos = initialPos[oldDigit];
    // Each animation is 5 frames long, and 103px down the background image.
    // We delay each frame according to the speed we determined above.
    for (var k = 0; k < animationFrames; k++){
                if(oldDigit<newDigit)
                    pos = pos - frameShift;
                else
                    pos = pos + frameShift;
        if (k == (animationFrames - 1)){
            (jQuery)("#d" + n).delay(speed).animate({'background-position': '0 ' + pos + 'px'}, 0, function(){
                // At end of animation, shift position to new digit.
                (jQuery)("#d" + n).css({'background-position': '0 ' + initialPos[newDigit] + 'px'}, 0);
            });
        }
        else{
            (jQuery)("#d" + n).delay(speed).animate({'background-position': '0 ' + pos + 'px'}, 0);
        }
    }
}

// Splits each value into an array of digits
function splitToArray(input){
    var digits = new Array();
    for (var i = 0, c = input.length; i < c; i++){
        subStart = input.length - (i + 1);
        subEnd = input.length - i;
        digits[i] = input.substring(subStart, subEnd);
    }
    return digits;
}

// Sets the correct digits on load
function initialDigitCheck(initial){
    var digits = splitToArray(initial.toString());
    for (var i = 0, c = digits.length; i < c; i++){
        (jQuery)("#d" + i).css({'background-position': '0 ' + initialPos[digits[i]] + 'px'});
    }
}

jQuery.noConflict();

(function($) {
    initialDigitCheck(theNumber);
    doCount();
//    setInterval(doCount, pace);
})(jQuery);

</script>