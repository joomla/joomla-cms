<?php
defined('_JEXEC') or die('Restricted access');
/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
        $templateParams = new JParameter($content);
} else {
        $templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$hlevel = $templateParams->get('headerLevelComponent', '2');
$ptlevel = $templateParams->get('pageTitleHeaderLevel', '1');

if ($this->params->get('show_page_title'))
{
        echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
        echo $this->params->get( 'page_title' );
        echo '</h' . $ptlevel . '>';
}

if ($this->category->image || $this->category->description)
{
        $wrap='';
        echo '<div class="contentdescription'.$this->params->get( 'pageclass_sfx' ).'" >';
        if ($this->params->get('image') != -1 && $this->params->get('image') != '')
        {
                $wrap = '<div class="wrap_image">&nbsp;</div>';
                echo '<img src="images/stories/' . $this->params->get('image') . '" class="image_' . $this->params->get('image_align') . '" alt="'.JText::_( 'Contacts' ).'"/>';
        }
        elseif ($this->category->image)
        {
                $wrap = '<div class="wrap_image">&nbsp;</div>';
                echo '<img src="images/stories/' . $this->category->image . '" class="image_' . $this->category->image_position . '" alt="'.JText::_( 'Contacts' ).'"/>';
        }

    if ($this->category->description) {
            echo $this->category->description;
    }
        echo $wrap;
        echo '</div>';
}
?>

<script language="javascript" type="text/javascript">
<!--
        function tableOrdering( order, dir, task ) {
        var form = document.adminForm;

        form.filter_order.value         = order;
        form.filter_order_Dir.value        = dir;
        document.adminForm.submit( task );
// -->
}
</script>
<?php
echo '<form action="'. $this->action.'" method="post" name="adminForm">';

if ($this->params->get('display'))
{       echo '<div class="display">';
        echo JText::_('Display Num') .'&nbsp;';
    echo $this->pagination->getLimitBox();
    echo '</div>';
}
echo '<input type="hidden" name="catid" value="'.$this->category->id.'" />';
echo '<input type="hidden" name="filter_order" value="'.$this->lists['order'].'" />';
echo '<input type="hidden" name="filter_order_Dir" value="" />';
echo '</form>';

echo '<table class="category'.$this->params->get( 'pageclass_sfx').'" >';
if ($this->params->get( 'show_headings' ))
{
        echo '<tr><th  id="Count"  class="sectiontableheader'.$this->params->get( 'pageclass_sfx').'" >';
        echo JText::_('Num');
        echo '</th>';

        if ( $this->params->get( 'show_position' ) )
        {
                echo '<th id="Position" class="sectiontableheader'.$this->params->get( 'pageclass_sfx').'" >';
				echo JHTML::_('grid.sort',  'Position', 'cd.con_position', $this->lists['order_Dir'], $this->lists['order'] );
                echo '</th>';
        }
                     echo '<th  id="Name" class="sectiontableheader'.$this->params->get( 'pageclass_sfx').'" >';
		echo JHTML::_('grid.sort',  'Name', 'cd.name', $this->lists['order_Dir'], $this->lists['order'] );
        echo '</th>';
        if ( $this->params->get( 'show_email' ) )
        {
                echo '<th id="Mail" class="sectiontableheader'.$this->params->get( 'pageclass_sfx').'" >';
                echo JText::_( 'Email' );
                echo '</th>';
        }

        if ( $this->params->get( 'show_telephone' ) )
        {
                echo '<th id="Phone" class="sectiontableheader'.$this->params->get( 'pageclass_sfx').'" >';
                echo JText::_( 'Phone' );
                echo '</th>';
        }

        if ( $this->params->get( 'show_mobile' ) )
        {
                echo '<th id="mobile" class="sectiontableheader'.$this->params->get( 'pageclass_sfx').'" >';
                echo JText::_( 'Mobile' );
                echo '</th>';
        }

        if ( $this->params->get( 'show_fax' ) )
        {
                echo '<th id="Fax" class="sectiontableheader'.$this->params->get( 'pageclass_sfx').'" >';
                echo JText::_( 'Fax' );
                echo '</th>';
        }
        echo '</tr>';
}

echo $this->loadTemplate('items');

echo '</table>';

echo '<p class="counter">';
echo $this->pagination->getPagesCounter();
echo '</p>';
echo $this->pagination->getPagesLinks();
?>