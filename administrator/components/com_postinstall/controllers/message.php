<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class PostinstallControllerMessage extends FOFController
{
	/**
	 * Resets all post-installation messages of the specified extension
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function reset()
	{
		$model = $this->getThisModel();

		$eid = $this->input->getInt('eid', '700');

		$model->resetMessages($eid);

		$this->setRedirect('index.php?option=com_postinstall');
	}

	/**
	 * Executes the action associated with an item
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function action()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$item = $model->getItem();

		switch ($item->type)
		{
			case 'link':
				$this->setRedirect($item->action);

				return;

				break;

			case 'action':
				jimport('joomla.filesystem.file');

				$file = FOFTemplateUtils::parsePath($item->action_file, true);

				if (JFile::exists($file))
				{
					require_once $file;

					call_user_func($item->action);
				}
				break;

			case 'message':
			default:
				break;
		}

		$this->setRedirect('index.php?option=com_postinstall');
	}
}
