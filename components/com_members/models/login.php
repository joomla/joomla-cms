<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');

/**
 * Login model class for Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersModelLogin extends JModel
{
	/**
	 * Method to get the form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for members plugins to extend the form with extra fields.
	 *
	 * @param	string	$type	The type of form to load (view, model);
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function &getForm()
	{
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		$form = &JForm::getInstance('jform', 'login', true);

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Check the session for previously entered login form data.
		$data = $app->getUserState('members.login.form.data', array());

		// Set the return URL if empty.
		if (!isset($data['return']) || empty($data['return'])) {
			$data['return'] = 'index.php?option=com_members&view=profile';
			$app->setUserState('members.login.form.data', $data);
		}

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('member');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareMembersLoginForm', array(&$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		return $form;
	}
}