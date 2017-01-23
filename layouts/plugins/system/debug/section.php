<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$section = $displayData['section'];
$suffix  = isset($displayData['suffix']) ? $displayData['suffix'] : '';
$data    = $displayData['data'];
$title   = isset($displayData['title']) ? $displayData['title'] : JText::_('PLG_DEBUG_' . strtoupper($section));

?>

<div class="dbg-header<?php echo $data['hasError'] ? ' dbg-error' : ''; ?>"
	onclick="toggleContainer('dbg_container_<?php echo $section, $suffix; ?>');">
    <a href="javascript:void(0);"><h3><?php echo $title; ?></h3></a>
</div>
<div style="display: none;" class="dbg-container" id="dbg_container_<?php echo $section, $suffix; ?>">
        <?php echo $this->sublayout($section, $data); ?>
</div>
