<?php // @version $Id$
defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_page_title',1)) : ?>
<h2 class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->escape($this->params->get('page_title')) ?>
</h2>
<?php endif; ?>
<script type="text/javascript">
<!--
	Window.onDomReady(function(){
		document.formvalidator.setHandler('passverify', function (value) { return ($('password').value == value); }	);
	});
// -->
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userform" autocomplete="off" class="user">

	<div class="user_name">
		<label for="username"><?php echo JText::_('User Name'); ?>: </label>
		<span><?php echo $this->user->get('username'); ?></span>
	</div>

	<div class="name">
		<label for="name"><?php echo JText::_('Your Name'); ?>: </label>
		<input class="inputbox" type="text" id="name" name="name" value="<?php echo $this->user->get('name');?>" size="40" />
	</div>

	<div class="email">
		<label for="email"><?php echo JText::_('email'); ?>: </label>
		<input class="inputbox required validate-email" type="text" id="email" name="email" value="<?php echo $this->user->get('email');?>" size="40" />
	</div>

	<?php if ($this->user->get('password')) : ?>
	<div class="pass">
		<label for="password"><?php echo JText::_('Password'); ?>: </label>
		<input class="inputbox validate-password" type="password" id="password" name="password" value="" size="40" />
	</div>

	<div class="verify_pass">
		<label for="verifyPass"><?php echo JText::_('Verify Password'); ?>: </label>
		<input class="inputbox validate-passverify" type="password" id="password2" name="password2" size="40" />
	</div>
	<?php endif; ?>
	<?php if (isset($this->params)) :
		echo $this->params->render('params');
	endif; ?>

	<button class="button validate" type="submit" onclick="submitbutton(this.form);return false;"><?php echo JText::_('Save'); ?></button>

	<input type="hidden" name="username" value="<?php echo $this->user->get('username');?>" />
	<input type="hidden" name="id" value="<?php echo $this->user->get('id');?>" />
	<input type="hidden" name="gid" value="<?php echo $this->user->get('gid');?>" />
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="save" />
	<?php echo JHtml::_('form.token'); ?>

</form>
