<?php
/**
 * @version $Id: section.html.php 3393 2006-05-05 23:26:10Z Jinx $
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
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentViewSection extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Section';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		$document	= &$this->getDocument();
		switch ($document->getType())
		{
			case 'feeed':
				$this->displayFeed();
				break;
			default:
				$this->displayHtml();
				break;
		}
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function displayHtml()
	{
		// Initialize some variables
		$app	= &$this->getApplication();
		$user	= &$app->getUser();
		$menus	= JMenu::getInstance();
		$menu	= &$menus->getCurrent();
		$params	= &JComponentHelper::getMenuParams();
		$doc	= & $app->getDocument();
		
		$Itemid = $menu->id;

		$gid 	= $user->get('gid');
		$task 	= JRequest::getVar('task');
		$id 	= JRequest::getVar('id');
		$option = JRequest::getVar('option');

		// Lets get our data from the model
		$section		= & $this->get( 'Section' );
		$categories	= & $this->get( 'Categories' );
		
		//add alternate feed link
		$link    = $app->getBaseURL() .'feed.php?option=com_content&task='.$task.'&id='.$id.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$doc->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$doc->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);

		/*
		 * Lets set the page title
		 */
		if (!empty ($menu->name)) {
			$app->setPageTitle($menu->name);
		}

		/*
		 * Handle BreadCrumbs
		 */
		$breadcrumbs = & $app->getPathWay();
		$breadcrumbs->addItem($section->title, '');

		if ($params->get('page_title')) {
		?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $section->name; ?>
			</div>
		<?php
		}
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
				<?php
				if ($section->image) {
					$link = 'images/stories/'.$section->image;
					?>
					<img src="<?php echo $link;?>" align="<?php echo $section->image_position;?>" hspace="6" alt="<?php echo $section->image;?>" />
					<?php
				}
				echo $section->description;
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php
				// Displays listing of Categories
				if (count($categories) > 0) {
					if ($params->get('other_cat_section')) {
						$this->_buildCategories($categories, $params, $section->id);
					}
				}
				?>
			</td>
		</tr>
		</table>
		<?php
	}

	function _buildCategories( $categories, $params, $sid)
	{
		// Get some variables
		$app		= &$this->getApplication();
		$menus		= JMenu::getInstance();
		$menu		= &$menus->getCurrent();
		$params		= &JComponentHelper::getMenuParams();
		$user		= &$app->getUser();
		$Itemid		= $menu->id;

		if ( count($categories) ) {
			?>
			<ul>
				<?php
				foreach ($categories as $row) {
					?>
					<li>
						<?php
						if ($row->access <= $user->get('gid')) {
							$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
							?>
							<a href="<?php echo $link; ?>" class="category">
								<?php echo $row->name;?></a>
								<?php
								if ($params->get('cat_items')) {
									?>
									&nbsp;<i>( <?php echo $row->numitems ." ". JText::_( 'items' );?> )</i>
									<?php
								}

								// Writes Category Description
								if ($params->get('cat_description') && $row->description) {
									?>
									<br />
									<?php
									echo $row->description;
								}
						} else {
							echo $row->name;
							?>
							<a href="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=register' ); ?>">
								( <?php echo JText::_( 'Registered Users Only' ); ?> )</a>
							<?php
						}
						?>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}
	}
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function displayFeed()
	{
		$app =& $this->getApplication();
		$doc = $app->getDocument();

		//Initialize some variables
		$menus		= JMenu::getInstance();
		$menu		= &$menus->getCurrent();
		$params		= &JComponentHelper::getMenuParams();
		$Itemid		= $menu->id;

		// Lets get our data from the model
		$rows = & $this->get( 'Section' );

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$itemid = $app->getItemid( $row->id );
			if ($itemid) {
				$_Itemid = '&Itemid='. $itemid;
			}

			$link = 'index.php?option=com_content&task=view&id='. $row->id . $_Itemid;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $row->introtext;
			@$date = ( $row->created ? date( 'r', $row->created ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= $row->category;

			// loads item info into rss array
			$doc->addItem( $item );
		}
	}
}
?>
