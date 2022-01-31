<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\View\Newsfeed;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a newsfeed.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The item object for the newsfeed
	 *
	 * @var    \Joomla\CMS\Object\CMSObject
	 *
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The form object for the newsfeed
	 *
	 * @var    \Joomla\CMS\Form\Form
	 *
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The model state of the newsfeed
	 *
	 * @var    \Joomla\CMS\Object\CMSObject
	 *
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// If we are forcing a language in modal (used for associations).
		if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
		{
			// Set the language field to the forcedLanguage and disable changing it.
			$this->form->setValue('language', null, $forcedLanguage);
			$this->form->setFieldAttribute('language', 'readonly', 'true');

			// Only allow to select categories with All language or with the forced language.
			$this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);

			// Only allow to select tags with All language or with the forced language.
			$this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
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
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user       = Factory::getUser();
		$isNew      = ($this->item->id == 0);
		$checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $user->get('id'));

		// Since we don't track these assets at the item level, use the category id.
		$canDo = ContentHelper::getActions('com_newsfeeds', 'category', $this->item->catid);

		$title = $isNew ? Text::_('COM_NEWSFEEDS_MANAGER_NEWSFEED_NEW') : Text::_('COM_NEWSFEEDS_MANAGER_NEWSFEED_EDIT');
		ToolbarHelper::title($title, 'rss newsfeeds');

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0))
		{
			ToolbarHelper::apply('newsfeed.apply');

			$toolbarButtons[] = ['save', 'newsfeed.save'];
		}

		if (!$checkedOut && count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)
		{
			$toolbarButtons[] = ['save2new', 'newsfeed.save2new'];
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			$toolbarButtons[] = ['save2copy', 'newsfeed.save2copy'];
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('newsfeed.cancel');
		}
		else
		{
			ToolbarHelper::cancel('newsfeed.cancel', 'JTOOLBAR_CLOSE');

			if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit'))
			{
				ToolbarHelper::versions('com_newsfeeds.newsfeed', $this->item->id);
			}
		}

		if (!$isNew && Associations::isEnabled() && ComponentHelper::isEnabled('com_associations'))
		{
			ToolbarHelper::custom('newsfeed.editAssociations', 'contract', '', 'JTOOLBAR_ASSOCIATIONS', false, false);
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('News_Feeds:_New_or_Edit');
	}
}
