<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');

	JHTML::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHTML::_('behavior.tooltip');
	$aclType = $this->state->get('list.acl_type', 1);
?>
<style type="text/css">
/* @TODO Mode to stylesheet */
.scroll {
	overflow: auto;
}
</style>

<form action="<?php echo JRoute::_('index.php?option=com_acl&view=rules');?>" method="post" name="adminForm">

	<?php echo $this->loadTemplate('type'.$aclType); ?>

	<input type="hidden" name="acl_type" value="<?php echo $aclType;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('orderCol'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('orderDirn'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
