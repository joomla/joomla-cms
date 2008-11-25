<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<?php
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.formvalidation');

	// clean item data
	JFilterOutput::objectHTMLSafe($this->item, ENT_QUOTES, '');

	/*
	JHTML::script('passwordGenerator.js');
	$this->document->addStyleDeclaration('#passwordContainer span{margin-right:20px;}');
	*/
?>

<script type="text/javascript">
	function submitbutton(task)
	{
		if (task == 'user.cancel' || document.formvalidator.isValid(document.adminForm)) {
			submitform(task);
		}

	}

	var pg;
	window.addEvent("domready",function(){
		document.formvalidator.setHandler('username2',
			function (value) {
				regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
				return !regex.test(value);
			}
		);
	});
	/*
	window.addEvent("domready",function(){
		pg=new PasswordGenerator("passwordContainer");
	});
	*/

</script>


@todo Form validation moving to unobtrusive js methods
<form action="<?php echo JRoute::_('index.php?option=com_users'); ?>" method="post" name="adminForm" autocomplete="off" class="form-validate">
	<div class="col width-45">
		<?php echo $this->loadTemplate('main'); ?>
	</div>

	<div class="col width-55">
		<?php echo $this->loadTemplate('parameters'); ?>
		<?php echo $this->loadTemplate('contact'); ?>
		<?php echo $this->loadTemplate('groups'); ?>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="contact_id" value="" />
	<input type="hidden" name="<?php echo JUtility::getToken();?>" value="1" />
</form>

<div id="passwordContainer"></div>
