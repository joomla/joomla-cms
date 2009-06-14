<?php
/**
 * @version     $Id: controller.php 2009-05-15 10:43:09Z bembelimen $
 * @package     Joomla!.Administrator
 * @subpackage  Components.Categories
 * @license     GNU/GPL, see http://www.gnu.org/copyleft/gpl.html and LICENSE.php
 * 
 * Starting point of com_categories
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

// include basic controller file
jimport('joomla.application.component.controller');

/**
 * 
 * Standard Categories Controller
 * @package Joomla!
 * @subpackage Categories
 * @since 1.6
 *
 */
class CategoriesController extends JController {
    
    /**
     * 
     * Constructor
     * 
     * @access public
     * @param void
     * @return void
     * @since 1.5
     *
     */
    public function __construct() {
        
        parent::__construct();
        
        // Register extra tasks
        $this->registerTask('add',  'display');
        $this->registerTask('new',  'display');
        $this->registerTask('apply', 'save');
        $this->registerTask('publish', 'publish');
        $this->registerTask('unpublish', 'publish');
        /**
         * @todo copy/move
         */
        
    }
    
    /**
     * 
     * Display the output
     * 
     * @access public
     * @param void
     * @return void
     * @since 1.5
     *
     */
    public function display() {
        
        // let's check the task and load the differnet views
        switch($this->getTask()) :
        
        // we'll add a new category
        case 'add':
            {
                // hide the menu
                JRequest::setVar('hidemainmenu', 1);
                // load the category view
                JRequest::setVar('view'  , 'category');
                // and no, we will not edit
                JRequest::setVar('edit', false);
            } break;
        
        // we'll edit an old category
        case 'edit':
            {
                // hide the menu
                JRequest::setVar('hidemainmenu', 1);
                // load the category view
                JRequest::setVar('view'  , 'category');
                // yep, we'll edit
                JRequest::setVar('edit', true);
            } break;
        /**
         * 
         * @todo move/copy
         * 
         */
        
        endswitch;
        parent::display();
        
    }
    
    /**
     * 
     * Save a row
     * 
     * @access public
     * @param void
     * @return void
     * @since 1.6
     *
     */
    public function save() {
        
        // is this really a form submit?
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        // get Instance of the database object
        $db = JFactory::getDBO();
        // get Instance of the application
        $app = JFactory::getApplication();
        // load the component, where we are saving for (default: com_content)
        $extension = JRequest::getCmd('extension', 'com_content');
        
        // load all fields
        $request = JRequest::get('request');
        
        // clean up specific fields
        $request['description'] = JRequest::getVar('description', '', 'request', 'string', JREQUEST_ALLOWRAW);
        
        // get the table Instance
        $row = JTable::getInstance('category');
        
        // check if the row was saved
        if(!$row->save($request)) :
        
            // if not, checkout and raise error
            $row->checkin();
            JError::raiseError(500, $row->getError());
        
        endif;
        
        // switch the task
        switch ($this->getTask()) :
        
        // if we have pressed the "apply" button
        case 'apply' :
            {
                // set the info message
                $msg = JText::_('Changes to Category saved');
                // redirect back to the edit field
                $app->redirect('index.php?option=com_categories&extension='. $extension .'&task=edit&cid[]='. $row->id, $msg);
            }break;
        
        // if we have saved the input
        case 'save' :
        default :
            {
                // check in the row
                $row->checkin();
                // set the info message
                $msg = JText::_('Category saved');
                // redirect to the category overview
                $app->redirect('index.php?option=com_categories&extension='. $extension, $msg);
            }break;
        
        endswitch;
        
    }
    
    /**
     * 
     * Delete a row
     * 
     * @access public
     * @param void
     * @return void
     * @since 1.6
     *
     */
    public function remove() {
        
        // is this really a form submit?
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        // get Instance of the database object
        $db = JFactory::getDBO();
        // get Instance of the application
        $app = JFactory::getApplication();
        // load the component, where we are saving for (default: com_content)
        $extension = JRequest::getCmd('extension', 'com_content');
        // mark all deleted rows
        $deleted = array();
        
        // load all ids, we have to delete
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        
        // check, if we have min 1 id
        if (count($cid) < 1) {
            
            // if not, raise an Error
            JError::raiseError(500, JText::_('Select a category to delete', true));
            
        }
        
        // convert all ids to (int)
        JArrayHelper::toInteger($cid);

        // generate the database table
        if (strpos($extension, 'com_') === 0) :
            
            // remove "com_"
            $table = substr($extension, 4);
            
        else :
        
            // or use only $extension
            $table = $extension;
            
        endif;

        // check if we're allowed to use the table
        $tablesAllowed = $db->getTableList();
        
        // if not, return false
        if (!in_array($db->getPrefix().$table, $tablesAllowed)) :
        
            JError::raiseError(500, JText::_("Table not supported"));
        
        endif;
        
        // get a JTable Instance
        $row = JTable::getInstance('category');
        
        // loop all ids
        foreach ($cid as $id) :
        
            // load the whole tree of the item
            $rows = $row->getTree($id);
            
            // if this id not exists, continue
            if (count($rows) < 1) :
            
                continue;
            
            endif;
            
            // clear $temp
            unset($temp);
            
            // now get all ids
            foreach($rows as $tid) :
            
                $temp[] = $tid->id;
            
            endforeach;
            
            // load the model Instance
            $model = $this->getModel('categories');
            
            // get number of not empty categories
            $count = $model->countRows($table, $temp);
            
            // if we have some
            if ($count > 0) :
            
                // mark the id
                $deleted[$id] = false;
                
                // continue the foreach
                continue;
            
            endif;
            
            // try to delete the row (+childs)
            if (!$row->delete($id)) :
            
                JError::raiseError(500, $row->getError());
                return false;
            
            endif;
            
            // loop all deleted ids
            foreach ($temp as $tid) :
            
                // mark all succestfull categories as true
                $deleted[$tid] = true;
                
            endforeach;
        
        endforeach;
        
        // clear $tempdel
        unset($tempdel);
        
        // loop all deleted categories
        foreach ($deleted as $key => $value) :
        
            // if deletion failed
            if($value == false) :
                
                // save the key
                $tempdel[] = $key;
            
            endif;
        
        endforeach;
        
        // are there some categories left?
        if (count($tempdel)) :
            
            // output warning
            $cids = implode(", ", $tempdel);
            $msg = JText::sprintf('WARNNOTREMOVEDRECORDS', $cids);
        
        else :
        
            // all succestfull
            $msg = JText::_('Categories successfully deleted');
        
        endif;
        
        // redirect to the overview
        $this->setRedirect('index.php?option=com_categories&extension='.$extension, $msg);
        
    }
    
    /**
     * 
     * orderup a row
     * 
     * @access public
     * @param void
     * @return void
     * @since 1.6
     *
     */
    public function orderup () {
        
        // is this really a form submit?
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        // load the component, where we are saving for (default: com_content)
        $extension = JRequest::getCmd('extension', 'com_content');
        
        // load all ids, we have to delete
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        
        $id = (int)$cid[0];

        if (!is_array( $cid ) || $id < 1) :
        
            // if not, raise an Error
            JError::raiseError(500, JText::_('Select a Category to move', true));
            return false;
            
        endif;
        
        $row = JTable::getInstance('category');
        
        if(!$row->orderup($id)) :
        
            $msg = JText::_( 'Ordering failed' );
            
        else :

            $msg = JText::_( 'Categories reordered');
            
        endif;
        
        // redirect to the overview
        $this->setRedirect('index.php?option=com_categories&extension='.$extension, $msg);
        
    }

    public function orderdown () {
        
        // is this really a form submit?
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        // load the component, where we are saving for (default: com_content)
        $extension = JRequest::getCmd('extension', 'com_content');
        
        // load all ids, we have to delete
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        
        $id = (int)$cid[0];

        if (!is_array( $cid ) || $id < 1) :
        
            // if not, raise an Error
            JError::raiseError(500, JText::_('Select a Category to move', true));
            return false;
            
        endif;
        
        // load JTable Instance
        $row = JTable::getInstance('category');
        
        // check if successful
        if(!$row->orderdown($id)) :
        
            // generate error message
            $msg = JText::_( 'Ordering failed' );
            
        else :

            // generate successt message
            $msg = JText::_( 'Categories reordered');
            
        endif;
        
        // redirect to the overview
        $this->setRedirect('index.php?option=com_categories&extension='.$extension, $msg);
        
    }
    
    public function publish () {
        
        // is this really a form submit?
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        // load the current user object
        $user = JFactory::getUser();
        // load the component, where we are saving for (default: com_content)
        $extension = JRequest::getCmd('extension', 'com_content');
        
        // load all ids, we have to delete
        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        
        // convert all ids to (int)
        JArrayHelper::toInteger($cid);
        
        // load the JTable Instance
        $row = JTable::getInstance('category');
        
        // switch the task
        switch ($this->getTask()) :
        
        // let's publish it
        case 'publish' :
            {
                
                // check if it worked
                if ($row->publish($cid, 1, $user->get('id'))) :
                
                    // yep
                    $msg = JText::_( 'Categories Published' );
                    
                else :
                
                    // generate error
                    $msg = JText::_( 'Publish failed' );
                
                endif;
                
            }break;
        
        // let's unpublish it
        case 'unpublish' :
            {
                
                // check if it worked
                if ($row->publish($cid, 0, $user->get('id'))) :
                    
                    // yep
                    $msg = $msg = JText::_( 'Categories unpublished' );
                    
                else :
                
                    // generate error
                    $msg = $msg = JText::_( 'Unpublish failed' );
                
                endif;
                
            }break;
        
        endswitch;
        
        // redirect to the overview
        $this->setRedirect('index.php?option=com_categories&extension='.$extension, $msg);
        
    }

}
