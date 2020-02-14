<?php
/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>
<style type="text/css">
    /* Reset template style sheet */
    body{margin:0;padding:0;}div{margin:0;padding:0;}img{margin:0;padding:0;}
</style>
<div id="wf_popup_image">
    <?php if ($this->features['mode']) {
    ?>
        <div class="contentheading"><?php echo $this->features['title']; ?></div>
    <?php 
} ?>
    <?php if ($this->features['mode'] && $this->features['print']) {
    ?>
        <div class="buttonheading"><a href="javascript:;" onClick="window.print();
                return false"><img src="<?php echo JURI::root(); ?>components/com_jce/media/img/print.png" width="16" height="16" alt="<?php echo JText::_('Print'); ?>" title="<?php echo JText::_('Print'); ?>" /></a></div>
<?php 
} ?>
    <div><img src="<?php echo $this->features['img']; ?>" width="<?php echo $this->features['width']; ?>" height="<?php echo $this->features['height']; ?>" alt="<?php echo $this->features['alt']; ?>" onclick="window.close();" /></div>
</div>