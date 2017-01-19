<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$sections = $displayData['sections'];
$callbacks = $displayData['callbacks'];

?>

<script>function toggleContainer(name)
{
    var e = document.getElementById(name);// MooTools might not be available ;)
    e.style.display = (e.style.display == 'none') ? 'block' : 'none';
}</script>

<div id="system-debug" class="profiler">
    <h1><?php echo JText::_('PLG_DEBUG_TITLE'); ?></h1>

    <?php foreach ($sections as $name => $section) : ?>
        <?php echo JLayoutHelper::render('plugins.system.debug.section', array('section' => $name, 'data' => $section)); ?>
    <?php endforeach; ?>

    <?php foreach ($callbacks as $name => $result) : ?>
        <?php
            $displayData = array('section' => 'callback', 'title' => $name, 'data' => $result);
            echo JLayoutHelper::render('plugins.system.debug.section', $displayData);
        ?>
    <?php endforeach; ?>
</div>