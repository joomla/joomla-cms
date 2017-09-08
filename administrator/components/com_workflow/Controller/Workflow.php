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

use Joomla\CMS\Factory;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\CMS\Controller\Form;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  4.0
 */
class Workflow extends Form
{
	/**
	 * The extension for which the categories apply.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MvcFactoryInterface  $factory  The factory.
	 * @param   \CMSApplication      $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since  1.6
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		if (empty($this->extension))
		{
			$this->extension = $this->input->get('extension', 'com_content');
		}
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   4.0
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&extension=' . $this->extension;

		return $append;
	}


	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   4.0
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   \JModelLegacy  $model      The data model object.
	 * @param   array          $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function postSaveHook(\JModelLegacy $model, $validData = array())
	{
		$task = $this->getTask();

		// The save2copy task needs to be handled slightly differently.
		if ($task === 'save2copy')
		{
			$table = $model->getTable();

			$key = $table->getKeyName();

			$recordId = $this->input->getInt($key);

			$db = $model->getDbo();
			$query = $db->getQuery(true);

			$query->select('*')
				->from($db->qn('#__workflow_states'))
				->where($db->qn('workflow_id') . ' = ' . (int) $recordId);

			$statuses = $db->setQuery($query)->loadAssocList();

			$smodel = $this->getModel('State');

			$context    = $this->option . '.' . $smodel->getName();

			Factory::getApplication()->setUserState($context . '.filter.workflow_id', (int) $model->getState($model->getName() . '.id'));

			$mapping = [];

			foreach ($statuses as $status)
			{
				$smodel = $this->getModel('State');

				$oldID = $status['id'];

				$status['tags'] = null;
				$status['id'] = 0;
				unset($status['asset_id']);

				$smodel->save($status);

				$mapping[$oldID] = (int) $smodel->getState($model->getName() . '.id');
			}
		}

		parent::postSaveHook($model, $validData);
	}

	public function save($key = null, $urlVar = null)
	{
		$task = $this->getTask();

		// The save2copy task needs to be handled slightly differently.
		if ($task === 'save2copy')
		{
			$data  = $this->input->post->get('jform', array(), 'array');

			// Prevent default
			$data['default'] = 0;

			$this->input->post->set('jform', $data);
		}

		parent::save($key, $urlVar);
	}
}
