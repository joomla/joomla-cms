<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.privacyconsent
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('radio');

/**
 * Provides input for privacy
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldprivacy extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'privacy';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		$privacynote = !empty($this->element['note']) ? $this->element['note'] : JText::_('PLG_USER_PRIVACY_NOTE_FIELD_DEFAULT');

		echo '<div class="alert alert-info">' . $privacynote . '</div>';

		return parent::getInput();
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLabel()
	{
		$label = '';

		if ($this->hidden)
		{
			return $label;
		}

		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
		$text = $this->translateLabel ? JText::_($text) : $text;

		// Set required to true as this field is not displayed at all if not required.
		$this->required = true;

		// Load Bootstrap js for the modal
		JHtml::_('bootstrap.framework');

		// Build the class for the label.
		$class = !empty($this->description) ? 'hasTooltip' : '';
		$class = $class . ' required';
		$class = !empty($this->labelClass) ? $class . ' ' . $this->labelClass : $class;
		$modal = '';

		// Add the opening label tag and main attributes.
		$label .= '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->description))
		{
			$label .= ' title="'
				. htmlspecialchars(
					trim($text, ':') . '<br />' . ($this->translateDescription ? JText::_($this->description) : $this->description),
					ENT_COMPAT, 'UTF-8'
				) . '"';
		}

		$privacyarticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

		if ($privacyarticle && JFactory::getApplication()->isClient('site'))
		{
			JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(array('id', 'alias', 'catid', 'language')))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . (int) $privacyarticle);
			$db->setQuery($query);
			$article = $db->loadObject();

			if (JLanguageAssociations::isEnabled())
			{
				$privacyassociated = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $privacyarticle);
			}

			$current_lang = JFactory::getLanguage()->getTag();

			if (isset($privacyassociated) && $current_lang !== $article->language && array_key_exists($current_lang, $privacyassociated))
			{
				$url  = ContentHelperRoute::getArticleRoute($privacyassociated[$current_lang]->id, $privacyassociated[$current_lang]->catid);
				$modalLink = JRoute::_($url . '&tmpl=component&lang=' . $privacyassociated[$current_lang]->language);
			}
			else
			{
				$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
				$url  = ContentHelperRoute::getArticleRoute($slug, $article->catid);
				$modalLink = JRoute::_($url . '&tmpl=component&lang=' . $article->language);
			}

			// Prepare the modal HTML
			$modal = JHtml::_('bootstrap.renderModal', $this->id . '_modal',
				array(
					'title' => htmlspecialchars($text, ENT_COMPAT, 'UTF-8'),
					'url'   => $modalLink,
				)
			);
		}

		// Add the label text and closing tag.
		$label .= '><a href="' . $this->id . '_modal">' . $text . '</a><span class="star">&#160;*</span></label>' . $modal;

		return $label;
	}
}
