<?php
/**
 * @version     $Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Categories view class for the Category package.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
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
        $parentId = 0;

        JToolBarHelper::title(JText::_('Category Manager') .': <small><small>[ '. JText::_($extension->name).' ]</small></small>', 'categories.png');
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList();
        JToolBarHelper::editListX();
        JToolBarHelper::addNewX();
        JToolBarHelper::help('screen.categories');

        foreach ($rows as $v )
        {
            $pt = $v->parent_id;
            $list = @$children[$pt] ? $children[$pt] : array();
            array_push( $list, $v );
            $children[$pt] = $list;
            if($v->parent_id == 0) :
                $parentId++;
            endif;
        }

        $ordering['parent_id'] = $parentId;

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
