<?php
/**
 * @version     $Id: default.php 2009-05-15 10:43:09Z bembelimen $
 * @package     Joomla!.Administrator
 * @subpackage  Components.Categories
 * @license     GNU/GPL, see http://www.gnu.org/copyleft/gpl.html and LICENSE.php
 * 
 * Categories view default
 * 
 * Joomla! is free software. you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * Joomla! is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Joomla!; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */


// Ensure, that the file was included by Joomla!
defined('_JEXEC') or jexit();

// load tooltip method
JHTML::_('behavior.tooltip');

?>
<form action="<?php echo JRoute::_('index.php?option=com_categories&amp;extension=' . $this->extension->option); ?>" method="post" name="adminForm">
    <table>
        <tr>
            <td width="100%">
                <?php echo JText::_('Filter'); ?>:
                <input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
                <button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
            </td>
            <td nowrap="nowrap">
                <?php echo JHtml::_('grid.state', $this->filter->state); ?>
            </td>
        </tr>
    </table>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="10" align="left">
                    <?php echo JText::_('Num'); ?>
                </th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows);?>);" />
                </th>
                <th class="title">
                    <?php echo JHtml::_('grid.sort',   'Title', 'c.title', @$this->filter->order_Dir, @$this->filter->order); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort',   'Published', 'c.published', @$this->filter->order_Dir, @$this->filter->order); ?>
                </th>
                <th width="5%" nowrap="nowrap">
                    <?php echo JHtml::_('grid.sort',   'Order by', 'c.lft', @$this->filter->order_Dir, @$this->filter->order); ?>
                </th>
                <?php
                    if ($this->extension->option == 'com_content') :
                ?>
                        <th width="5%">
                            <?php echo JText::_('Num Active'); ?>
                        </th>
                        <th width="5%">
                            <?php echo JText::_('Num Trash'); ?>
                        </th>
                <?php
                    endif;
                ?>
                <th width="1%" nowrap="nowrap">
                    <?php echo JHtml::_('grid.sort',   'ID', 'c.id', @$this->filter->order_Dir, @$this->filter->order); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
        <?php
            if ($this->extension->option == 'com_content') :
        ?>
                <td colspan="8">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
        <?php
            else :
        ?>
                <td colspan="6">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
        <?php
            endif;
        ?>
        </tfoot>
        <tbody>
            <?php
                if (count($this->rows)) :
                
                    $i = 0;
                    
                    $parent = $this->ordering['parent'];
                    $n = $this->ordering['parent'];
                    foreach($this->rows as $row) :
                    
                        
                        $link = 'index.php?option=com_categories&extension='. $this->extension->option .'&task=edit&cid[]='. $row->id;
                        $checked = JHTML::_('grid.checkedout',   $row, $i);
                        $published = JHTML::_('grid.published', $row, $i);
                        if ($row->parent == 0) :
                            --$parent;
                        endif;
                        
                        
            ?>
                        <tr class="<?php echo 'row'.$i%2; ?>">
                            <td>
                                <?php echo $this->pagination->getRowOffset($i); ?>
                            </td>
                            <td>
                                <?php echo $checked; ?>
                            </td>
                            <td>
                                <span class="editlinktip hasTip" title="<?php echo JText::_('Title');?>::<?php echo $row->title; ?>">
            <?php
                                    if ( JTable::isCheckedOut($this->user->get ('id'), $row->checked_out) ) :
                                        echo $row->treename;
                                    else :
            ?>
                                    <a href="<?php echo JRoute::_($link); ?>">
                                        <?php echo $row->treename; ?></a>
            <?php
                                    endif;
            ?>
                                </span>
                            </td>
                            <td class="order">
                                <?php echo $published;?>
                            </td>
                            <td class="order">
                                <span><?php echo $this->pagination->orderUpIcon( $i, $row->parent == 0 || ($row->lft > @$this->rows[$row->parent]->lft+1 && @$this->rows[$row->parent]->children > 1), 'orderup', 'Move Up', true); ?></span>
                                <span><?php echo $this->pagination->orderDownIcon( $i, $n, (($row->parent == 0 && $parent > 0) || ($row->rgt < @$this->rows[$row->parent]->rgt-1 && @$this->rows[$row->parent]->children > 1)), 'orderdown', 'Move Down', true ); ?></span>
                            </td>
            <?php
                            if ($this->extension->option == 'com_content') :
            ?>
                                <td class="order">
                                    <?php echo $row->active; ?>
                                </td>
                                <td class="order">
                                    <?php echo $row->trash; ?>
                                </td>
            <?php
                            endif;
            ?>
                            <td class="order">
                                <?php echo $row->id; ?>
                            </td>
                        </tr>
            <?php
                        ++$i;
                    endforeach;
                else :
                
                    if ($this->extension->option == 'com_content') :
            ?>
                        <tr>
                            <td colspan="8">
                                <?php echo JText::_('There are no Categories'); ?>
                            </td>
                        </tr>
            <?php
                    else :
            ?>
                        <tr>
                            <td colspan="6">
                                <?php echo JText::_('There are no Categories'); ?>
                            </td>
                        </tr>
            <?php
                    endif;
                
                endif;
            ?>
        </tbody>
    </table>
    <input type="hidden" name="option" value="com_categories" />
    <input type="hidden" name="extension" value="<?php echo $this->extension->option;?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
