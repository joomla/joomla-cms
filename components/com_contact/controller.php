<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Contact Component Controller
 *
 * @package		Joomla
 * @subpackage	Contact
 * @since 1.6
 */
class ContactController extends JController
{
	public function display()
	{
		$document =& JFactory::getDocument();

		$viewName	= JRequest::getCmd('view');
		$viewType	= $document->getType();

		$view = &$this->getView($viewName, $viewType);
		$model	= &$this->getModel( $viewName );
		if (!JError::isError( $model )) {
			$view->setModel( $model, true );
		}

		$view->assign('error', $this->getError());
		$view->display();
	}

	/**
	 * Method to send an email to a contact
	 *
	 */
	public function submit()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = &JFactory::getUser();
		$model =& $this->getModel('contact');

		if($model->mailTo($user)) {
			$contact = $model->getData($user->get('aid', 0));
			$msg = JText::_('THANK_MESSAGE');
			JFactory::getApplication()->enqueueMessage($msg, 'message');
			$this->display();
			//$link = JRoute::_('index.php?option=com_contact&view=contact&id='.$contact->slug, false);
			//$this->setRedirect($link, $msg);
		} else {
			$this->setError($model->getError());
			$this->display();
		}
	}
}
