<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Modules list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesControllerModules extends JControllerAdmin
{
	protected $_context = 'com_modules';
	
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
		$this->setURL('index.php?option=com_modules&view=modules');
	}

	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Module', $prefix = 'ModulesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to clone an existing module.
	 */
	public function duplicate()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
			}
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::sprintf((count($pks) == 1) ? 'COM_MODULES_MODULE_DUPLICATED' : 'COM_MODULES_N_MODULES_DUPLICATED', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_modules&view=modules');
	}
}