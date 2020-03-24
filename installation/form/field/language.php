<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Installation Language field.
 *
 * @since  1.6
 */
class InstallationFormFieldLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Language';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$app = JFactory::getApplication();

		// Detect the native language.
		$native = JLanguageHelper::detectLanguage();

		if (empty($native))
		{
			$native = 'en-GB';
		}

		// Get a forced language if it exists.
		$forced = $app->getLocalise();

		if (!empty($forced['language']))
		{
			$native = $forced['language'];
		}

		// If a language is already set in the session, use this instead
		$model   = new InstallationModelSetup;
		$options = $model->getOptions();

		if (isset($options['language']))
		{
			$native = $options['language'];
		}

		// Get the list of available languages.
		$options = JLanguageHelper::createLanguageList($native);

		// Fix wrongly set parentheses in RTL languages
		if (JFactory::getLanguage()->isRtl())
		{
			foreach ($options as &$option)
			{
				$option['text'] .= '&#x200E;';
			}
		}

		if (!$options || $options  instanceof Exception)
		{
			$options = array();
		}
		// Sort languages by name
		else
		{
			usort($options, array($this, '_sortLanguages'));
		}

		// Set the default value from the native language.
		$this->value = $native;

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to sort languages by name.
	 *
	 * @param   string  $a  The first value to determine sort
	 * @param   string  $b  The second value to determine sort
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function _sortLanguages($a, $b)
	{
		return strcmp($a['text'], $b['text']);
	}
}
