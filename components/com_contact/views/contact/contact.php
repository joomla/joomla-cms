<?php
/**
 * @version $Id: contact.php 3690 2006-05-27 04:59:14Z eddieajau $
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
		$app		= &$this->getApplication();
		$user 		= & $app->getUser();
		$mParams	= JComponentHelper::getMenuParams();

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

		$cParams = &JComponentHelper::getControlParams();
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