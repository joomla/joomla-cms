<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$memory = $displayData['memory'];
$peak = $displayData['peak'];
$limit = $displayData['limit'];

$decimal = JText::_('DECIMALS_SEPARATOR');
$thousands = JText::_('THOUSANDS_SEPARATOR');
$bytes = JText::_('PLG_DEBUG_BYTES');

?>

<div>
    <span class="label label-default"><?php echo JHtml::_('number.bytes', $memory); ?></span>
    (<span class="label label-default">
        <?php echo number_format($memory, 0, $decimal, $thousands), ' ', $bytes; ?>
    </span>)
</div>

<div>
    <span class="label label-default"><?php echo JHtml::_('number.bytes', $peak); ?></span>
    (<span class="label label-default">
        <?php echo number_format($peak, 0, $decimal, $thousands), ' ', $bytes; ?>
    </span>)
</div>
