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
	global $_LANG;

	?>
	<div style="float:<?php echo ($_LANG->rtl()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="<?php echo $link; ?>">
				<?php echo mosAdminMenus::imageCheck( $image, '/administrator/images/', NULL, NULL, $text ); ?>
				<span><?php echo $text; ?></span>
			</a>
		</div>
	</div>
	<?php
}
?>
<div id="cpanel">
	<?php
	$link = 'index2.php?option=com_content&amp;sectionid=0&amp;task=new';
	quickiconButton( $link, 'module.png', $_LANG->_( 'Add New Content' ) );

	$link = 'index2.php?option=com_content&sectionid=0';
	quickiconButton( $link, 'addedit.png', $_LANG->_( 'Content Items Manager' ) );

	$link = 'index2.php?option=com_typedcontent';
	quickiconButton( $link, 'addedit.png', $_LANG->_( 'Static Content Manager' ) );

	$link = 'index2.php?option=com_frontpage';
	quickiconButton( $link, 'frontpage.png', $_LANG->_( 'Frontpage Manager' ) );

	$link = 'index2.php?option=com_sections&amp;scope=content';
	quickiconButton( $link, 'sections.png', $_LANG->_( 'Section Manager' ) );

	$link = 'index2.php?option=com_categories&amp;section=content';
	quickiconButton( $link, 'categories.png', $_LANG->_( 'Category Manager' ) );

	$link = 'index2.php?option=com_media';
	quickiconButton( $link, 'mediamanager.png', $_LANG->_( 'Media Manager' ) );

	if ( $my->gid > 23 ) {
		$link = 'index2.php?option=com_trash';
		quickiconButton( $link, 'trash.png', $_LANG->_( 'Trash Manager' ) );
	}

	if ( $my->gid > 23 ) {
		$link = 'index2.php?option=com_menumanager';
		quickiconButton( $link, 'menu.png', $_LANG->_( 'Menu Manager' ) );
	}

	if ( $my->gid > 24 ) {
		$link = 'index2.php?option=com_languages';
		quickiconButton( $link, 'langmanager.png', $_LANG->_( 'Language Manager' ) );
	}

	if ( $my->gid > 23 ) {
		$link = 'index2.php?option=com_users';
		quickiconButton( $link, 'user.png', $_LANG->_( 'User Manager' ) );
	}

	if ( $my->gid > 24 ) {
		$link = 'index2.php?option=com_config&hidemainmenu=1';
		quickiconButton( $link, 'config.png', $_LANG->_( 'Global Configuration' ) );
	}
	?>
</div>
