
<?php
JText::script('TPL_BEEZ2_ALTOPEN');
JText::script('TPL_BEEZ2_ALTCLOSE');
JText::script('TPL_BEEZ2_TEXTRIGHTOPEN');
JText::script('TPL_BEEZ2_TEXTRIGHTCLOSE');
JText::script('TPL_BEEZ2_FONTSIZE');
JText::script('TPL_BEEZ2_BIGGER');
JText::script('TPL_BEEZ2_RESET');
JText::script('TPL_BEEZ2_SMALLER');
JText::script('TPL_BEEZ2_INCREASE_SIZE');
JText::script('TPL_BEEZ2_REVERT_STYLES_TO_DEFAULT');
JText::script('TPL_BEEZ2_DECREASE_SIZE');
JText::script('TPL_BEEZ2_OPENMENU');
JText::script('TPL_BEEZ2_CLOSEMENU');
?>



<script type="text/javascript">
	var big ='<?php echo (int)$this->params->get('wrapperLarge');?>%';
	var small='<?php echo (int)$this->params->get('wrapperSmall'); ?>%';
	var bildauf='<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/plus.png';
	var bildzu='<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/minus.png';

</script>
