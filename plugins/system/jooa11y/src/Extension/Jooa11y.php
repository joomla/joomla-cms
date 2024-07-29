<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.jooa11y
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Jooa11y\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Jooa11y plugin to add an accessibility checker
 *
 * @since  4.1.0
 */
final class Jooa11y extends CMSPlugin implements SubscriberInterface
{
    /**
     * Subscribe to certain events
     *
     * @return string[]  An array of event mappings
     *
     * @since 4.1.0
     *
     * @throws \Exception
     */
    public static function getSubscribedEvents(): array
    {
        return ['onBeforeCompileHead' => 'initJooa11y'];
    }

    /**
     * Method to check if the current user is allowed to see the debug information or not.
     *
     * @return  boolean  True if access is allowed.
     *
     * @since   4.1.0
     */
    private function isAuthorisedDisplayChecker(): bool
    {
        static $result;

        if (\is_bool($result)) {
            return $result;
        }

        // If the user is not allowed to view the output then end here.
        $filterGroups = (array) $this->params->get('filter_groups', []);

        if (!empty($filterGroups)) {
            $userGroups = $this->getApplication()
                ->getIdentity()
                ->get('groups');

            if (!array_intersect($filterGroups, $userGroups)) {
                $result = false;
                return $result;
            }
        }

        $result = true;
        return $result;
    }

    /**
     * Add the checker.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function initJooa11y()
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        // Check if we are in a preview modal or the plugin has enforced loading
        $showJooa11y = $this->getApplication()
            ->getInput()
            ->get('jooa11y', $this->params->get('showAlways', 0));

        // Load the checker if authorised
        if (!$showJooa11y || !$this->isAuthorisedDisplayChecker()) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        // Detect the current active language
        $getLang = $this->getApplication()
            ->getLanguage()
            ->getTag();

        // Get the right locale
        $splitLang = explode('-', $getLang);
        $lang      = $splitLang[0];
        $country   = $splitLang[1] ?? '';

        // Sa11y is available in the following languages
        $supportedLang = [
            'bg',
            'cs',
            'da',
            'de',
            'el',
            'en',
            'es',
            'et',
            'fi',
            'fr',
            'hu',
            'id',
            'it',
            'ja',
            'ko',
            'lt',
            'lv',
            'nb',
            'nl',
            'pl',
            'pt',
            'ro',
            'sl',
            'sk',
            'sv',
            'tr',
            'uk',
            'ua',
            'zh',
        ];

        // Check if Sa11y supports language
        if (!\in_array($lang, $supportedLang)) {
            $lang = 'en';
        } elseif ($lang === 'pt') {
            $lang = $country === 'BR' ? 'ptBR' : 'ptPT';
        } elseif ($lang === 'uk') {
            $lang = 'ua';
        } elseif ($lang === 'en') {
            $lang = $country === 'US' ? 'enUS' : 'en';
        }

        // Get the document object
        $document = $this->getApplication()->getDocument();

        // Get plugin options from xml
        $getOptions = [
            'checkRoot'           => $this->params->get('checkRoot', 'main'),
            'readabilityRoot'     => $this->params->get('readabilityRoot', 'main'),
            'containerIgnore'     => $this->params->get('containerIgnore'),
            'contrastPlugin'      => $this->params->get('contrastPlugin', 1),
            'formLabelsPlugin'    => $this->params->get('formLabelsPlugin', 1),
            'linksAdvancedPlugin' => $this->params->get('linksAdvancedPlugin', 1),
            'colourFilterPlugin'  => $this->params->get('colourFilterPlugin', 1),
            'checkAllHideToggles' => $this->params->get('additionalChecks', 0),
            'shadowComponents'    => $this->params->get('shadowComponents'),
        ];
        $getExtraProps = $this->params->get('extraProps', []);


        // Process extra props
        $extraProps = [];
        foreach ($getExtraProps as $prop) {
            $decodedValue = json_decode($prop->value);
            if (is_numeric($decodedValue) || \is_bool($decodedValue)) {
                $extraProps[$prop->key] = $decodedValue;
            } else {
                $extraProps[$prop->key] = "{$prop->value}";
            }
        }

        // Merge all options together and add to page
        $allOptions = array_merge($getOptions, $extraProps);
        $document->addScriptOptions('jooa11yOptions', $allOptions);

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa*/
        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('plg_system_jooa11y');

        // Load scripts and instantiate
        $wa->useStyle('sa11y')
            ->useScript('sa11y')
            ->registerAndUseScript(
                'sa11y-lang',
                'vendor/sa11y/' . $lang . '.js',
                ['importmap' => true]
            )
            ->useScript('plg_system_jooa11y.jooa11y');
    }
}
