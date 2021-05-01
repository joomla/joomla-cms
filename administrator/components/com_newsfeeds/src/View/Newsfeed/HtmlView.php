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
use Joomla\CMS\Plugin\PluginHelper;
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
	 * @var    \JObject
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The form object for the newsfeed
	 *
	 * @var    \JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The model state of the newsfeed
	 *
	 * @var    \JObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Is Versionable plugin enabled
	 *
	 * @var  boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $isVersionable = false;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->canDo = ContentHelper::getActions('com_newsfeeds', 'category', $this->item->catid);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Cache isVersionable so we can reuse when adding toolbar, and if isVersionable is false, remove form fields
		if (PluginHelper::isEnabled('behaviour', 'versionable')
			&& ComponentHelper::isEnabled('com_contenthistory')
			&& $this->state->params->get('save_history', 0)
			&& ($this->canDo->get('core.edit')
			|| ($this->canDo->get('core.edit.own')
			&& $this->item->created_by == Factory::getApplication()->getIdentity()->get('id')))
		)
		{
			$this->isVersionable = true;
		}
		else
		{
			$this->form->removeField('version_note');
		}

		// If the plugin Taggable behaviour is disabled then remove the tags feature.
		if (!PluginHelper::isEnabled('behaviour', 'taggable'))
		{
			$this->form->removeField('tags');
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

		$title = $isNew ? Text::_('COM_NEWSFEEDS_MANAGER_NEWSFEED_NEW') : Text::_('COM_NEWSFEEDS_MANAGER_NEWSFEED_EDIT');
		ToolbarHelper::title($title, 'rss newsfeeds');

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if (!$checkedOut && ($this->canDo->get('core.edit') || count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0))
		{
			ToolbarHelper::apply('newsfeed.apply');

			$toolbarButtons[] = ['save', 'newsfeed.save'];
		}

		if (!$checkedOut && count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)
		{
			$toolbarButtons[] = ['save2new', 'newsfeed.save2new'];
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $this->canDo->get('core.create'))
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

			if ($this->isVersionable)
			{
				ToolbarHelper::versions('com_newsfeeds.newsfeed', $this->item->id);
			}
		}

		if (!$isNew && Associations::isEnabled() && ComponentHelper::isEnabled('com_associations'))
		{
			ToolbarHelper::custom('newsfeed.editAssociations', 'contract', '', 'JTOOLBAR_ASSOCIATIONS', false, false);
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS_EDIT');
	}
}
