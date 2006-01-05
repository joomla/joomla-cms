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

$text 				= $params->get( 'text', 			'');
$moduleclass_sfx 	= $params->get( 'moduleclass_sfx', 	'' );
$rss091  			= $params->get( 'rss091', 			1 );
$rss10  			= $params->get( 'rss10', 			1 );
$rss20  			= $params->get( 'rss20', 			1 );
$atom  				= $params->get( 'atom', 			1 );
$opml  				= $params->get( 'opml', 			1 );
$rss091_image		= $params->get( 'rss091_image', 	'' );
$rss10_image		= $params->get( 'rss10_image', 		'' );
$rss20_image		= $params->get( 'rss20_image', 		'' );
$atom_image			= $params->get( 'atom_image', 		'' );
$opml_image			= $params->get( 'opml_image', 		'' );

$cur_template 		= $mainframe->getTemplate();
$t_path 			= JURL_SITE .'/templates/'. $cur_template .'/images/';
$d_path				= JURL_SITE .'/images/M_images/';
?>
<div class="syndicate<?php echo $moduleclass_sfx;?>">
	<?php
	// text
	if ( $text ) {
		?>
		<div align="center" class="syndicate_text<?php echo $moduleclass_sfx;?>">
			<?php echo $text;?>
		</div>
		<?php
	}

	// rss091 link
	if ( $rss091 ) {
		$link = 'index.php?option=com_rss&amp;feed=RSS0.91&amp;no_html=1';
		output_rssfeed( $link, 'rss091.gif', $rss091_image, 'RSS 0.91' );
	}

	// rss10 link
	if ( $rss10 ) {
		$link = 'index.php?option=com_rss&amp;feed=RSS1.0&amp;no_html=1';
		output_rssfeed( $link, 'rss10.gif', $rss10_image, 'RSS 1.0' );
	}
	
	// rss20 link
	if ( $rss20 ) {
		$link = 'index.php?option=com_rss&amp;feed=RSS2.0&amp;no_html=1';
		output_rssfeed( $link, 'rss20.gif', $rss20_image, 'RSS 2.0' );
	}

	// atom link
	if ( $atom ) {
		$link = 'index.php?option=com_rss&amp;feed=ATOM0.3&amp;no_html=1';
		output_rssfeed( $link, 'atom03.gif', $atom_image, 'ATOM 0.3' );
	}
	
	// opml link
	if ( $opml ) {
		$link = 'index.php?option=com_rss&amp;feed=OPML&amp;no_html=1';
		output_rssfeed( $link, 'opml.png', $opml_image, 'OPML' );
	}
	?>
</div>

<?php
function output_rssfeed( $link, $img_default, $img_file, $img_alt ) {	
	$img = mosAdminMenus::ImageCheck( $img_default, '/images/M_images/', $img_file, '/images/M_images/', $img_alt, $img_alt );
	?>
	<div align="center">
		<a href="<?php echo sefRelToAbs( $link ); ?>">
			<?php echo $img ?></a>
	</div>
	<?php
}
?>