<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Index controller class for Finder.
 *
 * @since  2.5
 */
class IndexController extends AdminController
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since   2.5
	 */
	public function getModel($name = 'Index', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to purge all indexed links from the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function purge()
	{
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		// Remove the script time limit.
		@set_time_limit(0);

		/* @var \Joomla\Component\Finder\Administrator\Model\IndexModel $model */
		$model = $this->getModel('Index', 'Administrator');

		// Attempt to purge the index.
		$return = $model->purge();

		if (!$return)
		{
			$message = \JText::_('COM_FINDER_INDEX_PURGE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_finder&view=index', $message);

			return false;
		}
		else
		{
			$message = \JText::_('COM_FINDER_INDEX_PURGE_SUCCESS');
			$this->setRedirect('index.php?option=com_finder&view=index', $message);

			return true;
		}
	}
}
