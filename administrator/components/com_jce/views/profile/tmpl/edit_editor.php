<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 */
defined('JPATH_PLATFORM') or die;
?>
<div class="tabbable tabs-left flex-column">
    <?php //echo JHtml::_('bootstrap.startTabSet', 'profile-editor', array('active' => 'profile-editor-setup')); ?>

    <ul class="nav nav-tabs">
        <?php foreach(array('setup', 'typography', 'filesystem', 'advanced') as $key => $item) : ?>
            <li class="nav-item<?php echo $key === 0 ? ' active show' : '';?>"><a href="#" class="nav-link"><?php echo JText::_('WF_PROFILES_EDITOR_' . strtoupper($item), true);?></a></li>
        <?php endforeach;?>
    </ul>
    <div class="tab-content">
        <?php foreach(array('setup', 'typography', 'filesystem', 'advanced') as $key => $item) : ?>
            <div class="tab-pane<?php echo $key === 0 ? ' active show' : '';?>">
                <?php //echo JHtml::_('bootstrap.addTab', 'profile-editor', 'profile-editor-' . $item, JText::_('WF_PROFILES_EDITOR_' . strtoupper($item), true));?>

                <div class="row-fluid">
                    <?php echo $this->loadTemplate('editor_' . $item); ?>
                </div>
            </div>
            <?php //echo JHtml::_('bootstrap.endTab');?>
        <?php endforeach;?>
    </div>
    <?php //echo JHtml::_('bootstrap.endTabSet'); ?>
</div>