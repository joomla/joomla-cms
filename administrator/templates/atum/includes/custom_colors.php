<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;

defined('_JEXEC') or die;

/**
 * @var \Joomla\CMS\WebAsset\WebAssetManager $wa
 * @var \Joomla\CMS\Document\HtmlDocument    $this
 */

/**
 * Applies the Atum custom colors and the optional Dark Mode.
 *
 * @param   \Joomla\CMS\WebAsset\WebAssetManager  $wa    The Web Asset Manager.
 * @param   string                                $hue   The hue for the template accent color.
 * @param   bool                                  $dark  Is this about the Dark Mode colors?
 *
 * @since  __DEPLOY_VERSION__
 */
$atumApplyCustomColor = function (\Joomla\CMS\WebAsset\WebAssetManager $wa, string $hue, bool $dark = false) {
    if (empty($hue)) {
        return;
    }

    $paramPrefix = $dark ? 'dark-' : '';

    $bgLight      = $this->params->get($paramPrefix . 'bg-light') ?? '';
    $textDark     = $this->params->get($paramPrefix . 'text-dark') ?? '';
    $textLight    = $this->params->get($paramPrefix . 'text-light') ?? '';
    $linkColor    = $this->params->get($paramPrefix . 'link-color') ?? '';
    $specialColor = $this->params->get($paramPrefix . 'special-color') ?? '';

    // Add CSS variables for the custom colors
    $css = ":root{\n";
    $css .= "\t--hue: $hue;\n";

    if (!empty(trim($bgLight))) {
        $css .= "\t--template-bg-light: $bgLight;\n";
    }

    if (!empty(trim($textDark))) {
        $css .= "\t--template-text-dark: $textDark;\n";
    }

    if (!empty($textLight)) {
        $css .= "\t--template-text-light: $textLight;\n";
    }


    if (!empty($linkColor)) {
        $css .= "\t--template-link-color: $linkColor;\n";
    }


    if (!empty($specialColor)) {
        $css .= "\t--template-special-color: $specialColor;\n";
    }

    $css .= "}";

    if ($dark) {
        $css = "@media (prefers-color-scheme: dark) {\n$css\n}";
    }

    $wa->addInlineStyle($css);
};

// Get the hue value and apply Light Mode colors
preg_match(
    '#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i',
    $this->params->get('hue', 'hsl(214, 63%, 20%)'),
    $matches
);

$atumApplyCustomColor($wa, $matches[1]);

// Conditionally apply Dark Mode
if ($this->params->get('darkmode', 1) == 1) {
    call_user_func(function () use ($atumApplyCustomColor, $wa) {
        // Load the Dark Mode CSS
        $wa->useStyle('template.darkmode');

        // Get the Dark Mode hue value
        preg_match(
            '#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i',
            $this->params->get('dark-hue', 'hsl(214, 63%, 20%)'),
            $matches
        );

        // Apply Dark Mode colors for the template
        $atumApplyCustomColor($wa, $matches[1], true);

        // If Dark Mode support for editors is disabled, exit early.
        if ($this->params->get('darkmode_editors', 1) != 1) {
            return;
        }

        // Hard-coded Dark Mode support for the TinyMCE editor
        call_user_func(function () {
            $opts = $this->getScriptOptions('plg_editor_tinymce');

            if (
                empty($opts) || !is_array($opts) || !isset($opts['tinyMCE'])
                || !isset($opts['tinyMCE']['default'])
                || !isset($opts['tinyMCE']['default']['skin'])
                || $opts['tinyMCE']['default']['skin'] != 'oxide'
            ) {
                return;
            }

            // Use a custom skin which supports Dark and Light Mode at the same time!
            $opts['tinyMCE']['default']['skin'] = 'oxide';
            $opts['tinyMCE']['default']['skin_url'] = '/media/templates/administrator/atum/css/vendor/tinymce';

            // Optional: force Dark Mode compatibility in all TinyMCE content
            if ($this->params->get('darkmode_tinymce_content', 1) == 1) {
                $autoDark = HTMLHelper::_(
                    'stylesheet',
                    'content-dark.css',
                    [
                        'pathOnly' => true,
                        'relative' => true,
                        'detectDebug' => true,
                    ]
                );
                $opts['tinyMCE']['default']['content_css'] =
                    $opts['tinyMCE']['default']['content_css'] .
                    ',' . $autoDark;
            }

            // Apply the new TinyMCE options
            $this->addScriptOptions('plg_editor_tinymce', $opts);
        });

        // Hard-coded support for CodeMirror
        if ($this->params->get('darkmode_codemirror', 1) == 1) {
            call_user_func(function () {
                /**
                 * If we are editing the CodeMirror plugin we have to NOT apply the custom
                 * dark mode CSS file since it will override the theme preview.
                 */

                $input = Factory::getApplication()->getInput();
                $extension = PluginHelper::getPlugin('editors', 'codemirror');

                if (
                    $input->getCmd('option') === 'com_plugins'
                    && $input->getCmd('view') === 'plugin'
                    && $input->getCmd('layout') === 'edit'
                    && $input->getCmd('extension_id') == (is_object($extension) ? $extension->id : -1)
                ) {
                    return;
                }

                HTMLHelper::_(
                    'stylesheet',
                    'codemirror-dark.css',
                    [
                        'relative' => true,
                        'detectDebug' => true,
                    ]
                );
            });
        }

        /**
         * Call a plugin method against editor plugins (onTemplateDarkModeSupported).
         *
         * This allows third party editors to load additional CSS for Dark Mode support.
         */
        PluginHelper::getPlugin('editors');

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new Event('onTemplateDarkModeSupported', []);
        $dispatcher->dispatch($event->getName(), $event);
    });
}

// Add a 'color-scheme' meta header
if (empty($this->getMetaData('color-scheme', 'value'))) {
    /**
     * Using 'only light' prevents native, unstyled browser controls (input boxes, check boxes,
     * dialogs, etc) from adopting a dark theme when Dark Mode is enabled system-wide.
     *
     * See https://developer.mozilla.org/en-US/docs/Web/CSS/color-scheme
     */
    $this->setMetaData(
        'color-scheme',
        $this->params->get('darkmode', 1) == 1
            ? 'light dark'
            : 'only light',
        'value'
    );
}

// Clean up
unset($atumApplyCustomColor, $matches);
