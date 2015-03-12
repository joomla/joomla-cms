<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class ConfigControllerStore
 * This is a custom store controller
 * We customized here because com_config does not create records, it only updates them.
 */
class ConfigControllerStore extends JControllerAdministrate
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		/** @var JModelAdministrator $model */
		$model   = $this->getModel($this->config['resource']);
		$keyName = $model->getKeyName();

		$data = $this->input->get('jform', array(), 'array');

		if(!$model->allowAction('core.admin'))
		{
			throw new ErrorException(JText::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		$model->update($data);

		$config = $this->config;
		$url    = 'index.php?option=' . $config['option'] . '&view=' . $config['view'] . '&layout=form';

		if($this->config['resource'] == 'component' && isset($data[$keyName]))
		{
			$url .= '&id=' . $data[$keyName];
		}

		$this->addMessage(JText::_('BABELU_LIB_CONTROLLER_MESSAGE_SAVE_COMPLETED'));
		$this->setReturn($url);

		//execute any controllers we might have
		return $this->executeController();
	}
}