<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<script type="text/javascript">
function alphabetFilter(val){
	document.getElementById('alpha').value=val;
	document.contactForm.submit();
}
</script>

<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo $this->escape($this->params->get('page_title'));?>
</div>
<?php endif; ?>

<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php if ($this->params->get('image') || $this->params->get('description')) : ?>
		<div class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php if ($this->params->get('show_image')) : ?>
				<img src="<?php echo $this->baseurl .'/'. $this->cparams->get('image_path') . '/'. $this->params->get('image'); ?>" align="<?php echo $this->params->get('image_align'); ?>" hspace="6" alt="<?php echo JText::_( 'DECRIPTION_IMG' ); ?>" />
			<?php endif;
				if ($this->params->get('show_description')) :
					echo $this->params->get('description');
				endif; ?>
		</div>
	<?php endif; ?>

	<form action="<?php echo $this->action; ?>" method="post" name="contactForm">
	<table  width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
		<?php if($this->params->get('alphabet') || $this->params->get('search')): ?>
		<thead>
			<tr>
				<td colspan="2" height="50">
					<?php if($this->params->get('alphabet')): ?>
					<a href="javascript:alphabetFilter('a')">A</a>
					<a href="javascript:alphabetFilter('b')">B</a>
					<a href="javascript:alphabetFilter('c')">C</a>
					<a href="javascript:alphabetFilter('d')">D</a>
					<a href="javascript:alphabetFilter('e')">E</a>
					<a href="javascript:alphabetFilter('f')">F</a>
					<a href="javascript:alphabetFilter('g')">G</a>
					<a href="javascript:alphabetFilter('h')">H</a>
					<a href="javascript:alphabetFilter('i')">I</a>
					<a href="javascript:alphabetFilter('j')">J</a>
					<a href="javascript:alphabetFilter('k')">K</a>
					<a href="javascript:alphabetFilter('l')">L</a>
					<a href="javascript:alphabetFilter('m')">M</a>
					<a href="javascript:alphabetFilter('n')">N</a>
					<a href="javascript:alphabetFilter('o')">O</a>
					<a href="javascript:alphabetFilter('p')">P</a>
					<a href="javascript:alphabetFilter('q')">Q</a>
					<a href="javascript:alphabetFilter('r')">R</a>
					<a href="javascript:alphabetFilter('s')">S</a>
					<a href="javascript:alphabetFilter('t')">T</a>
					<a href="javascript:alphabetFilter('u')">U</a>
					<a href="javascript:alphabetFilter('v')">V</a>
					<a href="javascript:alphabetFilter('w')">W</a>
					<a href="javascript:alphabetFilter('x')">X</a>
					<a href="javascript:alphabetFilter('y')">Y</a>
					<a href="javascript:alphabetFilter('z')">Z</a> |
					<a href="javascript:alphabetFilter('')">Reset</a>
					<input type="text" name="alphabet" id="alpha" value="" style="display:none;"/>
					<?php endif; ?>
				</td>
				<td align="right" nowrap="nowrap" colspan="2">
					<?php if($this->params->get('search')): ?>
					<?php echo JText::_( 'FILTER' ); ?>:
			 		<input type="text" name="search" id="searchword" size="15" value="<?php echo $this->lists['search'];?>" class="inputbox" onchange="document.contactForm.submit();"/>
					<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
					<button onclick="document.getElementById('searchword').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
					<?php endif; ?>
				</td>
			</tr>
		</thead>
		<?php endif; ?>
		<tfoot>
			<tr>
				<td nowrap="nowrap" height="50">
					<?php if ($this->params->get('show_limit')) :
						echo JText::_('DISPLAY #') .'&nbsp;';
						echo $this->pagination->getLimitBox();
					endif; ?>
				</td>
				<td align="center" colspan="2" class="sectiontablefooter<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</td>
				<td align="right" class="sectiontablefooter<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if(!$this->params->get('groupby_cat')) echo $this->loadTemplate('list'); ?>
			<?php if($this->params->get('groupby_cat')) echo $this->loadTemplate('groupby'); ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_contactdirectory" />
	</form>
</div>