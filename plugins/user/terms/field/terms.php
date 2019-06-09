<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.terms
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

FormHelper::loadFieldClass('radio');

/**
 * Provides input for privacyterms
 *
 * @since  3.9.0
 */
class JFormFieldterms extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $type = 'terms';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   3.9.0
	 */
	protected function getInput()
	{
		$termsnote = !empty($this->element['note']) ? $this->element['note'] : Text::_('PLG_USER_TERMS_NOTE_FIELD_DEFAULT');

		echo '<div class="alert alert-info">' . $termsnote . '</div>';

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

		// Set required to true
		$this->required = true;

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

		$termsArticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

		if ($termsArticle && Factory::getApplication()->isClient('site'))
		{
			JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

			$attribs                = [];
			$attribs['data-toggle'] = 'modal';
			$attribs['data-target'] = '#termsModal';

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(array('id', 'alias', 'catid', 'language')))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . (int) $termsArticle);
			$db->setQuery($query);
			$article = $db->loadObject();

			if (Associations::isEnabled())
			{
				$termsAssociated = Associations::getAssociations('com_content', '#__content', 'com_content.item', $termsArticle);
			}

			$currentLang = Factory::getLanguage()->getTag();

			if (isset($termsAssociated) && $currentLang !== $article->language && array_key_exists($currentLang, $termsAssociated))
			{
				$url  = ContentHelperRoute::getArticleRoute(
					$termsAssociated[$currentLang]->id,
					$termsAssociated[$currentLang]->catid,
					$termsAssociated[$currentLang]->language
				);
				$link = HTMLHelper::_('link', Route::_($url . '&tmpl=component'), $text, $attribs);
			}
			else
			{
				$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
				$url  = ContentHelperRoute::getArticleRoute($slug, $article->catid, $article->language);
				$link = HTMLHelper::_('link', Route::_($url . '&tmpl=component'), $text, $attribs);
			}

			echo HTMLHelper::_(
				'bootstrap.renderModal',
				'termsModal',
				array(
					'url'    => Route::_($url . '&tmpl=component'),
					'title'  => $text,
					'height' => '100%',
					'width'  => '100%',
					'modalWidth'  => '800',
					'bodyHeight'  => '500',
					'footer' => '<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
						. Text::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
				)
			);
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
