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
use Joomla\CMS\Uri\Uri;

$options         = $displayData->options;
$params          = $displayData->params;
$name            = $displayData->name;
$id              = $displayData->id;
$cols            = $displayData->cols;
$rows            = $displayData->rows;
$content         = $displayData->content;
$extJS           = JDEBUG ? '.js' : '.min.js';
$modifier        = $params->get('fullScreenMod', array()) ? implode(' + ', $params->get('fullScreenMod', array())) . ' + ' : '';
$basePath        = $displayData->basePath;
$modePath        = $displayData->modePath;
$modPath         = 'mod-path="' . Uri::root() . $modePath . $extJS . '"';
$fskeys          = $params->get('fullScreenMod', array());
$fskeys[]        = $params->get('fullScreen', 'F10');
$fullScreenCombo = implode('-', $fskeys);
$fsCombo         = 'fs-combo=' . json_encode($fullScreenCombo);
$option          = 'options=\'' . json_encode($options) . '\'';
$mediaVersion    = Factory::getDocument()->getMediaVersion();
$editor          = 'editor="' . ltrim(HTMLHelper::_('script', $basePath . 'lib/codemirror' . $extJS, ['version' => 'auto', 'pathOnly' => true]), '/') . '?' . $mediaVersion . '"';
$addons          = 'addons="' . ltrim(HTMLHelper::_('script', $basePath . 'lib/addons' . $extJS, ['version' => 'auto', 'pathOnly' => true]), '/') . '?' . $mediaVersion . '"';

Factory::getDocument()->getWebAssetManager()
	->registerAndUseStyle('codemirror.lib.main', $basePath . 'lib/codemirror.css')
	->registerAndUseStyle('codemirror.lib.addons', $basePath . 'lib/addons.css', [], [], ['codemirror.lib.main'])
	->registerScript(
			'webcomponent.editor-codemirror-es5',
			'plg_editors_codemirror/joomla-editor-codemirror-es5.min.js',
			['dependencies' => ['wcpolyfill']],
			['defer' => true, 'nomodule' => true],
			['wcpolyfill']
	)
	->registerAndUseScript(
		'webcomponent.editor-codemirror',
		'plg_editors_codemirror/joomla-editor-codemirror.min.js',
			[],
			['type' => 'module'],
			['webcomponent.editor-codemirror-es5']
	);
?>
<joomla-editor-codemirror <?php echo $editor . ' ' . $addons . ' ' . $modPath . ' ' . $fsCombo . ' ' . $option; ?>>
<?php echo '<textarea name="', $name, '" id="', $id, '" cols="', $cols, '" rows="', $rows, '">', $content, '</textarea>'; ?>
</joomla-editor-codemirror>
<?php echo $displayData->buttons; ?>
