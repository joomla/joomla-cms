<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Modules\Administrator\View\Module;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View to edit a module.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var    \JObject
	 * @since  4.0.0
	 */
	protected $canDo;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = ContentHelper::getActions('com_modules', 'module', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		\JFactory::getApplication()->input->set('hidemainmenu', true);

		$user       = \JFactory::getUser();
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo      = $this->canDo;

		\JToolbarHelper::title(\JText::sprintf('COM_MODULES_MANAGER_MODULE', \JText::_($this->item->module)), 'cube module');

		// For new records, check the create permission.
		if ($isNew && $canDo->get('core.create'))
		{
			\JToolbarHelper::saveGroup(
				[
					['apply', 'module.apply'],
					['save', 'module.save'],
					['save2new', 'module.save2new']
				],
				'btn-success'
			);

			\JToolbarHelper::cancel('module.cancel');
		}
		else
		{
			$toolbarButtons = [];

			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission.
				if ($canDo->get('core.edit'))
				{
					$toolbarButtons[] = ['apply', 'module.apply'];
					$toolbarButtons[] = ['save', 'module.save'];

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						$toolbarButtons[] = ['save2new', 'module.save2new'];
					}
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'module.save2copy'];
			}

			\JToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			\JToolbarHelper::cancel('module.cancel', 'JTOOLBAR_CLOSE');
		}

		// Get the help information for the menu item.
		$lang = \JFactory::getLanguage();

		$help = $this->get('Help');

		if ($lang->hasKey($help->url))
		{
			$debug = $lang->setDebug(false);
			$url = \JText::_($help->url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		\JToolbarHelper::help($help->key, false, $url);
	}
}
