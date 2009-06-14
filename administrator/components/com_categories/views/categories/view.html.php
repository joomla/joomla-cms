<?php
/**
 * @version     $Id: view.html.php 2009-05-15 10:43:09Z bembelimen $
 * @package     Joomla!.Administrator
 * @subpackage  Components.Categories
 * @license     GNU/GPL, see http://www.gnu.org/copyleft/gpl.html and LICENSE.php
 * 
 * Categories view
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

// include basic view file
jimport('joomla.application.component.view');

/**
 * 
 * Standard Categories View
 * @package Joomla!
 * @subpackage Categories
 * @since 1.5
 *
 */
class CategoriesViewCategories extends JView {
    
    /**
     * Creates the Main Categories View
     *
     * @access      public
     * @param       string $tpl The name of the template file to parse.
     * @return      void
     * @since       1.5
     * @version     1.6
     */
    public function display($tpl =   null) {
        
        // load current User object
        $user = JFactory::getUser();

        // Get data from the model
        $rows = $this->get('Data');
        $pagination = $this->get('Pagination');
        $extension = $this->get('Extension');
        $filter = $this->get('Filter');
        $parent = 0;
        
        JToolBarHelper::title(JText::_('Category Manager') .': <small><small>[ '. JText::_($extension->name).' ]</small></small>', 'categories.png');
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList();
        JToolBarHelper::editListX();
        JToolBarHelper::addNewX();
        JToolBarHelper::help('screen.categories');

        foreach ($rows as $v )
        {
            $pt = $v->parent;
            $list = @$children[$pt] ? $children[$pt] : array();
            array_push( $list, $v );
            $children[$pt] = $list;
            if($v->parent == 0) :
                $parent++;
            endif;
        }
        
        $ordering['parent'] = $parent;
        
        $rows = JHTML::_('menu.treerecurse', 0, '', array(), $children );
        
        
        $this->assignRef('user', $user);
        $this->assignRef('type', $type);
        $this->assignRef('extension', $extension);
        $this->assignRef('rows', $rows);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('filter', $filter);
        $this->assignRef('ordering', $ordering);

        parent::display($tpl);
        
    }
    
}
