<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<div id="config-document">
	<div id="page-site">
		<table class="noshow">
		<tr>
			<td>
				<?php echo $this->loadTemplate('system'); ?>
			</td>
		</tr>
		</table>
	</div>

	<div id="page-phpsettings">
		<table class="noshow">
		<tr>
			<td>
				<?php echo $this->loadTemplate('phpsettings'); ?>
			</td>
		</tr>
		</table>
	</div>

	<div id="page-config">
		<table class="noshow">
		<tr>
			<td>
				<?php echo $this->loadTemplate('config'); ?>
			</td>
		</tr>
		</table>
	</div>

	<div id="page-directory">
		<table class="noshow">
		<tr>
			<td>
				<?php echo $this->loadTemplate('directory'); ?>
			</td>
		</tr>
		</table>
	</div>

	<div id="page-phpinfo">
		<table class="noshow">
		<tr>
			<td>
				<?php echo $this->loadTemplate('phpinfo'); ?>
			</td>
		</tr>
		</table>
	</div>
</div>

<div class="clr"></div>