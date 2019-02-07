<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.terms
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

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
		// Display the message before the field
		echo $this->getRenderer('plugins.user.terms.message')->render($this->getLayoutData());

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

		return $this->getRenderer('plugins.user.terms.label')->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();


		$extraData = array(
			'termsnote' => !empty($this->element['note']) ? $this->element['note'] : Text::_('PLG_USER_TERMS_NOTE_FIELD_DEFAULT'),
			'options' => $this->getOptions(),
			'value'   => (string) $this->value,
			'translateLabel' => $this->translateLabel,
			'translateDescription' => $this->translateDescription,
			'translateHint' => $this->translateHint,
			'termsArticle' => $this->element['article'] > 0 ? (int) $this->element['article'] : 0,
		);

		return array_merge($data, $extraData);
	}
}
