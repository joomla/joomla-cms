<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagecode
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Language Code plugin class.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.languagecode
 * @since       2.5
 */
class PlgSystemLanguagecode extends JPlugin
{
	/**
	 * Plugin that change the language code used in the <html /> tag
	 *
	 * @since  2.5
	 */
	public function onAfterRender()
	{
		// Use this plugin only in site application
		if (JFactory::getApplication()->isSite())
		{
			// Get the response body
			$body = JResponse::getBody();

			// Get the current language code
			$code = JFactory::getDocument()->getLanguage();

			// Get the new code
			$new_code  = $this->params->get($code);

			// Replace the old code by the new code in the <html /> tag
			if ($new_code)
			{
				// Replace the new code in the HTML document
				$patterns = array(
					chr(1) . '(<html.*\s+xml:lang=")(' . $code . ')(".*>)' . chr(1) . 'i',
					chr(1) . '(<html.*\s+lang=")(' . $code . ')(".*>)' . chr(1) . 'i',
				);
				$replace = array(
					'${1}' . strtolower($new_code) . '${3}',
					'${1}' . strtolower($new_code) . '${3}'
				);
			}
			else
			{
				$patterns = array();
				$replace = array();
			}

			// Replace codes in <link hreflang="" /> attributes
			preg_match_all(chr(1) . '(<link.*\s+hreflang=")([0-9a-z\-]*)(".*\s+rel="alternate".*/>)' . chr(1) . 'i', $body, $matches);
			foreach ($matches[2] as $match)
			{
				$new_code = $this->params->get(strtolower($match));
				if ($new_code)
				{
					$patterns[] = chr(1) . '(<link.*\s+hreflang=")(' . $match . ')(".*\s+rel="alternate".*/>)' . chr(1) . 'i';
					$replace[] = '${1}' . $new_code . '${3}';
				}
			}
			preg_match_all(chr(1) . '(<link.*\s+rel="alternate".*\s+hreflang=")([0-9A-Za-z\-]*)(".*/>)' . chr(1) . 'i', $body, $matches);
			foreach ($matches[2] as $match)
			{
				$new_code = $this->params->get(strtolower($match));
				if ($new_code)
				{
					$patterns[] = chr(1) . '(<link.*\s+rel="alternate".*\s+hreflang=")(' . $match . ')(".*/>)' . chr(1) . 'i';
					$replace[] = '${1}' . $new_code . '${3}';
				}
			}
			JResponse::setBody(preg_replace($patterns, $replace, $body));
		}
	}

	/**
	 * @param   JForm	$form	The form to be altered.
	 * @param   array  $data	The associated data for the form.
	 *
	 * @return  boolean
	 * @since	2.5
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Ensure that data is an object
		$data = (object) $data;

		// Check we have a form
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		$app = JFactory::getApplication();
		if ($form->getName() != 'com_plugins.plugin'
			|| isset($data->name) && $data->name != 'plg_system_languagecode'
			|| empty($data) && !$app->getUserState('plg_system_language_code.edit')
		)
		{
			return true;
		}

		// Mark the plugin as being edited
		$app->setUserState('plg_system_language_code.edit', $data->name == 'plg_system_languagecode');

		// Get site languages
		if ($languages = JLanguage::getKnownLanguages(JPATH_SITE))
		{
			// Inject fields into the form
			foreach ($languages as $tag => $language)
			{
				$form->load('
					<form>
						<fields name="params">
							<fieldset
								name="languagecode"
								label="PLG_SYSTEM_LANGUAGECODE_FIELDSET_LABEL"
								description="PLG_SYSTEM_LANGUAGECODE_FIELDSET_DESC"
							>
								<field
									name="'.strtolower($tag).'"
									type="text"
									description="' . htmlspecialchars(JText::sprintf('PLG_SYSTEM_LANGUAGECODE_FIELD_DESC', $language['name']), ENT_COMPAT, 'UTF-8') . '"
									translate_description="false"
									label="' . $tag . '"
									translate_label="false"
									size="7"
									filter="cmd"
								/>
							</fieldset>
						</fields>
					</form>
				');
			}
		}

		return true;
	}
}
