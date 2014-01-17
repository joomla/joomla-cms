<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * FrameworkOnFramework raw output class. It works like an HTML view, but the
 * output is bare HTML.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFViewRaw extends FOFView
{
	/** @var array Data lists */
	protected $lists = null;

	/** @var array Permissions map */
	protected $perms = null;

	/**
	 * Class constructor
	 *
	 * @param   array  $config  Configuration parameters
	 */
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		parent::__construct($config);

		$this->config = $config;

		// Get the input
		if (array_key_exists('input', $config))
		{
			if ($config['input'] instanceof FOFInput)
			{
				$this->input = $config['input'];
			}
			else
			{
				$this->input = new FOFInput($config['input']);
			}
		}
		else
		{
			$this->input = new FOFInput;
		}

		$this->lists = new JObject;

		if (!FOFPlatform::getInstance()->isCli())
		{
			$platform = FOFPlatform::getInstance();
			$perms = (object) array(
					'create'	 => $platform->authorise('core.create', $this->input->getCmd('option', 'com_foobar')),
					'edit'		 => $platform->authorise('core.edit', $this->input->getCmd('option', 'com_foobar')),
					'editstate'	 => $platform->authorise('core.edit.state', $this->input->getCmd('option', 'com_foobar')),
					'delete'	 => $platform->authorise('core.delete', $this->input->getCmd('option', 'com_foobar')),
			);
			$this->assign('aclperms', $perms);
			$this->perms = $perms;
		}
	}

	/**
	 * Displays the view
	 *
	 * @param   string  $tpl  The template to use
	 *
	 * @return  boolean|null False if we can't render anything
	 */
	public function display($tpl = null)
	{
		// Get the task set in the model
		$model = $this->getModel();
		$task = $model->getState('task', 'browse');

		// Call the relevant method
		$method_name = 'on' . ucfirst($task);

		if (method_exists($this, $method_name))
		{
			$result = $this->$method_name($tpl);
		}
		else
		{
			$result = $this->onDisplay();
		}

		if ($result === false)
		{
			return;
		}

		// Show the view
		if ($this->doPreRender)
		{
			$this->preRender();
		}

		parent::display($tpl);

		if ($this->doPostRender)
		{
			$this->postRender();
		}
	}

	/**
	 * Last chance to output something before rendering the view template
	 *
	 * @return  void
	 */
	protected function preRender()
	{
	}

	/**
	 * Last chance to output something after rendering the view template and
	 * before returning to the caller
	 *
	 * @return  void
	 */
	protected function postRender()
	{
	}

	/**
	 * Executes before rendering the page for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onBrowse($tpl = null)
	{
		// When in interactive browsing mode, save the state to the session
		$this->getModel()->savestate(1);

		return $this->onDisplay($tpl);
	}

	/**
	 * Executes before rendering a generic page, default to actions necessary
	 * for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onDisplay($tpl = null)
	{
		$view = $this->input->getCmd('view', 'cpanel');

		if (in_array($view, array('cpanel', 'cpanels')))
		{
			return;
		}

		// Load the model
		$model = $this->getModel();

		// ...ordering
		$this->lists->set('order', $model->getState('filter_order', 'id', 'cmd'));
		$this->lists->set('order_Dir', $model->getState('filter_order_Dir', 'DESC', 'cmd'));

		// Assign data to the view
		$this->assign('items', $model->getItemList());
		$this->assign('pagination', $model->getPagination());
		$this->assignRef('lists', $this->lists);

		// Pass page params on frontend only
		if (FOFPlatform::getInstance()->isFrontend())
		{
			$params = JFactory::getApplication()->getParams();
			$this->assignRef('params', $params);
		}

		return true;
	}

	/**
	 * Executes before rendering the page for the Add task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onAdd($tpl = null)
	{
		JRequest::setVar('hidemainmenu', true);
		$model = $this->getModel();
		$this->assign('item', $model->getItem());

		return true;
	}

	/**
	 * Executes before rendering the page for the Edit task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onEdit($tpl = null)
	{
		// An editor is an editor, no matter if the record is new or old :p

		return $this->onAdd();
	}

	/**
	 * Executes before rendering the page for the Read task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onRead($tpl = null)
	{
		// All I need is to read the record

		return $this->onAdd();
	}

	/**
	 * Determines if the current Joomla! version and your current table support
	 * AJAX-powered drag and drop reordering. If they do, it will set up the
	 * drag & drop reordering feature.
	 *
	 * @return  boolean|array  False if not suported, a table with necessary
	 *                         information (saveOrder: should you enabled DnD
	 *                         reordering; orderingColumn: which column has the
	 *                         ordering information).
	 */
	public function hasAjaxOrderingSupport()
	{
		if (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'lt'))
		{
			return false;
		}

		$model = $this->getModel();

		if (!method_exists($model, 'getTable'))
		{
			return false;
		}

		$table = $this->getModel()->getTable();

		if (!method_exists($table, 'getColumnAlias') || !method_exists($table, 'getTableFields'))
		{
			return false;
		}

		$orderingColumn = $table->getColumnAlias('ordering');
		$fields = $table->getTableFields();

		if (!array_key_exists($orderingColumn, $fields))
		{
			return false;
		}

		$listOrder = $this->escape($model->getState('filter_order', null, 'cmd'));
		$listDirn = $this->escape($model->getState('filter_order_Dir', 'ASC', 'cmd'));
		$saveOrder = $listOrder == $orderingColumn;

		if ($saveOrder)
		{
			$saveOrderingUrl = 'index.php?option=' . $this->config['option'] . '&view=' . $this->config['view'] . '&task=saveorder&format=json';
			JHtml::_('sortablelist.sortable', 'itemsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
		}

		return array(
			'saveOrder'		 => $saveOrder,
			'orderingColumn' => $orderingColumn
		);
	}

	/**
	 * Returns the internal list of useful variables to the benefit of
	 * FOFFormHeader fields.
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function getLists()
	{
		return $this->lists;
	}

	/**
	 * Returns a reference to the permissions object of this view
	 *
	 * @return stdClass
	 */
	public function getPerms()
	{
		return $this->perms;
	}
}
