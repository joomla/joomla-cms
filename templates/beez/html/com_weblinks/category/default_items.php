<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id$
 * @author Design & Accessible Team ( Angie Radtke / Robert Deutz )
 * @package Joomla
 * @subpackage Accessible-Template-Beez
 * @copyright Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

 ?>

<script language="javascript" type="text/javascript">
<!--
	function tableOrdering( order, dir, task ) {
        var form = document.adminForm;

        form.filter_order.value         = order;
        form.filter_order_Dir.value        = dir;
        document.adminForm.submit( task );
	}
// -->
</script>
<?php

if ($this->params->get('display')) {
	echo '<div class="display">';
	echo '<form action="'.JRoute::_($this->request_url).'" method="post" name="adminForm">';
	echo JText :: _('Display Num') . '&nbsp;';
	echo $this->pagination->getLimitBox();
	echo '<input type="hidden" name="filter_order" value="'.$this->lists['order'].'" />';
	echo '<input type="hidden" name="filter_order_Dir" value="" />';
	echo '</form>';
	echo '</div>';
}

echo '<table class="weblinks">';

if ($this->params->def( 'headings', 1 )) {
	echo '<tr><th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" width="5" id="num">';
	echo JText :: _('Num');
	echo '</th>';
	echo '<th width="90% class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" id="title">';
	JCommonHTML::tableOrdering( 'Web Link', 'title', $this->lists );
	echo '</th>';
	if ($this->params->get('hits')) {
		echo '<th width="10% class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" nowrap="nowrap" id="hits">';
		JCommonHTML::tableOrdering( 'Hits', 'hits', $this->lists );
		echo '</th>';
	}
}
foreach ($this->items as $item)
{
	$odd=$item->odd + 1;
	echo '<tr class="sectiontableentry' . $odd . '">';
    echo '<td align="center" headers="num">';
	echo $this->pagination->getRowOffset( $item->count );
	echo '</td>';
	echo '<td  headers="title">';
	if ( $item->image )
	{
		echo $item->image;
	}
	echo $item->link;
	if ( $this->params->get( 'item_description' ) )
	{
		echo '<br />';
		echo nl2br($item->description);
	}
	echo '</td>';
	if ( $this->params->get( 'hits' ) )
	{
		echo '<td headers="hits">' . $item->hits . '</td>';
	}
	echo '</tr>';
}
echo '</table>';

echo '<p class="counter">';
echo $this->pagination->getPagesCounter();
echo '</p>';
echo $this->pagination->getPagesLinks();
?>