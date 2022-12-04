<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Event\Template\DarkModeSupported;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;

defined('_JEXEC') or die;

/**
 * @var \Joomla\CMS\WebAsset\WebAssetManager $wa
 * @var \Joomla\CMS\Document\HtmlDocument    $this
 */

$darkMode = (Factory::getApplication()->getIdentity() ?? new User())
    ->getParam('admin_dark', -1);
$darkMode = ($darkMode >= 0) ? $darkMode : $this->params->get('darkmode', 1);

/**
 * Applies the Atum custom colors and the optional Dark Mode.
 *
 * @param   \Joomla\CMS\WebAsset\WebAssetManager  $wa    The Web Asset Manager.
 * @param   string                                $hue   The hue for the template accent color.
 * @param   bool                                  $dark  Is this about the Dark Mode colors?
 *
 * @since  __DEPLOY_VERSION__
 */
$atumApplyCustomColor = function (\Joomla\CMS\WebAsset\WebAssetManager $wa, string $hue, bool $dark = false) use ($darkMode) {
    if (empty($hue) || ($dark && $darkMode < 1)) {
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

    if ($dark && $darkMode == 1) {
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
if ($darkMode >= 1) {
    call_user_func(function () use ($atumApplyCustomColor, $wa, $darkMode) {
        // Load the Dark Mode CSS
        $wa->useStyle($darkMode == 1 ? 'template.atum.autodark' : 'template.atum.dark');

        // Get the Dark Mode hue value
        preg_match(
            '#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i',
            $this->params->get('dark-hue', 'hsl(214, 63%, 20%)'),
            $matches
        );

        // Apply Dark Mode colors for the template
        $atumApplyCustomColor($wa, $matches[1], true);

        // Tell the editors Dark Mode is enabled?
        if ($this->params->get('darkmode_editors', 1) == 1) {
            /**
             * Call a plugin method against editor plugins (onTemplateDarkModeSupported).
             *
             * This allows third party editors to load additional CSS for Dark Mode support.
             *
             * We need to call this here instead of letting the editor figure it out because the editor
             * is instantiated and displayed before the template has the chance to load. We cannot go
             * back in time, so calling a plugin event is the next best thing.
             *
             * Note that editor plugins are NOT real plugins. As a result, we cannot actually call their
             * events through the Dispatcher. Hence, the convoluted code below.
             */
            foreach (PluginHelper::getPlugin('editors') as $editor) {
                $className = 'PlgEditor' . ucfirst($editor->name);

                if (!class_exists($className)) {
                    continue;
                }

                Editor::getInstance($editor->name)->notifyDarkMode($darkMode == 2);
            }
        }
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
unset($atumApplyCustomColor, $matches, $darkMode);
