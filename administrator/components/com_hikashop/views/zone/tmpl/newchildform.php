<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('savechild');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('HIKA_SAVE'); ?></button>
		<button class="btn" type="button" onclick="submitbutton('selectchildlisting');"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png"/><?php echo JText::_('HIKA_CANCEL'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=zone&amp;tmpl=component" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php
	$this->setLayout('information');
	echo $this->loadTemplate();
	?>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="0" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="main_namekey" value="<?php echo JRequest::getCmd('main_namekey'); ?>" />
	<input type="hidden" name="main_id" value="<?php echo JRequest::getInt('main_id'); ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="zone" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
