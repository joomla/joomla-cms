<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
$plugins = array_values(array_filter($this->plugins, function($plugin) {
    return $plugin->editable && !empty($plugin->form);
}));

?>
<div class="form-horizontal tabbable tabs-left flex-column">
    <?php //echo JHtml::_('bootstrap.startTabSet', 'profile-plugins', array('active' => 'profile-plugins-' . $plugins[0]->name));?>

    <ul class="nav nav-tabs" id="profile-plugins-tabs">

    <?php

    $key = 0;

    foreach ($plugins as $plugin) :
        $plugin->state = "hide";

        if ($plugin->active) {
            $plugin->state = "";

            $key++;

            if ($key === 1) {
                $plugin->state = "active";
            }
        }

        $icons = '';
        $title = '';

        $title .= '<p>' . JText::_($plugin->title, true) . '</p>';
        
        if (!empty($plugin->icon)) {

            foreach ($plugin->icon as $icon) {
                $icons .= '<div class="mce-widget mce-btn mceButton ' . $plugin->class . '" title="' . $plugin->title . '"><span class="mce-ico mce-i-' . $icon . ' mceIcon mce_' . $icon . '"></span></div>';
            }

            $title .= '<div class="mceEditor defaultSkin"><div class="mce-container mce-toolbar mceToolBarItem">' . $icons . '</div></div>';
        }

        //echo JHtml::_('bootstrap.addTab', 'profile-plugins', 'profile-plugins-' . $plugin->name, $title); ?>
        <li class="nav-item <?php echo $plugin->state;?>"><a href="#profile-plugins-<?php echo $plugin->name;?>" class="nav-link"><?php echo $title;?></a></li>
    <?php endforeach;?>

    </ul>
    <div class="tab-content">
    <?php foreach ($plugins as $plugin) : ?>
        <div class="tab-pane <?php echo $plugin->state;?>" id="profile-plugins-<?php echo $plugin->name;?>">
            <div class="row-fluid">

                <?php if ($plugin->form) :
                    $plugin->fieldsname = "";
                    $plugin->name = $plugin->title;
                    $plugin->description = "";
                    echo JLayoutHelper::render('joomla.content.options_default', $plugin);
                    
                    foreach ($plugin->extensions as $type => $extensions) : ?>
                        
                        <h3><?php echo JText::_('WF_EXTENSIONS_' . strtoupper($type) . '_TITLE', true); ?></h3>

                        <?php foreach ($extensions as $name => $extension) : ?>
                            <div class="row-fluid">  
                                        
                                <?php if ($extension->form) :
                                    $extension->fieldsname = "";
                                    $extension->name = JText::_($extension->title, true);
                                    $extension->description = "";
                                    echo JLayoutHelper::render('joomla.content.options_default', $extension);

                                endif; ?>

                            </div>

                        <?php endforeach; ?>

                    <?php endforeach;

                endif; ?>
            </div>
            <?php //echo JHtml::_('bootstrap.endTab');?>
        </div>
        <?php endforeach;?>
    </div>
    <?php //echo JHtml::_('bootstrap.endTabSet'); ?>
</div>