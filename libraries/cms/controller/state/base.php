<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JCmsControllerStateBase extends JCmsControllerBase
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$config = $this->config;
		$input = $this->input;

		$prefix = $this->getPrefix();
		$model = $this->getModel($prefix, $config['subject'], $config);

		if (!$model->allowAction('core.edit.state'))
		{
			throw new ErrorException($this->translate('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			return false;
		}

		$cid = $input->post->get('cid', array(), 'array');
		// Make sure the item ids are integers
		$cid = $this->cleanCid($cid);


		$this->updateRecordState($model, $cid);

		return true;
	}

	/**
	 * Method to update record states
	 * @param JCmsModelData $model
	 * @param array $cid
	 */
	abstract protected function updateRecordState($model, $cid);


}