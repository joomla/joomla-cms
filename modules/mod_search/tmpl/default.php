<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo JRoute::_('index.php?option=com_search&Itemid='.$itemid) ?>" method="post">
	<div class="search<?php echo $params->get('moduleclass_sfx') ?>">
		<?php echo $inputfield ?>
	</div>

	<input type="hidden" name="task"   value="search" />
	<input type="hidden" name="option" value="com_search" />
</form>