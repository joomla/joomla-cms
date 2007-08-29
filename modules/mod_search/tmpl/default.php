<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post">
	<div class="search<?php echo $params->get('moduleclass_sfx') ?>">
		<?php echo $inputfield ?>
	</div>

	<input type="hidden" name="task"   value="search" />
	<input type="hidden" name="option" value="com_search" />
</form>