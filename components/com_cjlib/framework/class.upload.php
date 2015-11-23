<?php
// +------------------------------------------------------------------------+
// | class.upload.php                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) Colin Verot 2003-2014. All rights reserved.              |
// | Version       0.33dev                                                  |
// | Last modified 28/03/2014                                               |
// | Email         colin@verot.net                                          |
// | Web           http://www.verot.net                                     |
// +------------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify   |
// | it under the terms of the GNU General Public License version 2 as      |
// | published by the Free Software Foundation.                             |
// |                                                                        |
// | This program is distributed in the hope that it will be useful,        |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of         |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          |
// | GNU General Public License for more details.                           |
// |                                                                        |
// | You should have received a copy of the GNU General Public License      |
// | along with this program; if not, write to the                          |
// |   Free Software Foundation, Inc., 59 Temple Place, Suite 330,          |
// |   Boston, MA 02111-1307 USA                                            |
// |                                                                        |
// | Please give credit on sites that use class.upload and submit changes   |
// | of the script so other people can use them as well.                    |
// | This script is free to use, don't abuse.                               |
// +------------------------------------------------------------------------+
//

/**
 * Class upload
 *
 * @version   0.33dev
 * @author    Colin Verot <colin@verot.net>
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Colin Verot
 * @package   cmf
 * @subpackage external
 */

/**
 * Class upload
 *
 * <b>What does it do?</b>
 *
 * It manages file uploads for you. In short, it manages the uploaded file,
 * and allows you to do whatever you want with the file, especially if it
 * is an image, and as many times as you want.
 *
 * It is the ideal class to quickly integrate file upload in your site.
 * If the file is an image, you can convert, resize, crop it in many ways.
 * You can also apply filters, add borders, text, watermarks, etc...
 * That's all you need for a gallery script for instance. Supported formats
 * are PNG, JPG, GIF and BMP.
 *
 * You can also use the class to work on local files, which is especially
 * useful to use the image manipulation features. The class also supports
 * Flash uploaders.
 *
 * The class works with PHP 4 and 5, and its error messages can
 * be localized at will.
 *
 * <b>How does it work?</b>
 *
 * You instanciate the class with the $_FILES['my_field'] array
 * where my_field is the field name from your upload form.
 * The class will check if the original file has been uploaded
 * to its temporary location (alternatively, you can instanciate
 * the class with a local filename).
 *
 * You can then set a number of processing variables to act on the file.
 * For instance, you can rename the file, and if it is an image,
 * convert and resize it in many ways.
 * You can also set what will the class do if the file already exists.
 *
 * Then you call the function {@link process} to actually perform the actions
 * according to the processing parameters you set above.
 * It will create new instances of the original file,
 * so the original file remains the same between each process.
 * The file will be manipulated, and copied to the given location.
 * The processing variables will be reset once it is done.
 *
 * You can repeat setting up a new set of processing variables,
 * and calling {@link process} again as many times as you want.
 * When you have finished, you can call {@link clean} to delete
 * the original uploaded file.
 *
 * If you don't set any processing parameters and call {@link process}
 * just after instanciating the class. The uploaded file will be simply
 * copied to the given location without any alteration or checks.
 *
 * Don't forget to add <i>enctype="multipart/form-data"</i> in your form
 * tag <form> if you want your form to upload the file.
 *
 * <b>How to use it?</b><br>
 * Create a simple HTML file, with a form such as:
 * <pre>
 * <form enctype="multipart/form-data" method="post" action="upload.php">
 *   <input type="file" size="32" name="image_field" value="">
 *   <input type="submit" name="Submit" value="upload">
 * </form>
 * </pre>
 * Create a file called upload.php:
 * <pre>
 *  $handle = new upload($_FILES['image_field']);
 *  if ($handle->uploaded) {
 *      $handle->file_new_name_body   = 'image_resized';
 *      $handle->image_resize         = true;
 *      $handle->image_x              = 100;
 *      $handle->image_ratio_y        = true;
 *      $handle->process('/home/user/files/');
 *      if ($handle->processed) {
 *          echo 'image resized';
 *          $handle->clean();
 *      } else {
 *          echo 'error : ' . $handle->error;
 *      }
 *  }
 * </pre>
 *
 * <b>How to process a file uploaded via XMLHttpRequest?</b><br>
 * Use the class as following, the rest being the same as above:
 * <pre>
 *  $handle = new upload('php:'.$_SERVER['HTTP_X_FILE_NAME']);
 * </pre>
 * Prefixing the argument with "php:" tells the class to retrieve the uploaded data 
 * in php://input, and the rest is the stream's filename, which is generally in 
 * $_SERVER['HTTP_X_FILE_NAME']. But you can use any other name you see fit:
 * <pre>
 *  $handle = new upload('php:mycustomname.ext');
 * </pre>
 *
 * <b>How to process local files?</b><br>
 * Use the class as following, the rest being the same as above:
 * <pre>
 *  $handle = new upload('/home/user/myfile.jpg');
 * </pre>
 *
 * <b>How to set the language?</b><br>
 * Instantiate the class with a second argument being the language code:
 * <pre>
 *  $handle = new upload($_FILES['image_field'], 'fr_FR');
 *  $handle = new upload('/home/user/myfile.jpg', 'fr_FR');
 * </pre>
 *
 * <b>How to output the resulting file or picture directly to the browser?</b><br>
 * Simply call {@link process}() without an argument (or with null as first argument):
 * <pre>
 *  $handle = new upload($_FILES['image_field']);
 *  header('Content-type: ' . $handle->file_src_mime);
 *  echo $handle->Process();
 *  die();
 * </pre>
 * Or if you want to force the download of the file:
 * <pre>
 *  $handle = new upload($_FILES['image_field']);
 *  header('Content-type: ' . $handle->file_src_mime);
 *  header("Content-Disposition: attachment; filename=".rawurlencode($handle->file_src_name).";");
 *  echo $handle->Process();
 *  die();
 * </pre>
 *
 * <b>Processing parameters</b> (reset after each process)
 * <ul>
 *  <li><b>{@link file_new_name_body}</b> replaces the name body (default: null)<br>
 *  <pre>$handle->file_new_name_body = 'new name';</pre></li>
 *  <li><b>{@link file_name_body_add}</b> appends to the name body (default: null)<br>
 *  <pre>$handle->file_name_body_add = '_uploaded';</pre></li>
 *  <li><b>{@link file_name_body_pre}</b> prepends to the name body (default: null)<br>
 *  <pre>$handle->file_name_body_pre = 'thumb_';</pre></li>
 *  <li><b>{@link file_new_name_ext}</b> replaces the file extension (default: null)<br>
 *  <pre>$handle->file_new_name_ext = 'txt';</pre></li>
 *  <li><b>{@link file_safe_name}</b> formats the filename (spaces changed to _) (default: true)<br>
 *  <pre>$handle->file_safe_name = true;</pre></li>
 *  <li><b>{@link file_force_extension}</b> forces an extension if there is't any (default: true)<br>
 *  <pre>$handle->file_force_extension = true;</pre></li>
 *  <li><b>{@link file_overwrite}</b> sets behaviour if file already exists (default: false)<br>
 *  <pre>$handle->file_overwrite = true;</pre></li>
 *  <li><b>{@link file_auto_rename}</b> automatically renames file if it already exists (default: true)<br>
 *  <pre>$handle->file_auto_rename = true;</pre></li>
 *  <li><b>{@link dir_auto_create}</b> automatically creates destination directory if missing (default: true)<br>
 *  <pre>$handle->auto_create_dir = true;</pre></li>
 *  <li><b>{@link dir_auto_chmod}</b> automatically attempts to chmod the destination directory if not writeable (default: true)<br>
 *  <pre>$handle->dir_auto_chmod = true;</pre></li>
 *  <li><b>{@link dir_chmod}</b> chmod used when creating directory or if directory not writeable (default: 0777)<br>
 *  <pre>$handle->dir_chmod = 0777;</pre></li>
 *  <li><b>{@link file_max_size}</b> sets maximum upload size (default: upload_max_filesize from php.ini)<br>
 *  <pre>$handle->file_max_size = '1024'; // 1KB</pre></li>
 *  <li><b>{@link mime_check}</b> sets if the class check the MIME against the {@link allowed} list (default: true)<br>
 *  <pre>$handle->mime_check = true;</pre></li>
 *  <li><b>{@link no_script}</b> sets if the class turns scripts into text files (default: true)<br>
 *  <pre>$handle->no_script = false;</pre></li>
 *  <li><b>{@link allowed}</b> array of allowed mime-types (or one string). wildcard accepted, as in image/* (default: check {@link Init})<br>
 *  <pre>$handle->allowed = array('application/pdf','application/msword', 'image/*');</pre></li>
 *  <li><b>{@link forbidden}</b> array of forbidden mime-types (or one string). wildcard accepted, as in image/*  (default: check {@link Init})<br>
 *  <pre>$handle->forbidden = array('application/*');</pre></li>
 * </ul>
 * <ul>
 *  <li><b>{@link image_convert}</b> if set, image will be converted (possible values : ''|'png'|'jpeg'|'gif'|'bmp'; default: '')<br>
 *  <pre>$handle->image_convert = 'jpg';</pre></li>
 *  <li><b>{@link image_background_color}</b> if set, will forcibly fill transparent areas with the color, in hexadecimal (default: null)<br>
 *  <pre>$handle->image_background_color = '#FF00FF';</pre></li>
 *  <li><b>{@link image_default_color}</b> fallback color background color for non alpha-transparent output formats, such as JPEG or BMP, in hexadecimal (default: #FFFFFF)<br>
 *  <pre>$handle->image_default_color = '#FF00FF';</pre></li>
 *  <li><b>{@link png_compression}</b> sets the compression level for PNG images, between 1 (fast but large files) and 9 (slow but smaller files) (default: null (Zlib default))<br>
 *  <pre>$handle->png_compression = 9;</pre></li>
 *  <li><b>{@link jpeg_quality}</b> sets the compression quality for JPEG images (default: 85)<br>
 *  <pre>$handle->jpeg_quality = 50;</pre></li>
 *  <li><b>{@link jpeg_size}</b> if set to a size in bytes, will approximate {@link jpeg_quality} so the output image fits within the size (default: null)<br>
 *  <pre>$handle->jpeg_size = 3072;</pre></li>
 *  <li><b>{@link image_interlace}</b> if set to true, the image will be saved interlaced (default: false)<br>
 *  <pre>$handle->image_interlace = true;</pre></li>
 * </ul>
 * The following eight settings can be used to invalidate an upload if the file is an image (note that <i>open_basedir</i> restrictions prevent the use of these settings)
 * <ul>
 *  <li><b>{@link image_max_width}</b> if set to a dimension in pixels, the upload will be invalid if the image width is greater (default: null)<br>
 *  <pre>$handle->image_max_width = 200;</pre></li>
 *  <li><b>{@link image_max_height}</b> if set to a dimension in pixels, the upload will be invalid if the image height is greater (default: null)<br>
 *  <pre>$handle->image_max_height = 100;</pre></li>
 *  <li><b>{@link image_max_pixels}</b> if set to a number of pixels, the upload will be invalid if the image number of pixels is greater (default: null)<br>
 *  <pre>$handle->image_max_pixels = 50000;</pre></li>
 *  <li><b>{@link image_max_ratio}</b> if set to a aspect ratio (width/height), the upload will be invalid if the image apect ratio is greater (default: null)<br>
 *  <pre>$handle->image_max_ratio = 1.5;</pre></li>
 *  <li><b>{@link image_min_width}</b> if set to a dimension in pixels, the upload will be invalid if the image width is lower (default: null)<br>
 *  <pre>$handle->image_min_width = 100;</pre></li>
 *  <li><b>{@link image_min_height}</b> if set to a dimension in pixels, the upload will be invalid if the image height is lower (default: null)<br>
 *  <pre>$handle->image_min_height = 500;</pre></li>
 *  <li><b>{@link image_min_pixels}</b> if set to a number of pixels, the upload will be invalid if the image number of pixels is lower (default: null)<br>
 *  <pre>$handle->image_min_pixels = 20000;</pre></li>
 *  <li><b>{@link image_min_ratio}</b> if set to a aspect ratio (width/height), the upload will be invalid if the image apect ratio is lower (default: null)<br>
 *  <pre>$handle->image_min_ratio = 0.5;</pre></li>
 * </ul>
 * <ul>
 *  <li><b>{@link image_resize}</b> determines is an image will be resized (default: false)<br>
 *  <pre>$handle->image_resize = true;</pre></li>
 * </ul>
 *  The following variables are used only if {@link image_resize} == true
 * <ul>
 *  <li><b>{@link image_x}</b> destination image width (default: 150)<br>
 *  <pre>$handle->image_x = 100;</pre></li>
 *  <li><b>{@link image_y}</b> destination image height (default: 150)<br>
 *  <pre>$handle->image_y = 200;</pre></li>
 * </ul>
 *  Use either one of the following
 * <ul>
 *  <li><b>{@link image_ratio}</b> if true, resize image conserving the original sizes ratio, using {@link image_x} AND {@link image_y} as max sizes if true (default: false)<br>
 *  <pre>$handle->image_ratio = true;</pre></li>
 *  <li><b>{@link image_ratio_crop}</b> if true, resize image conserving the original sizes ratio, using {@link image_x} AND {@link image_y} as max sizes, and cropping excedent to fill the space. setting can also be a string, with one or more from 'TBLR', indicating which side of the image will be kept while cropping (default: false)<br>
 *  <pre>$handle->image_ratio_crop = true;</pre></li>
 *  <li><b>{@link image_ratio_fill}</b> if true, resize image conserving the original sizes ratio, using {@link image_x} AND {@link image_y} as max sizes, fitting the image in the space and coloring the remaining space. setting can also be a string, with one or more from 'TBLR', indicating which side of the space the image will be in (default: false)<br>
 *  <pre>$handle->image_ratio_fill = true;</pre></li>
 *  <li><b>{@link image_ratio_no_zoom_in}</b> same as {@link image_ratio}, but won't resize if the source image is smaller than {@link image_x} x {@link image_y} (default: false)<br>
 *  <pre>$handle->image_ratio_no_zoom_in = true;</pre></li>
 *  <li><b>{@link image_ratio_no_zoom_out}</b> same as {@link image_ratio}, but won't resize if the source image is bigger than {@link image_x} x {@link image_y} (default: false)<br>
 *  <pre>$handle->image_ratio_no_zoom_out = true;</pre></li>
 *  <li><b>{@link image_ratio_x}</b> if true, resize image, calculating {@link image_x} from {@link image_y} and conserving the original sizes ratio (default: false)<br>
 *  <pre>$handle->image_ratio_x = true;</pre></li>
 *  <li><b>{@link image_ratio_y}</b> if true, resize image, calculating {@link image_y} from {@link image_x} and conserving the original sizes ratio (default: false)<br>
 *  <pre>$handle->image_ratio_y = true;</pre></li>
 *  <li><b>{@link image_ratio_pixels}</b> if set to a long integer, resize image, calculating {@link image_y} and {@link image_x} to match a the number of pixels (default: false)<br>
 *  <pre>$handle->image_ratio_pixels = 25000;</pre></li>
 * </ul>
 *  The following image manipulations require GD2+
 * <ul>
 *  <li><b>{@link image_brightness}</b> if set, corrects the brightness. value between -127 and 127 (default: null)<br>
 *  <pre>$handle->image_brightness = 40;</pre></li>
 *  <li><b>{@link image_contrast}</b> if set, corrects the contrast. value between -127 and 127 (default: null)<br>
 *  <pre>$handle->image_contrast = 50;</pre></li>
 *  <li><b>{@link image_opacity}</b> if set, changes the image opacity. value between 0 and 100 (default: null)<br>
 *  <pre>$handle->image_opacity = 50;</pre></li>
 *  <li><b>{@link image_tint_color}</b> if set, will tint the image with a color, value as hexadecimal #FFFFFF (default: null)<br>
 *  <pre>$handle->image_tint_color = '#FF0000';</pre></li>
 *  <li><b>{@link image_overlay_color}</b> if set, will add a colored overlay, value as hexadecimal #FFFFFF (default: null)<br>
 *  <pre>$handle->image_overlay_color = '#FF0000';</pre></li>
 *  <li><b>{@link image_overlay_opacity}</b> used when {@link image_overlay_color} is set, determines the opacity (default: 50)<br>
 *  <pre>$handle->image_overlay_opacity = 20;</pre></li>
 *  <li><b>{@link image_negative}</b> inverts the colors in the image (default: false)<br>
 *  <pre>$handle->image_negative = true;</pre></li>
 *  <li><b>{@link image_greyscale}</b> transforms an image into greyscale (default: false)<br>
 *  <pre>$handle->image_greyscale = true;</pre></li>
 *  <li><b>{@link image_threshold}</b> applies a threshold filter. value between -127 and 127 (default: null)<br>
 *  <pre>$handle->image_threshold = 20;</pre></li>
 *  <li><b>{@link image_pixelate}</b> pixelate an image, value is block size (default: null)<br>
 *  <pre>$handle->image_pixelate = 10;</pre></li>
 *  <li><b>{@link image_unsharp}</b> applies an unsharp mask, with alpha transparency support (default: false)<br>
 *  <pre>$handle->image_unsharp = true;</pre></li>
 *  <li><b>{@link image_unsharp_amount}</b> unsharp mask amount, typically 50 - 200 (default: 80)<br>
 *  <pre>$handle->image_unsharp_amount = 120;</pre></li>
 *  <li><b>{@link image_unsharp_radius}</b> unsharp mask radius, typically 0.5 - 1 (default: 0.5)<br>
 *  <pre>$handle->image_unsharp_radius = 1;</pre></li>
 *  <li><b>{@link image_unsharp_threshold}</b> unsharp mask threshold, typically 0 - 5 (default: 1)<br>
 *  <pre>$handle->image_unsharp_threshold = 0;</pre></li>
 * </ul>
 * <ul>
 *  <li><b>{@link image_text}</b> creates a text label on the image, value is a string, with eventual replacement tokens (default: null)<br>
 *  <pre>$handle->image_text = 'test';</pre></li>
 *  <li><b>{@link image_text_direction}</b> text label direction, either 'h' horizontal or 'v' vertical (default: 'h')<br>
 *  <pre>$handle->image_text_direction = 'v';</pre></li>
 *  <li><b>{@link image_text_color}</b> text color for the text label, in hexadecimal (default: #FFFFFF)<br>
 *  <pre>$handle->image_text_color = '#FF0000';</pre></li>
 *  <li><b>{@link image_text_opacity}</b> text opacity on the text label, integer between 0 and 100 (default: 100)<br>
 *  <pre>$handle->image_text_opacity = 50;</pre></li>
 *  <li><b>{@link image_text_background}</b> text label background color, in hexadecimal (default: null)<br>
 *  <pre>$handle->image_text_background = '#FFFFFF';</pre></li>
 *  <li><b>{@link image_text_background_opacity}</b> text label background opacity, integer between 0 and 100 (default: 100)<br>
 *  <pre>$handle->image_text_background_opacity = 50;</pre></li>
 *  <li><b>{@link image_text_font}</b> built-in font for the text label, from 1 to 5. 1 is the smallest (default: 5)<br>
 *  <pre>$handle->image_text_font = 4;</pre></li>
 *  <li><b>{@link image_text_x}</b> absolute text label position, in pixels from the left border. can be negative (default: null)<br>
 *  <pre>$handle->image_text_x = 5;</pre></li>
 *  <li><b>{@link image_text_y}</b> absolute text label position, in pixels from the top border. can be negative (default: null)<br>
 *  <pre>$handle->image_text_y = 5;</pre></li>
 *  <li><b>{@link image_text_position}</b> text label position withing the image, a combination of one or two from 'TBLR': top, bottom, left, right (default: null)<br>
 *  <pre>$handle->image_text_position = 'LR';</pre></li>
 *  <li><b>{@link image_text_padding}</b> text label padding, in pixels. can be overridden by {@link image_text_padding_x} and {@link image_text_padding_y} (default: 0)<br>
 *  <pre>$handle->image_text_padding = 5;</pre></li>
 *  <li><b>{@link image_text_padding_x}</b> text label horizontal padding (default: null)<br>
 *  <pre>$handle->image_text_padding_x = 2;</pre></li>
 *  <li><b>{@link image_text_padding_y}</b> text label vertical padding (default: null)<br>
 *  <pre>$handle->image_text_padding_y = 10;</pre></li>
 *  <li><b>{@link image_text_alignment}</b> text alignment when text has multiple lines, either 'L', 'C' or 'R' (default: 'C')<br>
 *  <pre>$handle->image_text_alignment = 'R';</pre></li>
 *  <li><b>{@link image_text_line_spacing}</b> space between lines in pixels, when text has multiple lines (default: 0)<br>
 *  <pre>$handle->image_text_line_spacing = 3;</pre></li>
 * </ul>
 * <ul>
 *  <li><b>{@link image_flip}</b> flips image, wither 'h' horizontal or 'v' vertical (default: null)<br>
 *  <pre>$handle->image_flip = 'h';</pre></li>
 *  <li><b>{@link image_rotate}</b> rotates image. possible values are 90, 180 and 270 (default: null)<br>
 *  <pre>$handle->image_rotate = 90;</pre></li>
 *  <li><b>{@link image_crop}</b> crops image. accepts 4, 2 or 1 values as 'T R B L' or 'TB LR' or 'TBLR'. dimension can be 20, or 20px or 20% (default: null)<br>
 *  <pre>$handle->image_crop = array(50,40,30,20); OR '-20 20%'...</pre></li>
 *  <li><b>{@link image_precrop}</b> crops image, before an eventual resizing. accepts 4, 2 or 1 values as 'T R B L' or 'TB LR' or 'TBLR'. dimension can be 20, or 20px or 20% (default: null)<br>
 *  <pre>$handle->image_precrop = array(50,40,30,20); OR '-20 20%'...</pre></li>
 * </ul>
 * <ul>
 *  <li><b>{@link image_bevel}</b> adds a bevel border to the image. value is thickness in pixels (default: null)<br>
 *  <pre>$handle->image_bevel = 20;</pre></li>
 *  <li><b>{@link image_bevel_color1}</b> top and left bevel color, in hexadecimal (default: #FFFFFF)<br>
 *  <pre>$handle->image_bevel_color1 = '#FFFFFF';</pre></li>
 *  <li><b>{@link image_bevel_color2}</b> bottom and right bevel color, in hexadecimal (default: #000000)<br>
 *  <pre>$handle->image_bevel_color2 = '#000000';</pre></li>
 *  <li><b>{@link image_border}</b> adds a unicolor border to the image. accepts 4, 2 or 1 values as 'T R B L' or 'TB LR' or 'TBLR'. dimension can be 20, or 20px or 20% (default: null)<br>
 *  <pre>$handle->image_border = '3px'; OR '-20 20%' OR array(3,2)...</pre></li>
 *  <li><b>{@link image_border_color}</b> border color, in hexadecimal (default: #FFFFFF)<br>
 *  <pre>$handle->image_border_color = '#FFFFFF';</pre></li>
 *  <li><b>{@link image_border_opacity}</b> border opacity, integer between 0 and 100 (default: 100)<br>
 *  <pre>$handle->image_border_opacity = 50;</pre></li>
 *  <li><b>{@link image_border_transparent}</b> adds a fading-to-transparent border to the image. accepts 4, 2 or 1 values as 'T R B L' or 'TB LR' or 'TBLR'. dimension can be 20, or 20px or 20% (default: null)<br>
 *  <pre>$handle->image_border_transparent = '3px'; OR '-20 20%' OR array(3,2)...</pre></li>
 *  <li><b>{@link image_frame}</b> type of frame: 1=flat 2=crossed (default: null)<br>
 *  <pre>$handle->image_frame = 2;</pre></li>
 *  <li><b>{@link image_frame_colors}</b> list of hex colors, in an array or a space separated string (default: '#FFFFFF #999999 #666666 #000000')<br>
 *  <pre>$handle->image_frame_colors = array('#999999',  '#FF0000', '#666666', '#333333', '#000000');</pre></li>
 *  <li><b>{@link image_frame_opacity}</b> frame opacity, integer between 0 and 100 (default: 100)<br>
 *  <pre>$handle->image_frame_opacity = 50;</pre></li>
 * </ul>
 * <ul>
 *  <li><b>{@link image_watermark}</b> adds a watermark on the image, value is a local filename. accepted files are GIF, JPG, BMP, PNG and PNG alpha (default: null)<br>
 *  <pre>$handle->image_watermark = 'watermark.png';</pre></li>
 *  <li><b>{@link image_watermark_x}</b> absolute watermark position, in pixels from the left border. can be negative (default: null)<br>
 *  <pre>$handle->image_watermark_x = 5;</pre></li>
 *  <li><b>{@link image_watermark_y}</b> absolute watermark position, in pixels from the top border. can be negative (default: null)<br>
 *  <pre>$handle->image_watermark_y = 5;</pre></li>
 *  <li><b>{@link image_watermark_position}</b> watermark position withing the image, a combination of one or two from 'TBLR': top, bottom, left, right (default: null)<br>
 *  <pre>$handle->image_watermark_position = 'LR';</pre></li>
 *  <li><b>{@link image_watermark_no_zoom_in}</b> prevents the watermark to be resized up if it is smaller than the image (default: true)<br>
 *  <pre>$handle->image_watermark_no_zoom_in = false;</pre></li>
 *  <li><b>{@link image_watermark_no_zoom_out}</b> prevents the watermark to be resized down if it is bigger than the image (default: false)<br>
 *  <pre>$handle->image_watermark_no_zoom_out = true;</pre></li>
 * </ul>
 * <ul>
 *  <li><b>{@link image_reflection_height}</b> if set, a reflection will be added. Format is either in pixels or percentage, such as 40, '40', '40px' or '40%' (default: null)<br>
 *  <pre>$handle->image_reflection_height = '25%';</pre></li>
 *  <li><b>{@link image_reflection_space}</b> space in pixels between the source image and the reflection, can be negative (default: null)<br>
 *  <pre>$handle->image_reflection_space = 3;</pre></li>
 *  <li><b>{@link image_reflection_color}</b> reflection background color, in hexadecimal. Now deprecated in favor of {@link image_default_color} (default: #FFFFFF)<br>
 *  <pre>$handle->image_default_color = '#000000';</pre></li>
 *  <li><b>{@link image_reflection_opacity}</b> opacity level at which the reflection starts, integer between 0 and 100 (default: 60)<br>
 *  <pre>$handle->image_reflection_opacity = 60;</pre></li>
 * </ul>
 *
 * <b>Values that can be read before calling {@link process}()</b>
 * <ul>
 *  <li><b>{@link file_src_name}</b> Source file name</li>
 *  <li><b>{@link file_src_name_body}</b> Source file name body</li>
 *  <li><b>{@link file_src_name_ext}</b> Source file extension</li>
 *  <li><b>{@link file_src_pathname}</b> Source file complete path and name</li>
 *  <li><b>{@link file_src_mime}</b> Source file mime type</li>
 *  <li><b>{@link file_src_size}</b> Source file size in bytes</li>
 *  <li><b>{@link file_src_error}</b> Upload error code</li>
 *  <li><b>{@link file_is_image}</b> Boolean flag, true if the file is a supported image type</li>
 * </ul>
 * If the file is a supported image type (and <i>open_basedir</i> restrictions allow it)
 * <ul>
 *  <li><b>{@link image_src_x}</b> Source file width in pixels</li>
 *  <li><b>{@link image_src_y}</b> Source file height in pixels</li>
 *  <li><b>{@link image_src_pixels}</b> Source file number of pixels</li>
 *  <li><b>{@link image_src_type}</b> Source file type (png, jpg, gif or bmp)</li>
 *  <li><b>{@link image_src_bits}</b> Source file color depth</li>
 * </ul>
 *
 * <b>Values that can be read after calling {@link process}()</b>
 * <ul>
 *  <li><b>{@link file_dst_path}</b> Destination file path</li>
 *  <li><b>{@link file_dst_name_body}</b> Destination file name body</li>
 *  <li><b>{@link file_dst_name_ext}</b> Destination file extension</li>
 *  <li><b>{@link file_dst_name}</b> Destination file name</li>
 *  <li><b>{@link file_dst_pathname}</b> Destination file complete path and name</li>
 * </ul>
 * If the file is a supported image type
 * <ul>
 *  <li><b>{@link image_dst_type}</b> Destination file type (png, jpg, gif or bmp)</li>
 *  <li><b>{@link image_dst_x}</b> Destination file width</li>
 *  <li><b>{@link image_dst_y}</b> Destination file height</li>
 * </ul>
 *
 * <b>Requirements</b>
 *
 * Most of the image operations require GD. GD2 is greatly recommended
 *
 * The class requires PHP 4.3+, and is compatible with PHP5
 *
 * <b>Changelog</b>
 * <ul>
 *  <li><b>v 0.33dev</b> 28/03/2014<br>
 *   - added {@link image_dst_type}<br>
 *   - fixed filesize when using XMLHttpRequest<br>
 *   - added Hungarian translation<br>
 *   - fixed Turkish translation<br>
 *   - updated regex rules for MIME detection<br>
 *   - added composer.json</li>
 *  <li><b>v 0.32</b> 15/01/2013<br>
 *   - add support for XMLHttpRequest uploads<br>
 *   - added {@link image_pixelate}<br>
 *   - added {@link image_interlace}<br>
 *   - added {@link png_compression} to change PNG compressoin level<br>
 *   - deactivate exec() if Suhosin is enabled<br>
 *   - add more extension to dangerous scripts detection<br>
 *   - imagejpeg takes null as second argument since PHP 5.4<br>
 *   - default PECL Fileinfo MAGIC path to null<br>
 *   - set gd.jpeg_ignore_warning to true by default<br>
 *   - fixed file name normalization</li>
 *  <li><b>v 0.31</b> 11/04/2011<br>
 *   - added application/x-rar MIME type<br>
 *   - make sure exec() and ini_get_all()function are not disabled if we want to use them<br>
 *   - make sure that we don't divide by zero when calculating JPEG size<br>
 *   - {@link allowed} and {@link forbidden} can now accept strings<br>
 *   - try to guess the file extension from the MIME type if there is no file extension<br>
 *   - better class properties when changing the file extension<br>
 *   - added {@link file_force_extension} to allow extension-less files if needed<br>
 *   - better file safe conversion of the filename<br>
 *   - allow shorthand byte values, such as 1K, 2M, 3G for {@link file_max_size} and {@link jpeg_size}<br>
 *   - added {@link image_opacity} to change picture opacity<br>
 *   - added {@link image_border_opacity} to allow semi-transparent borders<br>
 *   - added {@link image_frame_opacity} to allow semi-transparent frames<br>
 *   - added {@link image_border_transparent} to allow borders fading to transparent<br>
 *   - duplicated {@link image_overlay_percent} into {@link image_overlay_opacity}<br>
 *   - duplicated {@link image_text_percent} into {@link image_text_opacity}<br>
 *   - duplicated {@link image_text_background_percent} into {@link image_text_background_opacity}</li>
 *  <li><b>v 0.30</b> 05/09/2010<br>
 *   - implemented an unsharp mask, with alpha transparency support, activated if {@link image_unsharp} is true. added {@link image_unsharp_amount}, {@link image_unsharp_radius}, and {@link image_unsharp_threshold}<br>
 *   - added text/rtf MIME type, and no_script exception<br>
 *   - corrected bug when {@link no_script} is activated and several process() are called<br>
 *   - better error handling for finfo<br>
 *   - display upload_max_filesize information from php.ini in the log<br>
 *   - automatic extension for extension-less images<br>
 *   - fixed {@link image_ratio_fill} top and left filling<br>
 *   - fixed alphablending issue when applying a transparent PNG watermark on a transparent PNG<br>
 *   - added {@link image_watermark_no_zoom_in} and {@link image_watermark_no_zoom_out} to allow the watermark to be resized down (or up) to fit in the image. By default, the watermark may be resized down, but not up.</li>
 *  <li><b>v 0.29</b> 03/02/2010<br>
 *   - added protection against malicious images<br>
 *   - added zip and torrent MIME type<br>
 *   - replaced split() with explode()<br>
 *   - initialise image_dst_x/y with image_src_x/y<br>
 *   - removed {@link mime_fileinfo}, {@link mime_file}, {@link mime_magic} and {@link mime_getimagesize} from the docs since they are used before {@link process}<br>
 *   - added more extensions and MIME types<br>
 *   - improved MIME type validation<br>
 *   - improved logging</li>
 *  <li><b>v 0.28</b> 10/08/2009<br>
 *   - replaced ereg functions to be compatible with PHP 5.3<br>
 *   - added flv MIME type<br>
 *   - improved MIME type detection<br>
 *   - added {@link file_name_body_pre} to prepend a string to the file name<br>
 *   - added {@link mime_fileinfo}, {@link mime_file}, {@link mime_magic} and {@link mime_getimagesize} so that it is possible to deactivate some MIME type checking method<br>
 *   - use exec() rather than shell_exec(), to play better with safe mode <br>
 *   - added some error messages<br>
 *   - fix bug when checking on conditions, {@link processed} wasn't propagated properly</li>
 *  <li><b>v 0.27</b> 14/05/2009<br>
 *   - look for the language files directory from __FILE__<br>
 *   - deactivate {@link file_auto_rename} if {@link file_overwrite} is set<br>
 *   - improved transparency replacement for true color images<br>
 *   - fixed calls to newer version of UNIX file utility<br>
 *   - fixed error when using PECL Fileinfo extension in SAFE MODE, and when using the finfo class<br>
 *   - added {@link image_precrop} to crop the image before an eventual resizing</li>
 *  <li><b>v 0.26</b> 13/11/2008<br>
 *   - rewrote conversion from palette to true color to handle transparency better<br>
 *   - fixed imagecopymergealpha() when the overlayed image is of wrong dimensions<br>
 *   - fixed imagecreatenew() when the image to create have less than 1 pixels width or height<br>
 *   - rewrote MIME type detection to be more secure and not rely on browser information; now using Fileinfo PECL extension, UNIX file() command, MIME magic, and getimagesize(), in that order<br>
 *   - added support for Flash uploaders<br>
 *   - some bug fixing and error handling</li>
 *  <li><b>v 0.25</b> 17/11/2007<br>
 *   - added translation files and mechanism to instantiate the class with a language different from English<br>
 *   - added {@link forbidden} to set an array of forbidden MIME types<br>
 *   - implemented support for simple wildcards in {@link allowed} and {@link forbidden}, such as image/*<br>
 *   - preset the file extension to the desired conversion format when converting an image<br>
 *   - added read and write support for BMP images<br>
 *   - added a flag {@link file_is_image} to determine if the file is a supported image type<br>
 *   - the class now provides some information about the image, before calling {@link process}(). Available are {@link image_src_x}, {@link image_src_y} and the newly introduced {@link image_src_bits}, {@link image_src_pixels} and {@link image_src_type}. Note that this will not work if <i>open_basedir</i> restrictions are in place<br>
 *   - improved logging; now provides useful system information<br>
 *   - added some more pre-processing checks for files that are images: {@link image_max_width}, {@link image_max_height}, {@link image_max_pixels}, {@link image_max_ratio}, {@link image_min_width}, {@link image_min_height}, {@link image_min_pixels} and {@link image_min_ratio}<br>
 *   - added {@link image_ratio_pixels} to resize an image to a number of pixels, keeping aspect ratio<br>
 *   - added {@link image_is_palette} and {@link image_is_transparent} and {@link image_transparent_color} for GIF images<br>
 *   - added {@link image_default_color} to define a fallback color for non alpha-transparent output formats, such as JPEG or BMP<br>
 *   - changed {@link image_background_color}, which now forces transparent areas to be painted<br>
 *   - improved reflections and color overlays so that it works with alpha transparent images<br>
 *   - {@link image_reflection_color} is now deprecated in favour of {@link image_default_color}<br />
 *   - transparent PNGs are now processed in true color, and fully preserving the alpha channel when doing merges<br>
 *   - transparent GIFs are now automatically detected. {@link preserve_transparency} is deprecated<br>
 *   - transparent true color images can be saved as GIF while retaining transparency, semi transparent areas being merged with {@link image_default_color}<br>
 *   - transparent true color images can be saved as JPG/BMP with the semi transparent areas being merged with {@link image_default_color}<br>
 *   - fixed conversion of images to true color<br>
 *   - the class can now output the uploaded files content as the return value of process() if the function is called with an empty or null argumenti, or no argument</li>
 *  <li><b>v 0.24</b> 25/05/2007<br>
 *   - added {@link image_background_color}, to set the default background color of an image<br>
 *   - added possibility of using replacement tokens in text labels<br>
 *   - changed default JPEG quality to 85<br>
 *   - fixed a small bug when using greyscale filter and associated filters<br>
 *   - added {@link image_ratio_fill} in order to fit an image within some dimensions and color the remaining space. Very similar to {@link image_ratio_crop}<br>
 *   - improved the recursive creation of directories<br>
 *   - the class now converts palette based images to true colors before doing graphic manipulations</li>
 *  <li><b>v 0.23</b> 23/12/2006<br>
 *   - fixed a bug when processing more than once the same uploaded file. If there is an open_basedir restriction, the class now creates a temporary file for the first call to process(). This file will be used for subsequent processes, and will be deleted upon calling clean()</li>
 *  <li><b>v 0.22</b> 16/12/2006<br>
 *   - added automatic creation of a temporary file if the upload directory is not within open_basedir<br>
 *   - fixed a bug which was preventing to work on a local file by overwriting it with its processed copy<br>
 *   - added MIME types video/x-ms-wmv and image/x-png and fixed PNG support for IE weird MIME types<br>
 *   - modified {@link image_ratio_crop} so it can accept one or more from string 'TBLR', determining which side of the image is kept while cropping<br>
 *   - added support for multiple lines in the text, using "\n" as a line break<br>
 *   - added {@link image_text_line_spacing} which allow to set the space between several lines of text<br>
 *   - added {@link image_text_alignment} which allow to set the alignment when text has several lines<br>
 *   - {@link image_text_font} can now be set to the path of a GDF font to load external fonts<br>
 *   - added {@link image_reflection_height} to create a reflection of the source image, which height is in pixels or percentage<br>
 *   - added {@link image_reflection_space} to set the space in pixels between the source image and the reflection<br>
 *   - added {@link image_reflection_color} to set the reflection background color<br>
 *   - added {@link image_reflection_opacity} to set the initial level of opacity of the reflection</li>
 *  <li><b>v 0.21</b> 30/09/2006<br>
 *   - added {@link image_ratio_crop} which resizes within {@link image_x} and {@link image_y}, keeping ratio, but filling the space by cropping excedent of image<br>
 *   - added {@link mime_check}, which default is true, to set checks against {@link allowed} MIME list<br>
 *   - if MIME is empty, the class now triggers an error<br>
 *   - color #000000 is OK for {@link image_text_color}, and related text transparency bug fixed<br>
 *   - {@link gd_version}() now uses gd_info(), or else phpinfo()<br>
 *   - fixed path issue when the destination path has no trailing slash on Windows systems <br>
 *   - removed inline functions to be fully PHP5 compatible </li>
 *  <li><b>v 0.20</b> 11/08/2006<br>
 *   - added some more error checking and messages (GD presence, permissions...)<br>
 *   - fix when uploading files without extension<br>
 *   - changed values for {@link image_brightness} and {@link image_contrast} to be between -127 and 127<br>
 *   - added {@link dir_auto_create} to automatically and recursively create destination directory if missing.<br>
 *   - added {@link dir_auto_chmod} to automatically chmod the destination directory if not writeable.<br>
 *   - added {@link dir_chmod} to set the default chmod to use.<br>
 *   - added {@link image_crop} to crop images<br>
 *   - added {@link image_negative} to invert the colors on the image<br>
 *   - added {@link image_greyscale} to turn the image into greyscale<br>
 *   - added {@link image_threshold} to apply a threshold filter on the image<br>
 *   - added {@link image_bevel}, {@link image_bevel_color1} and {@link image_bevel_color2} to add a bevel border<br>
 *   - added {@link image_border} and {@link image_border_color} to add a single color border<br>
 *   - added {@link image_frame} and {@link image_frame_colors} to add a multicolored frame</li>
 *  <li><b>v 0.19</b> 29/03/2006<br>
 *   - class is now compatible i18n (thanks Sylwester).<br>
 *   - the class can mow manipulate local files, not only uploaded files (instanciate the class with a local filename).<br>
 *   - {@link file_safe_name} has been improved a bit.<br>
 *   - added {@link image_brightness}, {@link image_contrast}, {@link image_tint_color}, {@link image_overlay_color} and {@link image_overlay_percent} to do color manipulation on the images.<br>
 *   - added {@link image_text} and all derivated settings to add a text label on the image.<br>
 *   - added {@link image_watermark} and all derivated settings to add a watermark image on the image.<br>
 *   - added {@link image_flip} and {@link image_rotate} for more image manipulations<br>
 *   - added {@link jpeg_size} to calculate the JPG compression quality in order to fit within one filesize.</li>
 *  <li><b>v 0.18</b> 02/02/2006<br>
 *   - added {@link no_script} to turn dangerous scripts into text files.<br>
 *   - added {@link mime_magic_check} to set the class to use mime_magic.<br>
 *   - added {@link preserve_transparency} *experimental*. Thanks Gregor.<br>
 *   - fixed size and mime checking, wasn't working :/ Thanks Willem.<br>
 *   - fixed memory leak when resizing images.<br>
 *   - when resizing, it is not necessary anymore to set {@link image_convert}.<br>
 *   - il is now possible to simply convert an image, with no resizing.<br>
 *   - sets the default {@link file_max_size} to upload_max_filesize from php.ini. Thanks Edward</li>
 *  <li><b>v 0.17</b> 28/05/2005<br>
 *   - the class can be used with any version of GD.<br>
 *   - added security check on the file with a list of mime-types.<br>
 *   - changed the license to GPL v2 only</li>
 *  <li><b>v 0.16</b> 19/05/2005<br>
 *   - added {@link file_auto_rename} automatic file renaming if the same filename already exists.<br>
 *   - added {@link file_safe_name} safe formatting of the filename (spaces to _underscores so far).<br>
 *   - added some more error reporting to avoid crash if GD is not present</li>
 *  <li><b>v 0.15</b> 16/04/2005<br>
 *   - added JPEG compression quality setting. Thanks Vad</li>
 *  <li><b>v 0.14</b> 14/03/2005<br>
 *   - reworked the class file to allow parsing with phpDocumentor</li>
 *  <li><b>v 0.13</b> 07/03/2005<br>
 *   - fixed a bug with {@link image_ratio}. Thanks Justin.<br>
 *   - added {@link image_ratio_no_zoom_in} and {@link image_ratio_no_zoom_out} </li>
 *  <li><b>v 0.12</b> 21/01/2005<br>
 *   - added {@link image_ratio} to resize within max values, keeping image ratio</li>
 *  <li><b>v 0.11</b> 22/08/2003<br>
 *   - update for GD2 (changed imageresized() into imagecopyresampled() and imagecreate() into imagecreatetruecolor())</li>
 * </ul>
 *
 * @package   cmf
 * @subpackage external
 */
class thumnail_upload {


    /**
     * Class version
     *
     * @access public
     * @var string
     */
    var $version;

    /**
     * Uploaded file name
     *
     * @access public
     * @var string
     */
    var $file_src_name;

    /**
     * Uploaded file name body (i.e. without extension)
     *
     * @access public
     * @var string
     */
    var $file_src_name_body;

    /**
     * Uploaded file name extension
     *
     * @access public
     * @var string
     */
    var $file_src_name_ext;

    /**
     * Uploaded file MIME type
     *
     * @access public
     * @var string
     */
    var $file_src_mime;

    /**
     * Uploaded file size, in bytes
     *
     * @access public
     * @var double
     */
    var $file_src_size;

    /**
     * Holds eventual PHP error code from $_FILES
     *
     * @access public
     * @var string
     */
    var $file_src_error;

    /**
     * Uloaded file name, including server path
     *
     * @access public
     * @var string
     */
    var $file_src_pathname;

    /**
     * Uloaded file name temporary copy
     *
     * @access private
     * @var string
     */
    var $file_src_temp;

    /**
     * Destination file name
     *
     * @access public
     * @var string
     */
    var $file_dst_path;

    /**
     * Destination file name
     *
     * @access public
     * @var string
     */
    var $file_dst_name;

    /**
     * Destination file name body (i.e. without extension)
     *
     * @access public
     * @var string
     */
    var $file_dst_name_body;

    /**
     * Destination file extension
     *
     * @access public
     * @var string
     */
    var $file_dst_name_ext;

    /**
     * Destination file name, including path
     *
     * @access public
     * @var string
     */
    var $file_dst_pathname;

    /**
     * Source image width
     *
     * @access public
     * @var integer
     */
    var $image_src_x;

    /**
     * Source image height
     *
     * @access public
     * @var integer
     */
    var $image_src_y;

    /**
     * Source image color depth
     *
     * @access public
     * @var integer
     */
    var $image_src_bits;

    /**
     * Number of pixels
     *
     * @access public
     * @var long
     */
    var $image_src_pixels;

    /**
     * Type of image (png, gif, jpg or bmp)
     *
     * @access public
     * @var string
     */
    var $image_src_type;

    /**
     * Destination image width
     *
     * @access public
     * @var integer
     */
    var $image_dst_x;

    /**
     * Destination image height
     *
     * @access public
     * @var integer
     */
    var $image_dst_y;

    /**
     * Destination image type (png, gif, jpg or bmp)
     *
     * @access public
     * @var integer
     */
    var $image_dst_type;

    /**
     * Supported image formats
     *
     * @access private
     * @var array
     */
    var $image_supported;

    /**
     * Flag to determine if the source file is an image
     *
     * @access public
     * @var boolean
     */
    var $file_is_image;

    /**
     * Flag set after instanciating the class
     *
     * Indicates if the file has been uploaded properly
     *
     * @access public
     * @var bool
     */
    var $uploaded;

    /**
     * Flag stopping PHP upload checks
     *
     * Indicates whether we instanciated the class with a filename, in which case
     * we will not check on the validity of the PHP *upload*
     *
     * This flag is automatically set to true when working on a local file
     *
     * Warning: for uploads, this flag MUST be set to false for security reason
     *
     * @access public
     * @var bool
     */
    var $no_upload_check;

    /**
     * Flag set after calling a process
     *
     * Indicates if the processing, and copy of the resulting file went OK
     *
     * @access public
     * @var bool
     */
    var $processed;

    /**
     * Holds eventual error message in plain english
     *
     * @access public
     * @var string
     */
    var $error;

    /**
     * Holds an HTML formatted log
     *
     * @access public
     * @var string
     */
    var $log;


    // overiddable processing variables


    /**
     * Set this variable to replace the name body (i.e. without extension)
     *
     * @access public
     * @var string
     */
    var $file_new_name_body;

    /**
     * Set this variable to append a string to the file name body
     *
     * @access public
     * @var string
     */
    var $file_name_body_add;

    /**
     * Set this variable to prepend a string to the file name body
     *
     * @access public
     * @var string
     */
    var $file_name_body_pre;

    /**
     * Set this variable to change the file extension
     *
     * @access public
     * @var string
     */
    var $file_new_name_ext;

    /**
     * Set this variable to format the filename (spaces changed to _)
     *
     * @access public
     * @var boolean
     */
    var $file_safe_name;

    /**
     * Forces an extension if the source file doesn't have one
     *
     * If the file is an image, then the correct extension will be added
     * Otherwise, a .txt extension will be chosen
     *
     * @access public
     * @var boolean
     */
    var $file_force_extension;

    /**
     * Set this variable to false if you don't want to check the MIME against the allowed list
     *
     * This variable is set to true by default for security reason
     *
     * @access public
     * @var boolean
     */
    var $mime_check;

    /**
     * Set this variable to false in the init() function if you don't want to check the MIME 
     * with Fileinfo PECL extension. On some systems, Fileinfo is known to be buggy, and you
     * may want to deactivate it in the class code directly.
     *
     * You can also set it with the path of the magic database file.
     * If set to true, the class will try to read the MAGIC environment variable
     *   and if it is empty, will default to the system's default
     * If set to an empty string, it will call finfo_open without the path argument
     *
     * This variable is set to true by default for security reason
     *
     * @access public
     * @var boolean
     */
    var $mime_fileinfo;

    /**
     * Set this variable to false in the init() function if you don't want to check the MIME 
     * with UNIX file() command
     *
     * This variable is set to true by default for security reason
     *
     * @access public
     * @var boolean
     */
    var $mime_file;

    /**
     * Set this variable to false in the init() function if you don't want to check the MIME 
     * with the magic.mime file
     *
     * The function mime_content_type() will be deprecated,
     * and this variable will be set to false in a future release
     *
     * This variable is set to true by default for security reason
     *
     * @access public
     * @var boolean
     */
    var $mime_magic;

    /**
     * Set this variable to false in the init() function if you don't want to check the MIME 
     * with getimagesize()
     *
     * The class tries to get a MIME type from getimagesize()
     * If no MIME is returned, it tries to guess the MIME type from the file type
     *
     * This variable is set to true by default for security reason
     *
     * @access public
     * @var boolean
     */
    var $mime_getimagesize;

    /**
     * Set this variable to false if you don't want to turn dangerous scripts into simple text files
     *
     * @access public
     * @var boolean
     */
    var $no_script;

    /**
     * Set this variable to true to allow automatic renaming of the file
     * if the file already exists
     *
     * Default value is true
     *
     * For instance, on uploading foo.ext,<br>
     * if foo.ext already exists, upload will be renamed foo_1.ext<br>
     * and if foo_1.ext already exists, upload will be renamed foo_2.ext<br>
     *
     * Note that this option doesn't have any effect if {@link file_overwrite} is true
     *
     * @access public
     * @var bool
     */
    var $file_auto_rename;

    /**
     * Set this variable to true to allow automatic creation of the destination
     * directory if it is missing (works recursively)
     *
     * Default value is true
     *
     * @access public
     * @var bool
     */
    var $dir_auto_create;

    /**
     * Set this variable to true to allow automatic chmod of the destination
     * directory if it is not writeable
     *
     * Default value is true
     *
     * @access public
     * @var bool
     */
    var $dir_auto_chmod;

    /**
     * Set this variable to the default chmod you want the class to use
     * when creating directories, or attempting to write in a directory
     *
     * Default value is 0777 (without quotes)
     *
     * @access public
     * @var bool
     */
    var $dir_chmod;

    /**
     * Set this variable tu true to allow overwriting of an existing file
     *
     * Default value is false, so no files will be overwritten
     *
     * @access public
     * @var bool
     */
    var $file_overwrite;

    /**
     * Set this variable to change the maximum size in bytes for an uploaded file
     *
     * Default value is the value <i>upload_max_filesize</i> from php.ini
     *
     * Value in bytes (integer) or shorthand byte values (string) is allowed. 
     * The available options are K (for Kilobytes), M (for Megabytes) and G (for Gigabytes)
     *
     * @access public
     * @var double
     */
    var $file_max_size;

    /**
     * Set this variable to true to resize the file if it is an image
     *
     * You will probably want to set {@link image_x} and {@link image_y}, and maybe one of the ratio variables
     *
     * Default value is false (no resizing)
     *
     * @access public
     * @var bool
     */
    var $image_resize;

    /**
     * Set this variable to convert the file if it is an image
     *
     * Possibles values are : ''; 'png'; 'jpeg'; 'gif'; 'bmp'
     *
     * Default value is '' (no conversion)<br>
     * If {@link resize} is true, {@link convert} will be set to the source file extension
     *
     * @access public
     * @var string
     */
    var $image_convert;

    /**
     * Set this variable to the wanted (or maximum/minimum) width for the processed image, in pixels
     *
     * Default value is 150
     *
     * @access public
     * @var integer
     */
    var $image_x;

    /**
     * Set this variable to the wanted (or maximum/minimum) height for the processed image, in pixels
     *
     * Default value is 150
     *
     * @access public
     * @var integer
     */
    var $image_y;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y}
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y}
     *
     * The image will be resized as to fill the whole space, and excedent will be cropped
     *
     * Value can also be a string, one or more character from 'TBLR' (top, bottom, left and right)
     * If set as a string, it determines which side of the image is kept while cropping.
     * By default, the part of the image kept is in the center, i.e. it crops equally on both sides
     *
     * Default value is false
     *
     * @access public
     * @var mixed
     */
    var $image_ratio_crop;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y}
     *
     * The image will be resized to fit entirely in the space, and the rest will be colored.
     * The default color is white, but can be set with {@link image_default_color}
     *
     * Value can also be a string, one or more character from 'TBLR' (top, bottom, left and right)
     * If set as a string, it determines in which side of the space the image is displayed.
     * By default, the image is displayed in the center, i.e. it fills the remaining space equally on both sides
     *
     * Default value is false
     *
     * @access public
     * @var mixed
     */
    var $image_ratio_fill;

    /**
     * Set this variable to a number of pixels so that {@link image_x} and {@link image_y} are the best match possible
     *
     * The image will be resized to have approximatively the number of pixels
     * The aspect ratio wil be conserved
     *
     * Default value is false
     *
     * @access public
     * @var mixed
     */
    var $image_ratio_pixels;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y},
     * but only if original image is bigger
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_no_zoom_in;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y},
     * but only if original image is smaller
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_no_zoom_out;

    /**
     * Set this variable to calculate {@link image_x} automatically , using {@link image_y} and conserving ratio
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_x;

    /**
     * Set this variable to calculate {@link image_y} automatically , using {@link image_x} and conserving ratio
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_y;

    /**
     * Set this variable to set a maximum image width, above which the upload will be invalid
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_max_width;

    /**
     * Set this variable to set a maximum image height, above which the upload will be invalid
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_max_height;

    /**
     * Set this variable to set a maximum number of pixels for an image, above which the upload will be invalid
     *
     * Default value is null
     *
     * @access public
     * @var long
     */
    var $image_max_pixels;

    /**
     * Set this variable to set a maximum image aspect ratio, above which the upload will be invalid
     *
     * Note that ratio = width / height
     *
     * Default value is null
     *
     * @access public
     * @var float
     */
    var $image_max_ratio;

    /**
     * Set this variable to set a minimum image width, below which the upload will be invalid
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_min_width;

    /**
     * Set this variable to set a minimum image height, below which the upload will be invalid
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_min_height;

    /**
     * Set this variable to set a minimum number of pixels for an image, below which the upload will be invalid
     *
     * Default value is null
     *
     * @access public
     * @var long
     */
    var $image_min_pixels;

    /**
     * Set this variable to set a minimum image aspect ratio, below which the upload will be invalid
     *
     * Note that ratio = width / height
     *
     * Default value is null
     *
     * @access public
     * @var float
     */
    var $image_min_ratio;

    /**
     * Compression level for PNG images
     * 
     * Between 1 (fast but large files) and 9 (slow but smaller files)
     *
     * Default value is null (Zlib default)
     *
     * @access public
     * @var integer
     */
    var $png_compression;

    /**
     * Quality of JPEG created/converted destination image
     *
     * Default value is 85
     *
     * @access public
     * @var integer
     */
    var $jpeg_quality;

    /**
     * Determines the quality of the JPG image to fit a desired file size
     *
     * The JPG quality will be set between 1 and 100%
     * The calculations are approximations.
     *
     * Value in bytes (integer) or shorthand byte values (string) is allowed. 
     * The available options are K (for Kilobytes), M (for Megabytes) and G (for Gigabytes)
     *
     * Default value is null (no calculations)
     *
     * @access public
     * @var integer
     */
    var $jpeg_size;

    /**
     * Turns the interlace bit on
     *
     * This is actually used only for JPEG images, and defaults to false
     *
     * @access public
     * @var boolean
     */
    var $image_interlace;

    /**
     * Preserve transparency when resizing or converting an image (deprecated)
     *
     * Default value is automatically set to true for transparent GIFs
     * This setting is now deprecated
     *
     * @access public
     * @var integer
     */
    var $preserve_transparency;

    /**
     * Flag set to true when the image is transparent
     *
     * This is actually used only for transparent GIFs
     *
     * @access public
     * @var boolean
     */
    var $image_is_transparent;

    /**
     * Transparent color in a palette
     *
     * This is actually used only for transparent GIFs
     *
     * @access public
     * @var boolean
     */
    var $image_transparent_color;

    /**
     * Background color, used to paint transparent areas with
     *
     * If set, it will forcibly remove transparency by painting transparent areas with the color
     * This setting will fill in all transparent areas in PNG and GIF, as opposed to {@link image_default_color}
     * which will do so only in BMP, JPEG, and alpha transparent areas in transparent GIFs
     * This setting overrides {@link image_default_color}
     *
     * Default value is null
     *
     * @access public
     * @var string
     */
    var $image_background_color;

    /**
     * Default color for non alpha-transparent images
     *
     * This setting is to be used to define a background color for semi transparent areas
     * of an alpha transparent when the output format doesn't support alpha transparency
     * This is useful when, from an alpha transparent PNG image, or an image with alpha transparent features
     * if you want to output it as a transparent GIFs for instance, you can set a blending color for transparent areas
     * If you output in JPEG or BMP, this color will be used to fill in the previously transparent areas
     *
     * The default color white
     *
     * @access public
     * @var boolean
     */
    var $image_default_color;

    /**
     * Flag set to true when the image is not true color
     *
     * @access public
     * @var boolean
     */
    var $image_is_palette;

    /**
     * Corrects the image brightness
     *
     * Value can range between -127 and 127
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_brightness;

    /**
     * Corrects the image contrast
     *
     * Value can range between -127 and 127
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_contrast;

    /**
     * Changes the image opacity
     *
     * Value can range between 0 and 100
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_opacity;

    /**
     * Applies threshold filter
     *
     * Value can range between -127 and 127
     *
     * Default value is null
     *
     * @access public
     * @var integer
     */
    var $image_threshold;

    /**
     * Applies a tint on the image
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * Default value is null
     *
     * @access public
     * @var string;
     */
    var $image_tint_color;

    /**
     * Applies a colored overlay on the image
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * To use with {@link image_overlay_opacity}
     *
     * Default value is null
     *
     * @access public
     * @var string;
     */
    var $image_overlay_color;

    /**
     * Sets the opacity for the colored overlay
     *
     * Value is a percentage, as an integer between 0 (transparent) and 100 (opaque)
     *
     * Unless used with {@link image_overlay_color}, this setting has no effect
     *
     * Default value is 50
     *
     * @access public
     * @var integer
     */
    var $image_overlay_opacity;

    /**
     * Soon to be deprecated old form of {@link image_overlay_opacity}
     *
     * @access public
     * @var integer
     */
    var $image_overlay_percent;

    /**
     * Inverts the color of an image
     *
     * Default value is FALSE
     *
     * @access public
     * @var boolean;
     */
    var $image_negative;

    /**
     * Turns the image into greyscale
     *
     * Default value is FALSE
     *
     * @access public
     * @var boolean;
     */
    var $image_greyscale;

    /**
     * Pixelate an image
     *
     * Value is integer, represents the block size
     *
     * Default value is null
     *
     * @access public
     * @var integer;
     */
    var $image_pixelate;

    /**
     * Applies an unsharp mask, with alpha transparency support
     *
     * Beware that this unsharp mask is quite resource-intensive
     *
     * Default value is FALSE
     *
     * @access public
     * @var boolean;
     */
    var $image_unsharp;

    /**
     * Sets the unsharp mask amount
     *
     * Value is an integer between 0 and 500, typically between 50 and 200
     *
     * Unless used with {@link image_unsharp}, this setting has no effect
     *
     * Default value is 80
     *
     * @access public
     * @var integer
     */
    var $image_unsharp_amount;
 
    /**
     * Sets the unsharp mask radius
     *
     * Value is an integer between 0 and 50, typically between 0.5 and 1
     * It is not recommended to change it, the default works best
     *
     * Unless used with {@link image_unsharp}, this setting has no effect
     *
     * From PHP 5.1, imageconvolution is used, and this setting has no effect
     *
     * Default value is 0.5
     *
     * @access public
     * @var integer
     */
    var $image_unsharp_radius;
 
    /**
     * Sets the unsharp mask threshold
     *
     * Value is an integer between 0 and 255, typically between 0 and 5
     *
     * Unless used with {@link image_unsharp}, this setting has no effect
     *
     * Default value is 1
     *
     * @access public
     * @var integer
     */
    var $image_unsharp_threshold;

    /**
     * Adds a text label on the image
     *
     * Value is a string, any text. Text will not word-wrap, although you can use breaklines in your text "\n"
     *
     * If set, this setting allow the use of all other settings starting with image_text_
     *
     * Replacement tokens can be used in the string:
     * <pre>
     * gd_version    src_name       src_name_body src_name_ext
     * src_pathname  src_mime       src_x         src_y
     * src_type      src_bits       src_pixels
     * src_size      src_size_kb    src_size_mb   src_size_human
     * dst_path      dst_name_body  dst_pathname
     * dst_name      dst_name_ext   dst_x         dst_y
     * date          time           host          server        ip
     * </pre>
     * The tokens must be enclosed in square brackets: [dst_x] will be replaced by the width of the picture
     *
     * Default value is null
     *
     * @access public
     * @var string;
     */
    var $image_text;

    /**
     * Sets the text direction for the text label
     *
     * Value is either 'h' or 'v', as in horizontal and vertical
     *
     * Default value is h (horizontal)
     *
     * @access public
     * @var string;
     */
    var $image_text_direction;

    /**
     * Sets the text color for the text label
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * Default value is #FFFFFF (white)
     *
     * @access public
     * @var string;
     */
    var $image_text_color;

    /**
     * Sets the text opacity in the text label
     *
     * Value is a percentage, as an integer between 0 (transparent) and 100 (opaque)
     *
     * Default value is 100
     *
     * @access public
     * @var integer
     */
    var $image_text_opacity;

    /**
     * Soon to be deprecated old form of {@link image_text_opacity}
     *
     * @access public
     * @var integer
     */
    var $image_text_percent;

    /**
     * Sets the text background color for the text label
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * Default value is null (no background)
     *
     * @access public
     * @var string;
     */
    var $image_text_background;

    /**
     * Sets the text background opacity in the text label
     *
     * Value is a percentage, as an integer between 0 (transparent) and 100 (opaque)
     *
     * Default value is 100
     *
     * @access public
     * @var integer
     */
    var $image_text_background_opacity;

    /**
     * Soon to be deprecated old form of {@link image_text_background_opacity}
     *
     * @access public
     * @var integer
     */
    var $image_text_background_percent;

    /**
     * Sets the text font in the text label
     *
     * Value is a an integer between 1 and 5 for GD built-in fonts. 1 is the smallest font, 5 the biggest
     * Value can also be a string, which represents the path to a GDF font. The font will be loaded into GD, and used as a built-in font.
     *
     * Default value is 5
     *
     * @access public
     * @var mixed;
     */
    var $image_text_font;

    /**
     * Sets the text label position within the image
     *
     * Value is one or two out of 'TBLR' (top, bottom, left, right)
     *
     * The positions are as following:
     * <pre>
     *                        TL  T  TR
     *                        L       R
     *                        BL  B  BR
     * </pre>
     *
     * Default value is null (centered, horizontal and vertical)
     *
     * Note that is {@link image_text_x} and {@link image_text_y} are used, this setting has no effect
     *
     * @access public
     * @var string;
     */
    var $image_text_position;

    /**
     * Sets the text label absolute X position within the image
     *
     * Value is in pixels, representing the distance between the left of the image and the label
     * If a negative value is used, it will represent the distance between the right of the image and the label
     *
     * Default value is null (so {@link image_text_position} is used)
     *
     * @access public
     * @var integer
     */
    var $image_text_x;

    /**
     * Sets the text label absolute Y position within the image
     *
     * Value is in pixels, representing the distance between the top of the image and the label
     * If a negative value is used, it will represent the distance between the bottom of the image and the label
     *
     * Default value is null (so {@link image_text_position} is used)
     *
     * @access public
     * @var integer
     */
    var $image_text_y;

    /**
     * Sets the text label padding
     *
     * Value is in pixels, representing the distance between the text and the label background border
     *
     * Default value is 0
     *
     * This setting can be overriden by {@link image_text_padding_x} and {@link image_text_padding_y}
     *
     * @access public
     * @var integer
     */
    var $image_text_padding;

    /**
     * Sets the text label horizontal padding
     *
     * Value is in pixels, representing the distance between the text and the left and right label background borders
     *
     * Default value is null
     *
     * If set, this setting overrides the horizontal part of {@link image_text_padding}
     *
     * @access public
     * @var integer
     */
    var $image_text_padding_x;

    /**
     * Sets the text label vertical padding
     *
     * Value is in pixels, representing the distance between the text and the top and bottom label background borders
     *
     * Default value is null
     *
     * If set, his setting overrides the vertical part of {@link image_text_padding}
     *
     * @access public
     * @var integer
     */
    var $image_text_padding_y;

    /**
     * Sets the text alignment
     *
     * Value is a string, which can be either 'L', 'C' or 'R'
     *
     * Default value is 'C'
     *
     * This setting is relevant only if the text has several lines.
     *
     * @access public
     * @var string;
     */
    var $image_text_alignment;

    /**
     * Sets the text line spacing
     *
     * Value is an integer, in pixels
     *
     * Default value is 0
     *
     * This setting is relevant only if the text has several lines.
     *
     * @access public
     * @var integer
     */
    var $image_text_line_spacing;

    /**
     * Sets the height of the reflection
     *
     * Value is an integer in pixels, or a string which format can be in pixels or percentage.
     * For instance, values can be : 40, '40', '40px' or '40%'
     *
     * Default value is null, no reflection
     *
     * @access public
     * @var mixed;
     */
    var $image_reflection_height;

    /**
     * Sets the space between the source image and its relection
     *
     * Value is an integer in pixels, which can be negative
     *
     * Default value is 2
     *
     * This setting is relevant only if {@link image_reflection_height} is set
     *
     * @access public
     * @var integer
     */
    var $image_reflection_space;

    /**
     * Sets the color of the reflection background (deprecated)
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * Default value is #FFFFFF
     *
     * This setting is relevant only if {@link image_reflection_height} is set
     *
     * This setting is now deprecated in favor of {@link image_default_color}
     *
     * @access public
     * @var string;
     */
    var $image_reflection_color;

    /**
     * Sets the initial opacity of the reflection
     *
     * Value is an integer between 0 (no opacity) and 100 (full opacity).
     * The reflection will start from {@link image_reflection_opacity} and end up at 0
     *
     * Default value is 60
     *
     * This setting is relevant only if {@link image_reflection_height} is set
     *
     * @access public
     * @var integer
     */
    var $image_reflection_opacity;

    /**
     * Flips the image vertically or horizontally
     *
     * Value is either 'h' or 'v', as in horizontal and vertical
     *
     * Default value is null (no flip)
     *
     * @access public
     * @var string;
     */
    var $image_flip;

    /**
     * Rotates the image by increments of 45 degrees
     *
     * Value is either 90, 180 or 270
     *
     * Default value is null (no rotation)
     *
     * @access public
     * @var string;
     */
    var $image_rotate;

    /**
     * Crops an image
     *
     * Values are four dimensions, or two, or one (CSS style)
     * They represent the amount cropped top, right, bottom and left.
     * These values can either be in an array, or a space separated string.
     * Each value can be in pixels (with or without 'px'), or percentage (of the source image)
     *
     * For instance, are valid:
     * <pre>
     * $foo->image_crop = 20                  OR array(20);
     * $foo->image_crop = '20px'              OR array('20px');
     * $foo->image_crop = '20 40'             OR array('20', 40);
     * $foo->image_crop = '-20 25%'           OR array(-20, '25%');
     * $foo->image_crop = '20px 25%'          OR array('20px', '25%');
     * $foo->image_crop = '20% 25%'           OR array('20%', '25%');
     * $foo->image_crop = '20% 25% 10% 30%'   OR array('20%', '25%', '10%', '30%');
     * $foo->image_crop = '20px 25px 2px 2px' OR array('20px', '25%px', '2px', '2px');
     * $foo->image_crop = '20 25% 40px 10%'   OR array(20, '25%', '40px', '10%');
     * </pre>
     *
     * If a value is negative, the image will be expanded, and the extra parts will be filled with black
     *
     * Default value is null (no cropping)
     *
     * @access public
     * @var string OR array;
     */
    var $image_crop;

    /**
     * Crops an image, before an eventual resizing
     *
     * See {@link image_crop} for valid formats
     *
     * Default value is null (no cropping)
     *
     * @access public
     * @var string OR array;
     */
    var $image_precrop;

    /**
     * Adds a bevel border on the image
     *
     * Value is a positive integer, representing the thickness of the bevel
     *
     * If the bevel colors are the same as the background, it makes a fade out effect
     *
     * Default value is null (no bevel)
     *
     * @access public
     * @var integer
     */
    var $image_bevel;

    /**
     * Top and left bevel color
     *
     * Value is a color, in hexadecimal format
     * This setting is used only if {@link image_bevel} is set
     *
     * Default value is #FFFFFF
     *
     * @access public
     * @var string;
     */
    var $image_bevel_color1;

    /**
     * Right and bottom bevel color
     *
     * Value is a color, in hexadecimal format
     * This setting is used only if {@link image_bevel} is set
     *
     * Default value is #000000
     *
     * @access public
     * @var string;
     */
    var $image_bevel_color2;

    /**
     * Adds a single-color border on the outer of the image
     *
     * Values are four dimensions, or two, or one (CSS style)
     * They represent the border thickness top, right, bottom and left.
     * These values can either be in an array, or a space separated string.
     * Each value can be in pixels (with or without 'px'), or percentage (of the source image)
     *
     * See {@link image_crop} for valid formats
     *
     * If a value is negative, the image will be cropped.
     * Note that the dimensions of the picture will be increased by the borders' thickness
     *
     * Default value is null (no border)
     *
     * @access public
     * @var integer
     */
    var $image_border;

    /**
     * Border color
     *
     * Value is a color, in hexadecimal format.
     * This setting is used only if {@link image_border} is set
     *
     * Default value is #FFFFFF
     *
     * @access public
     * @var string;
     */
    var $image_border_color;

    /**
     * Sets the opacity for the borders
     *
     * Value is a percentage, as an integer between 0 (transparent) and 100 (opaque)
     *
     * Unless used with {@link image_border}, this setting has no effect
     *
     * Default value is 100
     *
     * @access public
     * @var integer
     */
    var $image_border_opacity;

    /**
     * Adds a fading-to-transparent border on the image
     *
     * Values are four dimensions, or two, or one (CSS style)
     * They represent the border thickness top, right, bottom and left.
     * These values can either be in an array, or a space separated string.
     * Each value can be in pixels (with or without 'px'), or percentage (of the source image)
     *
     * See {@link image_crop} for valid formats
     *
     * Note that the dimensions of the picture will not be increased by the borders' thickness
     *
     * Default value is null (no border)
     *
     * @access public
     * @var integer
     */
    var $image_border_transparent;

    /**
     * Adds a multi-color frame on the outer of the image
     *
     * Value is an integer. Two values are possible for now:
     * 1 for flat border, meaning that the frame is mirrored horizontally and vertically
     * 2 for crossed border, meaning that the frame will be inversed, as in a bevel effect
     *
     * The frame will be composed of colored lines set in {@link image_frame_colors}
     *
     * Note that the dimensions of the picture will be increased by the borders' thickness
     *
     * Default value is null (no frame)
     *
     * @access public
     * @var integer
     */
    var $image_frame;

    /**
     * Sets the colors used to draw a frame
     *
     * Values is a list of n colors in hexadecimal format.
     * These values can either be in an array, or a space separated string.
     *
     * The colors are listed in the following order: from the outset of the image to its center
     *
     * For instance, are valid:
     * <pre>
     * $foo->image_frame_colors = '#FFFFFF #999999 #666666 #000000';
     * $foo->image_frame_colors = array('#FFFFFF', '#999999', '#666666', '#000000');
     * </pre>
     *
     * This setting is used only if {@link image_frame} is set
     *
     * Default value is '#FFFFFF #999999 #666666 #000000'
     *
     * @access public
     * @var string OR array;
     */
    var $image_frame_colors;

    /**
     * Sets the opacity for the frame
     *
     * Value is a percentage, as an integer between 0 (transparent) and 100 (opaque)
     *
     * Unless used with {@link image_frame}, this setting has no effect
     *
     * Default value is 100
     *
     * @access public
     * @var integer
     */
    var $image_frame_opacity;

    /**
     * Adds a watermark on the image
     *
     * Value is a local image filename, relative or absolute. GIF, JPG, BMP and PNG are supported, as well as PNG alpha.
     *
     * If set, this setting allow the use of all other settings starting with image_watermark_
     *
     * Default value is null
     *
     * @access public
     * @var string;
     */
    var $image_watermark;

    /**
     * Sets the watermarkposition within the image
     *
     * Value is one or two out of 'TBLR' (top, bottom, left, right)
     *
     * The positions are as following:   TL  T  TR
     *                                   L       R
     *                                   BL  B  BR
     *
     * Default value is null (centered, horizontal and vertical)
     *
     * Note that is {@link image_watermark_x} and {@link image_watermark_y} are used, this setting has no effect
     *
     * @access public
     * @var string;
     */
    var $image_watermark_position;

    /**
     * Sets the watermark absolute X position within the image
     *
     * Value is in pixels, representing the distance between the top of the image and the watermark
     * If a negative value is used, it will represent the distance between the bottom of the image and the watermark
     *
     * Default value is null (so {@link image_watermark_position} is used)
     *
     * @access public
     * @var integer
     */
    var $image_watermark_x;

    /**
     * Sets the twatermark absolute Y position within the image
     *
     * Value is in pixels, representing the distance between the left of the image and the watermark
     * If a negative value is used, it will represent the distance between the right of the image and the watermark
     *
     * Default value is null (so {@link image_watermark_position} is used)
     *
     * @access public
     * @var integer
     */
    var $image_watermark_y;

    /**
     * Prevents the watermark to be resized up if it is smaller than the image
     *
     * If the watermark if smaller than the destination image, taking in account the desired watermark position
     * then it will be resized up to fill in the image (minus the {@link image_watermark_x} or {@link image_watermark_y} values)
     *
     * If you don't want your watermark to be resized in any way, then
     * set {@link image_watermark_no_zoom_in} and {@link image_watermark_no_zoom_out} to true
     * If you want your watermark to be resized up or doan to fill in the image better, then
     * set {@link image_watermark_no_zoom_in} and {@link image_watermark_no_zoom_out} to false
     *
     * Default value is true (so the watermark will not be resized up, which is the behaviour most people expect)
     *
     * @access public
     * @var integer
     */
    var $image_watermark_no_zoom_in;

    /**
     * Prevents the watermark to be resized down if it is bigger than the image 
     *
     * If the watermark if bigger than the destination image, taking in account the desired watermark position
     * then it will be resized down to fit in the image (minus the {@link image_watermark_x} or {@link image_watermark_y} values)
     *
     * If you don't want your watermark to be resized in any way, then
     * set {@link image_watermark_no_zoom_in} and {@link image_watermark_no_zoom_out} to true
     * If you want your watermark to be resized up or doan to fill in the image better, then
     * set {@link image_watermark_no_zoom_in} and {@link image_watermark_no_zoom_out} to false
     *     
     * Default value is false (so the watermark may be shrinked to fit in the image)
     *
     * @access public
     * @var integer
     */
    var $image_watermark_no_zoom_out;

    /**
     * List of MIME types per extension
     *
     * @access private
     * @var array
     */
    var $mime_types;

    /**
     * Allowed MIME types
     *
     * Default is a selection of safe mime-types, but you might want to change it
     *
     * Simple wildcards are allowed, such as image/* or application/*
     * If there is only one MIME type allowed, then it can be a string instead of an array
     *
     * @access public
     * @var array OR string
     */
    var $allowed;

    /**
     * Forbidden MIME types
     *
     * Default is a selection of safe mime-types, but you might want to change it
     * To only check for forbidden MIME types, and allow everything else, set {@link allowed} to array('* / *') without the spaces
     *
     * Simple wildcards are allowed, such as image/* or application/*
     * If there is only one MIME type forbidden, then it can be a string instead of an array
     *
     * @access public
     * @var array OR string
     */
    var $forbidden;

    /**
     * Array of translated error messages
     *
     * By default, the language is english (en_GB)
     * Translations can be in separate files, in a lang/ subdirectory
     *
     * @access public
     * @var array
     */
    var $translation;

    /**
     * Language selected for the translations
     *
     * By default, the language is english ("en_GB")
     *
     * @access public
     * @var array
     */
    var $language;

    /**
     * Init or re-init all the processing variables to their default values
     *
     * This function is called in the constructor, and after each call of {@link process}
     *
     * @access private
     */
    function init() {

        // overiddable variables
        $this->file_new_name_body       = null;     // replace the name body
        $this->file_name_body_add       = null;     // append to the name body
        $this->file_name_body_pre       = null;     // prepend to the name body
        $this->file_new_name_ext        = null;     // replace the file extension
        $this->file_safe_name           = true;     // format safely the filename
        $this->file_force_extension     = true;     // forces extension if there isn't one
        $this->file_overwrite           = false;    // allows overwritting if the file already exists
        $this->file_auto_rename         = true;     // auto-rename if the file already exists
        $this->dir_auto_create          = true;     // auto-creates directory if missing
        $this->dir_auto_chmod           = true;     // auto-chmod directory if not writeable
        $this->dir_chmod                = 0777;     // default chmod to use

        $this->no_script                = true;     // turns scripts into test files
        $this->mime_check               = true;     // checks the mime type against the allowed list

        // these are the different MIME detection methods. if one of these method doesn't work on your
        // system, you can deactivate it here; just set it to false
        $this->mime_fileinfo            = true;     // MIME detection with Fileinfo PECL extension
        $this->mime_file                = true;     // MIME detection with UNIX file() command
        $this->mime_magic               = true;     // MIME detection with mime_magic (mime_content_type())
        $this->mime_getimagesize        = true;     // MIME detection with getimagesize()

        // get the default max size from php.ini
        $this->file_max_size_raw = trim(ini_get('upload_max_filesize'));
        $this->file_max_size = $this->getsize($this->file_max_size_raw);

        $this->image_resize             = false;    // resize the image
        $this->image_convert            = '';       // convert. values :''; 'png'; 'jpeg'; 'gif'; 'bmp'

        $this->image_x                  = 150;
        $this->image_y                  = 150;
        $this->image_ratio              = false;    // keeps aspect ratio with x and y dimensions
        $this->image_ratio_crop         = false;    // keeps aspect ratio with x and y dimensions, filling the space
        $this->image_ratio_fill         = false;    // keeps aspect ratio with x and y dimensions, fitting the image in the space, and coloring the rest
        $this->image_ratio_pixels       = false;    // keeps aspect ratio, calculating x and y so that the image is approx the set number of pixels
        $this->image_ratio_no_zoom_in   = false;
        $this->image_ratio_no_zoom_out  = false;
        $this->image_ratio_x            = false;    // calculate the $image_x if true
        $this->image_ratio_y            = false;    // calculate the $image_y if true
        $this->png_compression          = null;
        $this->jpeg_quality             = 85;
        $this->jpeg_size                = null;
        $this->image_interlace          = false;
        $this->preserve_transparency    = false;
        $this->image_is_transparent     = false;
        $this->image_transparent_color  = null;
        $this->image_background_color   = null;
        $this->image_default_color      = '#ffffff';
        $this->image_is_palette         = false;

        $this->image_max_width          = null;
        $this->image_max_height         = null;
        $this->image_max_pixels         = null;
        $this->image_max_ratio          = null;
        $this->image_min_width          = null;
        $this->image_min_height         = null;
        $this->image_min_pixels         = null;
        $this->image_min_ratio          = null;

        $this->image_brightness         = null;
        $this->image_contrast           = null;
        $this->image_opacity            = null;
        $this->image_threshold          = null;
        $this->image_tint_color         = null;
        $this->image_overlay_color      = null;
        $this->image_overlay_opacity    = null;
        $this->image_overlay_percent    = null;
        $this->image_negative           = false;
        $this->image_greyscale          = false;
        $this->image_pixelate           = null;
        $this->image_unsharp            = false;
        $this->image_unsharp_amount     = 80;
        $this->image_unsharp_radius     = 0.5;
        $this->image_unsharp_threshold  = 1;

        $this->image_text               = null;
        $this->image_text_direction     = null;
        $this->image_text_color         = '#FFFFFF';
        $this->image_text_opacity       = 100;
        $this->image_text_percent       = 100;
        $this->image_text_background    = null;
        $this->image_text_background_opacity = 100;
        $this->image_text_background_percent = 100;
        $this->image_text_font          = 5;
        $this->image_text_x             = null;
        $this->image_text_y             = null;
        $this->image_text_position      = null;
        $this->image_text_padding       = 0;
        $this->image_text_padding_x     = null;
        $this->image_text_padding_y     = null;
        $this->image_text_alignment     = 'C';
        $this->image_text_line_spacing  = 0;

        $this->image_reflection_height  = null;
        $this->image_reflection_space   = 2;
        $this->image_reflection_color   = '#ffffff';
        $this->image_reflection_opacity = 60;

        $this->image_watermark          = null;
        $this->image_watermark_x        = null;
        $this->image_watermark_y        = null;
        $this->image_watermark_position = null;
        $this->image_watermark_no_zoom_in  = true;
        $this->image_watermark_no_zoom_out = false;

        $this->image_flip               = null;
        $this->image_rotate             = null;
        $this->image_crop               = null;
        $this->image_precrop            = null;

        $this->image_bevel              = null;
        $this->image_bevel_color1       = '#FFFFFF';
        $this->image_bevel_color2       = '#000000';
        $this->image_border             = null;
        $this->image_border_color       = '#FFFFFF';
        $this->image_border_opacity     = 100;
        $this->image_border_transparent = null;
        $this->image_frame              = null;
        $this->image_frame_colors       = '#FFFFFF #999999 #666666 #000000';
        $this->image_frame_opacity      = 100;

        $this->forbidden = array();
        $this->allowed = array(
            'application/arj',
            'application/excel',
            'application/gnutar',
            'application/mspowerpoint',
            'application/msword',
            'application/octet-stream',
            'application/onenote',
            'application/pdf',
            'application/plain',
            'application/postscript',
            'application/powerpoint',
            'application/rar',
            'application/rtf',
            'application/vnd.ms-excel',
            'application/vnd.ms-excel.addin.macroEnabled.12',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.ms-office',
            'application/vnd.ms-officetheme',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'application/vnd.ms-word',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.ms-word.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.chart',
            'application/vnd.oasis.opendocument.database',
            'application/vnd.oasis.opendocument.formula',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.graphics-template',
            'application/vnd.oasis.opendocument.image',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.presentation-template',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.spreadsheet-template',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.text-master',
            'application/vnd.oasis.opendocument.text-template',
            'application/vnd.oasis.opendocument.text-web',
            'application/vnd.openofficeorg.extension',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'application/vocaltec-media-file',
            'application/wordperfect',
            'application/x-bittorrent',
            'application/x-bzip',
            'application/x-bzip2',
            'application/x-compressed',
            'application/x-excel',
            'application/x-gzip',
            'application/x-latex',
            'application/x-midi',
            'application/xml',
            'application/x-msexcel',
            'application/x-rar',
            'application/x-rar-compressed',
            'application/x-rtf',
            'application/x-shockwave-flash',
            'application/x-sit',
            'application/x-stuffit',
            'application/x-troff-msvideo',
            'application/x-zip',
            'application/x-zip-compressed',
            'application/zip',
            'audio/*',
            'image/*',
            'multipart/x-gzip',
            'multipart/x-zip',
            'text/plain',
            'text/rtf',
            'text/richtext',
            'text/xml',
            'video/*'
        );

        $this->mime_types = array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'flv' => 'video/x-flv',
            'js' => 'application/x-javascript',
            'json' => 'application/json',
            'tiff' => 'image/tiff',
            'css' => 'text/css',
            'xml' => 'application/xml',
            'doc' => 'application/msword',
            'docx' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'xlt' => 'application/vnd.ms-excel',
            'xlm' => 'application/vnd.ms-excel',
            'xld' => 'application/vnd.ms-excel',
            'xla' => 'application/vnd.ms-excel',
            'xlc' => 'application/vnd.ms-excel',
            'xlw' => 'application/vnd.ms-excel',
            'xll' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pps' => 'application/vnd.ms-powerpoint',
            'rtf' => 'application/rtf',
            'pdf' => 'application/pdf',
            'html' => 'text/html',
            'htm' => 'text/html',
            'php' => 'text/html',
            'txt' => 'text/plain',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mp3' => 'audio/mpeg3',
            'wav' => 'audio/wav',
            'aiff' => 'audio/aiff',
            'aif' => 'audio/aiff',
            'avi' => 'video/msvideo',
            'wmv' => 'video/x-ms-wmv',
            'mov' => 'video/quicktime',
            'zip' => 'application/zip',
            'tar' => 'application/x-tar',
            'swf' => 'application/x-shockwave-flash',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ott' => 'application/vnd.oasis.opendocument.text-template',
            'oth' => 'application/vnd.oasis.opendocument.text-web',
            'odm' => 'application/vnd.oasis.opendocument.text-master',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'otg' => 'application/vnd.oasis.opendocument.graphics-template',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'otp' => 'application/vnd.oasis.opendocument.presentation-template',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odi' => 'application/vnd.oasis.opendocument.image',
            'oxt' => 'application/vnd.openofficeorg.extension',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'thmx' => 'application/vnd.ms-officetheme',
            'onetoc' => 'application/onenote',
            'onetoc2' => 'application/onenote',
            'onetmp' => 'application/onenote',
            'onepkg' => 'application/onenote',
        );

    }

    /**
     * Constructor. Checks if the file has been uploaded
     *
     * The constructor takes $_FILES['form_field'] array as argument
     * where form_field is the form field name
     *
     * The constructor will check if the file has been uploaded in its temporary location, and
     * accordingly will set {@link uploaded} (and {@link error} is an error occurred)
     *
     * If the file has been uploaded, the constructor will populate all the variables holding the upload
     * information (none of the processing class variables are used here).
     * You can have access to information about the file (name, size, MIME type...).
     *
     *
     * Alternatively, you can set the first argument to be a local filename (string)
     * This allows processing of a local file, as if the file was uploaded
     *
     * The optional second argument allows you to set the language for the error messages
     *
     * @access private
     * @param  array  $file $_FILES['form_field']
     *    or   string $file Local filename
     * @param  string $lang Optional language code
     */
    function thumnail_upload($file, $lang = 'en_GB') {

        $this->version            = '0.33dev';

        $this->file_src_name      = '';
        $this->file_src_name_body = '';
        $this->file_src_name_ext  = '';
        $this->file_src_mime      = '';
        $this->file_src_size      = '';
        $this->file_src_error     = '';
        $this->file_src_pathname  = '';
        $this->file_src_temp      = '';

        $this->file_dst_path      = '';
        $this->file_dst_name      = '';
        $this->file_dst_name_body = '';
        $this->file_dst_name_ext  = '';
        $this->file_dst_pathname  = '';

        $this->image_src_x        = null;
        $this->image_src_y        = null;
        $this->image_src_bits     = null;
        $this->image_src_type     = null;
        $this->image_src_pixels   = null;
        $this->image_dst_x        = 0;
        $this->image_dst_y        = 0;
        $this->image_dst_type     = '';

        $this->uploaded           = true;
        $this->no_upload_check    = false;
        $this->processed          = true;
        $this->error              = '';
        $this->log                = '';
        $this->allowed            = array();
        $this->forbidden          = array();
        $this->file_is_image      = false;
        $this->init();
        $info                     = null;
        $mime_from_browser        = null;

        // sets default language
        $this->translation        = array();
        $this->translation['file_error']                  = 'File error. Please try again.';
        $this->translation['local_file_missing']          = 'Local file doesn\'t exist.';
        $this->translation['local_file_not_readable']     = 'Local file is not readable.';
        $this->translation['uploaded_too_big_ini']        = 'File upload error (the uploaded file exceeds the upload_max_filesize directive in php.ini).';
        $this->translation['uploaded_too_big_html']       = 'File upload error (the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form).';
        $this->translation['uploaded_partial']            = 'File upload error (the uploaded file was only partially uploaded).';
        $this->translation['uploaded_missing']            = 'File upload error (no file was uploaded).';
        $this->translation['uploaded_no_tmp_dir']         = 'File upload error (missing a temporary folder).';
        $this->translation['uploaded_cant_write']         = 'File upload error (failed to write file to disk).';
        $this->translation['uploaded_err_extension']      = 'File upload error (file upload stopped by extension).';
        $this->translation['uploaded_unknown']            = 'File upload error (unknown error code).';
        $this->translation['try_again']                   = 'File upload error. Please try again.';
        $this->translation['file_too_big']                = 'File too big.';
        $this->translation['no_mime']                     = 'MIME type can\'t be detected.';
        $this->translation['incorrect_file']              = 'Incorrect type of file.';
        $this->translation['image_too_wide']              = 'Image too wide.';
        $this->translation['image_too_narrow']            = 'Image too narrow.';
        $this->translation['image_too_high']              = 'Image too tall.';
        $this->translation['image_too_short']             = 'Image too short.';
        $this->translation['ratio_too_high']              = 'Image ratio too high (image too wide).';
        $this->translation['ratio_too_low']               = 'Image ratio too low (image too high).';
        $this->translation['too_many_pixels']             = 'Image has too many pixels.';
        $this->translation['not_enough_pixels']           = 'Image has not enough pixels.';
        $this->translation['file_not_uploaded']           = 'File not uploaded. Can\'t carry on a process.';
        $this->translation['already_exists']              = '%s already exists. Please change the file name.';
        $this->translation['temp_file_missing']           = 'No correct temp source file. Can\'t carry on a process.';
        $this->translation['source_missing']              = 'No correct uploaded source file. Can\'t carry on a process.';
        $this->translation['destination_dir']             = 'Destination directory can\'t be created. Can\'t carry on a process.';
        $this->translation['destination_dir_missing']     = 'Destination directory doesn\'t exist. Can\'t carry on a process.';
        $this->translation['destination_path_not_dir']    = 'Destination path is not a directory. Can\'t carry on a process.';
        $this->translation['destination_dir_write']       = 'Destination directory can\'t be made writeable. Can\'t carry on a process.';
        $this->translation['destination_path_write']      = 'Destination path is not a writeable. Can\'t carry on a process.';
        $this->translation['temp_file']                   = 'Can\'t create the temporary file. Can\'t carry on a process.';
        $this->translation['source_not_readable']         = 'Source file is not readable. Can\'t carry on a process.';
        $this->translation['no_create_support']           = 'No create from %s support.';
        $this->translation['create_error']                = 'Error in creating %s image from source.';
        $this->translation['source_invalid']              = 'Can\'t read image source. Not an image?.';
        $this->translation['gd_missing']                  = 'GD doesn\'t seem to be present.';
        $this->translation['watermark_no_create_support'] = 'No create from %s support, can\'t read watermark.';
        $this->translation['watermark_create_error']      = 'No %s read support, can\'t create watermark.';
        $this->translation['watermark_invalid']           = 'Unknown image format, can\'t read watermark.';
        $this->translation['file_create']                 = 'No %s create support.';
        $this->translation['no_conversion_type']          = 'No conversion type defined.';
        $this->translation['copy_failed']                 = 'Error copying file on the server. copy() failed.';
        $this->translation['reading_failed']              = 'Error reading the file.';

        // determines the language
        $this->lang               = $lang;
        if ($this->lang != 'en_GB' && file_exists(dirname(__FILE__).'/lang') && file_exists(dirname(__FILE__).'/lang/class.upload.' . $lang . '.php')) {
            $translation = null;
            include(dirname(__FILE__).'/lang/class.upload.' . $lang . '.php');
            if (is_array($translation)) {
                $this->translation = array_merge($this->translation, $translation);
            } else {
                $this->lang = 'en_GB';
            }
        }


        // determines the supported MIME types, and matching image format
        $this->image_supported = array();
        if ($this->gdversion()) {
            if (imagetypes() & IMG_GIF) {
                $this->image_supported['image/gif'] = 'gif';
            }
            if (imagetypes() & IMG_JPG) {
                $this->image_supported['image/jpg'] = 'jpg';
                $this->image_supported['image/jpeg'] = 'jpg';
                $this->image_supported['image/pjpeg'] = 'jpg';
            }
            if (imagetypes() & IMG_PNG) {
                $this->image_supported['image/png'] = 'png';
                $this->image_supported['image/x-png'] = 'png';
            }
            if (imagetypes() & IMG_WBMP) {
                $this->image_supported['image/bmp'] = 'bmp';
                $this->image_supported['image/x-ms-bmp'] = 'bmp';
                $this->image_supported['image/x-windows-bmp'] = 'bmp';
            }
        }

        // display some system information
        if (empty($this->log)) {
            $this->log .= '<b>system information</b><br />';
            if (function_exists('ini_get_all')) {
                $inis = ini_get_all();
                $open_basedir = (array_key_exists('open_basedir', $inis) && array_key_exists('local_value', $inis['open_basedir']) && !empty($inis['open_basedir']['local_value'])) ? $inis['open_basedir']['local_value'] : false;
            } else {
                $open_basedir = false;
            }
            $gd           = $this->gdversion() ? $this->gdversion(true) : 'GD not present';
            $supported    = trim((in_array('png', $this->image_supported) ? 'png' : '') . ' ' . (in_array('jpg', $this->image_supported) ? 'jpg' : '') . ' ' . (in_array('gif', $this->image_supported) ? 'gif' : '') . ' ' . (in_array('bmp', $this->image_supported) ? 'bmp' : ''));
            $this->log .= '-&nbsp;class version           : ' . $this->version . '<br />';
            $this->log .= '-&nbsp;operating system        : ' . PHP_OS . '<br />';
            $this->log .= '-&nbsp;PHP version             : ' . PHP_VERSION . '<br />';
            $this->log .= '-&nbsp;GD version              : ' . $gd . '<br />';
            $this->log .= '-&nbsp;supported image types   : ' . (!empty($supported) ? $supported : 'none') . '<br />';
            $this->log .= '-&nbsp;open_basedir            : ' . (!empty($open_basedir) ? $open_basedir : 'no restriction') . '<br />';
            $this->log .= '-&nbsp;upload_max_filesize     : ' . $this->file_max_size_raw . ' (' . $this->file_max_size . ' bytes)<br />';
            $this->log .= '-&nbsp;language                : ' . $this->lang . '<br />';
        }

        if (!$file) {
            $this->uploaded = false;
            $this->error = $this->translate('file_error');
        }

        // check if we sent a local filename or a PHP stream rather than a $_FILE element
        if (!is_array($file)) {
            if (empty($file)) {
                $this->uploaded = false;
                $this->error = $this->translate('file_error');
            } else {
                if (substr($file, 0, 4) == 'php:') {
                    // this is a local filename, i.e.not uploaded
                    $file = preg_replace('/^php:(.*)/i', '$1', $file);
                    if (!$file) $file = $_SERVER['HTTP_X_FILE_NAME'];
                    if (!$file) $file = 'unknown';
                    $this->log .= '<b>' . $this->translate("source is a PHP stream") . ' ' . $file . '</b><br />';
                    $this->no_upload_check = TRUE;
                    
                    $this->log .= '- this is a PHP stream, requires a temp file ... ';
                    $hash = $this->temp_dir() . md5($file . rand(1, 1000));
                    if (file_put_contents($hash, file_get_contents('php://input'))) {
                        $this->file_src_pathname = $hash;
                        $this->log .= ' file created<br />';
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;temp file is: ' . $this->file_src_pathname . '<br />';
                    } else {
                        $this->log .= ' failed<br />';
                        $this->uploaded = false;
                        $this->error = $this->translate('temp_file');
                    }

                    if ($this->uploaded) {
                        $this->file_src_name       = $file;
                        $this->log .= '- local file OK<br />';
                        preg_match('/\.([^\.]*$)/', $this->file_src_name, $extension);
                        if (is_array($extension) && sizeof($extension) > 0) {
                            $this->file_src_name_ext      = strtolower($extension[1]);
                            $this->file_src_name_body     = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
                        } else {
                            $this->file_src_name_ext      = '';
                            $this->file_src_name_body     = $this->file_src_name;
                        }
                        $this->file_src_size = (file_exists($this->file_src_pathname) ? filesize($this->file_src_pathname) : 0);
                    }
                    $this->file_src_error = 0;

                } else {
                    // this is a local filename, i.e.not uploaded
                    $this->log .= '<b>' . $this->translate("source is a local file") . ' ' . $file . '</b><br />';
                    $this->no_upload_check = TRUE;

                    if ($this->uploaded && !file_exists($file)) {
                        $this->uploaded = false;
                        $this->error = $this->translate('local_file_missing');
                    }

                    if ($this->uploaded && !is_readable($file)) {
                        $this->uploaded = false;
                        $this->error = $this->translate('local_file_not_readable');
                    }
                    
                    if ($this->uploaded) {
                        $this->file_src_pathname   = $file;
                        $this->file_src_name       = basename($file);
                        $this->log .= '- local file OK<br />';
                        preg_match('/\.([^\.]*$)/', $this->file_src_name, $extension);
                        if (is_array($extension) && sizeof($extension) > 0) {
                            $this->file_src_name_ext      = strtolower($extension[1]);
                            $this->file_src_name_body     = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
                        } else {
                            $this->file_src_name_ext      = '';
                            $this->file_src_name_body     = $this->file_src_name;
                        }
                        $this->file_src_size = (file_exists($this->file_src_pathname) ? filesize($this->file_src_pathname) : 0);
                    }
                    $this->file_src_error = 0;  
                }
            }
        } else {
            // this is an element from $_FILE, i.e. an uploaded file
            $this->log .= '<b>source is an uploaded file</b><br />';
            if ($this->uploaded) {
                $this->file_src_error         = trim($file['error']);
                switch($this->file_src_error) {
                    case UPLOAD_ERR_OK:
                        // all is OK
                        $this->log .= '- upload OK<br />';
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_too_big_ini');
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_too_big_html');
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_partial');
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_missing');
                        break;
                    case @UPLOAD_ERR_NO_TMP_DIR:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_no_tmp_dir');
                        break;
                    case @UPLOAD_ERR_CANT_WRITE:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_cant_write');
                        break;
                    case @UPLOAD_ERR_EXTENSION:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_err_extension');
                        break;
                    default:
                        $this->uploaded = false;
                        $this->error = $this->translate('uploaded_unknown') . ' ('.$this->file_src_error.')';
                }
            }

            if ($this->uploaded) {
                $this->file_src_pathname   = $file['tmp_name'];
                $this->file_src_name       = $file['name'];
                if ($this->file_src_name == '') {
                    $this->uploaded = false;
                    $this->error = $this->translate('try_again');
                }
            }

            if ($this->uploaded) {
                $this->log .= '- file name OK<br />';
                preg_match('/\.([^\.]*$)/', $this->file_src_name, $extension);
                if (is_array($extension) && sizeof($extension) > 0) {
                    $this->file_src_name_ext      = strtolower($extension[1]);
                    $this->file_src_name_body     = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
                } else {
                    $this->file_src_name_ext      = '';
                    $this->file_src_name_body     = $this->file_src_name;
                }
                $this->file_src_size = $file['size'];
                $mime_from_browser = $file['type'];
            }
        }

        if ($this->uploaded) {
            $this->log .= '<b>determining MIME type</b><br />';
            $this->file_src_mime = null;

            // checks MIME type with Fileinfo PECL extension
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE) {
                if ($this->mime_fileinfo) {
                    $this->log .= '- Checking MIME type with Fileinfo PECL extension<br />';
                    if (function_exists('finfo_open')) {
                        $path = null;
                        if ($this->mime_fileinfo !== '') {
                            if ($this->mime_fileinfo === true) {
                                if (getenv('MAGIC') === FALSE) {
                                    if (substr(PHP_OS, 0, 3) == 'WIN') {
                                        $path = realpath(ini_get('extension_dir') . '/../') . 'extras/magic';
                                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path defaults to ' . $path . '<br />';
                                    }
                                } else {
                                    $path = getenv('MAGIC');
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path is set to ' . $path . ' from MAGIC variable<br />';
                                }
                            } else {
                                $path = $this->mime_fileinfo;
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path is set to ' . $path . '<br />';
                            }
                        }
                        if ($path) {
                            $f = @finfo_open(FILEINFO_MIME, $path);
                        } else {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path will not be used<br />';
                            $f = @finfo_open(FILEINFO_MIME);
                        }
                        if (is_resource($f)) {
                            $mime = finfo_file($f, realpath($this->file_src_pathname));
                            finfo_close($f);
                            $this->file_src_mime = $mime;
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as ' . $this->file_src_mime . ' by Fileinfo PECL extension<br />';
                            if (preg_match("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", $this->file_src_mime)) {
                                $this->file_src_mime = preg_replace("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
                                $this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
                            } else {
                                $this->file_src_mime = null;
                            }
                        } else {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension failed (finfo_open)<br />';
                        }
                    } elseif (@class_exists('finfo')) {
                        $f = new finfo( FILEINFO_MIME );
                        if ($f) {
                            $this->file_src_mime = $f->file(realpath($this->file_src_pathname));
                            $this->log .= '- MIME type detected as ' . $this->file_src_mime . ' by Fileinfo PECL extension<br />';
                            if (preg_match("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", $this->file_src_mime)) {
                                $this->file_src_mime = preg_replace("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
                                $this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
                            } else {
                                $this->file_src_mime = null;
                            }
                        } else {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension failed (finfo)<br />';
                        }
                    } else {
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension not available<br />';
                    }
                } else {
                    $this->log .= '- Fileinfo PECL extension deactivated<br />';
                }
            }

            // checks MIME type with shell if unix access is authorized
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE) {
                if ($this->mime_file) {
                    $this->log .= '- Checking MIME type with UNIX file() command<br />';
                    if (substr(PHP_OS, 0, 3) != 'WIN') {
                        if (function_exists('exec') && function_exists('escapeshellarg') && !extension_loaded('suhosin')) {
                            if (strlen($mime = @exec("file -bi ".escapeshellarg($this->file_src_pathname))) != 0) {
                                $this->file_src_mime = trim($mime);
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as ' . $this->file_src_mime . ' by UNIX file() command<br />';
                                if (preg_match("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", $this->file_src_mime)) {
                                    $this->file_src_mime = preg_replace("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
                                    $this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
                                } else {
                                    $this->file_src_mime = null;
                                }
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;UNIX file() command failed<br />';
                            }
                        } else {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;PHP exec() function is disabled<br />';
                        }
                    } else {
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;UNIX file() command not availabled<br />';
                    }
                } else {
                    $this->log .= '- UNIX file() command is deactivated<br />';
                }
            }

            // checks MIME type with mime_magic
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE) {
                if ($this->mime_magic) {
                    $this->log .= '- Checking MIME type with mime.magic file (mime_content_type())<br />';
                    if (function_exists('mime_content_type')) {
                        $this->file_src_mime = mime_content_type($this->file_src_pathname);
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as ' . $this->file_src_mime . ' by mime_content_type()<br />';
                        if (preg_match("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", $this->file_src_mime)) {
                            $this->file_src_mime = preg_replace("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
                            $this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
                        } else {
                            $this->file_src_mime = null;
                        }
                    } else {
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;mime_content_type() is not available<br />';
                    }
                } else {
                    $this->log .= '- mime.magic file (mime_content_type()) is deactivated<br />';
                }
            }

            // checks MIME type with getimagesize()
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE) {
                if ($this->mime_getimagesize) {
                    $this->log .= '- Checking MIME type with getimagesize()<br />';
                    $info = getimagesize($this->file_src_pathname);
                    if (is_array($info) && array_key_exists('mime', $info)) {
                        $this->file_src_mime = trim($info['mime']);
                        if (empty($this->file_src_mime)) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME empty, guessing from type<br />';
                            $mime = (is_array($info) && array_key_exists(2, $info) ? $info[2] : null); // 1 = GIF, 2 = JPG, 3 = PNG
                            $this->file_src_mime = ($mime==IMAGETYPE_GIF ? 'image/gif' : ($mime==IMAGETYPE_JPEG ? 'image/jpeg' : ($mime==IMAGETYPE_PNG ? 'image/png' : ($mime==IMAGETYPE_BMP ? 'image/bmp' : null))));
                        }
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as ' . $this->file_src_mime . ' by PHP getimagesize() function<br />';
                        if (preg_match("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", $this->file_src_mime)) {
                            $this->file_src_mime = preg_replace("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
                            $this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
                        } else {
                            $this->file_src_mime = null;
                        }
                    } else {
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;getimagesize() failed<br />';
                    }
                } else {
                    $this->log .= '- getimagesize() is deactivated<br />';
                }
            }

            // default to MIME from browser (or Flash)
            if (!empty($mime_from_browser) && !$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime)) {
                $this->file_src_mime =$mime_from_browser;
                $this->log .= '- MIME type detected as ' . $this->file_src_mime . ' by browser<br />';
                if (preg_match("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", $this->file_src_mime)) {
                    $this->file_src_mime = preg_replace("/^([\.\-\w]+)\/([\.\-\w]+)(.*)$/i", '$1/$2', $this->file_src_mime);
                    $this->log .= '-&nbsp;MIME validated as ' . $this->file_src_mime . '<br />';
                } else {
                    $this->file_src_mime = null;
                }
            }

            // we need to work some magic if we upload via Flash
            if ($this->file_src_mime == 'application/octet-stream' || !$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE) {
                if ($this->file_src_mime == 'application/octet-stream') $this->log .= '- Flash may be rewriting MIME as application/octet-stream<br />';
                $this->log .= '- Try to guess MIME type from file extension (' . $this->file_src_name_ext . '): ';
                if (array_key_exists($this->file_src_name_ext, $this->mime_types)) $this->file_src_mime = $this->mime_types[$this->file_src_name_ext];
                if ($this->file_src_mime == 'application/octet-stream') {
                    $this->log .= 'doesn\'t look like anything known<br />';
                } else {
                    $this->log .= 'MIME type set to ' . $this->file_src_mime . '<br />';
                }
            }

            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, '/') === FALSE) {
                $this->log .= '- MIME type couldn\'t be detected! (' . (string) $this->file_src_mime . ')<br />';
            }

            // determine whether the file is an image
            if ($this->file_src_mime && is_string($this->file_src_mime) && !empty($this->file_src_mime) && array_key_exists($this->file_src_mime, $this->image_supported)) {
                $this->file_is_image = true;
                $this->image_src_type = $this->image_supported[$this->file_src_mime];
            }

            // if the file is an image, we gather some useful data
            if ($this->file_is_image) {
                if ($h = fopen($this->file_src_pathname, 'r')) {
                    fclose($h);
                    $info = getimagesize($this->file_src_pathname);
                    if (is_array($info)) {
                        $this->image_src_x    = $info[0];
                        $this->image_src_y    = $info[1];
                        $this->image_dst_x    = $this->image_src_x;
                        $this->image_dst_y    = $this->image_src_y;
                        $this->image_src_pixels = $this->image_src_x * $this->image_src_y;
                        $this->image_src_bits = array_key_exists('bits', $info) ? $info['bits'] : null;
                    } else {
                        $this->file_is_image = false;
                        $this->uploaded = false;
                        $this->log .= '- can\'t retrieve image information, image may have been tampered with<br />';
                        $this->error = $this->translate('source_invalid');
                    }
                } else {
                    $this->log .= '- can\'t read source file directly. open_basedir restriction in place?<br />';
                }
            }

            $this->log .= '<b>source variables</b><br />';
            $this->log .= '- You can use all these before calling process()<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name         : ' . $this->file_src_name . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_body    : ' . $this->file_src_name_body . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_ext     : ' . $this->file_src_name_ext . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_pathname     : ' . $this->file_src_pathname . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_mime         : ' . $this->file_src_mime . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_size         : ' . $this->file_src_size . ' (max= ' . $this->file_max_size . ')<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_error        : ' . $this->file_src_error . '<br />';

            if ($this->file_is_image) {
                $this->log .= '- source file is an image<br />';
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_src_x           : ' . $this->image_src_x . '<br />';
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_src_y           : ' . $this->image_src_y . '<br />';
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_src_pixels      : ' . $this->image_src_pixels . '<br />';
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_src_type        : ' . $this->image_src_type . '<br />';
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_src_bits        : ' . $this->image_src_bits . '<br />';
            }
        }

    }

    /**
     * Returns the version of GD
     *
     * @access public
     * @param  boolean  $full Optional flag to get precise version
     * @return float GD version
     */
    function gdversion($full = false) {
        static $gd_version = null;
        static $gd_full_version = null;
        if ($gd_version === null) {
            if (function_exists('gd_info')) {
                $gd = gd_info();
                $gd = $gd["GD Version"];
                $regex = "/([\d\.]+)/i";
            } else {
                ob_start();
                phpinfo(8);
                $gd = ob_get_contents();
                ob_end_clean();
                $regex = "/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i";
            }
            if (preg_match($regex, $gd, $m)) {
                $gd_full_version = (string) $m[1];
                $gd_version = (float) $m[1];
            } else {
                $gd_full_version = 'none';
                $gd_version = 0;
            }
        }
        if ($full) {
            return $gd_full_version;
        } else {
            return $gd_version;
        }
    }

    /**
     * Creates directories recursively
     *
     * @access private
     * @param  string  $path Path to create
     * @param  integer $mode Optional permissions
     * @return boolean Success
     */
    function rmkdir($path, $mode = 0777) {
        return is_dir($path) || ( $this->rmkdir(dirname($path), $mode) && $this->_mkdir($path, $mode) );
    }

    /**
     * Creates directory
     *
     * @access private
     * @param  string  $path Path to create
     * @param  integer $mode Optional permissions
     * @return boolean Success
     */
    function _mkdir($path, $mode = 0777) {
        $old = umask(0);
        $res = @mkdir($path, $mode);
        umask($old);
        return $res;
    }

    /**
     * Translate error messages
     *
     * @access private
     * @param  string  $str    Message to translate
     * @param  array   $tokens Optional token values
     * @return string Translated string
     */
    function translate($str, $tokens = array()) {
        if (array_key_exists($str, $this->translation)) $str = $this->translation[$str];
        if (is_array($tokens) && sizeof($tokens) > 0)   $str = vsprintf($str, $tokens);
        return $str;
    }

    /**
     * Returns the temp directory
     *
     * @access private
     * @return string Temp directory string
     */
    function temp_dir() {
        $dir = '';
        if (function_exists('sys_get_temp_dir')) $dir = sys_get_temp_dir();
        if (!$dir && $tmp=getenv('TMP'))    $dir = $tmp;
        if (!$dir && $tmp=getenv('TEMP'))   $dir = $tmp;
        if (!$dir && $tmp=getenv('TMPDIR')) $dir = $tmp;
        if (!$dir) {
            $tmp = tempnam(__FILE__,'');
            if (file_exists($tmp)) {
                unlink($tmp);
                $dir = dirname($tmp);
            }
        }
        if (!$dir) return '';
        $slash = (strtolower(substr(PHP_OS, 0, 3)) === 'win' ? '\\' : '/');
        if (substr($dir, -1) != $slash) $dir = $dir . $slash;
        return $dir;
    }

    /**
     * Decodes colors
     *
     * @access private
     * @param  string  $color  Color string
     * @return array RGB colors
     */
    function getcolors($color) {
        $color = str_replace('#', '', $color);
        if (strlen($color) == 3) $color = str_repeat(substr($color, 0, 1), 2) . str_repeat(substr($color, 1, 1), 2) . str_repeat(substr($color, 2, 1), 2);
        $r = sscanf($color, "%2x%2x%2x");
        $red   = (is_array($r) && array_key_exists(0, $r) && is_numeric($r[0]) ? $r[0] : 0);
        $green = (is_array($r) && array_key_exists(1, $r) && is_numeric($r[1]) ? $r[1] : 0);
        $blue  = (is_array($r) && array_key_exists(2, $r) && is_numeric($r[2]) ? $r[2] : 0);
        return array($red, $green, $blue);
    }

    /**
     * Decodes sizes
     *
     * @access private
     * @param  string  $size  Size in bytes, or shorthand byte options
     * @return integer Size in bytes
     */
    function getsize($size) {
        $last = strtolower($size{strlen($size)-1});
        switch($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        return $size;
    }

    /**
     * Decodes offsets
     *
     * @access private
     * @param  misc    $offsets  Offsets, as an integer, a string or an array
     * @param  integer $x        Reference picture width
     * @param  integer $y        Reference picture height
     * @param  boolean $round    Round offsets before returning them
     * @param  boolean $negative Allow negative offsets to be returned
     * @return array Array of four offsets (TRBL)
     */
    function getoffsets($offsets, $x, $y, $round = true, $negative = true) {
        if (!is_array($offsets)) $offsets = explode(' ', $offsets);
        if (sizeof($offsets) == 4) {
             $ct = $offsets[0]; $cr = $offsets[1]; $cb = $offsets[2]; $cl = $offsets[3];
        } else if (sizeof($offsets) == 2) {
            $ct = $offsets[0]; $cr = $offsets[1]; $cb = $offsets[0]; $cl = $offsets[1];
        } else {
            $ct = $offsets[0]; $cr = $offsets[0]; $cb = $offsets[0]; $cl = $offsets[0];
        }
        if (strpos($ct, '%')>0) $ct = $y * (str_replace('%','',$ct) / 100);
        if (strpos($cr, '%')>0) $cr = $x * (str_replace('%','',$cr) / 100);
        if (strpos($cb, '%')>0) $cb = $y * (str_replace('%','',$cb) / 100);
        if (strpos($cl, '%')>0) $cl = $x * (str_replace('%','',$cl) / 100);
        if (strpos($ct, 'px')>0) $ct = str_replace('px','',$ct);
        if (strpos($cr, 'px')>0) $cr = str_replace('px','',$cr);
        if (strpos($cb, 'px')>0) $cb = str_replace('px','',$cb);
        if (strpos($cl, 'px')>0) $cl = str_replace('px','',$cl);
        $ct = (int) $ct; $cr = (int) $cr; $cb = (int) $cb; $cl = (int) $cl;
        if ($round) { 
            $ct = round($ct); 
            $cr = round($cr); 
            $cb = round($cb); 
            $cl = round($cl); 
        }
        if (!$negative) { 
            if ($ct < 0) $ct = 0;
            if ($cr < 0) $cr = 0;
            if ($cb < 0) $cb = 0;
            if ($cl < 0) $cl = 0;
        }
        return array($ct, $cr, $cb, $cl);
    }

    /**
     * Creates a container image
     *
     * @access private
     * @param  integer  $x    Width
     * @param  integer  $y    Height
     * @param  boolean  $fill Optional flag to draw the background color or not
     * @param  boolean  $trsp Optional flag to set the background to be transparent
     * @return resource Container image
     */
    function imagecreatenew($x, $y, $fill = true, $trsp = false) {
        if ($x < 1) $x = 1; if ($y < 1) $y = 1;
        if ($this->gdversion() >= 2 && !$this->image_is_palette) {
            // create a true color image
            $dst_im = imagecreatetruecolor($x, $y);
            // this preserves transparency in PNGs, in true color
            if (empty($this->image_background_color) || $trsp) {
                imagealphablending($dst_im, false );
                imagefilledrectangle($dst_im, 0, 0, $x, $y, imagecolorallocatealpha($dst_im, 0, 0, 0, 127));
            }
        } else {
            // creates a palette image
            $dst_im = imagecreate($x, $y);
            // preserves transparency for palette images, if the original image has transparency
            if (($fill && $this->image_is_transparent && empty($this->image_background_color)) || $trsp) {
                imagefilledrectangle($dst_im, 0, 0, $x, $y, $this->image_transparent_color);
                imagecolortransparent($dst_im, $this->image_transparent_color);
            }
        }
        // fills with background color if any is set
        if ($fill && !empty($this->image_background_color) && !$trsp) {
            list($red, $green, $blue) = $this->getcolors($this->image_background_color);
            $background_color = imagecolorallocate($dst_im, $red, $green, $blue);
            imagefilledrectangle($dst_im, 0, 0, $x, $y, $background_color);
        }
        return $dst_im;
    }


    /**
     * Transfers an image from the container to the destination image
     *
     * @access private
     * @param  resource $src_im Container image
     * @param  resource $dst_im Destination image
     * @return resource Destination image
     */
    function imagetransfer($src_im, $dst_im) {
        if (is_resource($dst_im)) imagedestroy($dst_im);
        $dst_im = & $src_im;
        return $dst_im;
    }

    /**
     * Merges two images
     *
     * If the output format is PNG, then we do it pixel per pixel to retain the alpha channel
     *
     * @access private
     * @param  resource $dst_img Destination image
     * @param  resource $src_img Overlay image
     * @param  int      $dst_x   x-coordinate of destination point
     * @param  int      $dst_y   y-coordinate of destination point
     * @param  int      $src_x   x-coordinate of source point
     * @param  int      $src_y   y-coordinate of source point
     * @param  int      $src_w   Source width
     * @param  int      $src_h   Source height
     * @param  int      $pct     Optional percentage of the overlay, between 0 and 100 (default: 100)
     * @return resource Destination image
     */
    function imagecopymergealpha(&$dst_im, &$src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct = 0) {
        $dst_x = (int) $dst_x;
        $dst_y = (int) $dst_y;
        $src_x = (int) $src_x;
        $src_y = (int) $src_y;
        $src_w = (int) $src_w;
        $src_h = (int) $src_h;
        $pct   = (int) $pct;
        $dst_w = imagesx($dst_im);
        $dst_h = imagesy($dst_im);

        for ($y = $src_y; $y < $src_h; $y++) {
            for ($x = $src_x; $x < $src_w; $x++) {

                if ($x + $dst_x >= 0 && $x + $dst_x < $dst_w && $x + $src_x >= 0 && $x + $src_x < $src_w
                 && $y + $dst_y >= 0 && $y + $dst_y < $dst_h && $y + $src_y >= 0 && $y + $src_y < $src_h) {

                    $dst_pixel = imagecolorsforindex($dst_im, imagecolorat($dst_im, $x + $dst_x, $y + $dst_y));
                    $src_pixel = imagecolorsforindex($src_im, imagecolorat($src_im, $x + $src_x, $y + $src_y));

                    $src_alpha = 1 - ($src_pixel['alpha'] / 127);
                    $dst_alpha = 1 - ($dst_pixel['alpha'] / 127);
                    $opacity = $src_alpha * $pct / 100;
                    if ($dst_alpha >= $opacity) $alpha = $dst_alpha;
                    if ($dst_alpha < $opacity)  $alpha = $opacity;
                    if ($alpha > 1) $alpha = 1;

                    if ($opacity > 0) {
                        $dst_red   = round(( ($dst_pixel['red']   * $dst_alpha * (1 - $opacity)) ) );
                        $dst_green = round(( ($dst_pixel['green'] * $dst_alpha * (1 - $opacity)) ) );
                        $dst_blue  = round(( ($dst_pixel['blue']  * $dst_alpha * (1 - $opacity)) ) );
                        $src_red   = round((($src_pixel['red']   * $opacity)) );
                        $src_green = round((($src_pixel['green'] * $opacity)) );
                        $src_blue  = round((($src_pixel['blue']  * $opacity)) );
                        $red   = round(($dst_red   + $src_red  ) / ($dst_alpha * (1 - $opacity) + $opacity));
                        $green = round(($dst_green + $src_green) / ($dst_alpha * (1 - $opacity) + $opacity));
                        $blue  = round(($dst_blue  + $src_blue ) / ($dst_alpha * (1 - $opacity) + $opacity));
                        if ($red   > 255) $red   = 255;
                        if ($green > 255) $green = 255;
                        if ($blue  > 255) $blue  = 255;
                        $alpha =  round((1 - $alpha) * 127);
                        $color = imagecolorallocatealpha($dst_im, $red, $green, $blue, $alpha);
                        imagesetpixel($dst_im, $x + $dst_x, $y + $dst_y, $color);
                    }
                }
            }
        }
        return true;
    }



    /**
     * Actually uploads the file, and act on it according to the set processing class variables
     *
     * This function copies the uploaded file to the given location, eventually performing actions on it.
     * Typically, you can call {@link process} several times for the same file,
     * for instance to create a resized image and a thumbnail of the same file.
     * The original uploaded file remains intact in its temporary location, so you can use {@link process} several times.
     * You will be able to delete the uploaded file with {@link clean} when you have finished all your {@link process} calls.
     *
     * According to the processing class variables set in the calling file, the file can be renamed,
     * and if it is an image, can be resized or converted.
     *
     * When the processing is completed, and the file copied to its new location, the
     * processing class variables will be reset to their default value.
     * This allows you to set new properties, and perform another {@link process} on the same uploaded file
     *
     * If the function is called with a null or empty argument, then it will return the content of the picture
     *
     * It will set {@link processed} (and {@link error} is an error occurred)
     *
     * @access public
     * @param  string $server_path Optional path location of the uploaded file, with an ending slash
     * @return string Optional content of the image
     */
    function process($server_path = null) {
        $this->error        = '';
        $this->processed    = true;
        $return_mode        = false;
        $return_content     = null;

        // clean up dst variables
        $this->file_dst_path        = '';
        $this->file_dst_pathname    = '';
        $this->file_dst_name        = '';
        $this->file_dst_name_body   = '';
        $this->file_dst_name_ext    = '';

        // clean up some parameters
        $this->file_max_size = $this->getsize($this->file_max_size);
        $this->jpeg_size = $this->getsize($this->jpeg_size);
        // some parameters are being deprecated, and replaced with others
        if (is_null($this->image_overlay_opacity)) $this->image_overlay_opacity = $this->image_overlay_percent;
        if ($this->image_text_opacity == 100) $this->image_text_opacity = $this->image_text_percent;
        if ($this->image_text_background_opacity == 100) $this->image_text_background_opacity = $this->image_text_background_percent;

        // copy some variables as we need to keep them clean
        $file_src_name = $this->file_src_name;
        $file_src_name_body = $this->file_src_name_body;
        $file_src_name_ext = $this->file_src_name_ext;

        if (!$this->uploaded) {
            $this->error = $this->translate('file_not_uploaded');
            $this->processed = false;
        }

        if ($this->processed) {
            if (empty($server_path) || is_null($server_path)) {
                $this->log .= '<b>process file and return the content</b><br />';
                $return_mode = true;
            } else {
                if(strtolower(substr(PHP_OS, 0, 3)) === 'win') {
                    if (substr($server_path, -1, 1) != '\\') $server_path = $server_path . '\\';
                } else {
                    if (substr($server_path, -1, 1) != '/') $server_path = $server_path . '/';
                }
                $this->log .= '<b>process file to '  . $server_path . '</b><br />';
            }
        }

        if ($this->processed) {
            // checks file max size
            if ($this->file_src_size > $this->file_max_size) {
                $this->processed = false;
                $this->error = $this->translate('file_too_big');
            } else {
                $this->log .= '- file size OK<br />';
            }
        }

        if ($this->processed) {
            // if we have an image without extension, set it
            if ($this->file_force_extension && $this->file_is_image && !$this->file_src_name_ext) $file_src_name_ext = $this->image_src_type;
            // turn dangerous scripts into text files
            if ($this->no_script) {
                // if the file has no extension, we try to guess it from the MIME type
                if ($this->file_force_extension && empty($file_src_name_ext)) {
                    if ($key = array_search($this->file_src_mime, $this->mime_types)) {
                        $file_src_name_ext = $key;
                        $file_src_name = $file_src_name_body . '.' . $file_src_name_ext;
                        $this->log .= '- file renamed as ' . $file_src_name_body . '.' . $file_src_name_ext . '!<br />';
                    }
                }
                // if the file is text based, or has a dangerous extension, we rename it as .txt
                if ((((substr($this->file_src_mime, 0, 5) == 'text/' && $this->file_src_mime != 'text/rtf') || strpos($this->file_src_mime, 'javascript') !== false)  && (substr($file_src_name, -4) != '.txt'))
                    || preg_match('/\.(php|php5|php4|php3|phtml|pl|py|cgi|asp|js)$/i', $this->file_src_name)
                    || $this->file_force_extension && empty($file_src_name_ext)) {
                    $this->file_src_mime = 'text/plain';
                    if ($this->file_src_name_ext) $file_src_name_body = $file_src_name_body . '.' . $this->file_src_name_ext;
                    $file_src_name_ext = 'txt';
                    $file_src_name = $file_src_name_body . '.' . $file_src_name_ext;
                    $this->log .= '- script renamed as ' . $file_src_name_body . '.' . $file_src_name_ext . '!<br />';
                }
            }

            if ($this->mime_check && empty($this->file_src_mime)) {
                $this->processed = false;
                $this->error = $this->translate('no_mime');
            } else if ($this->mime_check && !empty($this->file_src_mime) && strpos($this->file_src_mime, '/') !== false) {
                list($m1, $m2) = explode('/', $this->file_src_mime);
                $allowed = false;
                // check wether the mime type is allowed
                if (!is_array($this->allowed)) $this->allowed = array($this->allowed);
                foreach($this->allowed as $k => $v) {
                    list($v1, $v2) = explode('/', $v);
                    if (($v1 == '*' && $v2 == '*') || ($v1 == $m1 && ($v2 == $m2 || $v2 == '*'))) {
                        $allowed = true;
                        break;
                    }
                }
                // check wether the mime type is forbidden
                if (!is_array($this->forbidden)) $this->forbidden = array($this->forbidden);
                foreach($this->forbidden as $k => $v) {
                    list($v1, $v2) = explode('/', $v);
                    if (($v1 == '*' && $v2 == '*') || ($v1 == $m1 && ($v2 == $m2 || $v2 == '*'))) {
                        $allowed = false;
                        break;
                    }
                }
                if (!$allowed) {
                    $this->processed = false;
                    $this->error = $this->translate('incorrect_file');
                } else {
                    $this->log .= '- file mime OK : ' . $this->file_src_mime . '<br />';
                }
            } else {
                $this->log .= '- file mime (not checked) : ' . $this->file_src_mime . '<br />';
            }

            // if the file is an image, we can check on its dimensions
            // these checks are not available if open_basedir restrictions are in place
            if ($this->file_is_image) {
                if (is_numeric($this->image_src_x) && is_numeric($this->image_src_y)) {
                    $ratio = $this->image_src_x / $this->image_src_y;
                    if (!is_null($this->image_max_width) && $this->image_src_x > $this->image_max_width) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_wide');
                    }
                    if (!is_null($this->image_min_width) && $this->image_src_x < $this->image_min_width) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_narrow');
                    }
                    if (!is_null($this->image_max_height) && $this->image_src_y > $this->image_max_height) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_high');
                    }
                    if (!is_null($this->image_min_height) && $this->image_src_y < $this->image_min_height) {
                        $this->processed = false;
                        $this->error = $this->translate('image_too_short');
                    }
                    if (!is_null($this->image_max_ratio) && $ratio > $this->image_max_ratio) {
                        $this->processed = false;
                        $this->error = $this->translate('ratio_too_high');
                    }
                    if (!is_null($this->image_min_ratio) && $ratio < $this->image_min_ratio) {
                        $this->processed = false;
                        $this->error = $this->translate('ratio_too_low');
                    }
                    if (!is_null($this->image_max_pixels) && $this->image_src_pixels > $this->image_max_pixels) {
                        $this->processed = false;
                        $this->error = $this->translate('too_many_pixels');
                    }
                    if (!is_null($this->image_min_pixels) && $this->image_src_pixels < $this->image_min_pixels) {
                        $this->processed = false;
                        $this->error = $this->translate('not_enough_pixels');
                    }
                } else {
                    $this->log .= '- no image properties available, can\'t enforce dimension checks : ' . $this->file_src_mime . '<br />';
                }
            }
        }

        if ($this->processed) {
            $this->file_dst_path        = $server_path;

            // repopulate dst variables from src
            $this->file_dst_name        = $file_src_name;
            $this->file_dst_name_body   = $file_src_name_body;
            $this->file_dst_name_ext    = $file_src_name_ext;
            if ($this->file_overwrite) $this->file_auto_rename = false;

            if ($this->image_convert && $this->file_is_image) { // if we convert as an image
                if ($this->file_src_name_ext) $this->file_dst_name_ext  = $this->image_convert;
                $this->log .= '- new file name ext : ' . $this->image_convert . '<br />';
            }
            if (!is_null($this->file_new_name_body)) { // rename file body
                $this->file_dst_name_body = $this->file_new_name_body;
                $this->log .= '- new file name body : ' . $this->file_new_name_body . '<br />';
            }
            if (!is_null($this->file_new_name_ext)) { // rename file ext
                $this->file_dst_name_ext  = $this->file_new_name_ext;
                $this->log .= '- new file name ext : ' . $this->file_new_name_ext . '<br />';
            }
            if (!is_null($this->file_name_body_add)) { // append a string to the name
                $this->file_dst_name_body  = $this->file_dst_name_body . $this->file_name_body_add;
                $this->log .= '- file name body append : ' . $this->file_name_body_add . '<br />';
            }
            if (!is_null($this->file_name_body_pre)) { // prepend a string to the name
                $this->file_dst_name_body  = $this->file_name_body_pre . $this->file_dst_name_body;
                $this->log .= '- file name body prepend : ' . $this->file_name_body_pre . '<br />';
            }
            if ($this->file_safe_name) { // formats the name
                $this->file_dst_name_body = utf8_encode(strtr(utf8_decode($this->file_dst_name_body), utf8_decode('Å Å½Å¡Å¾Å¸Ã€ÃÃ‚ÃƒÃ„Ã…Ã‡ÃˆÃ‰ÃŠÃ‹ÃŒÃÃŽÃÃ‘Ã’Ã“Ã”Ã•Ã–Ã˜Ã™ÃšÃ›ÃœÃÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã±Ã²Ã³Ã´ÃµÃ¶Ã¸Ã¹ÃºÃ»Ã¼Ã½Ã¿'), 'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy'));
                $this->file_dst_name_body = strtr($this->file_dst_name_body, array('Ãž' => 'TH', 'Ã¾' => 'th', 'Ã' => 'DH', 'Ã°' => 'dh', 'ÃŸ' => 'ss', 'Å’' => 'OE', 'Å“' => 'oe', 'Ã†' => 'AE', 'Ã¦' => 'ae', 'Âµ' => 'u'));
                $this->file_dst_name_body = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $this->file_dst_name_body);
                $this->log .= '- file name safe format<br />';
            }

            $this->log .= '- destination variables<br />';
            if (empty($this->file_dst_path) || is_null($this->file_dst_path)) {
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_path         : n/a<br />';
            } else {
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_path         : ' . $this->file_dst_path . '<br />';
            }
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_body    : ' . $this->file_dst_name_body . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_ext     : ' . $this->file_dst_name_ext . '<br />';

            // do we do some image manipulation?
            $image_manipulation  = ($this->file_is_image && (
                                    $this->image_resize
                                 || $this->image_convert != ''
                                 || is_numeric($this->image_brightness)
                                 || is_numeric($this->image_contrast)
                                 || is_numeric($this->image_opacity)
                                 || is_numeric($this->image_threshold)
                                 || !empty($this->image_tint_color)
                                 || !empty($this->image_overlay_color)
                                 || $this->image_pixelate
                                 || $this->image_unsharp
                                 || !empty($this->image_text)
                                 || $this->image_greyscale
                                 || $this->image_negative
                                 || !empty($this->image_watermark)
                                 || is_numeric($this->image_rotate)
                                 || is_numeric($this->jpeg_size)
                                 || !empty($this->image_flip)
                                 || !empty($this->image_crop)
                                 || !empty($this->image_precrop)
                                 || !empty($this->image_border)
                                 || !empty($this->image_border_transparent)
                                 || $this->image_frame > 0
                                 || $this->image_bevel > 0
                                 || $this->image_reflection_height));

            // set the destination file name
            $this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');

            if (!$return_mode) {
                if (!$this->file_auto_rename) {
                    $this->log .= '- no auto_rename if same filename exists<br />';
                    $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                } else {
                    $this->log .= '- checking for auto_rename<br />';
                    $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                    $body = $this->file_dst_name_body;
                    $ext = '';
                    // if we have changed the extension, then we add our increment before
                    if ($file_src_name_ext != $this->file_src_name_ext) {
                        if (substr($this->file_dst_name_body, -1 - strlen($this->file_src_name_ext)) == '.' . $this->file_src_name_ext) {
                            $body = substr($this->file_dst_name_body, 0, strlen($this->file_dst_name_body) - 1 - strlen($this->file_src_name_ext));
                            $ext = '.' . $this->file_src_name_ext;
                        }
                    }
                    $cpt = 1;
                    while (@file_exists($this->file_dst_pathname)) {
                        $this->file_dst_name_body = $body . '_' . $cpt . $ext;
                        $this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                        $cpt++;
                        $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                    }
                    if ($cpt>1) $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;auto_rename to ' . $this->file_dst_name . '<br />';
                }

                $this->log .= '- destination file details<br />';
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name         : ' . $this->file_dst_name . '<br />';
                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_pathname     : ' . $this->file_dst_pathname . '<br />';

                if ($this->file_overwrite) {
                     $this->log .= '- no overwrite checking<br />';
                } else {
                    if (@file_exists($this->file_dst_pathname)) {
                        $this->processed = false;
                        $this->error = $this->translate('already_exists', array($this->file_dst_name));
                    } else {
                        $this->log .= '- ' . $this->file_dst_name . ' doesn\'t exist already<br />';
                    }
                }
            }
        }

        if ($this->processed) {
            // if we have already moved the uploaded file, we use the temporary copy as source file, and check if it exists
            if (!empty($this->file_src_temp)) {
                $this->log .= '- use the temp file instead of the original file since it is a second process<br />';
                $this->file_src_pathname   = $this->file_src_temp;
                if (!file_exists($this->file_src_pathname)) {
                    $this->processed = false;
                    $this->error = $this->translate('temp_file_missing');
                }
            // if we haven't a temp file, and that we do check on uploads, we use is_uploaded_file()
            } else if (!$this->no_upload_check) {
                if (!is_uploaded_file($this->file_src_pathname)) {
                    $this->processed = false;
                    $this->error = $this->translate('source_missing');
                }
            // otherwise, if we don't check on uploaded files (local file for instance), we use file_exists()
            } else {
                if (!file_exists($this->file_src_pathname)) {
                    $this->processed = false;
                    $this->error = $this->translate('source_missing');
                }
            }

            // checks if the destination directory exists, and attempt to create it
            if (!$return_mode) {
                if ($this->processed && !file_exists($this->file_dst_path)) {
                    if ($this->dir_auto_create) {
                        $this->log .= '- ' . $this->file_dst_path . ' doesn\'t exist. Attempting creation:';
                        if (!$this->rmkdir($this->file_dst_path, $this->dir_chmod)) {
                            $this->log .= ' failed<br />';
                            $this->processed = false;
                            $this->error = $this->translate('destination_dir');
                        } else {
                            $this->log .= ' success<br />';
                        }
                    } else {
                        $this->error = $this->translate('destination_dir_missing');
                    }
                }

                if ($this->processed && !is_dir($this->file_dst_path)) {
                    $this->processed = false;
                    $this->error = $this->translate('destination_path_not_dir');
                }

                // checks if the destination directory is writeable, and attempt to make it writeable
                $hash = md5($this->file_dst_name_body . rand(1, 1000));
                if ($this->processed && !($f = @fopen($this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : ''), 'a+'))) {
                    if ($this->dir_auto_chmod) {
                        $this->log .= '- ' . $this->file_dst_path . ' is not writeable. Attempting chmod:';
                        if (!@chmod($this->file_dst_path, $this->dir_chmod)) {
                            $this->log .= ' failed<br />';
                            $this->processed = false;
                            $this->error = $this->translate('destination_dir_write');
                        } else {
                            $this->log .= ' success<br />';
                            if (!($f = @fopen($this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : ''), 'a+'))) { // we re-check
                                $this->processed = false;
                                $this->error = $this->translate('destination_dir_write');
                            } else {
                                @fclose($f);
                            }
                        }
                    } else {
                        $this->processed = false;
                        $this->error = $this->translate('destination_path_write');
                    }
                } else {
                    if ($this->processed) @fclose($f);
                    @unlink($this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : ''));
                }


                // if we have an uploaded file, and if it is the first process, and if we can't access the file directly (open_basedir restriction)
                // then we create a temp file that will be used as the source file in subsequent processes
                // the third condition is there to check if the file is not accessible *directly* (it already has positively gone through is_uploaded_file(), so it exists)
                if (!$this->no_upload_check && empty($this->file_src_temp) && !@file_exists($this->file_src_pathname)) {
                    $this->log .= '- attempting to use a temp file:';
                    $hash = md5($this->file_dst_name_body . rand(1, 1000));
                    if (move_uploaded_file($this->file_src_pathname, $this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : ''))) {
                        $this->file_src_pathname = $this->file_dst_path . $hash . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                        $this->file_src_temp = $this->file_src_pathname;
                        $this->log .= ' file created<br />';
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;temp file is: ' . $this->file_src_temp . '<br />';
                    } else {
                        $this->log .= ' failed<br />';
                        $this->processed = false;
                        $this->error = $this->translate('temp_file');
                    }
                }
            }
        }

        if ($this->processed) {

            // we do a quick check to ensure the file is really an image
            // we can do this only now, as it would have failed before in case of open_basedir
            if ($image_manipulation && !@getimagesize($this->file_src_pathname)) {
                $this->log .= '- the file is not an image!<br />';
                $image_manipulation = false;
            }

            if ($image_manipulation) {

                // make sure GD doesn't complain too much
                @ini_set("gd.jpeg_ignore_warning", 1);

                // checks if the source file is readable
                if ($this->processed && !($f = @fopen($this->file_src_pathname, 'r'))) {
                    $this->processed = false;
                    $this->error = $this->translate('source_not_readable');
                } else {
                    @fclose($f);
                }

                // we now do all the image manipulations
                $this->log .= '- image resizing or conversion wanted<br />';
                if ($this->gdversion()) {
                    switch($this->image_src_type) {
                        case 'jpg':
                            if (!function_exists('imagecreatefromjpeg')) {
                                $this->processed = false;
                                $this->error = $this->translate('no_create_support', array('JPEG'));
                            } else {
                                $image_src = @imagecreatefromjpeg($this->file_src_pathname);
                                if (!$image_src) {
                                    $this->processed = false;
                                    $this->error = $this->translate('create_error', array('JPEG'));
                                } else {
                                    $this->log .= '- source image is JPEG<br />';
                                }
                            }
                            break;
                        case 'png':
                            if (!function_exists('imagecreatefrompng')) {
                                $this->processed = false;
                                $this->error = $this->translate('no_create_support', array('PNG'));
                            } else {
                                $image_src = @imagecreatefrompng($this->file_src_pathname);
                                if (!$image_src) {
                                    $this->processed = false;
                                    $this->error = $this->translate('create_error', array('PNG'));
                                } else {
                                    $this->log .= '- source image is PNG<br />';
                                }
                            }
                            break;
                        case 'gif':
                            if (!function_exists('imagecreatefromgif')) {
                                $this->processed = false;
                                $this->error = $this->translate('no_create_support', array('GIF'));
                            } else {
                                $image_src = @imagecreatefromgif($this->file_src_pathname);
                                if (!$image_src) {
                                    $this->processed = false;
                                    $this->error = $this->translate('create_error', array('GIF'));
                                } else {
                                    $this->log .= '- source image is GIF<br />';
                                }
                            }
                            break;
                        case 'bmp':
                            if (!method_exists($this, 'imagecreatefrombmp')) {
                                $this->processed = false;
                                $this->error = $this->translate('no_create_support', array('BMP'));
                            } else {
                                $image_src = @$this->imagecreatefrombmp($this->file_src_pathname);
                                if (!$image_src) {
                                    $this->processed = false;
                                    $this->error = $this->translate('create_error', array('BMP'));
                                } else {
                                    $this->log .= '- source image is BMP<br />';
                                }
                            }
                            break;
                        default:
                            $this->processed = false;
                            $this->error = $this->translate('source_invalid');
                    }
                } else {
                    $this->processed = false;
                    $this->error = $this->translate('gd_missing');
                }

                if ($this->processed && $image_src) {

                    // we have to set image_convert if it is not already
                    if (empty($this->image_convert)) {
                        $this->log .= '- setting destination file type to ' . $this->image_src_type . '<br />';
                        $this->image_convert = $this->image_src_type;
                    }

                    if (!in_array($this->image_convert, $this->image_supported)) {
                        $this->image_convert = 'jpg';
                    }

                    // we set the default color to be the background color if we don't output in a transparent format
                    if ($this->image_convert != 'png' && $this->image_convert != 'gif' && !empty($this->image_default_color) && empty($this->image_background_color)) $this->image_background_color = $this->image_default_color;
                    if (!empty($this->image_background_color)) $this->image_default_color = $this->image_background_color;
                    if (empty($this->image_default_color)) $this->image_default_color = '#FFFFFF';

                    $this->image_src_x = imagesx($image_src);
                    $this->image_src_y = imagesy($image_src);
                    $gd_version = $this->gdversion();
                    $ratio_crop = null;

                    if (!imageistruecolor($image_src)) {  // $this->image_src_type == 'gif'
                        $this->log .= '- image is detected as having a palette<br />';
                        $this->image_is_palette = true;
                        $this->image_transparent_color = imagecolortransparent($image_src);
                        if ($this->image_transparent_color >= 0 && imagecolorstotal($image_src) > $this->image_transparent_color) {
                            $this->image_is_transparent = true;
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;palette image is detected as transparent<br />';
                        }
                        // if the image has a palette (GIF), we convert it to true color, preserving transparency
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;convert palette image to true color<br />';
                        $true_color = imagecreatetruecolor($this->image_src_x, $this->image_src_y);
                        imagealphablending($true_color, false);
                        imagesavealpha($true_color, true);
                        for ($x = 0; $x < $this->image_src_x; $x++) {
                            for ($y = 0; $y < $this->image_src_y; $y++) {
                                if ($this->image_transparent_color >= 0 && imagecolorat($image_src, $x, $y) == $this->image_transparent_color) {
                                    imagesetpixel($true_color, $x, $y, 127 << 24);
                                } else {
                                    $rgb = imagecolorsforindex($image_src, imagecolorat($image_src, $x, $y));
                                    imagesetpixel($true_color, $x, $y, ($rgb['alpha'] << 24) | ($rgb['red'] << 16) | ($rgb['green'] << 8) | $rgb['blue']);
                                }
                            }
                        }
                        $image_src = $this->imagetransfer($true_color, $image_src);
                        imagealphablending($image_src, false);
                        imagesavealpha($image_src, true);
                        $this->image_is_palette = false;
                    }


                    $image_dst = & $image_src;

                    // pre-crop image, before resizing
                    if ((!empty($this->image_precrop))) {
                        list($ct, $cr, $cb, $cl) = $this->getoffsets($this->image_precrop, $this->image_src_x, $this->image_src_y, true, true);
                        $this->log .= '- pre-crop image : ' . $ct . ' ' . $cr . ' ' . $cb . ' ' . $cl . ' <br />';
                        $this->image_src_x = $this->image_src_x - $cl - $cr;
                        $this->image_src_y = $this->image_src_y - $ct - $cb;
                        if ($this->image_src_x < 1) $this->image_src_x = 1;
                        if ($this->image_src_y < 1) $this->image_src_y = 1;
                        $tmp = $this->imagecreatenew($this->image_src_x, $this->image_src_y);

                        // we copy the image into the recieving image
                        imagecopy($tmp, $image_dst, 0, 0, $cl, $ct, $this->image_src_x, $this->image_src_y);

                        // if we crop with negative margins, we have to make sure the extra bits are the right color, or transparent
                        if ($ct < 0 || $cr < 0 || $cb < 0 || $cl < 0 ) {
                            // use the background color if present
                            if (!empty($this->image_background_color)) {
                                list($red, $green, $blue) = $this->getcolors($this->image_background_color);
                                $fill = imagecolorallocate($tmp, $red, $green, $blue);
                            } else {
                                $fill = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
                            }
                            // fills eventual negative margins
                            if ($ct < 0) imagefilledrectangle($tmp, 0, 0, $this->image_src_x, -$ct, $fill);
                            if ($cr < 0) imagefilledrectangle($tmp, $this->image_src_x + $cr, 0, $this->image_src_x, $this->image_src_y, $fill);
                            if ($cb < 0) imagefilledrectangle($tmp, 0, $this->image_src_y + $cb, $this->image_src_x, $this->image_src_y, $fill);
                            if ($cl < 0) imagefilledrectangle($tmp, 0, 0, -$cl, $this->image_src_y, $fill);
                        }

                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // resize image (and move image_src_x, image_src_y dimensions into image_dst_x, image_dst_y)
                    if ($this->image_resize) {
                        $this->log .= '- resizing...<br />';

                        if ($this->image_ratio_x) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;calculate x size<br />';
                            $this->image_dst_x = round(($this->image_src_x * $this->image_y) / $this->image_src_y);
                            $this->image_dst_y = $this->image_y;
                        } else if ($this->image_ratio_y) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;calculate y size<br />';
                            $this->image_dst_x = $this->image_x;
                            $this->image_dst_y = round(($this->image_src_y * $this->image_x) / $this->image_src_x);
                        } else if (is_numeric($this->image_ratio_pixels)) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;calculate x/y size to match a number of pixels<br />';
                            $pixels = $this->image_src_y * $this->image_src_x;
                            $diff = sqrt($this->image_ratio_pixels / $pixels);
                            $this->image_dst_x = round($this->image_src_x * $diff);
                            $this->image_dst_y = round($this->image_src_y * $diff);
                        } else if ($this->image_ratio || $this->image_ratio_crop || $this->image_ratio_fill || $this->image_ratio_no_zoom_in || $this->image_ratio_no_zoom_out) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;check x/y sizes<br />';
                            if ((!$this->image_ratio_no_zoom_in && !$this->image_ratio_no_zoom_out)
                                 || ($this->image_ratio_no_zoom_in && ($this->image_src_x > $this->image_x || $this->image_src_y > $this->image_y))
                                 || ($this->image_ratio_no_zoom_out && $this->image_src_x < $this->image_x && $this->image_src_y < $this->image_y)) {
                                $this->image_dst_x = $this->image_x;
                                $this->image_dst_y = $this->image_y;
                                if ($this->image_ratio_crop) {
                                    if (!is_string($this->image_ratio_crop)) $this->image_ratio_crop = '';
                                    $this->image_ratio_crop = strtolower($this->image_ratio_crop);
                                    if (($this->image_src_x/$this->image_x) > ($this->image_src_y/$this->image_y)) {
                                        $this->image_dst_y = $this->image_y;
                                        $this->image_dst_x = intval($this->image_src_x*($this->image_y / $this->image_src_y));
                                        $ratio_crop = array();
                                        $ratio_crop['x'] = $this->image_dst_x - $this->image_x;
                                        if (strpos($this->image_ratio_crop, 'l') !== false) {
                                            $ratio_crop['l'] = 0;
                                            $ratio_crop['r'] = $ratio_crop['x'];
                                        } else if (strpos($this->image_ratio_crop, 'r') !== false) {
                                            $ratio_crop['l'] = $ratio_crop['x'];
                                            $ratio_crop['r'] = 0;
                                        } else {
                                            $ratio_crop['l'] = round($ratio_crop['x']/2);
                                            $ratio_crop['r'] = $ratio_crop['x'] - $ratio_crop['l'];
                                        }
                                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;ratio_crop_x         : ' . $ratio_crop['x'] . ' (' . $ratio_crop['l'] . ';' . $ratio_crop['r'] . ')<br />';
                                        if (is_null($this->image_crop)) $this->image_crop = array(0, 0, 0, 0);
                                    } else {
                                        $this->image_dst_x = $this->image_x;
                                        $this->image_dst_y = intval($this->image_src_y*($this->image_x / $this->image_src_x));
                                        $ratio_crop = array();
                                        $ratio_crop['y'] = $this->image_dst_y - $this->image_y;
                                        if (strpos($this->image_ratio_crop, 't') !== false) {
                                            $ratio_crop['t'] = 0;
                                            $ratio_crop['b'] = $ratio_crop['y'];
                                        } else if (strpos($this->image_ratio_crop, 'b') !== false) {
                                            $ratio_crop['t'] = $ratio_crop['y'];
                                            $ratio_crop['b'] = 0;
                                        } else {
                                            $ratio_crop['t'] = round($ratio_crop['y']/2);
                                            $ratio_crop['b'] = $ratio_crop['y'] - $ratio_crop['t'];
                                        }
                                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;ratio_crop_y         : ' . $ratio_crop['y'] . ' (' . $ratio_crop['t'] . ';' . $ratio_crop['b'] . ')<br />';
                                        if (is_null($this->image_crop)) $this->image_crop = array(0, 0, 0, 0);
                                    }
                                } else if ($this->image_ratio_fill) {
                                    if (!is_string($this->image_ratio_fill)) $this->image_ratio_fill = '';
                                    $this->image_ratio_fill = strtolower($this->image_ratio_fill);
                                    if (($this->image_src_x/$this->image_x) < ($this->image_src_y/$this->image_y)) {
                                        $this->image_dst_y = $this->image_y;
                                        $this->image_dst_x = intval($this->image_src_x*($this->image_y / $this->image_src_y));
                                        $ratio_crop = array();
                                        $ratio_crop['x'] = $this->image_dst_x - $this->image_x;
                                        if (strpos($this->image_ratio_fill, 'l') !== false) {
                                            $ratio_crop['l'] = 0;
                                            $ratio_crop['r'] = $ratio_crop['x'];
                                        } else if (strpos($this->image_ratio_fill, 'r') !== false) {
                                            $ratio_crop['l'] = $ratio_crop['x'];
                                            $ratio_crop['r'] = 0;
                                        } else {
                                            $ratio_crop['l'] = round($ratio_crop['x']/2);
                                            $ratio_crop['r'] = $ratio_crop['x'] - $ratio_crop['l'];
                                        }
                                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;ratio_fill_x         : ' . $ratio_crop['x'] . ' (' . $ratio_crop['l'] . ';' . $ratio_crop['r'] . ')<br />';
                                        if (is_null($this->image_crop)) $this->image_crop = array(0, 0, 0, 0);
                                    } else {
                                        $this->image_dst_x = $this->image_x;
                                        $this->image_dst_y = intval($this->image_src_y*($this->image_x / $this->image_src_x));
                                        $ratio_crop = array();
                                        $ratio_crop['y'] = $this->image_dst_y - $this->image_y;
                                        if (strpos($this->image_ratio_fill, 't') !== false) {
                                            $ratio_crop['t'] = 0;
                                            $ratio_crop['b'] = $ratio_crop['y'];
                                        } else if (strpos($this->image_ratio_fill, 'b') !== false) {
                                            $ratio_crop['t'] = $ratio_crop['y'];
                                            $ratio_crop['b'] = 0;
                                        } else {
                                            $ratio_crop['t'] = round($ratio_crop['y']/2);
                                            $ratio_crop['b'] = $ratio_crop['y'] - $ratio_crop['t'];
                                        }
                                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;ratio_fill_y         : ' . $ratio_crop['y'] . ' (' . $ratio_crop['t'] . ';' . $ratio_crop['b'] . ')<br />';
                                        if (is_null($this->image_crop)) $this->image_crop = array(0, 0, 0, 0);
                                    }
                                } else {
                                    if (($this->image_src_x/$this->image_x) > ($this->image_src_y/$this->image_y)) {
                                        $this->image_dst_x = $this->image_x;
                                        $this->image_dst_y = intval($this->image_src_y*($this->image_x / $this->image_src_x));
                                    } else {
                                        $this->image_dst_y = $this->image_y;
                                        $this->image_dst_x = intval($this->image_src_x*($this->image_y / $this->image_src_y));
                                    }
                                }
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;doesn\'t calculate x/y sizes<br />';
                                $this->image_dst_x = $this->image_src_x;
                                $this->image_dst_y = $this->image_src_y;
                            }
                        } else {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;use plain sizes<br />';
                            $this->image_dst_x = $this->image_x;
                            $this->image_dst_y = $this->image_y;
                        }

                        if ($this->image_dst_x < 1) $this->image_dst_x = 1;
                        if ($this->image_dst_y < 1) $this->image_dst_y = 1;
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);

                        if ($gd_version >= 2) {
                            $res = imagecopyresampled($tmp, $image_src, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_src_x, $this->image_src_y);
                        } else {
                            $res = imagecopyresized($tmp, $image_src, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_src_x, $this->image_src_y);
                        }

                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;resized image object created<br />';
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_src_x y        : ' . $this->image_src_x . ' x ' . $this->image_src_y . '<br />';
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_dst_x y        : ' . $this->image_dst_x . ' x ' . $this->image_dst_y . '<br />';
                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);

                    } else {
                        $this->image_dst_x = $this->image_src_x;
                        $this->image_dst_y = $this->image_src_y;
                    }

                    // crop image (and also crops if image_ratio_crop is used)
                    if ((!empty($this->image_crop) || !is_null($ratio_crop))) {
                        list($ct, $cr, $cb, $cl) = $this->getoffsets($this->image_crop, $this->image_dst_x, $this->image_dst_y, true, true);
                        // we adjust the cropping if we use image_ratio_crop
                        if (!is_null($ratio_crop)) {
                            if (array_key_exists('t', $ratio_crop)) $ct += $ratio_crop['t'];
                            if (array_key_exists('r', $ratio_crop)) $cr += $ratio_crop['r'];
                            if (array_key_exists('b', $ratio_crop)) $cb += $ratio_crop['b'];
                            if (array_key_exists('l', $ratio_crop)) $cl += $ratio_crop['l'];
                        }
                        $this->log .= '- crop image : ' . $ct . ' ' . $cr . ' ' . $cb . ' ' . $cl . ' <br />';
                        $this->image_dst_x = $this->image_dst_x - $cl - $cr;
                        $this->image_dst_y = $this->image_dst_y - $ct - $cb;
                        if ($this->image_dst_x < 1) $this->image_dst_x = 1;
                        if ($this->image_dst_y < 1) $this->image_dst_y = 1;
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);

                        // we copy the image into the recieving image
                        imagecopy($tmp, $image_dst, 0, 0, $cl, $ct, $this->image_dst_x, $this->image_dst_y);

                        // if we crop with negative margins, we have to make sure the extra bits are the right color, or transparent
                        if ($ct < 0 || $cr < 0 || $cb < 0 || $cl < 0 ) {
                            // use the background color if present
                            if (!empty($this->image_background_color)) {
                                list($red, $green, $blue) = $this->getcolors($this->image_background_color);
                                $fill = imagecolorallocate($tmp, $red, $green, $blue);
                            } else {
                                $fill = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
                            }
                            // fills eventual negative margins
                            if ($ct < 0) imagefilledrectangle($tmp, 0, 0, $this->image_dst_x, -$ct-1, $fill);
                            if ($cr < 0) imagefilledrectangle($tmp, $this->image_dst_x + $cr, 0, $this->image_dst_x, $this->image_dst_y, $fill);
                            if ($cb < 0) imagefilledrectangle($tmp, 0, $this->image_dst_y + $cb, $this->image_dst_x, $this->image_dst_y, $fill);
                            if ($cl < 0) imagefilledrectangle($tmp, 0, 0, -$cl-1, $this->image_dst_y, $fill);
                        }

                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // flip image
                    if ($gd_version >= 2 && !empty($this->image_flip)) {
                        $this->image_flip = strtolower($this->image_flip);
                        $this->log .= '- flip image : ' . $this->image_flip . '<br />';
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);
                        for ($x = 0; $x < $this->image_dst_x; $x++) {
                            for ($y = 0; $y < $this->image_dst_y; $y++){
                                if (strpos($this->image_flip, 'v') !== false) {
                                    imagecopy($tmp, $image_dst, $this->image_dst_x - $x - 1, $y, $x, $y, 1, 1);
                                } else {
                                    imagecopy($tmp, $image_dst, $x, $this->image_dst_y - $y - 1, $x, $y, 1, 1);
                                }
                            }
                        }
                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // rotate image
                    if ($gd_version >= 2 && is_numeric($this->image_rotate)) {
                        if (!in_array($this->image_rotate, array(0, 90, 180, 270))) $this->image_rotate = 0;
                        if ($this->image_rotate != 0) {
                            if ($this->image_rotate == 90 || $this->image_rotate == 270) {
                                $tmp = $this->imagecreatenew($this->image_dst_y, $this->image_dst_x);
                            } else {
                                $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);
                            }
                            $this->log .= '- rotate image : ' . $this->image_rotate . '<br />';
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                for ($y = 0; $y < $this->image_dst_y; $y++){
                                    if ($this->image_rotate == 90) {
                                        imagecopy($tmp, $image_dst, $y, $x, $x, $this->image_dst_y - $y - 1, 1, 1);
                                    } else if ($this->image_rotate == 180) {
                                        imagecopy($tmp, $image_dst, $x, $y, $this->image_dst_x - $x - 1, $this->image_dst_y - $y - 1, 1, 1);
                                    } else if ($this->image_rotate == 270) {
                                        imagecopy($tmp, $image_dst, $y, $x, $this->image_dst_x - $x - 1, $y, 1, 1);
                                    } else {
                                        imagecopy($tmp, $image_dst, $x, $y, $x, $y, 1, 1);
                                    }
                                }
                            }
                            if ($this->image_rotate == 90 || $this->image_rotate == 270) {
                                $t = $this->image_dst_y;
                                $this->image_dst_y = $this->image_dst_x;
                                $this->image_dst_x = $t;
                            }
                            // we transfert tmp into image_dst
                            $image_dst = $this->imagetransfer($tmp, $image_dst);
                        }
                    }

                    // pixelate image
                    if ((is_numeric($this->image_pixelate) && $this->image_pixelate > 0)) {
                        $this->log .= '- pixelate image (' . $this->image_pixelate . 'px)<br />';
                        $filter = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);
                        if ($gd_version >= 2) {
                            imagecopyresampled($filter, $image_dst, 0, 0, 0, 0, round($this->image_dst_x / $this->image_pixelate), round($this->image_dst_y / $this->image_pixelate), $this->image_dst_x, $this->image_dst_y);
                            imagecopyresampled($image_dst, $filter, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, round($this->image_dst_x / $this->image_pixelate), round($this->image_dst_y / $this->image_pixelate));
                        } else {
                            imagecopyresized($filter, $image_dst, 0, 0, 0, 0, round($this->image_dst_x / $this->image_pixelate), round($this->image_dst_y / $this->image_pixelate), $this->image_dst_x, $this->image_dst_y);
                            imagecopyresized($image_dst, $filter, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, round($this->image_dst_x / $this->image_pixelate), round($this->image_dst_y / $this->image_pixelate));
                        }
                        imagedestroy($filter);
                    }
 
                    // unsharp mask
                    if ($gd_version >= 2 && $this->image_unsharp && is_numeric($this->image_unsharp_amount) && is_numeric($this->image_unsharp_radius) && is_numeric($this->image_unsharp_threshold)) {
                        // Unsharp Mask for PHP - version 2.1.1
                        // Unsharp mask algorithm by Torstein HÃ¸nsi 2003-07. 
                        // Used with permission
                        // Modified to support alpha transparency
                        if ($this->image_unsharp_amount > 500)    $this->image_unsharp_amount = 500; 
                        $this->image_unsharp_amount = $this->image_unsharp_amount * 0.016; 
                        if ($this->image_unsharp_radius > 50)    $this->image_unsharp_radius = 50; 
                        $this->image_unsharp_radius = $this->image_unsharp_radius * 2; 
                        if ($this->image_unsharp_threshold > 255)    $this->image_unsharp_threshold = 255; 
                        $this->image_unsharp_radius = abs(round($this->image_unsharp_radius));
                        if ($this->image_unsharp_radius != 0) {       
                            $this->image_dst_x = imagesx($image_dst); $this->image_dst_y = imagesy($image_dst); 
                            $canvas = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y, false, true); 
                            $blur = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y, false, true); 
                            if (function_exists('imageconvolution')) { // PHP >= 5.1  
                                $matrix = array(array( 1, 2, 1 ), array( 2, 4, 2 ), array( 1, 2, 1 ));  
                                imagecopy($blur, $image_dst, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y); 
                                imageconvolution($blur, $matrix, 16, 0);  
                            } else {  
                                for ($i = 0; $i < $this->image_unsharp_radius; $i++) { 
                                    imagecopy($blur, $image_dst, 0, 0, 1, 0, $this->image_dst_x - 1, $this->image_dst_y); // left 
                                    $this->imagecopymergealpha($blur, $image_dst, 1, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, 50); // right 
                                    $this->imagecopymergealpha($blur, $image_dst, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, 50); // center 
                                    imagecopy($canvas, $blur, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y); 
                                    $this->imagecopymergealpha($blur, $canvas, 0, 0, 0, 1, $this->image_dst_x, $this->image_dst_y - 1, 33.33333 ); // up 
                                    $this->imagecopymergealpha($blur, $canvas, 0, 1, 0, 0, $this->image_dst_x, $this->image_dst_y, 25); // down 
                                } 
                            } 
                            $p_new = array();
                            if($this->image_unsharp_threshold>0) { 
                                for ($x = 0; $x < $this->image_dst_x-1; $x++) { 
                                    for ($y = 0; $y < $this->image_dst_y; $y++) {
                                        $p_orig = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                        $p_blur = imagecolorsforindex($blur, imagecolorat($blur, $x, $y));
                                        $p_new['red'] = (abs($p_orig['red'] - $p_blur['red']) >= $this->image_unsharp_threshold) ? max(0, min(255, ($this->image_unsharp_amount * ($p_orig['red'] - $p_blur['red'])) + $p_orig['red'])) : $p_orig['red']; 
                                        $p_new['green'] = (abs($p_orig['green'] - $p_blur['green']) >= $this->image_unsharp_threshold) ? max(0, min(255, ($this->image_unsharp_amount * ($p_orig['green'] - $p_blur['green'])) + $p_orig['green'])) : $p_orig['green']; 
                                        $p_new['blue'] = (abs($p_orig['blue'] - $p_blur['blue']) >= $this->image_unsharp_threshold) ? max(0, min(255, ($this->image_unsharp_amount * ($p_orig['blue'] - $p_blur['blue'])) + $p_orig['blue'])) : $p_orig['blue'];         
                                        if (($p_orig['red'] != $p_new['red']) || ($p_orig['green'] != $p_new['green']) || ($p_orig['blue'] != $p_new['blue'])) { 
                                            $color = imagecolorallocatealpha($image_dst, $p_new['red'], $p_new['green'], $p_new['blue'], $p_orig['alpha']);
                                            imagesetpixel($image_dst, $x, $y, $color);                                            
                                        } 
                                    } 
                                } 
                            } else { 
                                for ($x = 0; $x < $this->image_dst_x; $x++) {
                                    for ($y = 0; $y < $this->image_dst_y; $y++) {
                                        $p_orig = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                        $p_blur = imagecolorsforindex($blur, imagecolorat($blur, $x, $y));
                                        $p_new['red'] = ($this->image_unsharp_amount * ($p_orig['red'] - $p_blur['red'])) + $p_orig['red']; 
                                        if ($p_new['red']>255) { $p_new['red']=255; } elseif ($p_new['red']<0) { $p_new['red']=0; } 
                                        $p_new['green'] = ($this->image_unsharp_amount * ($p_orig['green'] - $p_blur['green'])) + $p_orig['green']; 
                                        if ($p_new['green']>255) { $p_new['green']=255; }  elseif ($p_new['green']<0) { $p_new['green']=0; } 
                                        $p_new['blue'] = ($this->image_unsharp_amount * ($p_orig['blue'] - $p_blur['blue'])) + $p_orig['blue']; 
                                        if ($p_new['blue']>255) { $p_new['blue']=255; } elseif ($p_new['blue']<0) { $p_new['blue']=0; } 
                                        $color = imagecolorallocatealpha($image_dst, $p_new['red'], $p_new['green'], $p_new['blue'], $p_orig['alpha']);
                                        imagesetpixel($image_dst, $x, $y, $color);                                            
                                    } 
                                } 
                            } 
                            imagedestroy($canvas); 
                            imagedestroy($blur); 
                        }
                    }

                    // add color overlay
                    if ($gd_version >= 2 && (is_numeric($this->image_overlay_opacity) && $this->image_overlay_opacity > 0 && !empty($this->image_overlay_color))) {
                        $this->log .= '- apply color overlay<br />';
                        list($red, $green, $blue) = $this->getcolors($this->image_overlay_color);
                        $filter = imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        $color = imagecolorallocate($filter, $red, $green, $blue);
                        imagefilledrectangle($filter, 0, 0, $this->image_dst_x, $this->image_dst_y, $color);
                        $this->imagecopymergealpha($image_dst, $filter, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_overlay_opacity);
                        imagedestroy($filter);
                    }

                    // add brightness, contrast and tint, turns to greyscale and inverts colors
                    if ($gd_version >= 2 && ($this->image_negative || $this->image_greyscale || is_numeric($this->image_threshold)|| is_numeric($this->image_brightness) || is_numeric($this->image_contrast) || !empty($this->image_tint_color))) {
                        $this->log .= '- apply tint, light, contrast correction, negative, greyscale and threshold<br />';
                        if (!empty($this->image_tint_color)) list($tint_red, $tint_green, $tint_blue) = $this->getcolors($this->image_tint_color);
                        //imagealphablending($image_dst, true);
                        for($y=0; $y < $this->image_dst_y; $y++) {
                            for($x=0; $x < $this->image_dst_x; $x++) {
                                if ($this->image_greyscale) {
                                    $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                    $r = $g = $b = round((0.2125 * $pixel['red']) + (0.7154 * $pixel['green']) + (0.0721 * $pixel['blue']));
                                    $color = imagecolorallocatealpha($image_dst, $r, $g, $b, $pixel['alpha']);
                                    imagesetpixel($image_dst, $x, $y, $color);
                                    unset($color); unset($pixel);
                                }
                                if (is_numeric($this->image_threshold)) {
                                    $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                    $c = (round($pixel['red'] + $pixel['green'] + $pixel['blue']) / 3) - 127;
                                    $r = $g = $b = ($c > $this->image_threshold ? 255 : 0);
                                    $color = imagecolorallocatealpha($image_dst, $r, $g, $b, $pixel['alpha']);
                                    imagesetpixel($image_dst, $x, $y, $color);
                                    unset($color); unset($pixel);
                                }
                                if (is_numeric($this->image_brightness)) {
                                    $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                    $r = max(min(round($pixel['red'] + (($this->image_brightness * 2))), 255), 0);
                                    $g = max(min(round($pixel['green'] + (($this->image_brightness * 2))), 255), 0);
                                    $b = max(min(round($pixel['blue'] + (($this->image_brightness * 2))), 255), 0);
                                    $color = imagecolorallocatealpha($image_dst, $r, $g, $b, $pixel['alpha']);
                                    imagesetpixel($image_dst, $x, $y, $color);
                                    unset($color); unset($pixel);
                                }
                                if (is_numeric($this->image_contrast)) {
                                    $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                    $r = max(min(round(($this->image_contrast + 128) * $pixel['red'] / 128), 255), 0);
                                    $g = max(min(round(($this->image_contrast + 128) * $pixel['green'] / 128), 255), 0);
                                    $b = max(min(round(($this->image_contrast + 128) * $pixel['blue'] / 128), 255), 0);
                                    $color = imagecolorallocatealpha($image_dst, $r, $g, $b, $pixel['alpha']);
                                    imagesetpixel($image_dst, $x, $y, $color);
                                    unset($color); unset($pixel);
                                }
                                if (!empty($this->image_tint_color)) {
                                    $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                    $r = min(round($tint_red * $pixel['red'] / 169), 255);
                                    $g = min(round($tint_green * $pixel['green'] / 169), 255);
                                    $b = min(round($tint_blue * $pixel['blue'] / 169), 255);
                                    $color = imagecolorallocatealpha($image_dst, $r, $g, $b, $pixel['alpha']);
                                    imagesetpixel($image_dst, $x, $y, $color);
                                    unset($color); unset($pixel);
                                }
                                if (!empty($this->image_negative)) {
                                    $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                    $r = round(255 - $pixel['red']);
                                    $g = round(255 - $pixel['green']);
                                    $b = round(255 - $pixel['blue']);
                                    $color = imagecolorallocatealpha($image_dst, $r, $g, $b, $pixel['alpha']);
                                    imagesetpixel($image_dst, $x, $y, $color);
                                    unset($color); unset($pixel);
                                }
                            }
                        }
                    }

                    // adds a border
                    if ($gd_version >= 2 && !empty($this->image_border)) {
                        list($ct, $cr, $cb, $cl) = $this->getoffsets($this->image_border, $this->image_dst_x, $this->image_dst_y, true, false);
                        $this->log .= '- add border : ' . $ct . ' ' . $cr . ' ' . $cb . ' ' . $cl . '<br />';
                        $this->image_dst_x = $this->image_dst_x + $cl + $cr;
                        $this->image_dst_y = $this->image_dst_y + $ct + $cb;
                        if (!empty($this->image_border_color)) list($red, $green, $blue) = $this->getcolors($this->image_border_color);   
                        $opacity = (is_numeric($this->image_border_opacity) ? (int) (127 - $this->image_border_opacity / 100 * 127): 0);                       
                        // we now create an image, that we fill with the border color                                                                           
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);                                                                   
                        $background = imagecolorallocatealpha($tmp, $red, $green, $blue, $opacity);
                        imagefilledrectangle($tmp, 0, 0, $this->image_dst_x, $this->image_dst_y, $background);
                        // we then copy the source image into the new image, without merging so that only the border is actually kept
                        imagecopy($tmp, $image_dst, $cl, $ct, 0, 0, $this->image_dst_x - $cr - $cl, $this->image_dst_y - $cb - $ct);
                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // adds a fading-to-transparent border
                    if ($gd_version >= 2 && !empty($this->image_border_transparent)) {
                        list($ct, $cr, $cb, $cl) = $this->getoffsets($this->image_border_transparent, $this->image_dst_x, $this->image_dst_y, true, false);
                        $this->log .= '- add transparent border : ' . $ct . ' ' . $cr . ' ' . $cb . ' ' . $cl . '<br />';
                        // we now create an image, that we fill with the border color                                                                           
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);                                                                   
                        // we then copy the source image into the new image, without the borders
                        imagecopy($tmp, $image_dst, $cl, $ct, $cl, $ct, $this->image_dst_x - $cr - $cl, $this->image_dst_y - $cb - $ct);
                        // we now add the top border
                        $opacity = 100;
                        for ($y = $ct - 1; $y >= 0; $y--) {
                            $il = (int) ($ct > 0 ? ($cl * ($y / $ct)) : 0);
                            $ir = (int) ($ct > 0 ? ($cr * ($y / $ct)) : 0);
                            for ($x = $il; $x < $this->image_dst_x - $ir; $x++) {
                                $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                $alpha = (1 - ($pixel['alpha'] / 127)) * $opacity / 100;
                                if ($alpha > 0) {
                                    if ($alpha > 1) $alpha = 1;
                                    $color = imagecolorallocatealpha($tmp, $pixel['red'] , $pixel['green'], $pixel['blue'],  round((1 - $alpha) * 127));
                                    imagesetpixel($tmp, $x, $y, $color);
                                }
                            }
                            if ($opacity > 0) $opacity = $opacity - (100 / $ct);
                        }
                        // we now add the right border
                        $opacity = 100;
                        for ($x = $this->image_dst_x - $cr; $x < $this->image_dst_x; $x++) {
                            $it = (int) ($cr > 0 ? ($ct * (($this->image_dst_x - $x - 1) / $cr)) : 0);
                            $ib = (int) ($cr > 0 ? ($cb * (($this->image_dst_x - $x - 1) / $cr)) : 0);
                            for ($y = $it; $y < $this->image_dst_y - $ib; $y++) {
                                $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                $alpha = (1 - ($pixel['alpha'] / 127)) * $opacity / 100;
                                if ($alpha > 0) {
                                    if ($alpha > 1) $alpha = 1;
                                    $color = imagecolorallocatealpha($tmp, $pixel['red'] , $pixel['green'], $pixel['blue'],  round((1 - $alpha) * 127));
                                    imagesetpixel($tmp, $x, $y, $color);
                                }
                            }
                            if ($opacity > 0) $opacity = $opacity - (100 / $cr);
                        }
                        // we now add the bottom border
                        $opacity = 100;
                        for ($y = $this->image_dst_y - $cb; $y < $this->image_dst_y; $y++) {
                            $il = (int) ($cb > 0 ? ($cl * (($this->image_dst_y - $y - 1) / $cb)) : 0);
                            $ir = (int) ($cb > 0 ? ($cr * (($this->image_dst_y - $y - 1) / $cb)) : 0);
                            for ($x = $il; $x < $this->image_dst_x - $ir; $x++) {
                                $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                $alpha = (1 - ($pixel['alpha'] / 127)) * $opacity / 100;
                                if ($alpha > 0) {
                                    if ($alpha > 1) $alpha = 1;
                                    $color = imagecolorallocatealpha($tmp, $pixel['red'] , $pixel['green'], $pixel['blue'],  round((1 - $alpha) * 127));
                                    imagesetpixel($tmp, $x, $y, $color);
                                }
                            }
                            if ($opacity > 0) $opacity = $opacity - (100 / $cb);
                        }
                        // we now add the left border
                        $opacity = 100;
                        for ($x = $cl - 1; $x >= 0; $x--) {
                            $it = (int) ($cl > 0 ? ($ct * ($x / $cl)) : 0);
                            $ib = (int) ($cl > 0 ? ($cb * ($x / $cl)) : 0);
                            for ($y = $it; $y < $this->image_dst_y - $ib; $y++) {
                                $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                $alpha = (1 - ($pixel['alpha'] / 127)) * $opacity / 100;
                                if ($alpha > 0) {
                                    if ($alpha > 1) $alpha = 1;
                                    $color = imagecolorallocatealpha($tmp, $pixel['red'] , $pixel['green'], $pixel['blue'],  round((1 - $alpha) * 127));
                                    imagesetpixel($tmp, $x, $y, $color);
                                }
                            }
                            if ($opacity > 0) $opacity = $opacity - (100 / $cl);
                        }
                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // add frame border
                    if ($gd_version >= 2 && is_numeric($this->image_frame)) {
                        if (is_array($this->image_frame_colors)) {
                            $vars = $this->image_frame_colors;
                            $this->log .= '- add frame : ' . implode(' ', $this->image_frame_colors) . '<br />';
                        } else {
                            $this->log .= '- add frame : ' . $this->image_frame_colors . '<br />';
                            $vars = explode(' ', $this->image_frame_colors);
                        }
                        $nb = sizeof($vars);
                        $this->image_dst_x = $this->image_dst_x + ($nb * 2);
                        $this->image_dst_y = $this->image_dst_y + ($nb * 2);
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);
                        imagecopy($tmp, $image_dst, $nb, $nb, 0, 0, $this->image_dst_x - ($nb * 2), $this->image_dst_y - ($nb * 2));
                        $opacity = (is_numeric($this->image_frame_opacity) ? (int) (127 - $this->image_frame_opacity / 100 * 127): 0);                       
                        for ($i=0; $i<$nb; $i++) {
                            list($red, $green, $blue) = $this->getcolors($vars[$i]);
                            $c = imagecolorallocatealpha($tmp, $red, $green, $blue, $opacity);
                            if ($this->image_frame == 1) {
                                imageline($tmp, $i, $i, $this->image_dst_x - $i -1, $i, $c);
                                imageline($tmp, $this->image_dst_x - $i -1, $this->image_dst_y - $i -1, $this->image_dst_x - $i -1, $i, $c);
                                imageline($tmp, $this->image_dst_x - $i -1, $this->image_dst_y - $i -1, $i, $this->image_dst_y - $i -1, $c);
                                imageline($tmp, $i, $i, $i, $this->image_dst_y - $i -1, $c);
                            } else {
                                imageline($tmp, $i, $i, $this->image_dst_x - $i -1, $i, $c);
                                imageline($tmp, $this->image_dst_x - $nb + $i, $this->image_dst_y - $nb + $i, $this->image_dst_x - $nb + $i, $nb - $i, $c);
                                imageline($tmp, $this->image_dst_x - $nb + $i, $this->image_dst_y - $nb + $i, $nb - $i, $this->image_dst_y - $nb + $i, $c);
                                imageline($tmp, $i, $i, $i, $this->image_dst_y - $i -1, $c);
                            }
                        }
                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // add bevel border
                    if ($gd_version >= 2 && $this->image_bevel > 0) {
                        if (empty($this->image_bevel_color1)) $this->image_bevel_color1 = '#FFFFFF';
                        if (empty($this->image_bevel_color2)) $this->image_bevel_color2 = '#000000';
                        list($red1, $green1, $blue1) = $this->getcolors($this->image_bevel_color1);
                        list($red2, $green2, $blue2) = $this->getcolors($this->image_bevel_color2);
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y);
                        imagecopy($tmp, $image_dst, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y);
                        imagealphablending($tmp, true);
                        for ($i=0; $i<$this->image_bevel; $i++) {
                            $alpha = round(($i / $this->image_bevel) * 127);
                            $c1 = imagecolorallocatealpha($tmp, $red1, $green1, $blue1, $alpha);
                            $c2 = imagecolorallocatealpha($tmp, $red2, $green2, $blue2, $alpha);
                            imageline($tmp, $i, $i, $this->image_dst_x - $i -1, $i, $c1);
                            imageline($tmp, $this->image_dst_x - $i -1, $this->image_dst_y - $i, $this->image_dst_x - $i -1, $i, $c2);
                            imageline($tmp, $this->image_dst_x - $i -1, $this->image_dst_y - $i -1, $i, $this->image_dst_y - $i -1, $c2);
                            imageline($tmp, $i, $i, $i, $this->image_dst_y - $i -1, $c1);
                        }
                        // we transfert tmp into image_dst
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // add watermark image
                    if ($this->image_watermark!='' && file_exists($this->image_watermark)) {
                        $this->log .= '- add watermark<br />';
                        $this->image_watermark_position = strtolower($this->image_watermark_position);
                        $watermark_info = getimagesize($this->image_watermark);
                        $watermark_type = (array_key_exists(2, $watermark_info) ? $watermark_info[2] : null); // 1 = GIF, 2 = JPG, 3 = PNG
                        $watermark_checked = false;
                        if ($watermark_type == IMAGETYPE_GIF) {
                            if (!function_exists('imagecreatefromgif')) {
                                $this->error = $this->translate('watermark_no_create_support', array('GIF'));
                            } else {
                                $filter = @imagecreatefromgif($this->image_watermark);
                                if (!$filter) {
                                    $this->error = $this->translate('watermark_create_error', array('GIF'));
                                } else {
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is GIF<br />';
                                    $watermark_checked = true;
                                }
                            }
                        } else if ($watermark_type == IMAGETYPE_JPEG) {
                            if (!function_exists('imagecreatefromjpeg')) {
                                $this->error = $this->translate('watermark_no_create_support', array('JPEG'));
                            } else {
                                $filter = @imagecreatefromjpeg($this->image_watermark);
                                if (!$filter) {
                                    $this->error = $this->translate('watermark_create_error', array('JPEG'));
                                } else {
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is JPEG<br />';
                                    $watermark_checked = true;
                                }
                            }
                        } else if ($watermark_type == IMAGETYPE_PNG) {
                            if (!function_exists('imagecreatefrompng')) {
                                $this->error = $this->translate('watermark_no_create_support', array('PNG'));
                            } else {
                                $filter = @imagecreatefrompng($this->image_watermark);
                                if (!$filter) {
                                    $this->error = $this->translate('watermark_create_error', array('PNG'));
                                } else {
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is PNG<br />';
                                    $watermark_checked = true;
                                }
                            }
                        } else if ($watermark_type == IMAGETYPE_BMP) {
                            if (!method_exists($this, 'imagecreatefrombmp')) {
                                $this->error = $this->translate('watermark_no_create_support', array('BMP'));
                            } else {
                                $filter = @$this->imagecreatefrombmp($this->image_watermark);
                                if (!$filter) {
                                    $this->error = $this->translate('watermark_create_error', array('BMP'));
                                } else {
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is BMP<br />';
                                    $watermark_checked = true;
                                }
                            }
                        } else {
                            $this->error = $this->translate('watermark_invalid');
                        }
                        if ($watermark_checked) {
                            $watermark_dst_width  = $watermark_src_width  = imagesx($filter);
                            $watermark_dst_height = $watermark_src_height = imagesy($filter);

                            // if watermark is too large/tall, resize it first
                            if ((!$this->image_watermark_no_zoom_out && ($watermark_dst_width > $this->image_dst_x || $watermark_dst_height > $this->image_dst_y))
                             || (!$this->image_watermark_no_zoom_in && $watermark_dst_width < $this->image_dst_x && $watermark_dst_height < $this->image_dst_y)) {
                                $canvas_width  = $this->image_dst_x - abs($this->image_watermark_x);
                                $canvas_height = $this->image_dst_y - abs($this->image_watermark_y);                            
                                if (($watermark_src_width/$canvas_width) > ($watermark_src_height/$canvas_height)) {
                                    $watermark_dst_width = $canvas_width;
                                    $watermark_dst_height = intval($watermark_src_height*($canvas_width / $watermark_src_width));
                                } else {
                                    $watermark_dst_height = $canvas_height;
                                    $watermark_dst_width = intval($watermark_src_width*($canvas_height / $watermark_src_height));
                                }
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;watermark resized from '.$watermark_src_width.'x'.$watermark_src_height.' to '.$watermark_dst_width.'x'.$watermark_dst_height.'<br />';

                            }
                            // determine watermark position
                            $watermark_x = 0;
                            $watermark_y = 0;
                            if (is_numeric($this->image_watermark_x)) {
                                if ($this->image_watermark_x < 0) {
                                    $watermark_x = $this->image_dst_x - $watermark_dst_width + $this->image_watermark_x;
                                } else {
                                    $watermark_x = $this->image_watermark_x;
                                }
                            } else {
                                if (strpos($this->image_watermark_position, 'r') !== false) {
                                    $watermark_x = $this->image_dst_x - $watermark_dst_width;
                                } else if (strpos($this->image_watermark_position, 'l') !== false) {
                                    $watermark_x = 0;
                                } else {
                                    $watermark_x = ($this->image_dst_x - $watermark_dst_width) / 2;
                                }
                            }
                            if (is_numeric($this->image_watermark_y)) {
                                if ($this->image_watermark_y < 0) {
                                    $watermark_y = $this->image_dst_y - $watermark_dst_height + $this->image_watermark_y;
                                } else {
                                    $watermark_y = $this->image_watermark_y;
                                }
                            } else {
                                if (strpos($this->image_watermark_position, 'b') !== false) {
                                    $watermark_y = $this->image_dst_y - $watermark_dst_height;
                                } else if (strpos($this->image_watermark_position, 't') !== false) {
                                    $watermark_y = 0;
                                } else {
                                    $watermark_y = ($this->image_dst_y - $watermark_dst_height) / 2;
                                }
                            }
                            imagealphablending($image_dst, true);
                            imagecopyresampled($image_dst, $filter, $watermark_x, $watermark_y, 0, 0, $watermark_dst_width, $watermark_dst_height, $watermark_src_width, $watermark_src_height);
                        } else {
                            $this->error = $this->translate('watermark_invalid');
                        }
                    }

                    // add text
                    if (!empty($this->image_text)) {
                        $this->log .= '- add text<br />';

                        // calculate sizes in human readable format
                        $src_size       = $this->file_src_size / 1024;
                        $src_size_mb    = number_format($src_size / 1024, 1, ".", " ");
                        $src_size_kb    = number_format($src_size, 1, ".", " ");
                        $src_size_human = ($src_size > 1024 ? $src_size_mb . " MB" : $src_size_kb . " kb");

                        $this->image_text = str_replace(
                            array('[src_name]',
                                  '[src_name_body]',
                                  '[src_name_ext]',
                                  '[src_pathname]',
                                  '[src_mime]',
                                  '[src_size]',
                                  '[src_size_kb]',
                                  '[src_size_mb]',
                                  '[src_size_human]',
                                  '[src_x]',
                                  '[src_y]',
                                  '[src_pixels]',
                                  '[src_type]',
                                  '[src_bits]',
                                  '[dst_path]',
                                  '[dst_name_body]',
                                  '[dst_name_ext]',
                                  '[dst_name]',
                                  '[dst_pathname]',
                                  '[dst_x]',
                                  '[dst_y]',
                                  '[date]',
                                  '[time]',
                                  '[host]',
                                  '[server]',
                                  '[ip]',
                                  '[gd_version]'),
                            array($this->file_src_name,
                                  $this->file_src_name_body,
                                  $this->file_src_name_ext,
                                  $this->file_src_pathname,
                                  $this->file_src_mime,
                                  $this->file_src_size,
                                  $src_size_kb,
                                  $src_size_mb,
                                  $src_size_human,
                                  $this->image_src_x,
                                  $this->image_src_y,
                                  $this->image_src_pixels,
                                  $this->image_src_type,
                                  $this->image_src_bits,
                                  $this->file_dst_path,
                                  $this->file_dst_name_body,
                                  $this->file_dst_name_ext,
                                  $this->file_dst_name,
                                  $this->file_dst_pathname,
                                  $this->image_dst_x,
                                  $this->image_dst_y,
                                  date('Y-m-d'),
                                  date('H:i:s'),
                                  (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'n/a'),
                                  (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'n/a'),
                                  (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'n/a'),
                                  $this->gdversion(true)),
                            $this->image_text);

                        if (!is_numeric($this->image_text_padding)) $this->image_text_padding = 0;
                        if (!is_numeric($this->image_text_line_spacing)) $this->image_text_line_spacing = 0;
                        if (!is_numeric($this->image_text_padding_x)) $this->image_text_padding_x = $this->image_text_padding;
                        if (!is_numeric($this->image_text_padding_y)) $this->image_text_padding_y = $this->image_text_padding;
                        $this->image_text_position = strtolower($this->image_text_position);
                        $this->image_text_direction = strtolower($this->image_text_direction);
                        $this->image_text_alignment = strtolower($this->image_text_alignment);

                        // if the font is a string, we assume that we might want to load a font
                        if (!is_numeric($this->image_text_font) && strlen($this->image_text_font) > 4 && substr(strtolower($this->image_text_font), -4) == '.gdf') {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;try to load font ' . $this->image_text_font . '... ';
                            if ($this->image_text_font = @imageloadfont($this->image_text_font)) {
                                $this->log .=  'success<br />';
                            } else {
                                $this->log .=  'error<br />';
                                $this->image_text_font = 5;
                            }
                        }

                        $text = explode("\n", $this->image_text);
                        $char_width = imagefontwidth($this->image_text_font);
                        $char_height = imagefontheight($this->image_text_font);
                        $text_height = 0;
                        $text_width = 0;
                        $line_height = 0;
                        $line_width = 0;

                        foreach ($text as $k => $v) {
                            if ($this->image_text_direction == 'v') {
                                $h = ($char_width * strlen($v));
                                if ($h > $text_height) $text_height = $h;
                                $line_width = $char_height;
                                $text_width += $line_width + ($k < (sizeof($text)-1) ? $this->image_text_line_spacing : 0);
                            } else {
                                $w = ($char_width * strlen($v));
                                if ($w > $text_width) $text_width = $w;
                                $line_height = $char_height;
                                $text_height += $line_height + ($k < (sizeof($text)-1) ? $this->image_text_line_spacing : 0);
                            }
                        }
                        $text_width  += (2 * $this->image_text_padding_x);
                        $text_height += (2 * $this->image_text_padding_y);
                        $text_x = 0;
                        $text_y = 0;
                        if (is_numeric($this->image_text_x)) {
                            if ($this->image_text_x < 0) {
                                $text_x = $this->image_dst_x - $text_width + $this->image_text_x;
                            } else {
                                $text_x = $this->image_text_x;
                            }
                        } else {
                            if (strpos($this->image_text_position, 'r') !== false) {
                                $text_x = $this->image_dst_x - $text_width;
                            } else if (strpos($this->image_text_position, 'l') !== false) {
                                $text_x = 0;
                            } else {
                                $text_x = ($this->image_dst_x - $text_width) / 2;
                            }
                        }
                        if (is_numeric($this->image_text_y)) {
                            if ($this->image_text_y < 0) {
                                $text_y = $this->image_dst_y - $text_height + $this->image_text_y;
                            } else {
                                $text_y = $this->image_text_y;
                            }
                        } else {
                            if (strpos($this->image_text_position, 'b') !== false) {
                                $text_y = $this->image_dst_y - $text_height;
                            } else if (strpos($this->image_text_position, 't') !== false) {
                                $text_y = 0;
                            } else {
                                $text_y = ($this->image_dst_y - $text_height) / 2;
                            }
                        }

                        // add a background, maybe transparent
                        if (!empty($this->image_text_background)) {
                            list($red, $green, $blue) = $this->getcolors($this->image_text_background);
                            if ($gd_version >= 2 && (is_numeric($this->image_text_background_opacity)) && $this->image_text_background_opacity >= 0 && $this->image_text_background_opacity <= 100) {
                                $filter = imagecreatetruecolor($text_width, $text_height);
                                $background_color = imagecolorallocate($filter, $red, $green, $blue);
                                imagefilledrectangle($filter, 0, 0, $text_width, $text_height, $background_color);
                                $this->imagecopymergealpha($image_dst, $filter, $text_x, $text_y, 0, 0, $text_width, $text_height, $this->image_text_background_opacity);
                                imagedestroy($filter);
                            } else {
                                $background_color = imagecolorallocate($image_dst ,$red, $green, $blue);
                                imagefilledrectangle($image_dst, $text_x, $text_y, $text_x + $text_width, $text_y + $text_height, $background_color);
                            }
                        }

                        $text_x += $this->image_text_padding_x;
                        $text_y += $this->image_text_padding_y;
                        $t_width = $text_width - (2 * $this->image_text_padding_x);
                        $t_height = $text_height - (2 * $this->image_text_padding_y);
                        list($red, $green, $blue) = $this->getcolors($this->image_text_color);

                        // add the text, maybe transparent
                        if ($gd_version >= 2 && (is_numeric($this->image_text_opacity)) && $this->image_text_opacity >= 0 && $this->image_text_opacity <= 100) {
                            if ($t_width < 0) $t_width = 0;
                            if ($t_height < 0) $t_height = 0;
                            $filter = $this->imagecreatenew($t_width, $t_height, false, true);
                            $text_color = imagecolorallocate($filter ,$red, $green, $blue);

                            foreach ($text as $k => $v) {
                                if ($this->image_text_direction == 'v') {
                                    imagestringup($filter,
                                                  $this->image_text_font,
                                                  $k * ($line_width  + ($k > 0 && $k < (sizeof($text)) ? $this->image_text_line_spacing : 0)),
                                                  $text_height - (2 * $this->image_text_padding_y) - ($this->image_text_alignment == 'l' ? 0 : (($t_height - strlen($v) * $char_width) / ($this->image_text_alignment == 'r' ? 1 : 2))) ,
                                                  $v,
                                                  $text_color);
                                } else {
                                    imagestring($filter,
                                                $this->image_text_font,
                                                ($this->image_text_alignment == 'l' ? 0 : (($t_width - strlen($v) * $char_width) / ($this->image_text_alignment == 'r' ? 1 : 2))),
                                                $k * ($line_height  + ($k > 0 && $k < (sizeof($text)) ? $this->image_text_line_spacing : 0)),
                                                $v,
                                                $text_color);
                                }
                            }
                            $this->imagecopymergealpha($image_dst, $filter, $text_x, $text_y, 0, 0, $t_width, $t_height, $this->image_text_opacity);
                            imagedestroy($filter);

                        } else {
                            $text_color = imageColorAllocate($image_dst ,$red, $green, $blue);
                            foreach ($text as $k => $v) {
                                if ($this->image_text_direction == 'v') {
                                    imagestringup($image_dst,
                                                  $this->image_text_font,
                                                  $text_x + $k * ($line_width  + ($k > 0 && $k < (sizeof($text)) ? $this->image_text_line_spacing : 0)),
                                                  $text_y + $text_height - (2 * $this->image_text_padding_y) - ($this->image_text_alignment == 'l' ? 0 : (($t_height - strlen($v) * $char_width) / ($this->image_text_alignment == 'r' ? 1 : 2))),
                                                  $v,
                                                  $text_color);
                                } else {
                                    imagestring($image_dst,
                                                $this->image_text_font,
                                                $text_x + ($this->image_text_alignment == 'l' ? 0 : (($t_width - strlen($v) * $char_width) / ($this->image_text_alignment == 'r' ? 1 : 2))),
                                                $text_y + $k * ($line_height  + ($k > 0 && $k < (sizeof($text)) ? $this->image_text_line_spacing : 0)),
                                                $v,
                                                $text_color);
                                }
                            }
                        }
                    }

                    // add a reflection
                    if ($this->image_reflection_height) {
                        $this->log .= '- add reflection : ' . $this->image_reflection_height . '<br />';
                        // we decode image_reflection_height, which can be a integer, a string in pixels or percentage
                        $image_reflection_height = $this->image_reflection_height;
                        if (strpos($image_reflection_height, '%')>0) $image_reflection_height = $this->image_dst_y * (str_replace('%','',$image_reflection_height / 100));
                        if (strpos($image_reflection_height, 'px')>0) $image_reflection_height = str_replace('px','',$image_reflection_height);
                        $image_reflection_height = (int) $image_reflection_height;
                        if ($image_reflection_height > $this->image_dst_y) $image_reflection_height = $this->image_dst_y;
                        if (empty($this->image_reflection_opacity)) $this->image_reflection_opacity = 60;
                        // create the new destination image
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y + $image_reflection_height + $this->image_reflection_space, true);
                        $transparency = $this->image_reflection_opacity;

                        // copy the original image
                        imagecopy($tmp, $image_dst, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y + ($this->image_reflection_space < 0 ? $this->image_reflection_space : 0));

                        // we have to make sure the extra bit is the right color, or transparent
                        if ($image_reflection_height + $this->image_reflection_space > 0) {
                            // use the background color if present
                            if (!empty($this->image_background_color)) {
                                list($red, $green, $blue) = $this->getcolors($this->image_background_color);
                                $fill = imagecolorallocate($tmp, $red, $green, $blue);
                            } else {
                                $fill = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
                            }
                            // fill in from the edge of the extra bit
                            imagefill($tmp, round($this->image_dst_x / 2), $this->image_dst_y + $image_reflection_height + $this->image_reflection_space - 1, $fill);
                        }

                        // copy the reflection
                        for ($y = 0; $y < $image_reflection_height; $y++) {
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                $pixel_b = imagecolorsforindex($tmp, imagecolorat($tmp, $x, $y + $this->image_dst_y + $this->image_reflection_space));
                                $pixel_o = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $this->image_dst_y - $y - 1 + ($this->image_reflection_space < 0 ? $this->image_reflection_space : 0)));
                                $alpha_o = 1 - ($pixel_o['alpha'] / 127);
                                $alpha_b = 1 - ($pixel_b['alpha'] / 127);
                                $opacity = $alpha_o * $transparency / 100;
                                if ($opacity > 0) {
                                    $red   = round((($pixel_o['red']   * $opacity) + ($pixel_b['red']  ) * $alpha_b) / ($alpha_b + $opacity));
                                    $green = round((($pixel_o['green'] * $opacity) + ($pixel_b['green']) * $alpha_b) / ($alpha_b + $opacity));
                                    $blue  = round((($pixel_o['blue']  * $opacity) + ($pixel_b['blue'] ) * $alpha_b) / ($alpha_b + $opacity));
                                    $alpha = ($opacity + $alpha_b);
                                    if ($alpha > 1) $alpha = 1;
                                    $alpha =  round((1 - $alpha) * 127);
                                    $color = imagecolorallocatealpha($tmp, $red, $green, $blue, $alpha);
                                    imagesetpixel($tmp, $x, $y + $this->image_dst_y + $this->image_reflection_space, $color);
                                }
                            }
                            if ($transparency > 0) $transparency = $transparency - ($this->image_reflection_opacity / $image_reflection_height);
                        }

                        // copy the resulting image into the destination image
                        $this->image_dst_y = $this->image_dst_y + $image_reflection_height + $this->image_reflection_space;
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }

                    // change opacity
                    if ($gd_version >= 2 && is_numeric($this->image_opacity) && $this->image_opacity < 100) {
                        $this->log .= '- change opacity<br />';
                        // create the new destination image
                        $tmp = $this->imagecreatenew($this->image_dst_x, $this->image_dst_y, true);
                        for($y=0; $y < $this->image_dst_y; $y++) {
                            for($x=0; $x < $this->image_dst_x; $x++) {
                                $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                $alpha = $pixel['alpha'] + round((127 - $pixel['alpha']) * (100 - $this->image_opacity) / 100);
                                if ($alpha > 127) $alpha = 127;
                                if ($alpha > 0) {
                                    $color = imagecolorallocatealpha($tmp, $pixel['red'] , $pixel['green'], $pixel['blue'], $alpha);
                                    imagesetpixel($tmp, $x, $y, $color);
                                }
                            }
                        }
                        // copy the resulting image into the destination image
                        $image_dst = $this->imagetransfer($tmp, $image_dst);
                    }
                    
                    // reduce the JPEG image to a set desired size
                    if (is_numeric($this->jpeg_size) && $this->jpeg_size > 0 && ($this->image_convert == 'jpeg' || $this->image_convert == 'jpg')) {
                        // inspired by: JPEGReducer class version 1, 25 November 2004, Author: Huda M ElMatsani, justhuda at netscape dot net
                        $this->log .= '- JPEG desired file size : ' . $this->jpeg_size . '<br />';
                        // calculate size of each image. 75%, 50%, and 25% quality
                        ob_start(); imagejpeg($image_dst,null,75);  $buffer = ob_get_contents(); ob_end_clean();
                        $size75 = strlen($buffer);
                        ob_start(); imagejpeg($image_dst,null,50);  $buffer = ob_get_contents(); ob_end_clean();
                        $size50 = strlen($buffer);
                        ob_start(); imagejpeg($image_dst,null,25);  $buffer = ob_get_contents(); ob_end_clean();
                        $size25 = strlen($buffer);

                        // make sure we won't divide by 0
                        if ($size50 == $size25) $size50++;
                        if ($size75 == $size50 || $size75 == $size25) $size75++;

                        // calculate gradient of size reduction by quality
                        $mgrad1 = 25 / ($size50-$size25);
                        $mgrad2 = 25 / ($size75-$size50);
                        $mgrad3 = 50 / ($size75-$size25);
                        $mgrad  = ($mgrad1 + $mgrad2 + $mgrad3) / 3;
                        // result of approx. quality factor for expected size
                        $q_factor = round($mgrad * ($this->jpeg_size - $size50) + 50);

                        if ($q_factor<1) {
                            $this->jpeg_quality=1;
                        } elseif ($q_factor>100) {
                            $this->jpeg_quality=100;
                        } else {
                            $this->jpeg_quality=$q_factor;
                        }
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;JPEG quality factor set to ' . $this->jpeg_quality . '<br />';
                    }

                    // converts image from true color, and fix transparency if needed
                    $this->log .= '- converting...<br />';
                    $this->image_dst_type = $this->image_convert;
                    switch($this->image_convert) {
                        case 'gif':
                            // if the image is true color, we convert it to a palette
                            if (imageistruecolor($image_dst)) {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;true color to palette<br />';
                                // creates a black and white mask
                                $mask = array(array());
                                for ($x = 0; $x < $this->image_dst_x; $x++) {
                                    for ($y = 0; $y < $this->image_dst_y; $y++) {
                                        $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                        $mask[$x][$y] = $pixel['alpha'];
                                    }
                                }
                                list($red, $green, $blue) = $this->getcolors($this->image_default_color);
                                // first, we merge the image with the background color, so we know which colors we will have
                                for ($x = 0; $x < $this->image_dst_x; $x++) {
                                    for ($y = 0; $y < $this->image_dst_y; $y++) {
                                        if ($mask[$x][$y] > 0){
                                            // we have some transparency. we combine the color with the default color
                                            $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                            $alpha = ($mask[$x][$y] / 127);
                                            $pixel['red'] = round(($pixel['red'] * (1 -$alpha) + $red * ($alpha)));
                                            $pixel['green'] = round(($pixel['green'] * (1 -$alpha) + $green * ($alpha)));
                                            $pixel['blue'] = round(($pixel['blue'] * (1 -$alpha) + $blue * ($alpha)));
                                            $color = imagecolorallocate($image_dst, $pixel['red'], $pixel['green'], $pixel['blue']);
                                            imagesetpixel($image_dst, $x, $y, $color);
                                        }
                                    }
                                }
                                // transforms the true color image into palette, with its merged default color
                                if (empty($this->image_background_color)) {
                                    imagetruecolortopalette($image_dst, true, 255);
                                    $transparency = imagecolorallocate($image_dst, 254, 1, 253);
                                    imagecolortransparent($image_dst, $transparency);
                                    // make the transparent areas transparent
                                    for ($x = 0; $x < $this->image_dst_x; $x++) {
                                        for ($y = 0; $y < $this->image_dst_y; $y++) {
                                            // we test wether we have enough opacity to justify keeping the color
                                            if ($mask[$x][$y] > 120) imagesetpixel($image_dst, $x, $y, $transparency);
                                        }
                                    }
                                }
                                unset($mask);
                            }
                            break;
                        case 'jpg':
                        case 'bmp':
                            // if the image doesn't support any transparency, then we merge it with the default color
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;fills in transparency with default color<br />';
                            list($red, $green, $blue) = $this->getcolors($this->image_default_color);
                            $transparency = imagecolorallocate($image_dst, $red, $green, $blue);
                            // make the transaparent areas transparent
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                for ($y = 0; $y < $this->image_dst_y; $y++) {
                                    // we test wether we have some transparency, in which case we will merge the colors
                                    if (imageistruecolor($image_dst)) {
                                        $rgba = imagecolorat($image_dst, $x, $y);
                                        $pixel = array('red' => ($rgba >> 16) & 0xFF,
                                                       'green' => ($rgba >> 8) & 0xFF,
                                                       'blue' => $rgba & 0xFF,
                                                       'alpha' => ($rgba & 0x7F000000) >> 24);
                                    } else {
                                        $pixel = imagecolorsforindex($image_dst, imagecolorat($image_dst, $x, $y));
                                    }
                                    if ($pixel['alpha'] == 127) {
                                        // we have full transparency. we make the pixel transparent
                                        imagesetpixel($image_dst, $x, $y, $transparency);
                                    } else if ($pixel['alpha'] > 0) {
                                        // we have some transparency. we combine the color with the default color
                                        $alpha = ($pixel['alpha'] / 127);
                                        $pixel['red'] = round(($pixel['red'] * (1 -$alpha) + $red * ($alpha)));
                                        $pixel['green'] = round(($pixel['green'] * (1 -$alpha) + $green * ($alpha)));
                                        $pixel['blue'] = round(($pixel['blue'] * (1 -$alpha) + $blue * ($alpha)));
                                        $color = imagecolorclosest($image_dst, $pixel['red'], $pixel['green'], $pixel['blue']);
                                        imagesetpixel($image_dst, $x, $y, $color);
                                    }
                                }
                            }

                            break;
                        default:
                            break;
                    }

                    // interlace options
                    if($this->image_interlace) imageinterlace($image_dst, true);

                    // outputs image
                    $this->log .= '- saving image...<br />';
                    switch($this->image_convert) {
                        case 'jpeg':
                        case 'jpg':
                            if (!$return_mode) {
                                $result = @imagejpeg($image_dst, $this->file_dst_pathname, $this->jpeg_quality);
                            } else {
                                ob_start();
                                $result = @imagejpeg($image_dst, null, $this->jpeg_quality);
                                $return_content = ob_get_contents();
                                ob_end_clean();
                            }
                            if (!$result) {
                                $this->processed = false;
                                $this->error = $this->translate('file_create', array('JPEG'));
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;JPEG image created<br />';
                            }
                            break;
                        case 'png':
                            imagealphablending( $image_dst, false );
                            imagesavealpha( $image_dst, true );
                            if (!$return_mode) {
                                if (is_numeric($this->png_compression) && version_compare(PHP_VERSION, '5.1.2') >= 0) {
                                    $result = @imagepng($image_dst, $this->file_dst_pathname, $this->png_compression);
                                } else {
                                    $result = @imagepng($image_dst, $this->file_dst_pathname);
                                }
                            } else {
                                ob_start();
                                if (is_numeric($this->png_compression) && version_compare(PHP_VERSION, '5.1.2') >= 0) {
                                    $result = @imagepng($image_dst, null, $this->png_compression);
                                } else {
                                    $result = @imagepng($image_dst);
                                }
                                $return_content = ob_get_contents();
                                ob_end_clean();
                            }
                            if (!$result) {
                                $this->processed = false;
                                $this->error = $this->translate('file_create', array('PNG'));
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;PNG image created<br />';
                            }
                            break;
                        case 'gif':
                            if (!$return_mode) {
                                $result = @imagegif($image_dst, $this->file_dst_pathname);
                            } else {
                                ob_start();
                                $result = @imagegif($image_dst);
                                $return_content = ob_get_contents();
                                ob_end_clean();
                            }
                            if (!$result) {
                                $this->processed = false;
                                $this->error = $this->translate('file_create', array('GIF'));
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;GIF image created<br />';
                            }
                            break;
                        case 'bmp':
                            if (!$return_mode) {
                                $result = $this->imagebmp($image_dst, $this->file_dst_pathname);
                            } else {
                                ob_start();
                                $result = $this->imagebmp($image_dst);
                                $return_content = ob_get_contents();
                                ob_end_clean();
                            }
                            if (!$result) {
                                $this->processed = false;
                                $this->error = $this->translate('file_create', array('BMP'));
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;BMP image created<br />';
                            }
                            break;

                        default:
                            $this->processed = false;
                            $this->error = $this->translate('no_conversion_type');
                    }
                    if ($this->processed) {
                        if (is_resource($image_src)) imagedestroy($image_src);
                        if (is_resource($image_dst)) imagedestroy($image_dst);
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image objects destroyed<br />';
                    }
                }

            } else {
                $this->log .= '- no image processing wanted<br />';

                if (!$return_mode) {
                    // copy the file to its final destination. we don't use move_uploaded_file here
                    // if we happen to have open_basedir restrictions, it is a temp file that we copy, not the original uploaded file
                    if (!copy($this->file_src_pathname, $this->file_dst_pathname)) {
                        $this->processed = false;
                        $this->error = $this->translate('copy_failed');
                    }
                } else {
                    // returns the file, so that its content can be received by the caller
                    $return_content = @file_get_contents($this->file_src_pathname);
                    if ($return_content === FALSE) {
                        $this->processed = false;
                        $this->error = $this->translate('reading_failed');
                    }
                }
            }
        }

        if ($this->processed) {
            $this->log .= '- <b>process OK</b><br />';
        } else {
            $this->log .= '- <b>error</b>: ' . $this->error . '<br />';
        }

        // we reinit all the vars
        $this->init();

        // we may return the image content
        if ($return_mode) return $return_content;

    }

    /**
     * Deletes the uploaded file from its temporary location
     *
     * When PHP uploads a file, it stores it in a temporary location.
     * When you {@link process} the file, you actually copy the resulting file to the given location, it doesn't alter the original file.
     * Once you have processed the file as many times as you wanted, you can delete the uploaded file.
     * If there is open_basedir restrictions, the uploaded file is in fact a temporary file
     *
     * You might want not to use this function if you work on local files, as it will delete the source file
     *
     * @access public
     */
    function clean() {
        $this->log .= '<b>cleanup</b><br />';
        $this->log .= '- delete temp file '  . $this->file_src_pathname . '<br />';
        @unlink($this->file_src_pathname);
    }


    /**
     * Opens a BMP image
     *
     * This function has been written by DHKold, and is used with permission of the author
     *
     * @access public
     */
    function imagecreatefrombmp($filename) {
        if (! $f1 = fopen($filename,"rb")) return false;

        $file = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
        if ($file['file_type'] != 19778) return false;

        $bmp = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                      '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                      '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
        $bmp['colors'] = pow(2,$bmp['bits_per_pixel']);
        if ($bmp['size_bitmap'] == 0) $bmp['size_bitmap'] = $file['file_size'] - $file['bitmap_offset'];
        $bmp['bytes_per_pixel'] = $bmp['bits_per_pixel']/8;
        $bmp['bytes_per_pixel2'] = ceil($bmp['bytes_per_pixel']);
        $bmp['decal'] = ($bmp['width']*$bmp['bytes_per_pixel']/4);
        $bmp['decal'] -= floor($bmp['width']*$bmp['bytes_per_pixel']/4);
        $bmp['decal'] = 4-(4*$bmp['decal']);
        if ($bmp['decal'] == 4) $bmp['decal'] = 0;

        $palette = array();
        if ($bmp['colors'] < 16777216) {
            $palette = unpack('V'.$bmp['colors'], fread($f1,$bmp['colors']*4));
        }

        $im = fread($f1,$bmp['size_bitmap']);
        $vide = chr(0);

        $res = imagecreatetruecolor($bmp['width'],$bmp['height']);
        $P = 0;
        $Y = $bmp['height']-1;
        while ($Y >= 0) {
            $X=0;
            while ($X < $bmp['width']) {
                if ($bmp['bits_per_pixel'] == 24)
                    $color = unpack("V",substr($im,$P,3).$vide);
                elseif ($bmp['bits_per_pixel'] == 16) {
                    $color = unpack("n",substr($im,$P,2));
                    $color[1] = $palette[$color[1]+1];
                } elseif ($bmp['bits_per_pixel'] == 8) {
                    $color = unpack("n",$vide.substr($im,$P,1));
                    $color[1] = $palette[$color[1]+1];
                } elseif ($bmp['bits_per_pixel'] == 4) {
                    $color = unpack("n",$vide.substr($im,floor($P),1));
                    if (($P*2)%2 == 0) $color[1] = ($color[1] >> 4) ; else $color[1] = ($color[1] & 0x0F);
                    $color[1] = $palette[$color[1]+1];
                } elseif ($bmp['bits_per_pixel'] == 1)  {
                    $color = unpack("n",$vide.substr($im,floor($P),1));
                    if     (($P*8)%8 == 0) $color[1] =  $color[1]        >>7;
                    elseif (($P*8)%8 == 1) $color[1] = ($color[1] & 0x40)>>6;
                    elseif (($P*8)%8 == 2) $color[1] = ($color[1] & 0x20)>>5;
                    elseif (($P*8)%8 == 3) $color[1] = ($color[1] & 0x10)>>4;
                    elseif (($P*8)%8 == 4) $color[1] = ($color[1] & 0x8)>>3;
                    elseif (($P*8)%8 == 5) $color[1] = ($color[1] & 0x4)>>2;
                    elseif (($P*8)%8 == 6) $color[1] = ($color[1] & 0x2)>>1;
                    elseif (($P*8)%8 == 7) $color[1] = ($color[1] & 0x1);
                    $color[1] = $palette[$color[1]+1];
                } else
                    return FALSE;
                imagesetpixel($res,$X,$Y,$color[1]);
                $X++;
                $P += $bmp['bytes_per_pixel'];
            }
            $Y--;
            $P+=$bmp['decal'];
        }
        fclose($f1);
        return $res;
    }

    /**
     * Saves a BMP image
     *
     * This function has been published on the PHP website, and can be used freely
     *
     * @access public
     */
    function imagebmp(&$im, $filename = "") {

        if (!$im) return false;
        $w = imagesx($im);
        $h = imagesy($im);
        $result = '';

        // if the image is not true color, we convert it first
        if (!imageistruecolor($im)) {
            $tmp = imagecreatetruecolor($w, $h);
            imagecopy($tmp, $im, 0, 0, 0, 0, $w, $h);
            imagedestroy($im);
            $im = & $tmp;
        }

        $biBPLine = $w * 3;
        $biStride = ($biBPLine + 3) & ~3;
        $biSizeImage = $biStride * $h;
        $bfOffBits = 54;
        $bfSize = $bfOffBits + $biSizeImage;

        $result .= substr('BM', 0, 2);
        $result .=  pack ('VvvV', $bfSize, 0, 0, $bfOffBits);
        $result .= pack ('VVVvvVVVVVV', 40, $w, $h, 1, 24, 0, $biSizeImage, 0, 0, 0, 0);

        $numpad = $biStride - $biBPLine;
        for ($y = $h - 1; $y >= 0; --$y) {
            for ($x = 0; $x < $w; ++$x) {
                $col = imagecolorat ($im, $x, $y);
                $result .=  substr(pack ('V', $col), 0, 3);
            }
            for ($i = 0; $i < $numpad; ++$i)
                $result .= pack ('C', 0);
        }

        if($filename==""){
            echo $result;
        } else {
            $file = fopen($filename, "wb");
            fwrite($file, $result);
            fclose($file);
        }
        return true;
    }
}
?>