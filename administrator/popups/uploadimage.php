<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( "_VALID_MOS", 1 );
/** security check */
require( "../includes/auth.php" );
include_once ( $mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php' );

$directory		= mosGetParam( $_REQUEST, 'directory', '');
$media_path		= $mosConfig_absolute_path .'/media/';

$userfile2		= (isset($_FILES['userfile']['tmp_name']) ? $_FILES['userfile']['tmp_name'] : "");
$userfile_name	= (isset($_FILES['userfile']['name']) ? $_FILES['userfile']['name'] : "");

if (isset($_FILES['userfile'])) {
	if ($directory == 'banners') {
		$base_Dir = "../../images/banners/";
	} else if ( $directory != '' ) {
		$base_Dir = '../../images/stories/'. $directory;
	} else {
		$base_Dir = '../../images/stories/';
	}
	
	if (empty($userfile_name)) {
		mosErrorAlert( JText::_( 'Please select an image to upload' ), "document.location.href='uploadimage.php'");
	}

	$filename = split("\.", $userfile_name);

	if (eregi("[^0-9a-zA-Z_]", $filename[0])) {
		mosErrorAlert( JText::_( 'VALIDALPHANOSPACES' ));
	}

	if (file_exists($base_Dir.$userfile_name)) {
		mosErrorAlert( sprintf( JText::_( 'Image already exists' ), $userfile_name ) );
	}

	if ((strcasecmp(substr($userfile_name,-4),".gif")) && (strcasecmp(substr($userfile_name,-4),".jpg")) && (strcasecmp(substr($userfile_name,-4),".png")) && (strcasecmp(substr($userfile_name,-4),".bmp")) &&(strcasecmp(substr($userfile_name,-4),".doc")) && (strcasecmp(substr($userfile_name,-4),".xls")) && (strcasecmp(substr($userfile_name,-4),".ppt")) && (strcasecmp(substr($userfile_name,-4),".swf")) && (strcasecmp(substr($userfile_name,-4),".pdf"))) {
		mosErrorAlert( JText::_( 'The file must be' ) ." gif, png, jpg, bmp, swf, doc, xls or ppt");
	}

	if (eregi(".pdf", $userfile_name) || eregi(".doc", $userfile_name) || eregi(".xls", $userfile_name) || eregi(".ppt", $userfile_name)) {
		if (!move_uploaded_file ($_FILES['userfile']['tmp_name'],$media_path.$_FILES['userfile']['name']) || !mosChmod($media_path.$_FILES['userfile']['name'])) {
			mosErrorAlert( sprintf( JText::_( 'Upload of image failed' ), $userfile_name ) );
		}
		else {
			mosErrorAlert( sprintf( JText::_( 'Upload of image successful' ), $userfile_name, $media_path ) );
		}
	} elseif (!move_uploaded_file ($_FILES['userfile']['tmp_name'],$base_Dir.$_FILES['userfile']['name']) || !mosChmod($base_Dir.$_FILES['userfile']['name'])) {
		mosErrorAlert( sprintf( JText::_( 'Upload of image failed' ), $userfile_name ) );
	}
	else {
		mosErrorAlert( sprintf( JText::_( 'Upload of image successful' ), $userfile_name, $base_Dir ) );
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo JText::_( 'Upload a file' ); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"" />
</head>
<body>
<?php
$css = mosGetParam($_REQUEST,"t","");
?>
<link rel="stylesheet" href="../templates/<?php echo $css; ?>/css/template_css.css" type="text/css" />
<form method="post" action="uploadimage.php" enctype="multipart/form-data" name="filename">

<table class="adminform">
<tr>
	<th class="title"> 
		<?php echo JText::_( 'File Upload' ); ?> : <?php echo $directory; ?>
	</th>
</tr>
<tr>
	<td align="center">
		<input class="inputbox" name="userfile" type="file" />
	</td>
</tr>
<tr>
	<td>
		<input class="button" type="submit" value="<?php echo JText::_( 'Upload' ); ?>" name="fileupload" />
		<?php echo JText::_( 'Max size' ); ?> = <?php echo ini_get( 'post_max_size' );?>
	</td>
</tr>
</table>

<input type="hidden" name="directory" value="<?php echo $directory;?>" />
</form>

</body>
</html>
