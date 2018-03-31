<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Languages\Administrator\View\Language;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Languages\Administrator\Helper\LanguagesHelper;

/**
 * HTML View class for the Languages component.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The active item
	 *
	 * @var  object
	 */
	public $item;

	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	public $form;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	public $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var    \JObject
	 * @since  4.0.0
	 */
	protected $canDo;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->state = $this->get('State');
		$this->canDo = ContentHelper::getActions('com_languages');

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
		\JFactory::getApplication()->input->set('hidemainmenu', 1);
		$isNew = empty($this->item->lang_id);
		$canDo = $this->canDo;

		\JToolbarHelper::title(
			\JText::_($isNew ? 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_NEW_TITLE' : 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_EDIT_TITLE'), 'comments-2 langmanager'
		);

		$toolbarButtons = [];

		if (($isNew && $canDo->get('core.create')) || (!$isNew && $canDo->get('core.edit')))
		{
			$toolbarButtons[] = ['apply', 'language.apply'];
			$toolbarButtons[] = ['save', 'language.save'];
		}

		// If an existing item, can save to a copy only if we have create rights.
		if ($canDo->get('core.create'))
		{
			$toolbarButtons[] = ['save2new', 'language.save2new'];
		}

		\JToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if ($isNew)
		{
			\JToolbarHelper::cancel('language.cancel');
		}
		else
		{
			\JToolbarHelper::cancel('language.cancel', 'JTOOLBAR_CLOSE');
		}

		\JToolbarHelper::divider();
		\JToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_EDIT');
	}
}
