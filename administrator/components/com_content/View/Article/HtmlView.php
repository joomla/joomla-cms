<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\View\Article;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

\JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

/**
 * View to edit an article.
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
	 * @var  object
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

	/**
	 * Pagebreak TOC alias
	 *
	 * @var  string
	 */
	protected $eName;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'pagebreak')
		{
			return parent::display($tpl);
		}

		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = ContentHelper::getActions('com_content', 'article', $this->item->id);

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

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Built the actions for new and existing records.
		$canDo = $this->canDo;

		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title(
			Text::_('COM_CONTENT_PAGE_' . ($checkedOut ? 'VIEW_ARTICLE' : ($isNew ? 'ADD_ARTICLE' : 'EDIT_ARTICLE'))),
			'pencil-2 article-add'
		);

		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories('com_content', 'core.create')) > 0))
		{
			$apply = $toolbar->apply('article.apply');

			$saveGroup = $toolbar->dropdownButton('save-group');

			$saveGroup->configure(
				function (Toolbar $childBar)
				{
					$childBar->save('article.save');
					$childBar->save2new('article.save2new');
				}
			);
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

			if (!$checkedOut && $itemEditable)
			{
				$toolbar->apply('article.apply');
			}
			$saveGroup = $toolbar->dropdownButton('save-group');

			$saveGroup->configure(
				function (Toolbar $childBar) use ($checkedOut, $itemEditable, $canDo)
				{
					// Can't save the record if it's checked out and editable
					if (!$checkedOut && $itemEditable)
					{
						$childBar->save('article.save');

						// We can save this record, but check the create permission to see if we can return to make a new one.
						if ($canDo->get('core.create'))
						{
							$childBar->save2new('article.save2new');
						}
					}

					// If checked out, we can still save
					if ($canDo->get('core.create'))
					{
						$childBar->save2copy('article.save2copy');
					}
				}
			);

			if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $itemEditable)
			{
				$toolbar->versions('com_content.article', $this->item->id);
			}

			if (!$isNew)
			{
				$url = Route::link(
					'site',
					\ContentHelperRoute::getArticleRoute($this->item->id, $this->item->catid, $this->item->language),
					true
				);

				$toolbar->preview($url, 'JGLOBAL_PREVIEW')
					->bodyHeight(80)
					->modalWidth(90);

				// Add necessary code for a new menu item modal

				// Setup variables for display
				$linkSuffix = '&amp;layout=modal&amp;client_id=0&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
				$linkItem   = 'index.php?option=com_menus&amp;view=item' . $linkSuffix;

				// Force the language of the menu item when multilang is implemented
				if (Multilanguage::isEnabled() && $this->form->getValue('language') !== '*')
				{
					$linkItem .= '&amp;forcedLanguage=' . $this->form->getValue('language');
				}

				$urlNew  = $linkItem . '&amp;task=item.add';
				$modalId = 'jform_request_id';

				// Add button to open the modal
				ToolbarHelper::modal('ModalNewItem_' . $modalId, 'icon-new', 'COM_CONTENT_ADD_NEW_MENU_ITEM');

				// Add the modal field script to the document head.
				HTMLHelper::_('script', 'system/fields/modal-fields.min.js', array('version' => 'auto', 'relative' => true));

				// Load the language files
				$language = Factory::getLanguage();
				$language->load('com_menus', JPATH_ADMINISTRATOR, null, false, true);

				// Add the modal html to the document
				echo HTMLHelper::_(
					'bootstrap.renderModal',
					'ModalNewItem_' . $modalId,
					array(
						'title' => Text::_('COM_MENUS_NEW_MENUITEM'),
						'backdrop' => 'static',
						'keyboard' => false,
						'closeButton' => false,
						'url' => $urlNew,
						'height' => '400px',
						'width' => '800px',
						'bodyHeight' => 70,
						'modalWidth' => 80,
						'footer' => '<a role="button" class="btn btn-secondary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $modalId . '\', \'add\', \'item\', \'cancel\', \'item-form\'); return false;">'
							. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $modalId . '\', \'add\', \'item\', \'save\', \'item-form\'); return false;">'
							. Text::_('JSAVE') . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $modalId . '\', \'add\', \'item\', \'apply\', \'item-form\'); return false;">'
							. Text::_('JAPPLY') . '</a>'
					)
				);


				echo '<input type="hidden" class="form-control" id="' . $modalId . '_name" value="">';
				echo '<input type="hidden" id="' . $modalId . '_id" value="0">';
			}
		}

		if (Associations::isEnabled() && ComponentHelper::isEnabled('com_associations'))
		{
			$toolbar->standardButton('contract')
			->text('JTOOLBAR_ASSOCIATIONS')
			->task('article.editAssociations');
		}

		$toolbar->cancel('article.cancel', 'JTOOLBAR_CLOSE');

		$toolbar->divider();
		$toolbar->help('JHELP_CONTENT_ARTICLE_MANAGER_EDIT');
	}
}
