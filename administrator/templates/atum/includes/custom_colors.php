<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

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
$atumApplyCustomColor = function (\Joomla\CMS\WebAsset\WebAssetManager $wa, string $hue, bool $dark = false)
{
    $paramPrefix = $dark ? 'dark-' : '';

    $bgLight      = $this->params->get($paramPrefix . 'bg-light') ?? '';
    $textDark     = $this->params->get($paramPrefix . 'text-dark') ?? '';
    $textLight    = $this->params->get($paramPrefix . 'text-light') ?? '';
    $linkColor    = $this->params->get($paramPrefix . 'link-color') ?? '';
    $specialColor = $this->params->get($paramPrefix . 'special-color') ?? '';

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
    // Load the Dark Mode CSS
    $wa->useStyle('template.atum.dark');

    // Get the Dark Mode hue value
    preg_match(
        '#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i',
        $this->params->get('dark-hue', 'hsl(214, 63%, 20%)'),
        $matches
    );

    // Apply Dark Mode colors
    $atumApplyCustomColor($wa, $matches[1], true);
}

// Clean up
unset($atumApplyCustomColor);
