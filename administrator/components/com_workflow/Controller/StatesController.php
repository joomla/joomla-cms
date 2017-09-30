<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  __DEPLOY_VERSION__
 */
class StatesController extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 *                                         Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                         'view_path' (this list is not meant to be comprehensive).
	 * @param   MvcFactoryInterface  $factory  The factory.
	 * @param   CmsApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(array $config = array(), MvcFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('unsetDefault',	'setDefault');
	}

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\Model\Model  The model.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getModel($name = 'State', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to set the home property for a list of items
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setDefault()
	{
		// Check for request forgeries
		\JSession::checkToken('request') or die(\JText::_('JINVALID_TOKEN'));

		$app = $this->app;

		// Get items to publish from the request.
		$cid   = $this->input->get('cid', array(), 'array');
		$data  = array('setDefault' => 1, 'unsetDefault' => 0);
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (!$value)
		{
			$this->setMessage(\JText::_('COM_WORKFLOW_DISABLE_DEFAULT'), 'warning');
			$this->setRedirect(
				\JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. '&extension=' . $this->input->getCmd("extension"), false
				)
			);

			return;
		}

		if (empty($cid))
		{
			$this->setMessage(\JText::_('COM_WORKFLOW_NO_ITEM_SELECTED'), 'warning');
		}
		elseif (is_numeric($cid[1]))
		{
			$this->setMessage(\JText::_('COM_WORKFLOW_TO_MANY_ITEMS'), 'error');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers

			// Publish the items.
			if (!$model->setHome((int) $cid[0], $value))
			{
				$this->setMessage($model->getError(), 'warning');
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_WORKFLOW_ITEM_SET_DEFAULT';
				}
				else
				{
					$ntext = 'COM_WORKFLOW_ITEM_UNSET_DEFAULT';
				}

				$this->setMessage(\JText::_($ntext, count($cid)));
			}
		}

		$this->setRedirect(
			\JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. '&extension=' . $this->input->getCmd("extension")
				. '&workflow_id=' . $this->input->getCmd("workflow_id"), false
			)
		);
	}

	/**
	 * Deletes and returns correctly.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function delete()
	{
		parent::delete();
		$this->setRedirect(
			\JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. '&extension=' . $this->input->getCmd("extension")
				. '&workflow_id=' . $this->input->getCmd("workflow_id"), false
			)
		);
	}
}
