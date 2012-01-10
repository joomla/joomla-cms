<?php
//error_reporting(0);
//06/06/2010 10.32
//echo session_id();
$sid = $_GET['c'];
session_id($sid);
session_start();
$istanza=$_GET['i'];
$code=$_SESSION['digit'][$istanza];
$crypt = $_GET['x'];
 switch ($crypt) {
				case '0' :
					// no
					$rndstring=$code;
					
					break;
				case '1' :
					// blow
					require_once 'blow.php';
					$obj = new MyBlowfish("ALIKON_FIGHT_SPAM") ;          
          $rndstring = $obj->decryptString( $code ) ;
					break;	
				case '2' :
					// aes for php 4 users configuration issue	
					/* 
					include_once('AES.class.php');
          $key256 = '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4';	
          $Cipher = new AES(AES::AES256);        
          $content = $Cipher->decrypt($code, $key256);
          $rndstring = $Cipher->hexToString($content);
					break;		
					*/
					$z = "abcdefgh01234567"; // 128-bit key
					include_once('AES4.class.php');
					$aes = new AES($z);
					$rndstring=$aes->decrypt($code);
					break;		
					
      }
//
$length = 8;


$font='media/arial.ttf';


/* output type */

$output_type='jpeg';
#$output_type='png';
//--


/* font size range, angle range, character padding */

$min_font_size = 14;
$max_font_size = 20;

$min_angle = -20;
$max_angle = 20;
$char_padding = 1;


/* initialize variables  */
$turing_string='';
$data = array();
$image_width = $image_height = 0;


/* build the data array of the characters, size, placement, etc. */

for($i=0; $i<$length; $i++) {

    $char = substr($rndstring, $i, 1);
    
    $size = mt_rand($min_font_size, $max_font_size);
    $angle = mt_rand($min_angle, $max_angle);

    $bbox = ImageTTFBBox( $size, $angle, $font, $char );

    $char_width = max($bbox[2],$bbox[4]) - min($bbox[0],$bbox[6]);
    $char_height = max($bbox[1],$bbox[3]) - min($bbox[7],$bbox[5]);

    $image_width += $char_width + $char_padding;
    $image_height = max($image_height, $char_height);

    $data[] = array(
        'char'        => $char,
        'size'        => $size,
        'angle'       => $angle,
        'height'      => $char_height,
        'width'       => $char_width,
    );
}

/* calculate the final image size, adding some padding */

$x_padding = 12;

$image_width += ($x_padding * 1);
$image_height = ($image_height * 1.5) + 2;


/* build the image, and allocte the colors  */

$im = ImageCreate($image_width, $image_height);

$r = 51 * mt_rand(4,5);
$g = 51 * mt_rand(4,5);
$b = 51 * mt_rand(4,5);
$color_bg        = ImageColorAllocate($im,  $r,  $g,  $b );

$r = 51 * mt_rand(3,4);
$g = 51 * mt_rand(3,4);
$b = 51 * mt_rand(3,4);
$color_line0    = ImageColorAllocate($im,  $r,  $g,  $b );

$r = 51 * mt_rand(3,4);
$g = 51 * mt_rand(3,4);
$b = 51 * mt_rand(3,4);
$color_line1    = ImageColorAllocate($im,  $r,  $g,  $b );

$r = 51 * mt_rand(1,2);
$g = 51 * mt_rand(1,2);
$b = 51 * mt_rand(1,2);
$color_text        = ImageColorAllocate($im,  $r,  $g,  $b );

$color_border    = ImageColorAllocate($im,   0,   0,   0 );


/* make the random background lines */

for($l=0; $l<10; $l++) {

    $c = 'color_line' . ($l%2);

    $lx = mt_rand(0,$image_width+$image_height);
    $lw = mt_rand(0,3);
    if ($lx > $image_width) {
        $lx -= $image_width;
        ImageFilledRectangle($im, 0, $lx, $image_width-1, $lx+$lw, $$c );
    } else {
        ImageFilledRectangle($im, $lx, 0, $lx+$lw, $image_height-1, $$c );
    }

}
/* output each character */
$generatecode='';
$pos_x = $x_padding + ($char_padding / 2);
foreach($data as $d) {

    $pos_y = ( ( $image_height + $d['height'] ) / 2 );
    ImageTTFText($im, $d['size'], $d['angle'], $pos_x, $pos_y, $color_text, $font, $d['char'] );

    $pos_x += $d['width'] + $char_padding;
    
    $generatecode=$generatecode.$d['char'];

}


/* a nice border */

ImageRectangle($im, 0, 0, $image_width-1, $image_height-1, $color_border);
/* for rotate an image of x degrees */
//$im=imagerotate($im, 160, 0);



/* write it */

ob_start();
header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
header('Content-type: image/jpeg');
//ImagePNG($im);
ImageJPEG($im);
/* free memory  */
ImageDEstroy($im);
ob_end_flush();


?>