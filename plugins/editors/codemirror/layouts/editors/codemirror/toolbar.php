<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$options = $displayData->options;
$params  = $displayData->params;
$name    = $displayData->name;
$id      = $displayData->id;
$cols    = $displayData->cols;
$rows    = $displayData->rows;
$content = $displayData->content;
$buttons = $displayData->buttons;

$fskeys = $params->get('fullScreenMod', array());
$fskeys[] = $params->get('fullScreen', 'F10');
$fullScreenCombo = implode('-', $fskeys);

$tbParams = new JRegistry($params->get('toolbar', true));

?>
<div id="<?php echo $id, 'Toolbar'; ?>" class="CodeMirror-toolbar">
	<div class="clearfix">
		<?php if ($tbParams->get('fullscreen', true)) : ?>
		<button class="cm-tb-btn cm-tb-btn-fs"
			title="<?php echo JText::sprintf('PLG_CODEMIRROR_FIELD_TOOLBAR_FULLSCREEN_TITLE', $fullScreenCombo); ?>"
			data-option="fullScreen"
			data-value="1">
				<i class="icon icon-expand-2"></i>
		</button>
		<?php endif; ?>

		<?php if ($tbParams->get('indentOutdent', true)) : ?>
		<button class="cm-tb-btn cm-tb-btn-outdent"
			title="<?php echo JText::_('PLG_CODEMIRROR_FIELD_TOOLBAR_OUTDENT_TITLE'); ?>"
			data-command="indentLess">
				<i class="icon icon-chevron-left"></i>
		</button>

		<button class="cm-tb-btn cm-tb-btn-indent"
			title="<?php echo JText::_('PLG_CODEMIRROR_FIELD_TOOLBAR_INDENT_TITLE'); ?>"
			data-command="indentMore">
				<i class="icon icon-chevron-right"></i>
		</button>
		<?php endif; ?>

		<?php if ($tbParams->get('cleanup', true)) : ?>
		<button class="cm-tb-btn cm-tb-btn-cleanup"
			title="<?php echo JText::_('PLG_CODEMIRROR_FIELD_TOOLBAR_CLEANUP_TITLE'); ?>"
			data-command="indentAuto">
				<i class="icon icon-wand"></i>
		</button>
		<?php endif; ?>

		<?php if ($tbParams->get('undoRedo', true)) : ?>
		<button class="cm-tb-btn cm-tb-btn-undo"
			title="<?php echo JText::_('PLG_CODEMIRROR_FIELD_TOOLBAR_UNDO_TITLE'); ?>"
			disabled data-command="undo">
				<i class="icon icon-undo"></i>
		</button>

		<button class="cm-tb-btn cm-tb-btn-redo"
			title="<?php echo JText::_('PLG_CODEMIRROR_FIELD_TOOLBAR_REDO_TITLE'); ?>"
			disabled data-command="redo">
				<i class="icon icon-redo"></i>
		</button>
		<?php endif; ?>

		<?php if ($tbParams->get('closeable', true)) : ?>
		<button class="cm-tb-btn cm-tb-btn-close"
			title="<?php echo JText::_('PLG_CODEMIRROR_FIELD_TOOLBAR_CLOSE_TITLE'); ?>" >
				<i class="icon icon-remove"></i>
		</button>
		<?php endif; ?>
	</div>
</div>
