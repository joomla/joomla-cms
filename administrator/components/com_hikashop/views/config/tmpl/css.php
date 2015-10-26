<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm">
	<fieldset>
		<div class="hikashop_header" style="float: left;"><?php echo $this->type.'_'.$this->fileName.'.css'; ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="btn" type="button" onclick="javascript:submitbutton('savecss'); return false;"><?php echo JText::_('HIKA_SAVE'); ?></button>
		</div>
	</fieldset>
	<?php echo $this->editor->displayCode('csscontent',$this->content); ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="savecss" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="config" />
	<input type="hidden" name="file" value="<?php echo $this->type.'_'.$this->fileName; ?>" />
	<input type="hidden" name="var" value="<?php echo JRequest::getCmd('var'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
