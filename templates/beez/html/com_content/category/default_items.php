<?php
defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">
<!--
function tableOrdering( order, dir, task )
{
var form = document.adminForm;

form.filter_order.value         = order;
form.filter_order_Dir.value        = dir;
document.adminForm.submit( task );
}
// -->
</script>
<?php


echo '<form action="' . JRoute::_($this->request_url) . '" method="post" name="adminForm">';

if ($this->params->get('filter') || $this->params->get('display')) {
if ($this->params->get('filter')) {
echo '<div class="filter"><p>';
echo JText :: _('Filter') . '&nbsp;';
echo '<input type="text" name="filter" value="' . $this->lists['filter'] . '" class="inputbox" onchange="document.adminForm.submit();" />';
echo '</p></div>';
}
if ($this->params->get('display')) {
echo '<div class="display">';
echo '' . JText :: _('Display Num') . '&nbsp;';
echo $this->pagination->getLimitBox();
echo '</div>';
}
}

echo '<table class="category">';

if ($this->params->get('headings')) {
echo '<tr><th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '"  id="count">';
echo JText :: _('Num');
echo '</th>';
if ($this->params->get('title')) {
echo '<th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '"  id="tableOrdering">';
JCommonHTML :: tableOrdering('Item Title', 'a.title', $this->lists);
echo '</th>';
}
if ($this->params->get('date')) {
echo '<th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '"  id="tableOrdering2">';
JCommonHTML :: tableOrdering('Date', 'a.created', $this->lists);
echo '</th>';
}
if ($this->params->get('showAuthor')) {
echo '<th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '"   id="author">';
JCommonHTML :: tableOrdering('Author', 'author', $this->lists);
echo '</th>';
}
if ($this->params->get('hits')) {
echo '<th align="center" class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" width="5%" nowrap="nowrap" id="hits">';
JCommonHTML :: tableOrdering('Hits', 'a.hits', $this->lists);
echo '</th>';
}
echo '</tr>';
}

foreach ($this->items as $item) {
$oddoreven = $item->odd + 1;
echo '<tr class="sectiontableentry' . $oddoreven . $this->params->get('pageclass_sfx') . '" >';
echo '<td headers="count">' . $this->pagination->getRowOffset($item->count) . '</td>';
if ($this->params->get('title')) {
if ($item->access <= $this->user->get('aid', 0)) {
echo '<td headers="tableOrdering"><a href="' . $item->link . '">' . $item->title . '</a>' . $this->getIcon($item, 'edit') . '</td>';
} else {
echo '<td headers="tableOrdering1">' . $item->title . ' : ';
$link = JRoute::_('index.php?option=com_registration&amp;task=register');
echo '<a href="' . $link . '">' . JText :: _('Register to read more...') . '</a></td>';
}
}
if ($this->params->get('date')) {
echo '<td  headers="tableOrdering2">' . $item->created . '</td>';
}
if ($this->params->get('showAuthor')) {
echo '<td headers="author">';
echo $item->created_by_alias ? $item->created_by_alias : $item->author;
echo '</td>';
}
if ($this->params->get('hits')) {
echo '<td headers="hits">:';
echo $item->hits ? $item->hits : '-';
echo '</td>';
}
echo '</tr>';
}

echo '</table>';
if ($this->params->get('navigation')) {
echo '<p class="counter">' . $this->pagination->getPagesCounter() . '</p>';
echo $this->pagination->getPagesLinks();
}

echo '<input type="hidden" name="id" value="' . $this->category->id . '" />';
echo '<input type="hidden" name="sectionid" value="' . $this->category->sectionid . '" />';
echo '<input type="hidden" name="task" value="' . $this->lists['task'] . '" />';
echo '<input type="hidden" name="option" value="com_content" />';
echo '<input type="hidden" name="filter_order" value="' . $this->lists['order'] . '" />';
echo '<input type="hidden" name="filter_order_Dir" value="" />';
echo '<input type="hidden" name="Itemid" value="' . $Itemid . '" />';
echo '</form>';
?>
