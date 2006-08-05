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
class JContactViewContact extends JView
{
	/**
	 * Name of the view.
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Contact';

	/**
	 * Display the document
	 */
	function display()
	{
		$app		=& $this->getApplication();
		$user 		=& JFactory::getUser();

		$Itemid    		= JRequest::getVar('Itemid');

		// Get the paramaters of the active menu item
		$menus   =& JMenu::getInstance();
		$mParams =& $menus->getParams($Itemid);

		// Push a model into the view
		$ctrl		= &$this->getController();
		$model		= & $ctrl->getModel('contact', 'JContactModel');
		$modelCat	= & $ctrl->getModel('category', 'JContactModel');
		//$this->setModel($model, true);

		// Selected Request vars
		$contactId		= JRequest::getVar( 'contact_id', $mParams->get('contact_id', 0 ), '', 'int' );

		// query options
		$qOptions['id']			= $contactId;
		$qOptions['gid']		= $user->get('gid');

		$contact = $model->getContact( $qOptions );

		$qOptions['category_id']	= $contact->catid;
		$qOptions['order by']		= 'a.default_con DESC, a.ordering ASC';

		$contacts = $modelCat->getContacts( $qOptions );

		// check if we have a contact
		if (!is_object( $contact ))
		{
			$mParams->def('back_button', $app->getCfg('back_button'));
			$this->noContact($mParams);
			return;
		}

		// Set the document page title
		$app->setPageTitle(JText::_('Contact').' - '.$contact->name);

		/*
		 * Add the breadcrumbs items
		 * 	- Category item if the parameter is set
		 * 	- Contact item always
		 */
		$breadcrumbs = & $app->getPathWay();
		if (!$mParams->get('hideCatCrumbs')) {
			global $Itemid;
			$breadcrumbs->addItem($contact->category_name, "index.php?option=com_contact&catid=$contact->catid&Itemid=$Itemid");
		}
		$breadcrumbs->addItem($contact->name, '');

		// Adds parameter handling
		$params = new JParameter($contact->params);

		$params->def( 'name', 				1 );
		$params->def( 'email', 				0 );
		$params->def( 'street_address', 	1 );
		$params->def( 'suburb', 			1 );
		$params->def( 'state', 				1 );
		$params->def( 'country', 			1 );
		$params->def( 'postcode', 			1 );
		$params->def( 'telephone', 			1 );
		$params->def( 'fax', 				1 );
		$params->def( 'misc', 				1 );
		$params->def( 'image', 				1 );

		$cParams = &JSiteHelper::getControlParams();
		$template = JRequest::getVar( 'tpl', $cParams->get( 'template_name', 'default' ) );
		$template = preg_replace( '#\W#', '', $template );
		$tmplPath = dirname( __FILE__ ) . '/tmpl/' . $template . '.php';

		if (!file_exists( $tmplPath ))
		{
			$tmplPath = dirname( __FILE__ ) . '/tmpl/default.php';
		}

		require($tmplPath);
	}

	//
	// Helper methods
	//
	/**
	 * Method to output an error condition where there was no contact view
	 * @since 1.5
	 */
	function noContact( &$params )
	{
		?>
		<br />
		<br />
			<?php echo JText::_( 'There are no Contact Details listed.' );?>
		<br />
		<br />
		<?php
	}
}
?>