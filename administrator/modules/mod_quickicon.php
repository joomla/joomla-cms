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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

function quickiconButton( $link, $image, $text ) {
	?>
	<div style="float:left;">
		<div class="icon">
			<a href="<?php echo $link; ?>">
				<div class="iconimage">
					<?php echo mosAdminMenus::imageCheck( $image, '/administrator/images/', NULL, NULL, $text ); ?>
				</div>
				<?php echo $text; ?></a>
		</div>
	</div>
	<?php
}
?>
<div id="cpanel">
	<?php
	$link = 'index2.php?option=com_content&amp;sectionid=0&amp;task=new';
	quickiconButton( $link, 'module.png', 'Add New Content' );

	$link = 'index2.php?option=com_content&sectionid=0';
	quickiconButton( $link, 'addedit.png', 'Content Items Manager' );

	$link = 'index2.php?option=com_typedcontent';
	quickiconButton( $link, 'addedit.png', 'Static Content Manager' );

	$link = 'index2.php?option=com_frontpage';
	quickiconButton( $link, 'frontpage.png', 'Frontpage Manager' );

	$link = 'index2.php?option=com_sections&amp;scope=content';
	quickiconButton( $link, 'sections.png', 'Section Manager' );

	$link = 'index2.php?option=com_categories&amp;section=content';
	quickiconButton( $link, 'categories.png', 'Category Manager' );

	$link = 'index2.php?option=com_media';
	quickiconButton( $link, 'mediamanager.png', 'Media Manager' );

	if ( $my->gid > 23 ) {
		$link = 'index2.php?option=com_trash';
		quickiconButton( $link, 'trash.png', 'Trash Manager' );
	}

	if ( $my->gid > 23 ) {
		$link = 'index2.php?option=com_menumanager';
		quickiconButton( $link, 'menu.png', 'Menu Manager' );
	}

	if ( $my->gid > 24 ) {
		$link = 'index2.php?option=com_languages';
		quickiconButton( $link, 'langmanager.png', 'Language Manager' );
	}

	if ( $my->gid > 23 ) {
		$link = 'index2.php?option=com_users';
		quickiconButton( $link, 'user.png', 'User Manager' );
	}

	if ( $my->gid > 24 ) {
		$link = 'index2.php?option=com_config&hidemainmenu=1';
		quickiconButton( $link, 'config.png', 'Global Configuration' );
	}
	?>
</div>