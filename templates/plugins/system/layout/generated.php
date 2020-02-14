<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

$fields = JFolder::files( dirname( __FILE__ ) . '/fields', '\.php$', false, true);
foreach ($fields as $field)
{
    require_once $field;
}

require_once 'settings/settings.php';

echo RowColumnSettings::getRowSettings($rowSettings);
echo RowColumnSettings::getColumnSettings($columnSettings);

$layout_path  = JPATH_ROOT .'/plugins/system/helixultimate/layouts';
?>

<div class="hidden">
    <div class="save-box">
        <div class="form-group">
            <label><?php echo JText::_('HELIX_ENTER_LAYOUT_NAME'); ?></label>
            <input class="form-control addon-input addon-name" type="text" data-attrname="layout_name" value="" placeholder="">
        </div>
    </div>
</div>

<div class="hidden">
    <?php
        $lt_section = new JLayoutFile('backend.section', $layout_path );
        $obj = new stdClass;
        $obj->sectionID = true;
        echo $lt_section->render($obj);
    ?>
</div>

<div class="clearfix"></div>

<!-- Layout Builder Section -->
<div id="helix-ultimate-layout-builder" >
<?php
    $output = '';
    if ($layout_data)
    {
        foreach ($layout_data as $row)
        {
            $lt_section = new JLayoutFile('backend.section', $layout_path );
            $output .= $lt_section->render($row);
        }
    }

    echo $output;
    ?>
</div>

<div class="clearfix"></div>
