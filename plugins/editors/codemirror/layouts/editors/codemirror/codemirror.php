<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$options         = $displayData->options;
$params          = $displayData->params;
$name            = $displayData->name;
$id              = $displayData->id;
$cols            = $displayData->cols;
$rows            = $displayData->rows;
$content         = $displayData->content;
$extJS           = JDEBUG ? '.js' : '.min.js';
$modifier        = $params->get('fullScreenMod', []) ? implode(' + ', $params->get('fullScreenMod', [])) . ' + ' : '';
//$basePath        = $displayData->basePath;
//$modePath        = $displayData->modePath;
//$modPath         = 'mod-path="' . Uri::root() . $modePath . $extJS . '"';
$fskeys          = $params->get('fullScreenMod', []);
$fskeys[]        = $params->get('fullScreen', 'F10');
$fullScreenCombo = implode('-', $fskeys);
$fsCombo         = ' fs-combo=' . $this->escape($fullScreenCombo);
$option          = ' options="' . $this->escape(json_encode($options)) . '"';
$mediaVersion    = Factory::getApplication()->getDocument()->getMediaVersion();
//$editor          = 'editor="' . ltrim(HTMLHelper::_('script', $basePath . 'lib/codemirror' . $extJS, ['version' => 'auto', 'pathOnly' => true]), '/') . '?' . $mediaVersion . '"';
//$addons          = 'addons="' . ltrim(HTMLHelper::_('script', $basePath . 'lib/addons' . $extJS, ['version' => 'auto', 'pathOnly' => true]), '/') . '?' . $mediaVersion . '"';
$style           = '';

if ($options->width) {
    $style .= 'width:' . $options->width . ';';
}
if ($options->height) {
    $style .= 'height:' . $options->height . ';';
}

// Remove the fullscreen message and option if readonly not null.
if (isset($options->readOnly)) {
    $fsCombo = '';
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('plg_editors_codemirror');
$wa->useStyle('plg_editors_codemirror')
    ->useScript('webcomponent.editor-codemirror');
?>
<joomla-editor-codemirror <?php echo $fsCombo . $option; ?>>
<?php echo '<textarea name="' . $name . '" id="' . $id . '" cols="' . $cols . '" rows="' . $rows . '" style="' . $style . '">' . $content . '</textarea>'; ?>
<?php if ($fsCombo) : ?>
    <p class="small float-end">
        <?php echo Text::sprintf('PLG_CODEMIRROR_TOGGLE_FULL_SCREEN', $fullScreenCombo); ?>
    </p>
<?php endif; ?>
<?php echo $displayData->buttons; ?>
</joomla-editor-codemirror>
