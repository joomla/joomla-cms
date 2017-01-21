<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagecode
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Language Code plugin class.
 *
 * @since  2.5
 */
class PlgSystemLanguagecode extends JPlugin
{
	/**
	 * Plugin that changes the language code used in the <html /> tag.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		// Use this plugin only in site application.
		if ($app->isClient('site'))
		{
			// Get the response body.
			$body = $app->getBody();

			// Get the current language code.
			$code = JFactory::getDocument()->getLanguage();

			// Get the new code.
			$new_code  = $this->params->get($code);

			// Replace the old code by the new code in the <html /> tag.
			if ($new_code)
			{
				// Replace the new code in the HTML document.
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
				$replace  = array();
			}

			// Replace codes in <link hreflang="" /> attributes.
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

			// Replace codes in itemprop content
			preg_match_all(chr(1) . '(<meta.*\s+itemprop="inLanguage".*\s+content=")([0-9A-Za-z\-]*)(".*/>)' . chr(1) . 'i', $body, $matches);

			foreach ($matches[2] as $match)
			{
				$new_code = $this->params->get(strtolower($match));

				if ($new_code)
				{
					$patterns[] = chr(1) . '(<meta.*\s+itemprop="inLanguage".*\s+content=")(' . $match . ')(".*/>)' . chr(1) . 'i';
					$replace[] = '${1}' . $new_code . '${3}';
				}
			}

			$app->setBody(preg_replace($patterns, $replace, $body));
		}
	}

	/**
	 * Prepare form.
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since	2.5
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Check we have a form.
		if (!($form instanceof JForm))
		{
			throw new RuntimeException(JText::_('JERROR_NOT_A_FORM'), 500);
		}

		// Check we are manipulating the languagecode plugin.
		if ($form->getName() !== 'com_plugins.plugin' || !$form->getField('languagecodeplugin', 'params'))
		{
			return true;
		}

		// Get site languages.
		if ($languages = JLanguageHelper::getKnownLanguages(JPATH_SITE))
		{
			// Inject fields into the form.
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
									name="' . strtolower($tag) . '"
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
