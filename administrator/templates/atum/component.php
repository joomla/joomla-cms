<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** @var \Joomla\CMS\Document\HtmlDocument $this */

$app = Factory::getApplication();
$wa  = $this->getWebAssetManager();

// Get the hue value
preg_match('#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i', $this->params->get('hue', 'hsl(214, 63%, 20%)'), $matches);

$linkColor = $this->params->get('link-color', '#2a69b8');
list($r, $g, $b) = sscanf($linkColor, "#%02x%02x%02x");

$linkColorDark = $this->params->get('link-color-dark', '#6fbfdb');
list($rd, $gd, $bd) = sscanf($linkColorDark, "#%02x%02x%02x");

// Enable assets
$wa->usePreset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
    ->useStyle('template.active.language')
    ->useStyle('template.user')
    ->addInlineStyle(':root {
		--hue: ' . $matches[1] . ';
		--template-bg-light: ' . $this->params->get('bg-light', 'var(--template-bg-light)') . ';
		--template-text-dark: ' . $this->params->get('text-dark', 'var(--template-text-dark)') . ';
		--template-text-light: ' . $this->params->get('text-light', 'var(--template-text-light)') . ';
		--link-color: ' . $linkColor . ';
        --link-color-rgb: ' . $r . ',' . $g . ',' . $b . ';
		--template-special-color: ' . $this->params->get('special-color', 'var(--template-special-color)') . ';
	}')
   ->addInlineStyle('@media (prefers-color-scheme: dark) { :root {
		--link-color: ' . $linkColorDark . ';
		--link-color-rgb: ' . $rd . ',' . $gd . ',' . $bd . ';
	}}');

// No template.js for modals
$wa->disableScript('template.atum');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

$colorScheme   = $this->params->get('colorScheme', 'os');
$themeModeAttr = '';

if ($colorScheme) {
    $themeModes   = ['os' => ' data-color-scheme-os', 'light' => ' data-bs-theme="light" data-color-scheme="light"', 'dark' => ' data-bs-theme="dark" data-color-scheme="dark"'];
    // Check for User choose, for now this have a priority over the parameters
    $userColorScheme = $app->getInput()->cookie->get('userColorScheme', '');
    if ($userColorScheme && !empty($themeModes[$userColorScheme])) {
        $themeModeAttr = $themeModes[$userColorScheme];
    } else {
        // Check parameters first (User and Template), then look if we have detected the OS color scheme (if it set to 'os')
        $colorScheme   = $app->getIdentity()->getParam('colorScheme', $colorScheme);
        $osColorScheme = $colorScheme === 'os' ? $app->getInput()->cookie->get('osColorScheme', '') : '';
        $themeModeAttr = ($themeModes[$colorScheme] ?? '') . ($themeModes[$osColorScheme] ?? '');
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>"<?php echo $themeModeAttr; ?>>
<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
</head>
<body class="contentpane component">
    <jdoc:include type="message" />
    <jdoc:include type="component" />
</body>
</html>
