<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
JHtml::_('script', 'com_content/admin-article-pagebreak.min.js', array('version' => 'auto', 'relative' => true));

$document    = JFactory::getDocument();
$this->eName = JFactory::getApplication()->input->getCmd('e_name', '');
$this->eName = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $this->eName);

$document->setTitle(JText::_('COM_CONTENT_PAGEBREAK_DOC_TITLE'));
?>
<div class="container-popup">
	<form>
		<div class="control-group">
			<div class="control-label">
				<label for="title"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TITLE'); ?></label>
			</div>
			<div class="controls">
				<input type="text" id="title" name="title">
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="alias"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TOC'); ?></label>
			</div>
			<div class="controls">
				<input type="text" id="alt" name="alt">
			</div>
		</div>

		<button onclick="insertPagebreak('<?php echo $this->eName; ?>');" class="btn btn-success pull-xs-right">
			<?php echo JText::_('COM_CONTENT_PAGEBREAK_INSERT_BUTTON'); ?>
		</button>

	</form>
</div>
