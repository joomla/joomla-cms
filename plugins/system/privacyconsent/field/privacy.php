<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JFormHelper::loadFieldClass('radio');

/**
 * Provides input for privacy
 *
 * @since  3.9.0
 */
class JFormFieldprivacy extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $type = 'privacy';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   3.9.0
	 */
	protected function getInput()
	{
		$privacynote = !empty($this->element['note']) ? $this->element['note'] : Text::_('PLG_SYSTEM_PRIVACYCONSENT_NOTE_FIELD_DEFAULT');

		echo '<div class="alert alert-info">' . $privacynote . '</div>';

		return parent::getInput();
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.9.0
	 */
	protected function getLabel()
	{
		if ($this->hidden)
		{
			return '';
		}

		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
		$text = $this->translateLabel ? Text::_($text) : $text;

		// Set required to true as this field is not displayed at all if not required.
		$this->required = true;

		JHtml::_('behavior.modal');

		// Build the class for the label.
		$class = !empty($this->description) ? 'hasPopover' : '';
		$class = $class . ' required';
		$class = !empty($this->labelClass) ? $class . ' ' . $this->labelClass : $class;

		// Add the opening label tag and main attributes.
		$label = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->description))
		{
			$label .= ' title="' . htmlspecialchars(trim($text, ':'), ENT_COMPAT, 'UTF-8') . '"';
			$label .= ' data-content="' . htmlspecialchars(
				$this->translateDescription ? Text::_($this->description) : $this->description,
				ENT_COMPAT,
				'UTF-8'
			) . '"';
		}

		if (Factory::getLanguage()->isRtl())
		{
			$label .= ' data-placement="left"';
		}

		$privacyArticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

		if ($privacyArticle && Factory::getApplication()->isClient('site'))
		{
			JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

			$attribs          = array();
			$attribs['class'] = 'modal';
			$attribs['rel']   = '{handler: \'iframe\', size: {x:800, y:500}}';

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(array('id', 'alias', 'catid', 'language')))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . (int) $privacyArticle);
			$db->setQuery($query);
			$article = $db->loadObject();

			$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
			$url  = ContentHelperRoute::getArticleRoute($slug, $article->catid, $article->language);
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
