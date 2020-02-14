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

<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
    <div class="ui-jce container-fluid">
        <?php if (!empty($this->sidebar)) : ?>
	    <div id="j-sidebar-container" class="span2 col-md-2">
		    <?php echo $this->sidebar; ?>
	    </div>
	    <div id="j-main-container" class="span10 col-md-10">
        <?php else : ?>
	    <div id="j-main-container">
        <?php endif; ?>
            <fieldset class="adminform panelform">
                <?php echo JLayoutHelper::render('joomla.content.options_default', $this);?>
            </fieldset>
        </div>
    </div>
    <input type="hidden" name="option" value="com_jce" />
    <input type="hidden" name="view" value="config" />
    <input type="hidden" name="task" value="" />
    <?php echo JHTML::_('form.token'); ?>
</form>