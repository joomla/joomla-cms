<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Methods supporting a list of search terms.
 *
 * @since  __DEPLOY_VERSION__
 */
class SearchesController extends BaseController
{
	/**
	 * Method to reset the search log table.
	 *
	 * @return  boolean
	 */
	public function reset()
	{
		// Check for request forgeries.
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('Searches');

		if (!$model->reset())
		{
			$this->app->enqueueMessage($model->getError(), 'error');
		}

		$this->setRedirect('index.php?option=com_finder&view=searches');
	}
}
