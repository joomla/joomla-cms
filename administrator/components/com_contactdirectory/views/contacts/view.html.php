<?php
// ensure a valid entry point
defined( '_JEXEC' ) or die( 'Restricted access' );

// import the JModel class
jimport('joomla.application.component.view');

/**
 * Field View
 */
class ContactdirectoryViewContacts extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$db =& JFactory::getDBO();
		$uri =& JFactory::getURI();
		$user = & JFactory::getUser();
		$model	=& $this->getModel();

		if (!$user->authorize( 'com_contactdirectory', 'manage contacts' )) {
			$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
		}

		$filter_state = $mainframe->getUserStateFromRequest( $option.'filter_state', 'filter_state',	'', 'word' );
		$filter_catid = $mainframe->getUserStateFromRequest( $option.'filter_catid',	 'filter_catid', 0, 'int' );
		$filter_order = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order',	'c.ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir',	'', 'word' );
		$search	 = $mainframe->getUserStateFromRequest( $option.'search', 	'search', '',	 'string' );
		$search	 = JString::strtolower( $search );

		// Get data from the model
		$items = & $this->get( 'Data');
		$total = & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );

		// build list of categories
		$javascript = 'onchange="document.adminForm.submit();"';
		$lists['category'] = JHtml::_('list.category',  'filter_catid', $option, intval( $filter_catid ), $javascript );

		// state filter
		$lists['state']	= JHtml::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;

		$this->assignRef('user', JFactory::getUser());
		$this->assignRef('lists', $lists);
		$this->assignRef('items', $items);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
}
?>