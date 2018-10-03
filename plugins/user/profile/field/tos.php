<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('radio');

/**
 * Provides input for TOS
 *
 * @since  2.5.5
 */
class JFormFieldTos extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5.5
	 */
	protected $type = 'Tos';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   2.5.5
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

		// Build the class for the label.
		$class = !empty($this->description) ? 'hasPopover' : '';
		$class = $class . ' required';
		$class = !empty($this->labelClass) ? $class . ' ' . $this->labelClass : $class;

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->description))
		{
			$label .= ' title="' . htmlspecialchars(trim($text, ':'), ENT_COMPAT, 'UTF-8') . '"';
			$label .= ' data-content="' . htmlspecialchars(
				$this->translateDescription ? JText::_($this->description) : $this->description,
				ENT_COMPAT,
				'UTF-8'
			) . '"';

			if (JFactory::getLanguage()->isRtl())
			{
				$label .= ' data-placement="left"';
			}
		}

		$tosArticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

		if ($tosArticle)
		{
			JHtml::_('behavior.modal');
			JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

			$attribs          = array();
			$attribs['class'] = 'modal';
			$attribs['rel']   = '{handler: \'iframe\', size: {x:800, y:500}}';

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, alias, catid, language')
				->from('#__content')
				->where('id = ' . $tosArticle);
			$db->setQuery($query);
			$article = $db->loadObject();

			if (JLanguageAssociations::isEnabled())
			{
				$tosAssociated = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $tosArticle);
			}

			$currentLang = JFactory::getLanguage()->getTag();

			if (isset($tosAssociated) && $currentLang !== $article->language && array_key_exists($currentLang, $tosAssociated))
			{
				$url = ContentHelperRoute::getArticleRoute(
					$tosAssociated[$currentLang]->id,
					$tosAssociated[$currentLang]->catid,
					$tosAssociated[$currentLang]->language
				);

				$link = JHtml::_('link', JRoute::_($url . '&tmpl=component'), $text, $attribs);
			}
			else
			{
				$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
				$url  = ContentHelperRoute::getArticleRoute($slug, $article->catid, $article->language);
				$link = JHtml::_('link', JRoute::_($url . '&tmpl=component'), $text, $attribs);
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
