<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$script  = 'function insertPagebreak() {' . "\n\t";

// Get the pagebreak title
$script .= 'var title = document.getElementById("title").value;' . "\n\t";
$script .= 'if (title != \'\') {' . "\n\t\t";
$script .= 'title = "title=\""+title+"\" ";' . "\n\t";
$script .= '}' . "\n\t";

// Get the pagebreak toc alias -- not inserting for now
// don't know which attribute to use...
$script .= 'var alt = document.getElementById("alt").value;' . "\n\t";
$script .= 'if (alt != \'\') {' . "\n\t\t";
$script .= 'alt = "alt=\""+alt+"\" ";' . "\n\t";
$script .= '}' . "\n\t";
$script .= 'var tag = "<hr class=\"system-pagebreak\" "+title+" "+alt+"/>";' . "\n\t";
$script .= 'window.parent.jInsertEditorText(tag, ' . json_encode($this->eName) . ');' . "\n\t";
$script .= 'window.parent.jModalClose();' . "\n\t";
$script .= 'return false;' . "\n";
$script .= '}' . "\n";

JFactory::getDocument()->addScriptDeclaration($script);
?>
<div class="container-popup">
	<form>
		<div class="control-group">
			<div class="control-label">
				<label for="title"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TITLE'); ?></label>
			</div>
			<div class="controls">
				<input type="text" id="title" name="title" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="alias"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TOC'); ?></label>
			</div>
			<div class="controls">
				<input type="text" id="alt" name="alt" />
			</div>
		</div>
		<button onclick="insertPagebreak();" class="btn btn-primary"><?php echo JText::_('COM_CONTENT_PAGEBREAK_INSERT_BUTTON'); ?></button>
	</form>
</div>
