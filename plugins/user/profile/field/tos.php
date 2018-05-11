<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;

/**
 * Provides input for TOS
 *
 * @since  2.5.5
 */
class JFormFieldTos extends \Joomla\CMS\Form\Field\RadioField
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
		$text = $this->translateLabel ? Text::_($text) : $text;

		// Set required to true as this field is not displayed at all if not required.
		$this->required = true;

		HTMLHelper::_('behavior.modal');

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
					trim($text, ':') . '<br>' . ($this->translateDescription ? Text::_($this->description) : $this->description),
					ENT_COMPAT, 'UTF-8'
				) . '"';
		}

		$tosarticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

		if ($tosarticle)
		{
			JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

			$attribs          = array();
			$attribs['class'] = 'modal';
			$attribs['rel']   = '{handler: \'iframe\', size: {x:800, y:500}}';

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, alias, catid, language')
				->from('#__content')
				->where('id = ' . $tosarticle);
			$db->setQuery($query);
			$article = $db->loadObject();

			if (Associations::isEnabled())
			{
				$tosassociated = Associations::getAssociations('com_content', '#__content', 'com_content.item', $tosarticle);
			}

			$current_lang = Factory::getLanguage()->getTag();

			if (isset($tosassociated) && $current_lang !== $article->language && array_key_exists($current_lang, $tosassociated))
			{
				$url  = ContentHelperRoute::getArticleRoute($tosassociated[$current_lang]->id, $tosassociated[$current_lang]->catid);
				$link = HTMLHelper::_('link', Route::_($url . '&tmpl=component&lang=' . $tosassociated[$current_lang]->language), $text, $attribs);
			}
			else
			{
				$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
				$url  = ContentHelperRoute::getArticleRoute($slug, $article->catid);
				$link = HTMLHelper::_('link', Route::_($url . '&tmpl=component&lang=' . $article->language), $text, $attribs);
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
