<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.confirmconsent
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
 * Consentbox Field class for the Confirm Consent Plugin.
 *
 * @since  3.9.1
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
	 * @var    integer
	 * @since  3.9.1
	 */
	protected $articleid;

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.9.1
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'articleid':
				$this->articleid = (int) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.9.1
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'articleid':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.9.1
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->articleid = (int) $this->element['articleid'];
		}

		return $return;
	}

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

		// When we have an article let's add the modal and make the title clickable
		if ($data['articleid'])
		{
			$attribs['data-toggle'] = 'modal';

			$data['label'] = HTMLHelper::_(
				'link',
				'#modal-' . $this->id,
				$data['label'],
				$attribs
			);
		}

		// Here mainly for B/C with old layouts. This can be done in the layouts directly
		$extraData = array(
			'text'     => $data['label'],
			'for'      => $this->id,
			'classes'  => explode(' ', $data['labelclass']),
			'position' => $position,
		);

		return $this->getRenderer($this->renderLabelLayout)->render(array_merge($data, $extraData));
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   array  $options  Options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the html for the control group
	 *
	 * @since   3.9.1
	 */
	public function renderField($options = array())
	{
		$modalHtml  = '';
		$layoutData = $this->getLayoutData();

		if ($this->articleid)
		{
			$modalParams['title']  = $layoutData['label'];
			$modalParams['url']    = $this->getAssignedArticleUrl();
			$modalParams['height'] = 800;
			$modalParams['width']  = '100%';
			$modalHtml = HTMLHelper::_('bootstrap.renderModal', 'modal-' . $this->id, $modalParams);
		}

		return $modalHtml . parent::renderField($options);
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
			'articleid' => (integer) $this->articleid,
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Return the url of the assigned article based on the current user language
	 *
	 * @return  string  Returns the link to the article
	 *
	 * @since   3.9.1
	 */
	private function getAssignedArticleUrl()
	{
		$db = Factory::getDbo();

		// Get the info from the article
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'catid', 'language')))
			->from($db->quoteName('#__content'))
			->where($db->quoteName('id') . ' = ' . (int) $this->articleid);
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
				. $this->articleid . '&tmpl=component'
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
