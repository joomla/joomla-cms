<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/** @var \Joomla\CMS\Document\HtmlDocument $this */

$wa = $this->getWebAssetManager();

// Get the hue value
preg_match('#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i', $this->params->get('hue', 'hsl(214, 63%, 20%)'), $matches);

// Enable assets
$wa->usePreset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
    ->useStyle('template.active.language')
    ->useStyle('template.user')
    ->addInlineStyle(':root {
		--hue: ' . $matches[1] . ';
		--template-bg-light: ' . $this->params->get('bg-light', '--template-bg-light') . ';
		--template-text-dark: ' . $this->params->get('text-dark', '--template-text-dark') . ';
		--template-text-light: ' . $this->params->get('text-light', '--template-text-light') . ';
		--template-link-color: ' . $this->params->get('link-color', '--template-link-color') . ';
		--template-special-color: ' . $this->params->get('special-color', '--template-special-color') . ';
	}');

// No template.js for modals
$wa->disableScript('template.atum');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

// Getting user accessibility settings
$user           = $app->getIdentity();
// $a11y_mono      = (bool) $user->getParam('a11y_mono', '');
// $a11y_contrast  = (bool) $user->getParam('a11y_contrast', '');
// $a11y_highlight = (bool) $user->getParam('a11y_highlight', '');
// $a11y_font      = (bool) $user->getParam('a11y_font', '');
$a11yColorScheme = $user->getParam('prefers_color_scheme', '');
$prefersColorScheme = !empty($a11yColorScheme) ? $a11yColorScheme : 'light';
$prefersColorScheme = $input->cookie->get('atumPrefersColorScheme', $prefersColorScheme);
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" data-bs-theme="<?= $prefersColorScheme; ?>">

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
