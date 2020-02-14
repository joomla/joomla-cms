<?php
$item = $displayData->get('Item');
$form = $displayData->getForm();

$data = new JRegistry($form->getValue('config'));

$rows = $displayData->get('Rows');
$plugins = $displayData->get('Plugins');
$available = $displayData->get('AvailableButtons');

// width and height
$width = $data->get('width', '100%');
$height = $data->get('height', 'auto');

if (is_numeric($width) && strpos('%', $width) === false) {
    $width .= 'px';
}
if (is_numeric($height) && strpos('%', $height) === false) {
    $height .= 'px';
}

?>
<div class="control-group">
    <div class="control-label">
        <label><?php echo JText::_('WF_PROFILES_FEATURES_LAYOUT'); ?></label>
    </div>
    <div class="controls">
        <div class="editor-layout">
            <!-- Editor Toggle -->
            <span id="editor_toggle"><?php echo $data->get('toggle_label', '[Toggle Editor]'); ?></span>
            <!-- Width Marker -->
            <div class="widthMarker" style="width:<?php echo $width; ?>;">
                <span><?php echo $width; ?></span>
            </div>
            <!-- Toolbar -->
            <div class="mce-tinymce mce-container mce-panel mceEditor mceLayout defaultSkin" role="application">
                <div class="mce-container-body mce-stack-layout mceLayout" style="max-width:<?php echo $width; ?>" role="presentation">
                    <div class="mceToolbar mceLeft mceFirst" role="toolbar">
                        <div class="mce-container-body mce-stack-layout sortableList">
                            <?php foreach ($rows as $key => $groups) : ?>
                                <div class="mce-container mce-toolbar mce-stack-layout-item mceToolbarRow mceToolbarRow<?php echo $key;?> Enabled sortableListItem">
                                    <?php foreach ($groups as $buttons) : ?>
                                        <!--div class="mce-container mce-flow-layout-item mce-btn-group" role="group"-->
                                        <?php foreach ($buttons as $button) : ?>
                                                <div tabindex="-1" class="mceToolBarItem <?php echo $button->type; ?> mce-widget mce-btn" data-name="<?php echo $button->name; ?>" role="button" aria-label="<?php echo $button->title; ?>" aria-description="<?php echo $button->description; ?>">
                                                    <?php foreach ($button->icon as $icon): ?>
                                                    <div tabindex="-1" class="mceButton <?php echo $button->class; ?>" role="presentation" title="<?php echo $button->title; ?>">
                                                        <?php if ($button->image) : ?>
                                                            <span class="mceIcon mceIconImage"><img src="<?php echo $button->image; ?>" alt="" /></span>
                                                        <?php else : ?>
                                                            <span class="mce-ico mce-i-<?php echo $icon; ?> mceIcon mce_<?php echo $icon; ?>"></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <!--/div-->
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mce-edit-area mce-container mce-panel mce-stack-layout-item mceIframeContainer">
                        <div>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                        </div>
                    </div>

                    <div class="mce-statusbar mce-container mce-panel mce-last mce-stack-layout-item mceStatusbar mceLast">
                        <div class="mce-container-body mce-flow-layout mcePathRow" role="group" tabindex="-1">
                            <div class="mcePathLabel">Path: </div>
                            <div aria-level="0" tabindex="-1" data-index="0" class="mce-path-item mce-last mcePathPath" role="button">p</div>    
                        </div>
                        <div class="mce-flow-layout-item mce-last mce-resizehandle mceResize" tabindex="-1"></div>
                        <div class="mce-wordcount mce-widget mce-label mce-flow-layout-item mceWordCount">Words: 69</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="editor-button-pool">
            <div class="mce-tinymce mce-container mce-panel mceEditor mceLayout defaultSkin" role="application">
                <div class="mce-container-body mce-stack-layout mceLayout">
                    <div class="mce-toolbar-grp mce-container mce-panel mce-stack-layout-item mceToolbar mceLeft mceFirst" role="toolbar">
                        <div class="mce-container-body mce-stack-layout sortableList">
                            <?php for ($i = 0; $i < max(count($rows), 5); ++$i) : ?>
                                <div class="mce-container mce-toolbar mce-stack-layout-item mceToolbarRow mceToolbarRow<?php echo $i;?> Enabled sortableListItem">
                                    <!--div class="mce-container mce-flow-layout-item mce-btn-group"-->
                                        <?php foreach ($available as $plugin) : ?>
                                            <?php if ($plugin->row && $plugin->row === $i): ?>
                                                <div tabindex="-1" class="mceToolBarItem <?php echo $plugin->type; ?> mce-widget mce-btn" data-name="<?php echo $plugin->name; ?>" role="button" aria-label="<?php echo $plugin->title; ?>" aria-description="<?php echo $plugin->description; ?>">
                                                    <?php foreach ($plugin->icon as $icon): ?>
                                                        <div tabindex="-1" class="mceButton <?php echo $plugin->class; ?>" role="presentation" title="<?php echo $plugin->title; ?>">
                                                            <?php if ($plugin->image) : ?>
                                                                <span class="mceIcon mceIconImage"><img src="<?php echo $plugin->image; ?>" alt="" /></span>
                                                            <?php else : ?>
                                                                <span class="mce-ico mce-i-<?php echo $icon; ?> mceIcon mce_<?php echo $icon; ?>"></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <!--/div-->
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>