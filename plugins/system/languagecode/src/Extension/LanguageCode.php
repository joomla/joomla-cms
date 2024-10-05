<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagecode
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\LanguageCode\Extension;

use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Language Code plugin class.
 *
 * @since  2.5
 */
final class LanguageCode extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRender'        => 'onAfterRender',
            'onContentPrepareForm' => 'onContentPrepareForm',
        ];
    }

    /**
     * Plugin that changes the language code used in the <html /> tag.
     *
     * @param   AfterRenderEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onAfterRender(AfterRenderEvent $event): void
    {
        $app = $event->getApplication();

        // Use this plugin only in site application.
        if ($app->isClient('site')) {
            // Get the response body.
            $body = $app->getBody();

            // Get the current language code.
            $code = $app->getDocument()->getLanguage();

            // Get the new code.
            $new_code  = $this->params->get($code);

            // Replace the old code by the new code in the <html /> tag.
            if ($new_code) {
                // Replace the new code in the HTML document.
                $patterns = [
                    \chr(1) . '(<html.*\s+xml:lang=")(' . $code . ')(".*>)' . \chr(1) . 'i',
                    \chr(1) . '(<html.*\s+lang=")(' . $code . ')(".*>)' . \chr(1) . 'i',
                ];
                $replace = [
                    '${1}' . strtolower($new_code) . '${3}',
                    '${1}' . strtolower($new_code) . '${3}',
                ];
            } else {
                $patterns = [];
                $replace  = [];
            }

            // Replace codes in <link hreflang="" /> attributes.
            preg_match_all(\chr(1) . '(<link.*\s+hreflang=")([0-9a-z\-]*)(".*\s+rel="alternate".*>)' . \chr(1) . 'i', $body, $matches);

            foreach ($matches[2] as $match) {
                $new_code = $this->params->get(strtolower($match));

                if ($new_code) {
                    $patterns[] = \chr(1) . '(<link.*\s+hreflang=")(' . $match . ')(".*\s+rel="alternate".*>)' . \chr(1) . 'i';
                    $replace[]  = '${1}' . $new_code . '${3}';
                }
            }

            preg_match_all(\chr(1) . '(<link.*\s+rel="alternate".*\s+hreflang=")([0-9A-Za-z\-]*)(".*>)' . \chr(1) . 'i', $body, $matches);

            foreach ($matches[2] as $match) {
                $new_code = $this->params->get(strtolower($match));

                if ($new_code) {
                    $patterns[] = \chr(1) . '(<link.*\s+rel="alternate".*\s+hreflang=")(' . $match . ')(".*>)' . \chr(1) . 'i';
                    $replace[]  = '${1}' . $new_code . '${3}';
                }
            }

            // Replace codes in itemprop content
            preg_match_all(\chr(1) . '(<meta.*\s+itemprop="inLanguage".*\s+content=")([0-9A-Za-z\-]*)(".*>)' . \chr(1) . 'i', $body, $matches);

            foreach ($matches[2] as $match) {
                $new_code = $this->params->get(strtolower($match));

                if ($new_code) {
                    $patterns[] = \chr(1) . '(<meta.*\s+itemprop="inLanguage".*\s+content=")(' . $match . ')(".*>)' . \chr(1) . 'i';
                    $replace[]  = '${1}' . $new_code . '${3}';
                }
            }

            $app->setBody(preg_replace($patterns, $replace, $body));
        }
    }

    /**
     * Prepare form.
     *
     * @param   PrepareFormEvent  $event  The event object
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $form = $event->getForm();

        // Check we are manipulating the languagecode plugin.
        if ($form->getName() !== 'com_plugins.plugin' || !$form->getField('languagecodeplugin', 'params')) {
            return;
        }

        // Get site languages.
        if ($languages = LanguageHelper::getKnownLanguages(JPATH_SITE)) {
            // Inject fields into the form.
            foreach ($languages as $tag => $language) {
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
									label="' . $tag . '"
									description="' . htmlspecialchars(Text::sprintf('PLG_SYSTEM_LANGUAGECODE_FIELD_DESC', $language['name']), ENT_COMPAT, 'UTF-8') . '"
									translate_description="false"
									translate_label="false"
									size="7"
									filter="cmd"
								/>
							</fieldset>
						</fields>
					</form>');
            }
        }
    }
}
