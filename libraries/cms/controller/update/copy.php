<?php
/**
 * @package     Joomla.Libraries
 * @subpackage Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerUpdateCopy extends JControllerUpdateBase
{
	public function execute()
	{
		if (parent::execute())
		{
			$config = $this->config;

			$prefix = $this->getPrefix();
			$model = $this->getModel($prefix, $config['subject'], $config);

			$context = $model->getContext();
			$keyName = $model->getKeyName();
			$keyValue = $model->getState($context.'.id');

			$url = 'index.php?option='.$config['option'].'&task=edit.'.$config['subject'];
			$url .='&'.$keyName.'='.$keyValue;

			try
			{
				$this->checkin();
			}
			catch (Exception $e)
			{
				$msg = $e->getMessage();
				$this->setRedirect($url, $msg, 'warning', true, false);
			}

			$msg = $this->translate('JLIB_APPLICATION_MSG_COPY_COMPLETED');
			$this->setRedirect($url, $msg, 'message');

			return true;
		}

		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see JControllerUpdateBase::commit()
	 */
	protected function commit($model, $data)
	{
		if (!$model->allowAction('core.create'))
		{
			throw new ErrorException($this->translate('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
		}
		else
		{
			$keyName = $model->getKeyName();
			$data[$keyName] = 0;

			$model->create($data);
		}
	}
}