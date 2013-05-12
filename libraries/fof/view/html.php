<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');

/**
 * FrameworkOnFramework HTML List View class
 *
 * FrameworkOnFramework is a set of classes which extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFViewHtml extends FOFView
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
		list($isCli, ) = FOFDispatcher::isCliAdmin();

		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		} elseif (!is_array($config))
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
			$this->input = new FOFInput();
		}

		$this->lists = new JObject();

		if(!$isCli)
		{
			$user = JFactory::getUser();
			$perms = (object) array(
					'create'	 => $user->authorise('core.create', $this->input->getCmd('option', 'com_foobar')),
					'edit'		 => $user->authorise('core.edit', $this->input->getCmd('option', 'com_foobar')),
					'editstate'	 => $user->authorise('core.edit.state', $this->input->getCmd('option', 'com_foobar')),
					'delete'	 => $user->authorise('core.delete', $this->input->getCmd('option', 'com_foobar')),
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

		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		// Don't load the toolbar on CLI
		if(!$isCli)
		{
			$toolbar = FOFToolbar::getAnInstance($this->input->getCmd('option', 'com_foobar'), $this->config);
			$toolbar->perms = $this->perms;
			$toolbar->renderToolbar($this->input->getCmd('view', 'cpanel'), $task, $this->input);
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
	 * Renders the link bar (submenu) using Joomla!'s default
	 * JSubMenuHelper::addEntry method
	 */
	private function renderLinkbar()
	{
		// Do not render a submenu unless we are in the the admin area
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if (!$isAdmin || $isCli)
			return;
		$toolbar = FOFToolbar::getAnInstance($this->input->getCmd('option', 'com_foobar'), $this->config);
		$links = $toolbar->getLinks();
		if (!empty($links))
		{
			foreach ($links as $link)
			{
				JSubMenuHelper::addEntry($link['name'], $link['link'], $link['active']);
			}
		}
	}

	/**
	 * Runs before rendering the view template, echoing HTML to put before the
	 * view template's generated HTML
	 */
	protected function preRender()
	{
		$renderer = $this->getRenderer();
		if (!($renderer instanceof FOFRenderAbstract))
		{
			$this->renderLinkbar();
		}
		else
		{
			$view = $this->input->getCmd('view', 'cpanel');
			$task = $this->getModel()->getState('task', 'browse');
			$renderer->preRender($view, $task, $this->input, $this->config);
		}
	}

	/**
	 * Runs after rendering the view template, echoing HTML to put after the
	 * view template's generated HTML
	 */
	protected function postRender()
	{
		$renderer = $this->getRenderer();
		if ($renderer instanceof FOFRenderAbstract)
		{
			$view = $this->input->getCmd('view', 'cpanel');
			$task = $this->getModel()->getState('task', 'browse');
			$renderer->postRender($view, $task, $this->input, $this->config);
		}
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
			return;

		// Load the model
		$model = $this->getModel();

		// ...ordering
		$this->lists->set('order', $model->getState('filter_order', 'id', 'cmd'));
		$this->lists->set('order_Dir', $model->getState('filter_order_Dir', 'DESC', 'cmd'));

		// Assign data to the view
		$this->assign('items', $model->getItemList());
		$this->assign('pagination', $model->getPagination());
		$this->assignRef('lists', $this->lists);

		//pass page params on frontend only
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		if (!$isAdmin && !$isCli)
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
		if (version_compare(JVERSION, '3.0', 'lt'))
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