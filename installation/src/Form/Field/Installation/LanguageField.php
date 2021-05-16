<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Form\Field\Installation;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Language\LanguageHelper;

/**
 * Installation Language field.
 *
 * @since  1.6
 */
class LanguageField extends ListField
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
		$app = Factory::getApplication();

		// Detect the native language.
		$native = LanguageHelper::detectLanguage();

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
		$model   = new SetupModel;
		$options = $model->getOptions();

		if (isset($options['language']))
		{
			$native = $options['language'];
		}

		// Get the list of available languages.
		$options = LanguageHelper::createLanguageList($native);

		// Fix wrongly set parentheses in RTL languages
		if (Factory::getLanguage()->isRtl())
		{
			foreach ($options as &$option)
			{
				$option['text'] .= '&#x200E;';
			}
		}

		if (!$options || $options instanceof \Exception)
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
	 * @param   array  $a  The first value to determine sort
	 * @param   array  $b  The second value to determine sort
	 *
	 * @return  integer
	 *
	 * @since   3.1
	 */
	protected function _sortLanguages($a, $b)
	{
		return strcmp($a['text'], $b['text']);
	}
}
