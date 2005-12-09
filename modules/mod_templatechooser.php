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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

global $mainframe;

$cur_template = $mainframe->getTemplate();;

// titlelength can be set in module params
$titlelength 	= $params->get( 'title_length', 20 );
$preview_height = $params->get( 'preview_height', 90 );
$preview_width 	= $params->get( 'preview_width', 140 );
$show_preview 	= $params->get( 'show_preview', 0 );

// Read files from template directory
$template_path 	= JPATH_SITE . "/templates";
$templatefolder = @dir( $template_path );
$darray = array();

if ($templatefolder) {
	while ($templatefile = $templatefolder->read()) {
		if ($templatefile != "." && $templatefile != ".." && $templatefile != ".svn" && $templatefile != "css" && is_dir( "$template_path/$templatefile" )  ) {
			if(strlen($templatefile) > $titlelength) {
				$templatename = substr( $templatefile, 0, $titlelength-3 );
				$templatename .= "...";
			} else {
				$templatename = $templatefile;
			}
			$darray[] = mosHTML::makeOption( $templatefile, $templatename );
		}
	}
	$templatefolder->close();
}

sort( $darray );

// Show the preview image
// Set up JavaScript for instant preview
$onchange = "";
if ($show_preview) {
	$onchange = "showimage()";
?>
<img src="<?php echo "templates/$cur_template/template_thumbnail.png";?>" name="preview" border="1" width="<?php echo $preview_width;?>" height="<?php echo $preview_height;?>" alt="<?php echo $cur_template; ?>" />
<script language='JavaScript1.2' type='text/javascript'>
<!--
	function showimage() {
		//if (!document.images) return;
		document.images.preview.src = 'templates/' + getSelectedValue( 'templateform', 'jos_change_template' ) + '/template_thumbnail.png';
	}
	function getSelectedValue( frmName, srcListName ) {
		var form = eval( 'document.' + frmName );
		var srcList = eval( 'form.' + srcListName );

		i = srcList.selectedIndex;
		if (i != null && i > -1) {
			return srcList.options[i].value;
		} else {
			return null;
		}
	}
-->
</script>
<?php
}
?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" name='templateform' method="post">
	<?php
	echo mosHTML::selectList( $darray, 'jos_change_template', "id=\"mod_templatechooser_jos_change_template\" class=\"button\" onchange=\"$onchange\"",'value', 'text', $cur_template );
	?>
	<input class="button" type="submit" value="<?php echo JText::_( 'Select' );?>" />
</form>
