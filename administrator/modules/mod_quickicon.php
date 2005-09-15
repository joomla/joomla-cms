<?php
/**
* @version $Id: mod_quickicon.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
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
				<?php echo mosAdminHTML::imageCheck( $image, '/administrator/images/', NULL, NULL, $text ); ?>
			</div>
			<?php echo $text; ?>
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

	if ( $acl->acl_check( 'com_menumanager', 'manage', 'users', $my->usertype ) ) {
		$link = 'index2.php?option=com_menumanager';
		quickiconButton( $link, 'menu.png', $_LANG->_( 'Menu Manager' ) );
	}

	if ( $acl->acl_check( 'com_languages', 'manage', 'users', $my->usertype ) ) {
		$link = 'index2.php?option=com_languages';
		quickiconButton( $link, 'langmanager.png', $_LANG->_( 'Language Manager' ) );
	}

	if ( $acl->acl_check( 'com_users', 'manage', 'users', $my->usertype ) ) {
		$link = 'index2.php?option=com_users';
		quickiconButton( $link, 'user.png', $_LANG->_( 'User Manager' ) );
	}

	if ( $acl->acl_check( 'com_config', 'manage', 'users', $my->usertype ) ) {
		$link = 'index2.php?option=com_config';
		quickiconButton( $link, 'config.png', $_LANG->_( 'Global Configuration' ) );
	}
	?>
</div>