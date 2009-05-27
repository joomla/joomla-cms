<?php // @version $Id$
defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_page_title',1)) : ?>
<h2 class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->escape($this->params->get('page_title')) ?>
</h2>
<?php endif; ?>

<script type="text/javascript">
	Window.onDomReady(function(){
		document.formvalidator.setHandler('passverify', function (value) { return ($('password').value == value); }	);
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_users#content'); ?>" method="post" id="josForm" name="josForm" class="form-validate user">
	<?php if (isset($this->message)) :
		$this->display('message');
	endif; ?>
	<fieldset>
		<p><?php echo JText::_('REGISTER_REQUIRED'); ?></p>
		<div class="name">
			<label id="namemsg" for="name"><?php echo JText::_('Name'); ?>: *</label>
			<input type="text" name="name" id="name" value="<?php echo $this->user->get('name'); ?>" class="inputbox validate required none namemsg" maxlength="50" />
		</div>
		<div class="user">
			<label id="usernamemsg" for="username"><?php echo JText::_('Username'); ?>: *</label>
			<input type="text" id="username" name="username"  value="<?php echo $this->user->get('username'); ?>" class="inputbox validate required username usernamemsg" maxlength="25" />
		</div>
		<div class="email">
			<label id="emailmsg" for="email"><?php echo JText::_('Email'); ?>: *</label>
			<input type="text" id="email" name="email"  value="<?php echo $this->user->get('email'); ?>" class="inputbox validate required email emailmsg" maxlength="100" />
		</div>
	</fieldset>
	<fieldset>
		<div class="pass">
			<label id="pwmsg" for="password"><?php echo JText::_('Password'); ?>: *</label>
			<input type="password" id="password" name="password" value="" class="inputbox required validate-password" />
		</div>
		<div class="verify_pass">
			<label id="pw2msg" for="password2"><?php echo JText::_('Verify Password'); ?>: *</label>
			<input type="password" id="password2" name="password2" value="" class="inputbox required validate-passverify" />
		</div>
	</fieldset>
	<button class="button validate" type="submit"><?php echo JText::_('Register'); ?></button>
	<input type="hidden" name="task" value="register_save" />
	<input type="hidden" name="id" value="0" />
	<input type="hidden" name="gid" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
