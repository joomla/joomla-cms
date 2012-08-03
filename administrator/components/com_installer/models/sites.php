<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    Administrator
 * @package     com_installer
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class InstallerModelSites extends JModelList
{
    
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState('name', 'asc');
    }
    
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*')
              ->from('#__update_sites')
              ->order($this->getState('list.ordering').' '.$this->getState('list.direction'));
        
        return $query;
    }
    
    public function publish($ids = array(), $value = 1)
    {
        $result = true;
        
        if (!is_array($ids))
        {
            JError::raiseWarning(500, 'COM_INSTALLER_NO_SELECTION');
        }
        
        $db = JFactory::getDBO();
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        $table = JTable::getInstance('Updatesites', 'InstallerTable');
        
        foreach ($ids as $i => $id)
        {
            $table->load($id);
            $table->enabled = $value;
            if (!$table->store())
            {
                $this->setError($table->getError());
                $result = false;
            }
        }
        
        return $result;
    }
}