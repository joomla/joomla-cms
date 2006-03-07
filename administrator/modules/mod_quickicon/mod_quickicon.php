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

if (!defined( '_JOS_QUICKICON_MODULE' )) {
	/** ensure that functions are declared only once */
	define( '_JOS_QUICKICON_MODULE', 1 );

	function quickiconButton( $link, $image, $text ) {

		global $mainframe;
		$lang = $mainframe->getLanguage();
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $link; ?>">
					<?php echo mosAdminMenus::imageCheckAdmin( $image, '/images/', NULL, NULL, $text ); ?>
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
		quickiconButton( $link, 'module.png', JText::_( 'Add New Content' ) );

		$link = 'index2.php?option=com_content&amp;sectionid=0';
		quickiconButton( $link, 'addedit.png', JText::_( 'Content Items Manager' ) );

		$link = 'index2.php?option=com_typedcontent';
		quickiconButton( $link, 'addedit.png', JText::_( 'Static Content Manager' ) );

		$link = 'index2.php?option=com_frontpage';
		quickiconButton( $link, 'frontpage.png', JText::_( 'Frontpage Manager' ) );

		$link = 'index2.php?option=com_sections&amp;scope=content';
		quickiconButton( $link, 'sections.png', JText::_( 'Section Manager' ) );

		$link = 'index2.php?option=com_categories&amp;section=content';
		quickiconButton( $link, 'categories.png', JText::_( 'Category Manager' ) );

		$link = 'index2.php?option=com_media';
		quickiconButton( $link, 'mediamanager.png', JText::_( 'Media Manager' ) );

		if ( $my->gid > 23 ) {
			$link = 'index2.php?option=com_trash';
			quickiconButton( $link, 'trash.png', JText::_( 'Trash Manager' ) );
		}

		if ( $my->gid > 23 ) {
			$link = 'index2.php?option=com_menumanager';
			quickiconButton( $link, 'menu.png', JText::_( 'Menu Manager' ) );
		}

		if ( $my->gid > 24 ) {
			$link = 'index2.php?option=com_languages&amp;client=0';
			quickiconButton( $link, 'langmanager.png', JText::_( 'Language Manager' ) );
		}

		if ( $my->gid > 23 ) {
			$link = 'index2.php?option=com_users';
			quickiconButton( $link, 'user.png', JText::_( 'User Manager' ) );
		}

		if ( $my->gid > 24 ) {
			$link = 'index2.php?option=com_config&amp;hidemainmenu=1';
			quickiconButton( $link, 'config.png', JText::_( 'Global Configuration' ) );
		}
		?>
	</div>
	<?php
}
?>