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

include_once dirname(__FILE__).'/../default/view.php';

class InstallerViewSites extends InstallerViewDefault
{
    public function display($tpl = null)
    {
        if ($this->getLayout() == 'export')
        {
            $model = JModel::getInstance('Extensions', 'InstallerModel');
            $this->items = $model->getUnprotectedExtensions();
        }
        else
        {
            $this->state = $this->get('State');
            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');
        }
        
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {        
        JToolBarHelper::publish('sites.publish', 'JTOOLBAR_ENABLE', true);
        JToolBarHelper::unpublish('sites.unpublish', 'JTOOLBAR_DISABLE', true);
        JToolBarHelper::divider();
        parent::addToolbar();
    }
}