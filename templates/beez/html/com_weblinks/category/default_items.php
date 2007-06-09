<?php
defined('_JEXEC') or die('Restricted access');

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

echo '<div class="display">';
echo '<form action="'.$this->action.'" method="post" name="adminForm">';
echo JText :: _('Display Num') . '&nbsp;';
echo $this->pagination->getLimitBox();
echo '<input type="hidden" name="filter_order" value="'.$this->lists['order'].'" />';
echo '<input type="hidden" name="filter_order_Dir" value="" />';
echo '</form></div>';

echo '<table class="weblinks">';

if ($this->params->def( 'show_headings', 1 )) {
	echo '<tr><th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" width="5" id="num">';
	echo JText :: _('Num');
	echo '</th>';
	echo '<th width="90%" class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" id="title">';
	echo JHTML::_('grid.sort',  'Web Link', 'title', $this->lists['order_Dir'], $this->lists['order'] );
	echo '</th>';
	if ($this->params->get('show_link_hits')) {
		echo '<th width="10%" class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" nowrap="nowrap" id="hits">';
		echo JHTML::_('grid.sort',  'Hits', 'hits', $this->lists['order_Dir'], $this->lists['order'] );
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
	if ( $this->params->get( 'show_link_description' ) )
	{
		echo '<br />';
		echo nl2br($item->description);
	}
	echo '</td>';
	if ( $this->params->get( 'show_link_hits' ) )
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