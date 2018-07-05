<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Field\Modal;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * Supports a modal article picker.
 *
 * @since  1.6
 */
class ArticleField extends ModalField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Article';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Load language.
		Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

		return parent::getInput();
	}

	/**
	 * Method to get the urls used by this field.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getUrls()
	{
		// Setup variables for display.
		$linkArticles = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$linkArticle  = 'index.php?option=com_content&amp;view=article&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';

		if (isset($this->element['language']))
		{
			$linkArticles .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkArticle  .= '&amp;forcedLanguage=' . $this->element['language'];
		}

		return [
			'select' => $linkArticles . '&amp;function=jModalSelect',
			// The edit url needs the id of the selected item. It will be inserted dynamically in place of ${0}
			'edit'   => $linkArticle . '&amp;task=article.edit&amp;id=${0}',
			'new'    => $linkArticle . '&amp;task=article.add',
		];
	}

	/**
	 * Method to get the strings used by this field.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getText()
	{
		$selectTitle = Text::_('COM_CONTENT_CHANGE_ARTICLE') . (isset($this->element['language']) ? ' &#8212; ' . $this->element['label'] : '');

		return array_merge(parent::getText(), [
			'title-select' => $selectTitle,
			'title-new'    => Text::_('COM_CONTENT_NEW_ARTICLE'),
			'title-edit'   => Text::_('COM_CONTENT_EDIT_ARTICLE'),
			'placeholder'  => Text::_('COM_CONTENT_CHANGE_ARTICLE'),
		]);
	}

	/**
	 * Method to get the title of the currently selected item.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getSelectedTitle()
	{
		// The active article id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		if ($value)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		return empty($title) ? Text::_('COM_CONTENT_SELECT_AN_ARTICLE') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Method to get attributes for the html tag.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getAttributes()
	{
		return [
			'item-type' => 'article',
			'form-id'   => 'item-form',
		];
	}
}
