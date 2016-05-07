<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<p class="nowarning"><?php echo sprintf(JText::_('COM_INSTALLER_VIEW_INSTALLER_INPROGRESS'), $this->queue_active, $this->queue_count) ?></p>
<div id="update-progress">
  <div id="extprogress">
    <div id="progress" class="progress progress-striped active">
      <div id="progress-bar" class="bar bar-success" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="extprogrow">
      <span class="extlabel"><?php echo JText::_('COM_INSTALLER_VIEW_INSTALLER_PERCENT'); ?></span>
      <span class="extvalue" id="extpercent"></span>
    </div>
    <div class="extprogrow">
      <span class="extlabel"><?php echo JText::_('COM_INSTALLER_VIEW_INSTALLER_BYTESREAD'); ?></span>
      <span class="extvalue" id="extbytesin"></span>
    </div>
    <div class="extprogrow">
      <span class="extlabel"><?php echo JText::_('COM_INSTALLER_VIEW_INSTALLER_BYTESEXTRACTED'); ?></span>
      <span class="extvalue" id="extbytesout"></span>
    </div>
    <div class="extprogrow">
      <span class="extlabel"><?php echo JText::_('COM_INSTALLER_VIEW_INSTALLER_FILESEXTRACTED'); ?></span>
      <span class="extvalue" id="extfiles"></span>
    </div>
  </div>
</div>
