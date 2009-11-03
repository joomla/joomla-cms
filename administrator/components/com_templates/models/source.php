<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * @package		Joomla.Administrator
 * @subpackage	Templates
 */
class TemplatesModelSource extends JModelForm
{
	/**
	 * Cache for the template information.
	 *
	 * @var		object
	 */
	private $_template = null;

	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$id = $app->getUserState('com_templates.edit.source.id');

		// Parse the template id out of the compound reference.
		$temp	= explode(':', base64_decode($id));
		$this->setState('extension.id', (int) array_shift($temp));
		$this->setState('filename', array_shift($temp));

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_templates');
		$this->setState('params', $params);
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('source', 'com_templates.source', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_templates.edit.source.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getSource()
	{
		$item = new stdClass;

		$item->extension_id	= $this->getState('extension.id');
		$item->filename		= $this->getState('filename');
		$item->source		= 'Todo';

		return $item;
	}

	/**
	 * Method to get the template information.
	 *
	 * @return	mixed	Object if successful, false if not and internal error is set.
	 */
	public function &getTemplate()
	{
		// Initialise variables.
		$pk		= $this->getState('extension.id');
		$db		= $this->getDbo();
		$result	= false;

		// Get the template information.
		$db->setQuery(
			'SELECT extension_id, client_id, element' .
			' FROM #__extensions' .
			' WHERE extension_id = '.(int) $pk.
			'  AND type = '.$db->quote('template')
		);

		$result = $db->loadObject();
		if (empty($result))
		{
			if ($error = $db->getErrorMsg()) {
				$this->setError($error);
			}
			else {
				$this->setError(JText::_('Templates_Error_Extension_record_not_found'));
			}
			$this->_template = false;
		}
		else {
			$this->_template = $result;
		}

		return $this->_template;
	}






	/**
	 * Method to store the Template
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function store($filecontent)
	{
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = $this->_client->path.DS.'templates'.DS.$this->_template.DS.'index.php';

		// Try to make the template file writeable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			$this->setError(JText::_('Could not make the template file writable'));
			return false;
		}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the template file unwriteable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			$this->setError(JText::_('Could not make the template file unwritable'));
			return false;
		}

		if (!$return)
		{
			$this->setError(JText::_('Operation Failed').': '.JText::sprintf('Failed to open file for writing.', $file));
			return false;
		}

		return true;
	}
}
