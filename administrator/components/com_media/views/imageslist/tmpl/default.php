<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php if (count($this->images) > 0 || count($this->folders) > 0) { ?>
<div class="manager">

		<?php for ($i=0,$n=count($this->folders); $i<$n; $i++) :
			$this->setFolder($i);
			echo $this->loadTemplate('folder');
		endfor; ?>

		<?php for ($i=0,$n=count($this->images); $i<$n; $i++) :
			$this->setImage($i);
			echo $this->loadTemplate('image');
		endfor; ?>

</div>
<?php } else { ?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td>
		<div align="center" style="font-size:large;font-weight:bold;color:#CCCCCC;font-family: Helvetica, sans-serif;">
			<?php echo JText::_( 'No Images Found' ); ?>
		</div>
	</td>
</tr>
</table>
<?php } ?>
