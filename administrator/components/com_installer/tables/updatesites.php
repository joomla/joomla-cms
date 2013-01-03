<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    
 * @package     
 */

defined('_JEXEC') or die;

class InstallerTableUpdatesites extends JTable
{
    
    public function __construct(&$db)
    {
        parent::__construct('#__update_sites', 'update_site_id', $db);
    }
    
}