<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'Locale Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Time Zone' ); ?>::<?php echo JText::_( 'TIPDATETIMEDISPLAY' ) .': '. JHtml::_('date',  'now', JText::_('DATE_FORMAT_LC2')); ?>">
					<?php echo JText::_( 'Time Zone' ); ?>
				</span>
			</td>
			<td>
				<?php echo JHtml::_('config.locales', $this->row->offset); ?>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
