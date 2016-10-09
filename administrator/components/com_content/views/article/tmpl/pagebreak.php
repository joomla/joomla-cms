<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
JHtml::_('script', 'com_content/article-pagebreak.js', false, true);

$document    = JFactory::getDocument();
$this->eName = JFactory::getApplication()->input->get('e_name', '', 'cmd');
$this->eName = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $this->eName);

$document->setTitle(JText::_('COM_CONTENT_PAGEBREAK_DOC_TITLE'));
?>
<div class="container-popup">
	<form class="form-horizontal">

		<div class="control-group">
			<label for="title" class="control-label"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TITLE'); ?></label>
			<div class="controls"><input type="text" id="title" name="title" /></div>
		</div>

		<div class="control-group">
			<label for="alias" class="control-label"><?php echo JText::_('COM_CONTENT_PAGEBREAK_TOC'); ?></label>
			<div class="controls"><input type="text" id="alt" name="alt" /></div>
		</div>

		<button class="js-insert-pagebreak btn btn-success pull-right" data-editor="<?php echo $this->eName; ?>"><?php echo JText::_('COM_CONTENT_PAGEBREAK_INSERT_BUTTON'); ?></button>

	</form>
</div>
