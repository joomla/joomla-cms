<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.confirmconsent
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

JFormHelper::loadFieldClass('Checkboxes');

/**
 * Form Field class for the Joomla Platform.
 * Single checkbox field.
 * This is a boolean field with null for false and the specified option for true
 *
 * @link   http://www.w3.org/TR/html-markup/input.checkbox.html#input.checkbox
 * @see    JFormFieldCheckboxes
 * @since  3.9.0
 */
class JFormFieldConsentBox extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.1
	 */
	protected $type = 'ConsentBox';

	/**
	 * Flag to tell the field to always be in multiple values mode.
	 *
	 * @var    boolean
	 * @since  3.9.1
	 */
	protected $forceMultiple = false;

	/**
	 * The article ID.
	 *
	 * @var    string
	 * @since  3.9.1
	 */
	protected $articleid;

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.9.1
	 */
	protected function getLabel()
	{
		if ($this->hidden)
		{
			return '';
		}

		$data = $this->getLayoutData();

		// Forcing the Alias field to display the tip below
		$position = $this->element['name'] == 'alias' ? ' data-placement="bottom" ' : '';

		$modalHTML           = '';
		$consentBoxLabel     = $data['label'];
		$consentBoxArticleId = $data['articleid'];

		// When we have a article let's add the modal and make the title clickable
		if ($consentBoxArticleId)
		{
			$modalName = 'consentbox-' . $consentBoxArticleId;
			$modalParams['title']  = Text::_($consentBoxLabel);
			$modalParams['url']    = $this->getAssignedArticleUrl($consentBoxArticleId);
			$modalParams['height'] = 800;
			$modalParams['width']  = "100%";
			$modalHTML = HTMLHelper::_('bootstrap.renderModal', 'modal-' . $modalName, $modalParams);

			$attribs['data-toggle'] = 'modal';

			$consentBoxLabel = HTMLHelper::_(
				'link',
				'#modal-' . $modalName,
				$consentBoxLabel,
				$attribs
			);
		}

		$data['label'] = $consentBoxLabel;

		// Here mainly for B/C with old layouts. This can be done in the layouts directly
		$extraData = array(
			'text'     => $data['label'],
			'for'      => $this->id,
			'classes'  => explode(' ', $data['labelclass']),
			'position' => $position,
		);

		return $modalHTML . $this->getRenderer($this->renderLabelLayout)->render(array_merge($data, $extraData));
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   3.9.1
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$extraData = array(
			'articleid' => (integer) $this->element['articleid'],
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Return the url of the assigned article based on the current user language
	 *
	 * @param   integer  $articleId  The form to be altered.
	 *
	 * @return  string  Returns the a tag containing everything for the modal
	 *
	 * @since   3.9.1
	 */
	private function getAssignedArticleUrl($articleId)
	{
		$db = Factory::getDbo();

		// Get the info from the article
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'catid', 'language')))
			->from($db->quoteName('#__content'))
			->where($db->quoteName('id') . ' = ' . (int) $articleId);
		$db->setQuery($query);

		try
		{
			$article = $db->loadObject();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			// Something at the database layer went wrong
			return Route::_(
				'index.php?option=com_content&view=article&id='
				. $articleId . '&tmpl=component'
			);
		}

		// Register ContentHelperRoute
		JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

		if (!Associations::isEnabled())
		{
			return Route::_(
				ContentHelperRoute::getArticleRoute(
					$article->id,
					$article->catid,
					$article->language
				) . '&tmpl=component'
			);
		}

		$associatedArticles = Associations::getAssociations('com_content', '#__content', 'com_content.item', $article->id);
		$currentLang        = Factory::getLanguage()->getTag();

		if (isset($associatedArticles) && $currentLang !== $article->language && array_key_exists($currentLang, $associatedArticles))
		{
			return Route::_(
				ContentHelperRoute::getArticleRoute(
					$associatedArticles[$currentLang]->id,
					$associatedArticles[$currentLang]->catid,
					$associatedArticles[$currentLang]->language
				) . '&tmpl=component'
			);
		}

		// Association is enabled but this article is not associated
		return Route::_(
			'index.php?option=com_content&view=article&id='
				. $article->id . '&catid=' . $article->catid
				. '&tmpl=component&lang=' . $article->language
		);
	}
}
