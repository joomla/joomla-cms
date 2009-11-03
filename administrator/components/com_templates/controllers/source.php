<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Template style controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesControllerSource extends JController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply',		'save');
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return 	boolean
	 */
	protected function _allowEdit()
	{
		return JFactory::getUser()->authorise('core.edit', 'com_templates');
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string $name	The model name. Optional.
	 * @param	string $prefix	The class prefix. Optional.
	 * @param	array $config	Configuration array for model. Optional.
	 *
	 * @return	object			The model.
	 */
	public function &getModel($name = 'Source', $prefix = 'TemplatesModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * This controller does not have a display method. Redirect back to the list view of the component.
	 *
	 * @return	void
	 */
	public function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_templates&view=templates', false));
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialize variables.
		$app		= JFactory::getApplication();
		$model		= $this->getModel();
		$recordId	= JRequest::getVar('id');
		$context	= 'com_templates.edit.source';

		// Access check.
		if (!$this->_allowEdit()) {
			return JError::raiseWarning(403, 'JError_Core_Edit_not_permitted.');
		}

		// Check-out succeeded, push the new record id into the session.
		$app->setUserState($context.'.id',	$recordId);
		$app->setUserState($context.'.data', null);
		$this->setRedirect('index.php?option=com_templates&view=source&layout=edit');
		return true;
	}

	/**
	 * Method to cancel an edit
	 *
	 * @return	void
	 */
	public function cancel()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app		= JFactory::getApplication();
		$model		= &$this->getModel();
		$context	= 'com_templates.edit.source';

		// Get the record id.
		$recordId	= $app->getUserState($context.'.id');

		// Parse the template id out of the compound reference.
		$temp	= explode(':', base64_decode($recordId));
		$id		= (int) array_shift($temp);

		// Clean the session data and redirect.
		$app->setUserState($context.'.id',		null);
		$app->setUserState($context.'.data',	null);
		$this->setRedirect(JRoute::_('index.php?option=com_templates&view=template&id='.$id, false));
	}





	/**
	 * Override parent save method to deal with special template parameters.
	 *
	 * @return	void
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$iData	= JRequest::getVar('jform', array(), 'post', 'array');
		$pData	= JRequest::getVar('jformparams', array(), 'post', 'array');
		$model	= $this->getModel();

		// Get the template parameter form.
		$paramsForm	= $model->getParamsForm($iData['template'], $iData['client_id']);
		if (!$paramsForm) {
			JError::raiseError(500, $model->getError());
			return false;
		}

		// Validate and inject back into the main form data.
		$pData	= $model->validate($paramsForm, $pData);
		$iData['params'] = $pData;

		JRequest::setVar('jform', $iData, 'post');

		return parent::save();
	}
}