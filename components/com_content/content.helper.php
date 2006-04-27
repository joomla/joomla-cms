<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Content Component Helper
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentHelper
{
	function saveContentPrep(& $row)
	{
		/*
		 * Get submitted text from the request variables
		 */
		$text = JRequest::getVar('text', '', 'post', 'string', _J_ALLOWRAW);

		/*
		 * Clean text for xhtml transitional compliance
		 */
		$text = str_replace('<br>', '<br />', $text);
		$row->title = ampReplace($row->title);

		/*
		 * Now we need to search for the {readmore} tag and split the text up
		 * accordingly.
		 */
		$tagPos = JString::strpos($text, '{readmore}');

		if ($tagPos === false)	{
			$row->introtext = $text;
		} else 	{
			$row->introtext = JString::substr($text, 0, $tagPos);
			$row->fulltext = JString::substr($text, $tagPos +10);
		}

		return true;
	}

	function orderbyPrimary($orderby)
	{
		switch ($orderby)
		{
			case 'alpha' :
				$orderby = 'cc.title, ';
				break;

			case 'ralpha' :
				$orderby = 'cc.title DESC, ';
				break;

			case 'order' :
				$orderby = 'cc.ordering, ';
				break;

			default :
				$orderby = '';
				break;
		}

		return $orderby;
	}

	function orderbySecondary($orderby)
	{
		switch ($orderby)
		{
			case 'date' :
				$orderby = 'a.created';
				break;

			case 'rdate' :
				$orderby = 'a.created DESC';
				break;

			case 'alpha' :
				$orderby = 'a.title';
				break;

			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;

			case 'hits' :
				$orderby = 'a.hits';
				break;

			case 'rhits' :
				$orderby = 'a.hits DESC';
				break;

			case 'order' :
				$orderby = 'a.ordering';
				break;

			case 'author' :
				$orderby = 'a.created_by_alias, u.name';
				break;

			case 'rauthor' :
				$orderby = 'a.created_by_alias DESC, u.name DESC';
				break;

			case 'front' :
				$orderby = 'f.ordering';
				break;

			default :
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}

	/*
	* @param int 0 = Archives, 1 = Section, 2 = Category
	*/
	function buildWhere($type = 1, & $access, & $noauth, $gid, $id, $now = NULL, $year = NULL, $month = NULL)
	{
		global $database, $mainframe;

		$noauth = !$mainframe->getCfg('shownoauth');
		$nullDate = $database->getNullDate();
		$where = array ();

		// normal
		if ($type > 0) {
			$where[] = "a.state = 1";
			if (!$access->canEdit) {
				$where[] = "( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )";
				$where[] = "( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
			}
			if ($id > 0) {
				if ($type == 1) {
					$where[] = "a.sectionid IN ( $id ) ";
				} else
					if ($type == 2) {
						$where[] = "a.catid IN ( $id ) ";
					}
			}
		}

		// archive
		if ($type < 0)
		{
			$where[] = "a.state='-1'";
			if ($year) {
				$where[] = "YEAR( a.created ) = '$year'";
			}
			if ($month) {
				$where[] = "MONTH( a.created ) = '$month'";
			}
			if ($id > 0) {
				if ($type == -1) {
					$where[] = "a.sectionid = $id";
				} else
					if ($type == -2) {
						$where[] = "a.catid = $id";
					}
			}
		}

		if ($id == 0) {
			$where[] = "s.published = 1";
			$where[] = "cc.published = 1";
			if ($noauth) {
				$where[] = "a.access <= $gid";
				$where[] = "s.access <= $gid";
				$where[] = "cc.access <= $gid";
			}
		}

		return $where;
	}

	function buildVotingQuery()
	{
		global $mainframe;

		$voting = $mainframe->getCfg('vote');

		if ($voting) {
			// calculate voting count
			$select = "\n , ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count";
			$join = "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id";
		} else {
			$select = '';
			$join = '';
		}

		$results = array ('select' => $select, 'join' => $join);

		return $results;
	}

	function getSectionLink(& $row)
	{
		static $links;

		if (!isset ($links)) {
			$links = array ();
		}

		if (empty ($links[$row->sectionid])) {
			$query = "SELECT id, link" .
					"\n FROM #__menu" .
					"\n WHERE published = 1" .
					"\n AND (type = 'content_section' OR type = 'content_blog_section' )" .
					"\n AND componentid = $row->sectionid" .
					"\n ORDER BY type DESC, ordering";
			$database->setQuery($query);
			//$secLinkID = $database->loadResult();
			$result = $database->loadRow();

			$secLinkID = $result[0];
			$secLinkURL = $result[1];

			/*
			 * Did we find an Itemid for the section?
			 */
			$Itemid = null;
			if ($secLinkID)	{
				$Itemid = '&amp;Itemid='.(int) $secLinkID;

				if ($secLinkURL) {
					$link = sefRelToAbs($secLinkURL.$Itemid);
				} else {
					$link = sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$row->sectionid.$Itemid);
				}
				/*
				 * We found one.. and built the link, so lets set it
				 */
				$links[$row->sectionid] = '<a href="'.$link.'">'.$row->section.'</a>';
			} else {
				/*
				 * Didn't find an Itemid.. set the section name as the link
				 */
				$links[$row->sectionid] = $row->section;
			}
		}

		return $links[$row->sectionid];
	}

	function getCategoryLink(& $row)
	{
		static $links;

		if (!isset ($links)) {
			$links = array ();
		}

		if (empty ($links[$row->catid])) {

			$query = "SELECT id, link" .
					"\n FROM #__menu" .
					"\n WHERE published = 1" .
					"\n AND (type = 'content_category' OR type = 'content_blog_category' )" .
					"\n AND componentid = $row->catid" .
					"\n ORDER BY type DESC, ordering";
			$database->setQuery($query);
			$result = $database->loadRow();

			$catLinkID = $result[0];
			$catLinkURL = $result[1];

			/*
			 * Did we find an Itemid for the category?
			 */
			$Itemid = null;
			if ($catLinkID) 	{
				$Itemid = '&amp;Itemid='.(int) $catLinkID;
			} else {
				/*
				 * Nope, lets try to find it by section...
				 */
				$query = "SELECT id, link" .
						"\n FROM #__menu" .
						"\n WHERE published = 1" .
						"\n AND (type = 'content_section' OR type = 'content_blog_section' )" .
						"\n AND componentid = $row->sectionid" .
						"\n ORDER BY type DESC, ordering";
				$database->setQuery($query);
				$secLinkID = $database->loadResult();

				/*
				 * Find it by section?
				 */
				if ($secLinkID)	{
					$Itemid = '&amp;Itemid='.$secLinkID;
				}
			}

			if ($Itemid !== null) {
				if ($catLinkURL) {
					$link = sefRelToAbs($catLinkURL.$Itemid);
				} else {
					$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$row->sectionid.'&amp;id='.$row->catid.$Itemid);
				}
				/*
				 * We found an Itemid... build the link
				 */
				$links[$row->catid] = '<a href="'.$link.'">'.$row->category.'</a>';
			} else {
				/*
				 * Didn't find an Itemid.. set the section name as the link
				 */
				$links[$row->catid] = $row->category;
			}
		}

		return $links[$row->catid];
	}

	function getItemid($id)
	{

		$cache = & JFactory::getCache();
		$Itemid = $cache->get( md5($id), 'getItemid' );

		if ($Itemid === false)
		{
			global $mainframe;

			$db = & $mainframe->getDBO();
			$menu = JMenu::getInstance();
			$items = $menu->getMenu();
			$Itemid = null;

			if (count($items))
			{
				/*
				 * Do we have a content item linked to the menu with this id?
				 */
				foreach ($items as $item) {
					if ($item->link == "index.php?option=com_content&task=view&id=$id") {
						$cache->save( $item->id, md5($id), 'getItemid' );
						return $item->id;
					}
				}

				/*
				 * Not a content item, so perhaps is it in a section that is linked
				 * to the menu?
				 */
				$query = "SELECT m.id " .
						"\n FROM #__content AS i" .
						"\n LEFT JOIN #__sections AS s ON i.sectionid = s.id" .
						"\n LEFT JOIN #__menu AS m ON m.componentid = s.id " .
						"\n WHERE (m.type = 'content_section' OR m.type = 'content_blog_section')" .
						"\n AND m.published = 1" .
						"\n AND i.id = $id";
				$db->setQuery($query);
				$Itemid = $db->loadResult();
				if ($Itemid != '') {
					$cache->save( $Itemid, md5($id), 'getItemid' );
					return $Itemid;
				}

				/*
				 * Not a section either... is it in a category that is linked to the
				 * menu?
				 */
				$query = "SELECT m.id " .
						"\n FROM #__content AS i" .
						"\n LEFT JOIN #__categories AS c ON i.catid = c.id" .
						"\n LEFT JOIN #__menu AS m ON m.componentid = c.id " .
						"\n WHERE (m.type = 'content_blog_category' OR m.type = 'content_category')" .
						"\n AND m.published = 1" .
						"\n AND i.id = $id";
				$db->setQuery($query);
				$Itemid = $db->loadResult();
				if ($Itemid != '') {
					$cache->save( $Itemid, md5($id), 'getItemid' );
					return $Itemid;
				}

				/*
				 * Once we have exhausted all our options for finding the Itemid in
				 * the content structure, lets see if maybe we have a global blog
				 * section in the menu we can put it under.
				 */
				foreach ($items as $item)
				{
					if ($item->type == "content_blog_section" && $item->componentid == "0") {
						$cache->save( $item->id, md5($id), 'getItemid' );
						return $item->id;
					}
				}
			}

			if ($Itemid != '') {
				$cache->save( $Itemid, md5($id), 'getItemid' );
				return $Itemid;
			} else {
				return JRequest::getVar('Itemid', 9999, '', 'int');
			}
		}
		return $Itemid;
	}
}

class JContentHTMLHelper {

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

		$user     =& $mainframe->getUser();
		$document =& $mainframe->getDocument();

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

		$document->addScript('includes/js/joomla/popup.js');
		$document->addStyleSheet('includes/js/joomla/popup.css');

		$link = 'index2.php?option=com_content&amp;task=edit&amp;id='.$item->id.'&amp;Itemid='.$Itemid.'&amp;Returnid='.$Itemid;
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
		<a href="javascript:document.popup.show('<?php echo $link ?>', 700, 500, null);" onmouseover="return overlib('<?php echo $overlib; ?>', CAPTION, '<?php echo JText::_( 'Edit Item' ); ?>', BELOW, RIGHT);" onmouseout="return nd();">
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

		$user     =& $mainframe->getUser();
		$document =& $mainframe->getDocument();

		$document->addScript('components/com_content/theme/js/common.js');
		$document->addScript('components/com_content/theme/js/subModal.js');

		$document->addStyleSheet('components/com_content/theme/css/subModal.css');

		$link = 'index2.php?option=com_content&amp;task=new&amp;sectionid='.$item->sectionid.'&amp;Itemid='.$Itemid;
		$image = mosAdminMenus::ImageCheck('new.png', '/images/M_images/', NULL, NULL, JText::_('New'), JText::_('New'). $item->id );

		?>
		<a href="javascript:showPopWin('<?php echo $link ?>', 700, 500, null);">
			<?php echo $image ?>
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
			$link = 'index2.php?option=com_content&amp;id='.$row->id.'&amp;type=pdf';
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
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=400,height=250,directories=no,location=no';
			$link = 'index2.php?option=com_content&amp;task=emailform&amp;id='.$row->id;
			$acclink = 'index.php?option=com_content&amp;task=emailform&amp;id='.$row->id;
			if ($params->get('icons')) 	{
				$image = mosAdminMenus::ImageCheck('emailButton.png', '/images/M_images/', NULL, NULL, JText::_('Email'), JText::_('Email'));
			} else {
				$image = '&nbsp;'.JText::_('Email');
			}
			?>
			<td align="right" width="100%" class="buttonheading">
				<a href="<?php echo $acclink; ?>" onclick="window.open('<?php echo $link; ?>','win2','<?php echo $status; ?>'); return false;" title="<?php echo JText::_( 'Email' );?>">
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
					&nbsp;<?php JText::printf( 'Written by', ($row->created_by_alias ? $row->created_by_alias : $row->author) ); ?>
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
