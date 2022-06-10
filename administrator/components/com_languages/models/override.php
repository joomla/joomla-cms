<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Languages Override Model
 *
 * @since  2.5
 */
class LanguagesModelOverride extends JModelAdmin
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed A JForm object on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_languages.override', 'override', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$client   = $this->getState('filter.client', 'site');
		$language = $this->getState('filter.language', 'en-GB');
		$langName = JLanguage::getInstance($language)->getName();

		if (!$langName)
		{
			// If a language only exists in frontend, its metadata cannot be
			// loaded in backend at the moment, so fall back to the language tag.
			$langName = $language;
		}

		$form->setValue('client', null, JText::_('COM_LANGUAGES_VIEW_OVERRIDE_CLIENT_' . strtoupper($client)));
		$form->setValue('language', null, JText::sprintf('COM_LANGUAGES_VIEW_OVERRIDE_LANGUAGE', $langName, $language));
		$form->setValue('file', null, JPath::clean(constant('JPATH_' . strtoupper($client)) . '/language/overrides/' . $language . '.override.ini'));

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed The data for the form.
	 *
	 * @since   2.5
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_languages.edit.override.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_languages.override', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   string  $pk  The key name.
	 *
	 * @return  mixed  	Object on success, false otherwise.
	 *
	 * @since   2.5
	 */
	public function getItem($pk = null)
	{
		$input    = JFactory::getApplication()->input;
		$pk       = !empty($pk) ? $pk : $input->get('id');
		$fileName = constant('JPATH_' . strtoupper($this->getState('filter.client')))
			. '/language/overrides/' . $this->getState('filter.language', 'en-GB') . '.override.ini';
		$strings  = JLanguageHelper::parseIniFile($fileName);

		$result = new stdClass;
		$result->key      = '';
		$result->override = '';

		if (isset($strings[$pk]))
		{
			$result->key      = $pk;
			$result->override = $strings[$pk];
		}

		$oppositeFileName = constant('JPATH_' . strtoupper($this->getState('filter.client') == 'site' ? 'administrator' : 'site'))
			. '/language/overrides/' . $this->getState('filter.language', 'en-GB') . '.override.ini';
		$oppositeStrings  = JLanguageHelper::parseIniFile($oppositeFileName);
		$result->both = isset($oppositeStrings[$pk]) && ($oppositeStrings[$pk] == $strings[$pk]);

		return $result;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array    $data            The form data.
	 * @param   boolean  $oppositeClient  Indicates whether the override should not be created for the current client.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   2.5
	 */
	public function save($data, $oppositeClient = false)
	{
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();

		$client   = $app->getUserState('com_languages.overrides.filter.client', 0);
		$language = $app->getUserState('com_languages.overrides.filter.language', 'en-GB');

		// If the override should be created for both.
		if ($oppositeClient)
		{
			$client = 1 - $client;
		}

		// Return false if the constant is a reserved word, i.e. YES, NO, NULL, FALSE, ON, OFF, NONE, TRUE
		$blacklist = array('YES', 'NO', 'NULL', 'FALSE', 'ON', 'OFF', 'NONE', 'TRUE');

		if (in_array($data['key'], $blacklist))
		{
			$this->setError(JText::_('COM_LANGUAGES_OVERRIDE_ERROR_RESERVED_WORDS'));

			return false;
		}

		$client = $client ? 'administrator' : 'site';

		// Parse the override.ini file in oder to get the keys and strings.
		$fileName = constant('JPATH_' . strtoupper($client)) . '/language/overrides/' . $language . '.override.ini';
		$strings  = JLanguageHelper::parseIniFile($fileName);

		if (isset($strings[$data['id']]))
		{
			// If an existent string was edited check whether
			// the name of the constant is still the same.
			if ($data['key'] == $data['id'])
			{
				// If yes, simply override it.
				$strings[$data['key']] = $data['override'];
			}
			else
			{
				// If no, delete the old string and prepend the new one.
				unset($strings[$data['id']]);
				$strings = array($data['key'] => $data['override']) + $strings;
			}
		}
		else
		{
			// If it is a new override simply prepend it.
			$strings = array($data['key'] => $data['override']) + $strings;
		}

		// Write override.ini file with the strings.
		if (JLanguageHelper::saveToIniFile($fileName, $strings) === false)
		{
			return false;
		}

		// If the override should be stored for both clients save
		// it also for the other one and prevent endless recursion.
		if (isset($data['both']) && $data['both'] && !$oppositeClient)
		{
			return $this->save($data, true);
		}

		return true;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		$client = $app->getUserStateFromRequest('com_languages.overrides.filter.client', 'filter_client', 0, 'int') ? 'administrator' : 'site';
		$this->setState('filter.client', $client);

		$language = $app->getUserStateFromRequest('com_languages.overrides.filter.language', 'filter_language', 'en-GB', 'cmd');
		$this->setState('filter.language', $language);
	}
}
