<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('JDebugHelper', JPATH_PLUGINS . '/system/debug/helper.php');

$id = $displayData['id'] ? $displayData['id'] : 0;
$key = $displayData['key'] ? $displayData['key'] : '';
$session = $displayData['session'] ? $displayData['session'] : JFactory::getSession()->getData();

if (!is_array($session))
{
    echo $key, '<pre>', JDebugHelper::prettyPrintJSON($session), '</pre>';

    return;
}

foreach ($session as $sKey => $entries)
{
    $display = true;

    if (is_array($entries) && $entries)
    {
        $display = false;
    }

    if (is_object($entries))
    {
        $o = ArrayHelper::fromObject($entries);

        if ($o)
        {
            $entries = $o;
            $display = false;
        }
    }

    if (!$display)
    {
        $elementId = 'dbg_container_session' . $id . '_' . $sKey;

        ?>
        <div class="dbg-header" onclick="toggleContainer('<?php echo $elementId; ?>');">
            <a href="javascript:void(0);"><h3><?php echo $sKey; ?></h3></a>
        </div>

        <div style="display: none;" class="dbg-container" id="<?php echo $elementId; ?>">
            <?php

            $id++;
            $data = array('key' => $sKey, 'session' => $entries, 'id' => $id);
            echo JLayoutHelper::render('plugins.system.debug.section.session', $data);

            ?>
        </div>
        <?php

        continue;
    }

    if (is_array($entries))
    {
        $entries = implode($entries);
    }

    if (is_string($entries))
    {
        echo $sKey, '<pre>', JDebugHelper::prettyPrintJSON($entries), '</pre>';
    }
}
