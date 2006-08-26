<?php
/**
 * @version $Id: view.php 4695 2006-08-23 22:39:28Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * Content Section View Common Layout Methods
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentSectionViewCommon
{
	/**
	 * Helper method to print the content item's title block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @param string $linkOn 	Menu link for the content item
	 * @param object $access 	Access object for the content item
	 * @return void
	 * @since 1.0
	 */
	function title($row, $params, $linkOn, $access)
	{
		if ($params->get('item_title')) {
			?>
			<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" width="100%">
				<?php
				if ($params->get('link_titles') && $linkOn != '') {
					?>
					<a href="<?php echo $linkOn;?>" class="contentpagetitle<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo $row->title;?></a>
					<?php
				} else {
					echo $row->title;
				}
				?>
			</td>
			<?php
		}
	}

	/**
	 * Helper method to print the edit icon for the content item if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $item 		The content item
	 * @param object $params 	The content item's parameters object
	 * @param object $access 	Access object for the content item
	 * @return void
	 * @since 1.0
	 */
	function editIcon($item, $params, $access)
	{
		global $Itemid, $mainframe;

		$user     =& JFactory::getUser();
		$document =& JFactory::getDocument();

		if ($params->get('popup')) {
			return;
		}
		if ($item->state < 0) {
			return;
		}
		if (!$access->canEdit && !($access->canEditOwn && $item->created_by == $user->get('id'))) {
			return;
		}

		mosCommonHTML::loadOverlib();

		$link = 'index.php?option=com_content&amp;task=edit&amp;id='.$item->id.'&amp;Itemid='.$Itemid.'&amp;Returnid='.$Itemid;
		$image = mosAdminMenus::ImageCheck('edit.png', '/images/M_images/', NULL, NULL, JText::_('Edit'), JText::_('Edit'). $item->id );

		if ($item->state == 0) {
			$overlib = JText::_('Unpublished');
		} else {
			$overlib = JText::_('Published');
		}
		$date = mosFormatDate($item->created);
		$author = $item->created_by_alias ? $item->created_by_alias : $item->author;

		$overlib .= '<br />';
		$overlib .= $item->groups;
		$overlib .= '<br />';
		$overlib .= $date;
		$overlib .= '<br />';
		$overlib .= $author;
		?>
		<a href="<?php echo $link ?>" onmouseover="return overlib('<?php echo $overlib; ?>', CAPTION, '<?php echo JText::_( 'Edit Item' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
			<?php echo $image; ?></a>
		<?php
	}

	/**
	 * Helper method to print the new icon for the content item if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $item 		The content item
	 * @param object $params 	The content item's parameters object
	 * @param object $access 	Access object for the content item
	 * @return void
	 * @since 1.0
	 */
	function newIcon($item, $params, $access)
	{
		global $Itemid, $mainframe;

		$user     =& JFactory::getUser();
		$document =& JFactory::getDocument();

		$document->addScript('components/com_content/theme/js/common.js');
		$document->addScript('components/com_content/theme/js/subModal.js');

		$document->addStyleSheet('components/com_content/theme/css/subModal.css');

		$link = 'index2.php?option=com_content&amp;task=new&amp;sectionid='.$item->sectionid.'&amp;Itemid='.$Itemid;
		$image = mosAdminMenus::ImageCheck('new.png', '/images/M_images/', NULL, NULL, JText::_('New'), JText::_('New'). $item->id );

		?>
		<a href="javascript:document.popup.show('<?php echo $link ?>', 700, 500, null);">
			<?php echo $image; ?>
			&nbsp;<?php echo JText::_( 'New' );?>...
		</a>
		<?php
	}

	/**
	 * Helper method to print the content item's pdf icon if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object 	$row 	The content item
	 * @param object 	$params The content item's parameters object
	 * @param string 	$linkOn Menu link for the content item
	 * @param boolean 	$hideJS True to hide the javascript
	 * @return void
	 * @since 1.0
	 */
	function pdfIcon($row, $params, $linkOn, $hideJS)
	{
		if ($params->get('pdf') && !$params->get('popup') && !$hideJS)
		{
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			$link = 'index2.php?option=com_content&amp;view=article&amp;id='.$row->id.'&amp;format=pdf';
			if ($params->get('icons')) {
				$image = mosAdminMenus::ImageCheck('pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
			} else {
				$image = JText::_('PDF').'&nbsp;';
			}
			?>
			<td align="right" width="100%" class="buttonheading">
				<a href="<?php echo $link; ?>" onclick="window.open('<?php echo $link; ?>','win2','<?php echo $status; ?>'); return false;" title="<?php echo JText::_( 'PDF' );?>">
					<?php echo $image; ?></a>
			</td>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's email icon if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object 	$row 	The content item
	 * @param object 	$params The content item's parameters object
	 * @param boolean 	$hideJS True to hide javascript code
	 * @return void
	 * @since 1.0
	 */
	function emailIcon($row, $params, $hideJS)
	{
		if ($params->get('email') && !$params->get('popup') && !$hideJS) {
			if ($params->get('icons')) 	{
				$image = mosAdminMenus::ImageCheck('emailButton.png', '/images/M_images/', NULL, NULL, JText::_('Email'), JText::_('Email'));
			} else {
				$image = '&nbsp;'.JText::_('Email');
			}
			?>
			<td align="right" width="100%" class="buttonheading">
				<script language="JavaScript" type="text/javascript">
				<!--
				function mailToFriend() {
				  if (document.mailToFriend) {
				    window.open('about:blank',
				      'MailToFriend',
				      'width=400,height=300,menubar=yes,resizable=yes');
				    document.mailToFriend.submit();
				  }
				}
				-->
				</script>
				<form action="index2.php" name="mailToFriend" method="post" target="MailToFriend" style="display:inline">
				  <input type="hidden" name="option" value="com_mailto" />
				  <input type="hidden" name="link" value="<?php echo urlencode( JRequest::getUrl());?>" />
				</form>

				<a href="javascript:void mailToFriend()" title="<?php echo JText::_( 'Email' );?>">
					<?php echo $image; ?></a>
			</td>
			<?php
		}
	}

	/**
	 * Helper method to print a container for a category and section blocks
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The item to display
	 * @param object $params 	The item to display's parameters object
	 * @return void
	 * @since 1.0
	 */
	function sectionCategory($row, $params)
	{
		if (($params->get('section') && $row->sectionid) || ($params->get('category') && $row->catid)) {
			?>
			<tr>
				<td>
				<?php
		}

		// displays Section Name
		JContentHTMLHelper::section($row, $params);

		// displays Section Name
		JContentHTMLHelper::category($row, $params);

		if (($params->get('section') && $row->sectionid) || ($params->get('category') && $row->catid)) {
				?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the section block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The section item
	 * @param object $params 	The section item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function section($row, $params)
	{
		if ($params->get('section') && $row->sectionid) {
			?>
			<span>
				<?php
				echo $row->section;
				// writes dash between section & Category Name when both are active
				if ($params->get('category')) {
					echo ' - ';
				}
				?>
			</span>
			<?php
		}
	}

	/**
	 * Helper method to print the category block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The category item
	 * @param object $params 	The category's parameters object
	 * @return void
	 * @since 1.0
	 */
	function category($row, $params)
	{
		if ($params->get('category') && $row->catid) {
			?>
			<span>
				<?php
				echo $row->category;
				?>
			</span>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's author block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function author($row, $params)
	{
		if (($params->get('author')) && ($row->author != "")) {
			?>
			<tr>
				<td width="70%"  valign="top" colspan="2">
					<span class="small">
					<?php JText::printf( 'Written by', ($row->created_by_alias ? $row->created_by_alias : $row->author) ); ?>
					</span>
					&nbsp;&nbsp;
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's URL block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function url($row, $params)
	{
		if ($params->get('url') && $row->urls) 	{
			?>
			<tr>
				<td valign="top" colspan="2">
					<a href="http://<?php echo $row->urls ; ?>" target="_blank">
						<?php echo $row->urls; ?></a>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's created date block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function createDate($row, $params)
	{
		$create_date = null;
		if (intval($row->created) != 0) {
			$create_date = mosFormatDate($row->created);
		}
		if ($params->get('createdate')) {
			?>
			<tr>
				<td valign="top" colspan="2" class="createdate">
					<?php echo $create_date; ?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's modified date block if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param object $params 	The content item's parameters object
	 * @return void
	 * @since 1.0
	 */
	function modifiedDate($row, $params)
	{
		$mod_date = null;
		if (intval($row->modified) != 0) {
			$mod_date = mosFormatDate($row->modified);
		}
		if (($mod_date != '') && $params->get('modifydate')) {
			?>
			<tr>
				<td colspan="2"  class="modifydate">
					<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $mod_date; ?> )
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Helper method to print the content item's table of contents block if
	 * present.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row The content item
	 * @return void
	 * @since 1.0
	 */
	function toc($row)
	{
		if (isset ($row->toc)) {
			echo $row->toc;
		}
	}

	/**
	 * Helper method to print the content item's read more button if enabled.
	 *
	 * This method will be deprecated with full patTemplate integration in
	 * Joomla 1.2
	 *
	 * @static
	 * @param object $row 		The content item
	 * @param string $linkOn 	Button link for the read more button
	 * @param string $linkText 	Text for read more button
	 * @return void
	 * @since 1.0
	 */
	function readMore($params, $linkOn, $linkText)
	{
		if ($params->get('readmore')) {
			if ($params->get('intro_only') && $linkText) {
				?>
				<tr>
					<td  colspan="2">
						<a href="<?php echo $linkOn;?>" class="readon<?php echo $params->get( 'pageclass_sfx' ); ?>">
							<?php echo $linkText;?></a>
					</td>
				</tr>
				<?php
			}
		}
	}
}
?>
