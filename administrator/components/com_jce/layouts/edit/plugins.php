<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
$plugins = $displayData->get('Plugins');

?>
<div class="form-horizontal tabbable tabs-left flex-column">
    <?php echo JHtml::_('bootstrap.startTabSet', 'plugins', array('active' => ''));
    foreach ($plugins as $plugin) {
        if (!$plugin->editable || empty($plugin->form)) {
            continue;
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

        echo JHtml::_('bootstrap.addTab', 'plugins', 'tabs-plugins-' . $plugin->name, $title); ?>

            <div class="row-fluid">

                    <h2><?php echo $plugin->title; ?></h2>
                    <hr />

                    <?php if ($plugin->form) :

                        echo $plugin->form->renderFieldset('config'); ?>

                        <hr />
                        
                        <?php foreach ($plugin->extensions as $type => $extensions) : ?>
                            <h3><?php echo JText::_('WF_EXTENSION_' . strtoupper($type), true); ?></h3>

                            <?php foreach ($extensions as $extension) : ?>

                                <div class="row-fluid">
                                    <h4><?php echo JText::_('PLG_JCE_' . strtoupper($type) . '_' . strtoupper($extension->name), true); ?></h4>                                
                                    <?php echo $extension->form->renderFieldset($type . '.' . $extension->name); ?>
                                </div>

                            <?php endforeach; ?>

                            <hr />

                        <?php endforeach;

                        endif; ?>
            </div>
            <?php echo JHtml::_('bootstrap.endTab');
        }
        echo JHtml::_('bootstrap.endTabSet'); ?>
</div>