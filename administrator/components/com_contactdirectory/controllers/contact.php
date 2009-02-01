<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// ensure a valid entry point
defined('_JEXEC') or die('Restricted access');

// import the JModel class
jimport('joomla.application.component.controller');

/**
 * Contact Directory Contact Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	ContactDirectory
 * @since		1.6
 */
class ContactdirectoryControllerContact extends JController
{
	/**
	 * Display the list of contacts
	 */
	public function display()
	{
		JRequest::setVar('view', 'contacts');
		parent::display();
	}

	public function add()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view'  , 'contact');
		JRequest::setVar('edit', false);

		// Checkout the contact
		$model = $this->getModel('contact');
		$model->checkout();

		parent::display();
	}

	public function edit()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view'  , 'contact');
		JRequest::setVar('edit', true);

		// Checkout the contact
		$model = $this->getModel('contact');
		$model->checkout();

		parent::display();
	}

	public function importView()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view'  , 'import');

		parent::display();
	}

	public function apply()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post	= JRequest::get('post');
		$cid	= JRequest::getVar('cid', array(0), 'post', 'array');
		$post['id'] = (int) $cid[0];

		$model =& $this->getModel('contact');

		if ($id = $model->store($post)) {
			$msg = JText::_('CONTACT_SAVED');
		} else {
			$msg = $model->getError();//JText::_('ERROR_SAVING_CONTACT');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$link = 'index.php?option=com_contactdirectory&controller=contact&task=edit&cid[]='. $id;
		$this->setRedirect($link, $msg);
	}

	public function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post	= JRequest::get('post');
		$cid	= JRequest::getVar('cid', array(0), 'post', 'array');
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('contact');

		if ($model->store($post)) {
			$msg = JText::_('CONTACT_SAVED');
		} else {
			$model->getError();//$msg = JText::_('ERROR_SAVING_CONTACT');
		}
		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$link = 'index.php?option=com_contactdirectory&controller=contact';
		$this->setRedirect($link, $msg);
	}

	public function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('SELECT_ITEM_DELETE'));
		}

		$model = $this->getModel('contact');
		if (!$model->delete($cid)) {
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $msg);
	}


	public function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('SELECT_ITEM_PUBLISH'));
		}

		$model = $this->getModel('contact');
		if (!$model->publish($cid, 1)) {
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $msg);
	}

	public function unpublish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('SELECT_ITEM_UNPUBLISH'));
		}

		$model = $this->getModel('contact');
		if (!$model->publish($cid, 0)) {
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $msg);
	}

	public function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Checkin the contact
		$model = $this->getModel('contact');
		$model->checkin();

		$this->setRedirect('index.php?option=com_contactdirectory&controller=contact');
	}

	/**
	* Save the item(s) to the menu selected
	*/
	public function accesspublic()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get some variables from the request
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('Contact');
		if (!$model->setAccess($cid, 0)) {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	public function accessregistered()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get some variables from the request
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('Contact');
		if (!$model->setAccess($cid, 1)) {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	public function accessspecial()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get some variables from the request
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('Contact');
		if (!$model->setAccess($cid, 2)) {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $msg);
	}

	public function import()
	{
		$string = JRequest::getVar('importString', null, 'post', 'string', JREQUEST_ALLOWHTML);
		$file	= JRequest::getVar('importFile', array(), 'files', 'array');

		$model = $this->getModel('contact');

		if (!empty($string)){
			// import the contacts from the text editor
			if (!$model->import($string)){
				$message = $model->getError();
				//$message = JText::_('CONTACTS_IMPORT_FAILED');
			}else{
				$message = JText::_('CONTACTS_IMPORT_SUCCESS');
			}
			$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $message);
		}elseif (!empty($file) && $file['error'] == 0 && $file['size'] > 0 && is_readable($file['tmp_name'])){
			// import the contacts from a file
			if ($file['type'] = 'text/csv' || $file['type'] = 'text/comma-separated-values'){
				$string = implode("", file($file['tmp_name']));
				if (!$model->import($string)) {
					$message = $model->getError();
					//$message = JText::_('CONTACTS_IMPORT_FAILED');
				} else {
					$message = JText::_('CONTACTS_IMPORT_SUCCESS');
				}
				$this->setRedirect('index.php?option=com_contactdirectory&controller=contact', $message);
			}else{
				$message = JText::_('WRONG_FILE_TYPE');
				$this->setRedirect('index.php?option=com_contactdirectory&controller=contact&task=importView', $message);
			}
		}
	}
}