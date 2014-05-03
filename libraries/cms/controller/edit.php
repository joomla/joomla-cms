<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerEdit extends JControllerDisplay
{
	/**
	 * Instantiate the controller.
	 *
	 * @param   JInput            $input  The input object.
	 * @param   JApplicationBase  $app    The application object.
	 * @param   array             $config Configuration
	 * @since  12.1
	 */
	public function __construct(JInput $input, $app = null, $config = array())
	{
		$input->set('layout', 'edit');

		parent::__construct($input, $app, $config);
	}

	/**
	 * (non-PHPdoc)
	 * @see JControllerDisplay::execute()
	 */
	public function execute()
	{
		$config = $this->config;
		$prefix = $this->getPrefix();
		$model = $this->getModel($prefix, $config['subject'], $config);

		if (!$model->allowAction('core.edit'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED');
			$url = 'index.php?option='.$config['option'].'&task=display.'.$config['subject'];
			$this->setRedirect($url, $msg, 'error');
			return false;
		}

		$input = $this->input;
		$cid = $input->post->get('cid', array(), 'array');

		if (count($cid))
		{
			$pk = (int)$cid[0];
		}
		else
		{
			$keyName = $model->getKeyName();
			$pk = $input->getInt($keyName, 0);
		}

		$context = $model->getContext();
		$model->setState($context.'.id', $pk);

		try
		{
			$model->checkout($pk);
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$url = 'index.php?option='.$config['option'].'&task=display.'.$config['subject'];
			$this->setRedirect($url, $msg, 'error');
			return false;
		}

		return parent::execute();
	}
}