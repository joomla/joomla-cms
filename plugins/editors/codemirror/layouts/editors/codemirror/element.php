<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$options = $displayData->options;
$params  = $displayData->params;
$name    = $displayData->name;
$id      = $displayData->id;
$cols    = $displayData->cols;
$rows    = $displayData->rows;
$content = $displayData->content;
$buttons = $displayData->buttons;
$basePath = $params->get('basePath', 'media/vendor/codemirror/');
$modePath = $params->get('modePath', 'media/vendor/codemirror/mode/%N/%N');

$modifier = $params->get('fullScreenMod', '') !== '' ? implode($params->get('fullScreenMod', ''), ' + ') . ' + ' : '';
$basePath = $params->get('basePath', 'media/vendor/codemirror/');
$modePath = $params->get('modePath', 'media/vendor/codemirror/mode/%N/%N');
$extJS    = JDEBUG ? '.js' : '.min.js';

$fskeys          = $params->get('fullScreenMod', array());
$fskeys[]        = $params->get('fullScreen', 'F10');
$fullScreenCombo = implode('-', $fskeys);
$fsCombo         = 'fs-combo=' . json_encode($fullScreenCombo);
$modPath         = 'mod-path="' . Uri::root(true) . '/' . $modePath . $extJS . '"';
$option          = 'options=' . json_encode($options);

$basePath = $params->get('basePath', 'media/vendor/codemirror/');
$modePath = $params->get('modePath', 'media/vendor/codemirror/mode/%N/%N');
$extCSS   = JDEBUG ? '.css' : '.min.css';

HTMLHelper::_('stylesheet', $basePath . 'lib/codemirror' . $extCSS, array('version' => 'auto'));
HTMLHelper::_('stylesheet', $basePath . 'lib/addons' . $extCSS, array('version' => 'auto'));

$editor   = Uri::root() . ltrim(HTMLHelper::_('script',  $basePath . 'lib/codemirror' . $extJS, ['version' => 'auto', 'pathOnly' => true]), '/');
$addons    = Uri::root() . ltrim(HTMLHelper::_('script', $basePath . 'lib/addons' . $extJS, ['version' => 'auto', 'pathOnly' => true]), '/');

HTMLHelper::_('webcomponent', ['joomla-editor-codemirror' => 'system/webcomponents/joomla-editor-codemirror.js'], array('version' => 'auto', 'relative' => true));

?>
<joomla-editor-codemirror editor="<?php echo $editor; ?>" addons="<?php echo $addons; ?>" <?php echo $modPath; ?> <?php echo $fsCombo; ?> <?php echo $option; ?>>
<p class="badge badge-secondary">
    <?php echo Text::sprintf('PLG_CODEMIRROR_TOGGLE_FULL_SCREEN', $modifier, $params->get('fullScreen', 'F10')); ?>
</p>
<?php echo '<textarea name="', $name, '" id="', $id, '" cols="', $cols, '" rows="', $rows, '">', $content, '</textarea>'; ?>

<?php echo $displayData->buttons; ?>
</joomla-editor-codemirror>