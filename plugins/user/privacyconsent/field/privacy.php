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

		JHtml::_('behavior.modal');

		// Build the class for the label.
		$class = !empty($this->description) ? 'hasTooltip' : '';
		$class = $class . ' required';
		$class = !empty($this->labelClass) ? $class . ' ' . $this->labelClass : $class;

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

			$attribs          = array();
			$attribs['class'] = 'modal';
			$attribs['rel']   = '{handler: \'iframe\', size: {x:800, y:500}}';

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quotename(array('id, alias, catid, language')))
				->from($db->quotename('#__content'))
				->where($db->quotename('id') . ' = ' . (int) $privacyarticle);			
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
				$link = JHtml::_('link', JRoute::_($url . '&tmpl=component&lang=' . $privacyassociated[$current_lang]->language), $text, $attribs);
			}
			else
			{
				$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
				$url  = ContentHelperRoute::getArticleRoute($slug, $article->catid);
				$link = JHtml::_('link', JRoute::_($url . '&tmpl=component&lang=' . $article->language), $text, $attribs);
			}
		}
		else
		{
			$link = $text;
		}

		// Add the label text and closing tag.
		$label .= '>' . $link . '<span class="star">&#160;*</span></label>';

		return $label;
	}
}
