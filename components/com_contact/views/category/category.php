<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.view');

/**
 * @pacakge Joomla
 * @subpackage Contacts
 */
class JContactViewCategory extends JView
{
	/**
	 * Name of the view.
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Category';

	/**
	 * Display the document
	 */
	function display()
	{
		$document	= &$this->getDocument();
		switch ($document->getType())
		{
			case 'feed':
				$this->displayFeed();
				break;
			default:
				$this->displayHtml();
				break;
		}
	}

	/**
	 * Display an HTML document
	 */
	function displayHtml()
	{
		$app		= &$this->getApplication();
		$user 		= & $app->getUser();

		// Push a model into the view
		$ctrl	= &$this->getController();
		$model	= & $ctrl->getModel('category', 'JContactModel');
		$this->setModel($model, true);

		$Itemid   = JRequest::getVar('Itemid');

		// Get the paramaters of the active menu item
		$menus   =& JMenu::getInstance();
		$mParams =& $menus->getParams($Itemid);

		// Selected Request vars
		$categoryId			= JRequest::getVar( 'catid', $mParams->get('category_id', 0 ), '', 'int' );
		$limit				= JRequest::getVar('limit', $mParams->get('display_num'), '', 'int');
		$limitstart			= JRequest::getVar('limitstart', 0, '', 'int');
		$filter_order		= JRequest::getVar('filter_order', 		'cd.ordering');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir', 	'ASC');

		// query options
		$qOptions['gid']			= $user->get('gid');

		$categories = $model->getCategories( $qOptions );

		$qOptions['category_id']	= $categoryId;
		$qOptions['limit']			= $limit;
		$qOptions['limitstart']		= $limitstart;
		$qOptions['order by']		= "$filter_order $filter_order_Dir, cd.ordering";

		$contacts = $model->getContacts( $qOptions );
		$contactCount = $model->getContactCount( $qOptions );

		// find current category
		// TODO: Move to model
		$currentCategory = null;
		foreach ($categories as $i => $_cat)
		{
			if ($_cat->id == $categoryId)
			{
				$currentCategory = &$categories[$i];
				break;
			}
		}
		if ($currentCategory == null)
		{
			$db = &JFactory::getDBO();
			$currentCategory = JTable::getInstance( 'category', $db );
		}

		// Set the page title and breadcrumbs
		$breadcrumbs = & $app->getPathWay();

		if ($currentCategory->name)
		{
			// Add the category breadcrumbs item
			$breadcrumbs->addItem($currentCategory->name, '');
			$app->setPageTitle(JText::_('Contact').' - '.$currentCategory->name);
		}
		else
		{
			$app->SetPageTitle(JText::_('Contact'));
		}

		$cParams = &JSiteHelper::getControlParams();
		$template = JRequest::getVar( 'tpl', $cParams->get( 'template_name', 'table' ) );
		$template = preg_replace( '#\W#', '', $template );
		$tmplPath = dirname( __FILE__ ) . '/tmpl/' . $template . '.php';

		if (!file_exists( $tmplPath ))
		{
			$tmplPath = dirname( __FILE__ ) . '/tmpl/table.php';
		}

		require($tmplPath);
	}

	function displayFeed()
	{
		global $mainframe;

		$db		  =& $mainframe->getDBO();
		$document =& $mainframe->getDocument();

		$limit 			= JRequest::getVar('limit', 0, '', 'int');
		$limitstart 	= JRequest::getVar('limitstart', 0, '', 'int');
		$catid  		= JRequest::getVar('catid', 0);

		$where  = "\n WHERE a.published = 1";

		if ( $catid ) {
			$where .= "\n AND a.catid = $catid";
		}

    	$query = "SELECT"
    	. "\n a.name AS title,"
    	. "\n CONCAT( '$link', a.catid, '&id=', a.id ) AS link,"
    	. "\n CONCAT( a.con_position, ' - ',a.misc ) AS description,"
    	. "\n '' AS date,"
		. "\n c.title AS category,"
    	. "\n a.id AS id"
    	. "\n FROM #__contact_details AS a"
		. "\n LEFT JOIN #__categories AS c ON c.id = a.catid"
    	. $where
    	. "\n ORDER BY a.catid, a.ordering"
    	;
		$db->setQuery( $query, 0, $limit );
    	$rows = $db->loadObjectList();

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$itemid = JApplicationHelper::getItemid( $row->id );
			if ($itemid) {
				$_Itemid = '&Itemid='. $itemid;
			}

			$link = 'index.php?option=com_contact&task=view&id='. $row->id . '&catid='.$row->catid.$_Itemid;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $row->description;
			$date = ( $row->date ? date( 'r', $row->date ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= $row->category;

			// loads item info into rss array
			$document->addItem( $item );
		}

	}

	//
	// Helper functions
	//

	/**
	 * Method to output a contact categories view
	 *
	 * @since 1.0
	 */
	function showCategories( &$params, &$categories, $catid )
	{
		global $Itemid;
		?>
		<ul>
		<?php
		foreach ( $categories as $cat ) {
			if ( $catid == $cat->catid ) {
				?>
				<li>
					<b>
					<?php echo $cat->title;?>
					</b>
					&nbsp;
					<span class="small<?php echo $params->get( 'pageclass_sfx' ); ?>">
					(<?php echo $cat->numlinks;?>)
					</span>
				</li>
				<?php
			} else {
				$link = 'index.php?option=com_contact&amp;catid='. $cat->catid .'&amp;Itemid='. $Itemid;
				?>
				<li>
					<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo $cat->title;?></a>
					<?php
					if ( $params->get( 'cat_items' ) ) {
						?>
						&nbsp;
						<span class="small<?php echo $params->get( 'pageclass_sfx' ); ?>">
							(<?php echo $cat->numlinks;?>)
						</span>
						<?php
					}
					?>
					<?php
					// Writes Category Description
					if ( $params->get( 'cat_description' ) ) {
						echo '<br />';
						echo $cat->description;
					}
					?>
				</li>
				<?php
			}
		}
		?>
		</ul>
		<?php
	}
}

?>