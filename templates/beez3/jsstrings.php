<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JText::script('TPL_BEEZ3_ALTOPEN');
JText::script('TPL_BEEZ3_ALTCLOSE');
JText::script('TPL_BEEZ3_TEXTRIGHTOPEN');
JText::script('TPL_BEEZ3_TEXTRIGHTCLOSE');
JText::script('TPL_BEEZ3_FONTSIZE');
JText::script('TPL_BEEZ3_BIGGER');
JText::script('TPL_BEEZ3_RESET');
JText::script('TPL_BEEZ3_SMALLER');
JText::script('TPL_BEEZ3_INCREASE_SIZE');
JText::script('TPL_BEEZ3_REVERT_STYLES_TO_DEFAULT');
JText::script('TPL_BEEZ3_DECREASE_SIZE');
JText::script('TPL_BEEZ3_OPENMENU');
JText::script('TPL_BEEZ3_CLOSEMENU');
?>

<script type="text/javascript">
	var big = '<?php echo (int) $this->params->get('wrapperLarge');?>%';
	var small = '<?php echo (int) $this->params->get('wrapperSmall'); ?>%';
	var bildauf = '<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/plus.png';
	var bildzu = '<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/minus.png';
	var rightopen='<?php echo JText::_('TPL_BEEZ3_TEXTRIGHTOPEN', true); ?>';
	var rightclose='<?php echo JText::_('TPL_BEEZ3_TEXTRIGHTCLOSE', true); ?>';
	var altopen='<?php echo JText::_('TPL_BEEZ3_ALTOPEN', true); ?>';
	var altclose='<?php echo JText::_('TPL_BEEZ3_ALTCLOSE', true); ?>';
</script>
