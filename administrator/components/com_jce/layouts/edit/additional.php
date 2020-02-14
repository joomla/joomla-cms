<?php
$plugins = $displayData->get('additional');

?>
<fieldset>
    <legend><?php echo JText::_('WF_PROFILES_FEATURES_ADDITIONAL'); ?></legend>
    <div class="control-group">
        <div class="control-label"></div>
        <div class="controls">
            <div class="editor-features">
                <?php foreach ($plugins as $plugin): ?>
                    <div class="control-group">
                        <label class="checkbox">
                            <input type="checkbox" value="<?php echo $plugin->name; ?>" <?php echo $plugin->active ? ' checked="checked"' : ''; ?>> <?php echo JText::_($plugin->title); ?>
                        </label>
                        <span class="help-block"><?php echo JText::_($plugin->description); ?></span>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    </div>
</fieldset>