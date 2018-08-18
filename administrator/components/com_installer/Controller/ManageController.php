<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Installer Manage Controller
 *
 * @since  1.6
 */
class ManageController extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since  1.6
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('unpublish', 'publish');
		$this->registerTask('publish',   'publish');
	}

	/**
	 * Enable/Disable an extension (if supported).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function publish()
	{
		// Check for request forgeries.
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('publish' => 1, 'unpublish' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			$this->setMessage(\JText::_('COM_INSTALLER_ERROR_NO_EXTENSIONS_SELECTED'), 'warning');
		}
		else
		{
			/* @var \Joomla\Component\Installer\Administrator\Model\ManageModel $model */
			$model = $this->getModel('manage');

			// Change the state of the records.
			if (!$model->publish($ids, $value))
			{
				$this->setMessage(implode('<br>', $model->getErrors()), 'warning');
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_PUBLISHED';
				}
				else
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_UNPUBLISHED';
				}

				$this->setMessage(\JText::plural($ntext, count($ids)));
			}
		}

		$this->setRedirect(\JRoute::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Remove an extension (Uninstall).
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function remove()
	{
		// Check for request forgeries.
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		/* @var \Joomla\Component\Installer\Administrator\Model\ManageModel $model */
		$model = $this->getModel('manage');

		$eid = $this->input->get('cid', array(), 'array');
		$eid = ArrayHelper::toInteger($eid, array());
		$model->remove($eid);
		$this->setRedirect(\JRoute::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Refreshes the cached metadata about an extension.
	 *
	 * Useful for debugging and testing purposes when the XML file might change.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function refresh()
	{
		// Check for request forgeries.
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		/* @var \Joomla\Component\Installer\Administrator\Model\ManageModel $model */
		$model = $this->getModel('manage');

		$uid = $this->input->get('cid', array(), 'array');
		$uid = ArrayHelper::toInteger($uid, array());
		$model->refresh($uid);
		$this->setRedirect(\JRoute::_('index.php?option=com_installer&view=manage', false));
	}
}
