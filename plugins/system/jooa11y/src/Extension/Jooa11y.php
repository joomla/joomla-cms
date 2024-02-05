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
   * @throws Exception
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
      $userGroups = $this->getApplication()->getIdentity()->get('groups');

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
    $showJooa11y = $this->getApplication()->getInput()->get('jooa11y', $this->params->get('showAlways', 0));

    // Load the checker if authorised
    if (!$showJooa11y || !$this->isAuthorisedDisplayChecker()) {
      return;
    }

    // Load translations
    $this->loadLanguage();

    // Detect the current active language
    $getLang = $this->getApplication()->getLanguage()->getTag();

    // Get the right locale
    $splitLang = explode('-', $getLang);
    $lang = $splitLang[0];
    $country = isset($parts[1]) ? $parts[1] : '';

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
      'ua',
      'zh',
    ];

    // Check if Sa11y supports language
    if (!in_array($lang, $supportedLang)) {
      $lang = "en";
    } else if ($lang === "pt") {
      $lang = ($country === "br") ? "ptBR" : "ptPT";
    } else if ($lang === "uk") {
      $lang = "ua";
    } else if ($lang === "en") {
      $lang = ($country === "us") ? "enUS" : "en";
    }

    // Sa11y language file name
    $sa11yLang = 'Sa11yLang' . ucfirst($lang);

    // Get the document object
    $document = $this->getApplication()->getDocument();

    // Prepare `extraProps` into JSON.
    function prepareExtraProps($extraProps)
    {
      $pairs = explode(',', $extraProps);
      $data = [];

      foreach ($pairs as $pair) {
        list($property, $value) = array_map('trim', explode(':', $pair, 2));

        // Replace single quotes
        $value = trim($value, "'");

        // Handle booleans
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        // Remove special chars
        $property = preg_replace('/[^a-zA-Z0-9_]/', '', $property);
        $value = preg_replace('/[^a-zA-Z0-9_]/', '', $value);

        $data[$property] = $value;
      }
      return json_encode($data, JSON_NUMERIC_CHECK);
    }
    $extraPropsJSON = prepareExtraProps($this->params->get('extraProps'));

    // Add plugin settings from the xml
    $document->addScriptOptions(
      'jooa11yOptions',
      [
        'checkRoot' => $this->params->get('checkRoot', 'main'),
        'readabilityRoot' => $this->params->get('readabilityRoot', 'main'),
        'containerIgnore' => $this->params->get('containerIgnore'),
        'contrastPlugin' => $this->params->get('contrastPlugin'),
        'formLabelsPlugin' => $this->params->get('formLabelsPlugin'),
        'linksAdvancedPlugin' => $this->params->get('linksAdvancedPlugin'),
        'colourFilterPlugin' => $this->params->get('colourFilterPlugin'),
        'checkAllHideToggles' => $this->params->get('additionalChecks'),
        'shadowComponents' => $this->params->get('shadowComponents'),
      ],
    );

    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa*/
    $wa = $document->getWebAssetManager();

    // Load scripts and instantiate
    $wa->useScript('sa11y');
    $wa->useScript($sa11yLang);
    $wa->useStyle('sa11yCSS');
    $wa->addInlineScript(
      <<<EOT
        (() => {
          Sa11y.Lang.addI18n($sa11yLang.strings);
          const options = Joomla.getOptions('jooa11yOptions');
          const extraProps = $extraPropsJSON;
          const allOptions = Object.assign({}, options, extraProps);
          window.addEventListener('load', () => {
            const sa11y = new Sa11y.Sa11y(allOptions);
          });
        })();
      EOT,
    );

    return true;
  }
}
