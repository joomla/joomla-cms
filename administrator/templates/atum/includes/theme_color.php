<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;

defined('_JEXEC') or die;

/**
 * @var \Joomla\CMS\Document\HtmlDocument    $this
 */

$darkMode = (Factory::getApplication()->getIdentity() ?? new User())
    ->getParam('admin_dark', -1);
$darkMode = ($darkMode >= 0) ? $darkMode : $this->params->get('darkmode', 1);

$atumApplyColorTheme = function (string $hue, bool $dark = false) use ($darkMode)
{
    if (empty($hue)) {
        return;
    }

    $hasDarkMode = $darkMode >= 1;

    if ($dark && !$hasDarkMode) {
        return;
    }

    if (!$dark && $darkMode == 2) {
        return;
    }

    /**
     * Add a theme-color meta for Safari, Chrome, Opera, Edge etc.
     *
     * Yes, we can use HSL values for Theme Color.
     * See https://css-tricks.com/meta-theme-color-and-trickery/
     *
     * The saturation and lightness are lifted from the main header colors defined in the light
     * and dark modes' _variables.scss files. This creates a seamless experience for users of
     * browsers which support the Theme Color feature.
     */
    $themeColor = sprintf(
        'hsl(%u, %0.2f%%, %0.2f%%',
        $hue,
        40,
        $dark ? 3 : 20
    );

    $mediaQuery = '';

    if ($hasDarkMode && $darkMode == 1) {
        $mediaQuery = sprintf(
            ' media="(prefers-color-scheme: %s)"',
            $dark ? 'dark' : 'light'
        );
    }

    echo sprintf(
        '<meta name="theme-color" content="%s"%s>',
        $themeColor,
        $mediaQuery
    );
};

// Get the hue value and apply Light Mode colors
preg_match(
    '#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i',
    $this->params->get('hue', 'hsl(214, 63%, 20%)'),
    $matches
);

$atumApplyColorTheme($matches[1], false);

if ($darkMode >= 1) {
    // Get the Dark Mode hue value
    preg_match(
        '#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i',
        $this->params->get('dark-hue', 'hsl(214, 63%, 20%)'),
        $matches
    );

    $atumApplyColorTheme($matches[1], true);
}

unset($atumApplyColorTheme, $matches, $darkMode);
