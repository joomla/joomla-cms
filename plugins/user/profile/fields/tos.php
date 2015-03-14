<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
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

		// Add CSS and JS for the TOS field
		$doc = JFactory::getDocument();
		$css = "#jform_profile_tos {width: 18em; margin: 0 !important; padding: 0 2px !important;}
				#jform_profile_tos input {margin:0 5px 0 0 !important; width:10px !important;}
				#jform_profile_tos label {margin:0 15px 0 0 !important; width:auto;}
				";
		$doc->addStyleDeclaration($css);
		JHtml::_('behavior.modal');

		// Build the class for the label.
		$class = !empty($this->description) ? 'hasTooltip' : '';
		$class = $class . ' required';
		$class = !empty($this->labelClass) ? $class . ' ' . $this->labelClass : $class;

		// Add the opening label tag and main attributes attributes.
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

		$tosarticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

		$link = '';

		if ($tosarticle)
		{
			JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

			$attribs = array();
			$attribs['class'] = 'modal';
			$attribs['rel'] = '{handler: \'iframe\', size: {x:800, y:500}}';

			// TODO: This is broken!! We need the category ID, too, and the language
			$url = ContentHelperRoute::getArticleRoute($tosarticle);

			$link = JHtml::_('link', JRoute::_($url . '&tmpl=component'), $text, $attribs);
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
