<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage.php<br />
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or any later version.<br /><br />
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.<br /><br />
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br /><br />
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2007 Drew Phillips
 * @author drew010 <drew@drew-phillips.com>
 * @version 1.0.3.1 (March 24, 2008)
 * @package Securimage
 *
 */

/**
  ChangeLog

  1.0.3.1
  - Error reading from wordlist in some cases caused words to be cut off 1 letter short

  1.0.3
  - Removed shadow_text from code which could cause an undefined property error due to removal from previous version

  1.0.2
  - Audible CAPTCHA Code wav files
  - Create codes from a word list instead of random strings

  1.0
  - Added the ability to use a selected character set, rather than a-z0-9 only.
  - Added the multi-color text option to use different colors for each letter.
  - Switched to automatic session handling instead of using files for code storage
  - Added GD Font support if ttf support is not available.  Can use internal GD fonts or load new ones.
  - Added the ability to set line thickness
  - Added option for drawing arced lines over letters
  - Added ability to choose image type for output

*/

/**
 * Output images in JPEG format
 */
define('SI_IMAGE_JPEG', 1);
/**
 * Output images in PNG format
 */
define('SI_IMAGE_PNG',  2);
/**
 * Output images in GIF format
 * Must have GD >= 2.0.28!
 */
define('SI_IMAGE_GIF',  3);

/**
 * Securimage CAPTCHA Class.
 *
 * @package    Securimage
 * @subpackage classes
 *
 */
class Securimage {

  /**
   * The desired width of the CAPTCHA image.
   *
   * @var int
   */
  var $image_width = 175;

  /**
   * The desired width of the CAPTCHA image.
   *
   * @var int
   */
  var $image_height = 45;

  /**
   * The image format for output.<br />
   * Valid options: SI_IMAGE_PNG, SI_IMAGE_JPG, SI_IMAGE_GIF
   *
   * @var int
   */
  var $image_type = SI_IMAGE_PNG;

  /**
   * The length of the code to generate.
   *
   * @var int
   */
  var $code_length = 4;

  /**
   * The character set for individual characters in the image.<br />
   * Letters are converted to uppercase.<br />
   * The font must support the letters or there may be problematic substitutions.
   *
   * @var string
   */
  var $charset = 'ABCDEFGHKLMNPRSTUVWYZ23456789';
  //var $charset = '0123456789';

  /**
   * Create codes using this word list
   *
   * @var string  The path to the word list to use for creating CAPTCHA codes
   */
  var $wordlist_file = '../words/words.txt';

  /**
   * True to use a word list file instead of a random code
   *
   * @var bool
   */
  var $use_wordlist  = true;

  /**
   * Whether to use a GD font instead of a TTF font.<br />
   * TTF offers more support and options, but use this if your PHP doesn't support TTF.<br />
   *
   * @var boolean
   */
  var $use_gd_font = false;

  /**
   * The GD font to use.<br />
   * Internal gd fonts can be loaded by their number.<br />
   * Alternatively, a file path can be given and the font will be loaded from file.
   *
   * @var mixed
   */
  var $gd_font_file = 'gdfonts/bubblebath.gdf';

  /**
   * The approximate size of the font in pixels.<br />
   * This does not control the size of the font because that is determined by the GD font itself.<br />
   * This is used to aid the calculations of positioning used by this class.<br />
   *
   * @var int
   */
  var $gd_font_size = 20;

  // Note: These font options below do not apply if you set $use_gd_font to true with the exception of $text_color

  /**
   * The path to the TTF font file to load.
   *
   * @var string
   */
  var $ttf_file = "./elephant.ttf";

  /**
   * The font size.<br />
   * Depending on your version of GD, this should be specified as the pixel size (GD1) or point size (GD2)<br />
   *
   * @var int
   */
  var $font_size = 24;

  /**
   * The minimum angle in degrees, with 0 degrees being left-to-right reading text.<br />
   * Higher values represent a counter-clockwise rotation.<br />
   * For example, a value of 90 would result in bottom-to-top reading text.
   *
   * @var int
   */
  var $text_angle_minimum = -20;

  /**
   * The minimum angle in degrees, with 0 degrees being left-to-right reading text.<br />
   * Higher values represent a counter-clockwise rotation.<br />
   * For example, a value of 90 would result in bottom-to-top reading text.
   *
   * @var int
   */
  var $text_angle_maximum = 20;

  /**
   * The X-Position on the image where letter drawing will begin.<br />
   * This value is in pixels from the left side of the image.
   *
   * @var int
   */
  var $text_x_start = 8;

  /**
   * Letters can be spaced apart at random distances.<br />
   * This is the minimum distance between two letters.<br />
   * This should be <i>at least</i> as wide as a font character.<br />
   * Small values can cause letters to be drawn over eachother.<br />
   *
   * @var int
   */
  var $text_minimum_distance = 30;

  /**
   * Letters can be spaced apart at random distances.<br />
   * This is the maximum distance between two letters.<br />
   * This should be <i>at least</i> as wide as a font character.<br />
   * Small values can cause letters to be drawn over eachother.<br />
   *
   * @var int
   */
  var $text_maximum_distance = 33;

  /**
   * The background color for the image.<br />
   * This should be specified in HTML hex format.<br />
   * Make sure to include the preceding # sign!
   *
   * @var string
   */
  var $image_bg_color = "#e3daed";

  /**
   * The text color to use for drawing characters.<br />
   * This value is ignored if $use_multi_text is set to true.<br />
   * Make sure this contrasts well with the background color.<br />
   * Specify the color in HTML hex format with preceding # sign
   *
   * @see Securimage::$use_multi_text
   * @var string
   */
  var $text_color = "#ff0000";

  /**
   * Set to true to use multiple colors for each character.
   *
   * @see Securimage::$multi_text_color
   * @var boolean
   */
  var $use_multi_text = true;

  /**
   * String of HTML hex colors to use.<br />
   * Separate each possible color with commas.<br />
   * Be sure to precede each value with the # sign.
   *
   * @var string
   */
  var $multi_text_color = "#0a68dd,#f65c47,#8d32fd";

  /**
   * Set to true to make the characters appear transparent.
   *
   * @see Securimage::$text_transparency_percentage
   * @var boolean
   */
  var $use_transparent_text = true;

  /**
   * The percentage of transparency, 0 to 100.<br />
   * A value of 0 is completely opaque, 100 is completely transparent (invisble)
   *
   * @see Securimage::$use_transparent_text
   * @var int
   */
  var $text_transparency_percentage = 15;


  // Line options
  /**
   * Draw vertical and horizontal lines on the image.
   *
   * @see Securimage::$line_color
   * @see Securimage::$line_distance
   * @see Securimage::$line_thickness
   * @see Securimage::$draw_lines_over_text
   * @var boolean
   */
  var $draw_lines = true;

  /**
   * The color of the lines drawn on the image.<br />
   * Use HTML hex format with preceding # sign.
   *
   * @see Securimage::$draw_lines
   * @var string
   */
  var $line_color = "#80BFFF";

  /**
   * How far apart to space the lines from eachother in pixels.
   *
   * @see Securimage::$draw_lines
   * @var int
   */
  var $line_distance = 5;

  /**
   * How thick to draw the lines in pixels.<br />
   * 1-3 is ideal depending on distance
   *
   * @see Securimage::$draw_lines
   * @see Securimage::$line_distance
   * @var int
   */
  var $line_thickness = 1;

  /**
   * Set to true to draw angled lines on the image in addition to the horizontal and vertical lines.
   *
   * @see Securimage::$draw_lines
   * @var boolean
   */
  var $draw_angled_lines = false;

  /**
   * Draw the lines over the text.<br />
   * If fales lines will be drawn before putting the text on the image.<br />
   * This can make the image hard for humans to read depending on the line thickness and distance.
   *
   * @var boolean
   */
  var $draw_lines_over_text = false;

  /**
   * For added security, it is a good idea to draw arced lines over the letters to make it harder for bots to segment the letters.<br />
   * Two arced lines will be drawn over the text on each side of the image.<br />
   * This is currently expirimental and may be off in certain configurations.
   *
   * @var boolean
   */
  var $arc_linethrough = true;

  /**
   * The colors or color of the arced lines.<br />
   * Use HTML hex notation with preceding # sign, and separate each value with a comma.<br />
   * This should be similar to your font color for single color images.
   *
   * @var string
   */
  var $arc_line_colors = "#8080ff";

  /**
   * Full path to the WAV files to use to make the audio files, include trailing /.<br />
   * Name Files  [A-Z0-9].wav
   *
   * @since 1.0.1
   * @var string
   */
  var $audio_path = './audio/';


  //END USER CONFIGURATION
  //There should be no need to edit below unless you really know what you are doing.

  /**
   * The gd image resource.
   *
   * @access private
   * @var resource
   */
  var $im;

  /**
   * The background image resource
   *
   * @access private
   * @var resource
   */
  var $bgimg;

  /**
   * The code generated by the script
   *
   * @access private
   * @var string
   */
  var $code;

  /**
   * The code that was entered by the user
   *
   * @access private
   * @var string
   */
  var $code_entered;

  /**
   * Whether or not the correct code was entered
   *
   * @access private
   * @var boolean
   */
  var $correct_code;

  /**
   * Class constructor.<br />
   * Because the class uses sessions, this will attempt to start a session if there is no previous one.<br />
   * If you do not start a session before calling the class, the constructor must be called before any
   * output is sent to the browser.
   *
   * <code>
   *   $securimage = new Securimage();
   * </code>
   *
   */
  function Securimage()
  {
    if ( session_id() == '' ) { // no session has been started yet, which is needed for validation
      session_start();
    }
  }

  /**
   * Generate a code and output the image to the browser.
   *
   * <code>
   *   <?php
   *   include 'securimage.php';
   *   $securimage = new Securimage();
   *   $securimage->show('bg.jpg');
   *   ?>
   * </code>
   *
   * @param string $background_image  The path to an image to use as the background for the CAPTCHA
   */
  function show($background_image = "")
  {
    if($background_image != "" && is_readable($background_image)) {
      $this->bgimg = $background_image;
    }

    $this->doImage();
  }

  /**
   * Validate the code entered by the user.
   *
   * <code>
   *   $code = $_POST['code'];
   *   if ($securimage->check($code) == false) {
   *     die("Sorry, the code entered did not match.");
   *   } else {
   *     $valid = true;
   *   }
   * </code>
   * @param string $code  The code the user entered
   * @return boolean  true if the code was correct, false if not
   */
  function check($code)
  {
    $this->code_entered = $code;
    $this->validate();
    return $this->correct_code;
  }

  /**
   * Generate and output the image
   *
   * @access private
   *
   */
  function doImage()
  {
    if($this->use_transparent_text == true || $this->bgimg != "") {
      $this->im = imagecreatetruecolor($this->image_width, $this->image_height);
      $bgcolor = imagecolorallocate($this->im, hexdec(substr($this->image_bg_color, 1, 2)), hexdec(substr($this->image_bg_color, 3, 2)), hexdec(substr($this->image_bg_color, 5, 2)));
      imagefilledrectangle($this->im, 0, 0, imagesx($this->im), imagesy($this->im), $bgcolor);
    } else { //no transparency
      $this->im = imagecreate($this->image_width, $this->image_height);
      $bgcolor = imagecolorallocate($this->im, hexdec(substr($this->image_bg_color, 1, 2)), hexdec(substr($this->image_bg_color, 3, 2)), hexdec(substr($this->image_bg_color, 5, 2)));
    }

    if($this->bgimg != "") { $this->setBackground(); }

    $this->createCode();

    if (!$this->draw_lines_over_text && $this->draw_lines) $this->drawLines();

    $this->drawWord();

    if ($this->arc_linethrough == true) $this->arcLines();

    if ($this->draw_lines_over_text && $this->draw_lines) $this->drawLines();

    $this->output();

  }

  /**
   * Set the background of the CAPTCHA image
   *
   * @access private
   *
   */
  function setBackground()
  {
    $dat = @getimagesize($this->bgimg);
    if($dat == false) { return; }

    switch($dat[2]) {
      case 1:  $newim = @imagecreatefromgif($this->bgimg); break;
      case 2:  $newim = @imagecreatefromjpeg($this->bgimg); break;
      case 3:  $newim = @imagecreatefrompng($this->bgimg); break;
      case 15: $newim = @imagecreatefromwbmp($this->bgimg); break;
      case 16: $newim = @imagecreatefromxbm($this->bgimg); break;
      default: return;
    }

    if(!$newim) return;

    imagecopy($this->im, $newim, 0, 0, 0, 0, $this->image_width, $this->image_height);
  }

  /**
   * Draw arced lines over the text
   *
   * @access private
   *
   */
  function arcLines()
  {
    $colors = explode(',', $this->arc_line_colors);
    imagesetthickness($this->im, 3);

    $color = $colors[rand(0, sizeof($colors) - 1)];
    $linecolor = imagecolorallocate($this->im, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));

    $xpos   = $this->text_x_start + ($this->font_size * 2) + rand(-5, 5);
    $width  = $this->image_width / 2.66 + rand(3, 10);
    $height = $this->font_size * 2.14 - rand(3, 10);

    if ( rand(0,100) % 2 == 0 ) {
      $start = rand(0,66);
      $ypos  = $this->image_height / 2 - rand(5, 15);
      $xpos += rand(5, 15);
    } else {
      $start = rand(180, 246);
      $ypos  = $this->image_height / 2 + rand(5, 15);
    }

    $end = $start + rand(75, 110);

    imagearc($this->im, $xpos, $ypos, $width, $height, $start, $end, $linecolor);

    $color = $colors[rand(0, sizeof($colors) - 1)];
    $linecolor = imagecolorallocate($this->im, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));

    if ( rand(1,75) % 2 == 0 ) {
      $start = rand(45, 111);
      $ypos  = $this->image_height / 2 - rand(5, 15);
      $xpos += rand(5, 15);
    } else {
      $start = rand(200, 250);
      $ypos  = $this->image_height / 2 + rand(5, 15);
    }

    $end = $start + rand(75, 100);

    imagearc($this->im, $this->image_width * .75, $ypos, $width, $height, $start, $end, $linecolor);
  }

  /**
   * Draw lines on the image
   *
   * @access private
   *
   */
  function drawLines()
  {
    $linecolor = imagecolorallocate($this->im, hexdec(substr($this->line_color, 1, 2)), hexdec(substr($this->line_color, 3, 2)), hexdec(substr($this->line_color, 5, 2)));
    imagesetthickness($this->im, $this->line_thickness);

    //vertical lines
    for($x = 1; $x < $this->image_width; $x += $this->line_distance) {
      imageline($this->im, $x, 0, $x, $this->image_height, $linecolor);
    }

    //horizontal lines
    for($y = 11; $y < $this->image_height; $y += $this->line_distance) {
      imageline($this->im, 0, $y, $this->image_width, $y, $linecolor);
    }

    if ($this->draw_angled_lines == true) {
      for ($x = -($this->image_height); $x < $this->image_width; $x += $this->line_distance) {
        imageline($this->im, $x, 0, $x + $this->image_height, $this->image_height, $linecolor);
      }

      for ($x = $this->image_width + $this->image_height; $x > 0; $x -= $this->line_distance) {
        imageline($this->im, $x, 0, $x - $this->image_height, $this->image_height, $linecolor);
      }
    }
  }

  /**
   * Draw the CAPTCHA code over the image
   *
   * @access private
   *
   */
  function drawWord()
  {
    if ($this->use_gd_font == true) {
      if (!is_int($this->gd_font_file)) { //is a file name
        $font = @imageloadfont($this->gd_font_file);
        if ($font == false) {
          trigger_error("Failed to load GD Font file {$this->gd_font_file} ", E_USER_WARNING);
          return;
        }
      } else { //gd font identifier
        $font = $this->gd_font_file;
      }

      $color = imagecolorallocate($this->im, hexdec(substr($this->text_color, 1, 2)), hexdec(substr($this->text_color, 3, 2)), hexdec(substr($this->text_color, 5, 2)));
      imagestring($this->im, $font, $this->text_x_start, ($this->image_height / 2) - ($this->gd_font_size / 2), $this->code, $color);

    } else { //ttf font
      if($this->use_transparent_text == true) {
        $alpha = intval($this->text_transparency_percentage / 100 * 127);
        $font_color = imagecolorallocatealpha($this->im, hexdec(substr($this->text_color, 1, 2)), hexdec(substr($this->text_color, 3, 2)), hexdec(substr($this->text_color, 5, 2)), $alpha);
      } else { //no transparency
        $font_color = imagecolorallocate($this->im, hexdec(substr($this->text_color, 1, 2)), hexdec(substr($this->text_color, 3, 2)), hexdec(substr($this->text_color, 5, 2)));
      }

      $x = $this->text_x_start;
      $strlen = strlen($this->code);
      $y_min = ($this->image_height / 2) + ($this->font_size / 2) - 2;
      $y_max = ($this->image_height / 2) + ($this->font_size / 2) + 2;
      $colors = explode(',', $this->multi_text_color);

      for($i = 0; $i < $strlen; ++$i) {
        $angle = rand($this->text_angle_minimum, $this->text_angle_maximum);
        $y = rand($y_min, $y_max);
        if ($this->use_multi_text == true) {
          $idx = rand(0, sizeof($colors) - 1);
          $r = substr($colors[$idx], 1, 2);
          $g = substr($colors[$idx], 3, 2);
          $b = substr($colors[$idx], 5, 2);
          if($this->use_transparent_text == true) {
            $font_color = imagecolorallocatealpha($this->im, "0x$r", "0x$g", "0x$b", $alpha);
          } else {
            $font_color = imagecolorallocate($this->im, "0x$r", "0x$g", "0x$b");
          }
        }
        imagettftext($this->im, $this->font_size, $angle, $x, $y, $font_color, $this->ttf_file, $this->code{$i});

        $x += rand($this->text_minimum_distance, $this->text_maximum_distance);
      } //for loop
    } //else ttf font
  } //function

  /**
   * Create a code and save to the session
   *
   * @since 1.0.1
   *
   */
  function createCode()
  {
    $this->code = false;

    if ($this->use_wordlist && is_readable($this->wordlist_file)) {
      $this->code = $this->readCodeFromFile();
    }

    if ($this->code == false) {
      $this->code = $this->generateCode($this->code_length);
    }

    $this->saveData();
  }

  /**
   * Generate a code
   *
   * @access private
   * @param int $len  The code length
   * @return string
   */
  function generateCode($len)
  {
    $code = '';

    for($i = 1, $cslen = strlen($this->charset); $i <= $len; ++$i) {
      $code .= strtoupper( $this->charset{rand(0, $cslen - 1)} );
    }
    return $code;
  }

  /**
   * Reads a word list file to get a code
   *
   * @access private
   * @since 1.0.2
   * @return mixed  false on failure, a word on success
   */
  function readCodeFromFile()
  {
    $fp = @fopen($this->wordlist_file, 'rb');
    if (!$fp) return false;

    $fsize = filesize($this->wordlist_file);
    if ($fsize < 32) return false; // too small of a list to be effective

    if ($fsize < 128) {
      $max = $fsize; // still pretty small but changes the range of seeking
    } else {
      $max = 128;
    }

    fseek($fp, rand(0, $fsize - $max), SEEK_SET);
    $data = fread($fp, 128); // read a random 128 bytes from file
    fclose($fp);
    $data = preg_replace("/\r?\n/", "\n", $data);

    $start = strpos($data, "\n", rand(0, 100)) + 1; // random start position
    $end   = strpos($data, "\n", $start);           // find end of word

    return strtolower(substr($data, $start, $end - $start)); // return substring in 128 bytes
  }

  /**
   * Output image to the browser
   *
   * @access private
   *
   */
  function output()
  {
    header("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    switch($this->image_type)
    {
      case SI_IMAGE_JPEG:
        header("Content-Type: image/jpeg");
        imagejpeg($this->im, null, 90);
        break;

      case SI_IMAGE_GIF:
        header("Content-Type: image/gif");
        imagegif($this->im);
        break;

      default:
        header("Content-Type: image/png");
        imagepng($this->im);
        break;
    }

    imagedestroy($this->im);
  }

  /**
   * Get WAV file data of the spoken code.<br />
   * This is appropriate for output to the browser as audio/x-wav
   *
   * @since 1.0.1
   * @return string  WAV data
   *
   */
  function getAudibleCode()
  {
    $letters = array();
    $code    = $this->getCode();

    if ($code == '') {
      $this->createCode();
      $code = $this->getCode();
    }

    for($i = 0; $i < strlen($code); ++$i) {
      $letters[] = $code{$i};
    }

    return $this->generateWAV($letters);
  }

  /**
   * Save the code in the session
   *
   * @access private
   *
   */
  function saveData()
  {
    $_SESSION['securimage_code_value'] = strtolower($this->code);
  }

  /**
   * Validate the code to the user code
   *
   * @access private
   *
   */
  function validate()
  {
    if ( isset($_SESSION['securimage_code_value']) && !empty($_SESSION['securimage_code_value']) ) {
      if ( $_SESSION['securimage_code_value'] == strtolower(trim($this->code_entered)) ) {
        $this->correct_code = true;
        $_SESSION['securimage_code_value'] = '';
      } else {
        $this->correct_code = false;
      }
    } else {
      $this->correct_code = false;
    }
  }

  /**
   * Get the captcha code
   *
   * @since 1.0.1
   * @return string
   */
  function getCode()
  {
    if (isset($_SESSION['securimage_code_value']) && !empty($_SESSION['securimage_code_value'])) {
      return $_SESSION['securimage_code_value'];
    } else {
      return '';
    }
  }

  /**
   * Check if the user entered code was correct
   *
   * @access private
   * @return boolean
   */
  function checkCode()
  {
    return $this->correct_code;
  }

  /**
   * Generate a wav file by concatenating individual files
   * @since 1.0.1
   * @access private
   * @param array $letters  Array of letters to build a file from
   * @return string  WAV file data
   */
  function generateWAV($letters)
  {
    $first = true; // use first file to write the header...
    $data_len    = 0;
    $files       = array();
    $out_data    = '';

    foreach ($letters as $letter) {
      $filename = $this->audio_path . strtoupper($letter) . '.wav';

      $fp = fopen($filename, 'rb');

      $file = array();

      $data = fread($fp, filesize($filename)); // read file in

      $header = substr($data, 0, 36);
      $body   = substr($data, 44);


      $data = unpack('NChunkID/VChunkSize/NFormat/NSubChunk1ID/VSubChunk1Size/vAudioFormat/vNumChannels/VSampleRate/VByteRate/vBlockAlign/vBitsPerSample', $header);

      $file['sub_chunk1_id']   = $data['SubChunk1ID'];
      $file['bits_per_sample'] = $data['BitsPerSample'];
      $file['channels']        = $data['NumChannels'];
      $file['format']          = $data['AudioFormat'];
      $file['sample_rate']     = $data['SampleRate'];
      $file['size']            = $data['ChunkSize'] + 8;
      $file['data']            = $body;

      if ( ($p = strpos($file['data'], 'LIST')) !== false) {
        // If the LIST data is not at the end of the file, this will probably break your sound file
        $info         = substr($file['data'], $p + 4, 8);
        $data         = unpack('Vlength/Vjunk', $info);
        $file['data'] = substr($file['data'], 0, $p);
        $file['size'] = $file['size'] - (strlen($file['data']) - $p);
      }

      $files[] = $file;
      $data    = null;
      $header  = null;
      $body    = null;

      $data_len += strlen($file['data']);

      fclose($fp);
    }

    $out_data = '';
    for($i = 0; $i < sizeof($files); ++$i) {
      if ($i == 0) { // output header
        $out_data .= pack('C4VC8', ord('R'), ord('I'), ord('F'), ord('F'), $data_len + 36, ord('W'), ord('A'), ord('V'), ord('E'), ord('f'), ord('m'), ord('t'), ord(' '));

        $out_data .= pack('VvvVVvv',
                          16,
                          $files[$i]['format'],
                          $files[$i]['channels'],
                          $files[$i]['sample_rate'],
                          $files[$i]['sample_rate'] * (($files[$i]['bits_per_sample'] * $files[$i]['channels']) / 8),
                          ($files[$i]['bits_per_sample'] * $files[$i]['channels']) / 8,
                          $files[$i]['bits_per_sample'] );

        $out_data .= pack('C4', ord('d'), ord('a'), ord('t'), ord('a'));

        $out_data .= pack('V', $data_len);
      }

      $out_data .= $files[$i]['data'];
    }

    return $out_data;
  }
} /* class Securimage */

?>
