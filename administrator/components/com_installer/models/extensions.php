<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    
 * @package     
 */

defined('_JEXEC') or die;

class InstallerModelExtensions extends JModel
{    
    public function getUnprotectedExtensions()
    {
        $db = $this->getDbo();
        $list = $db->setQuery('SELECT extension_id, name, element, folder, type, client_id FROM #__extensions WHERE protected = 0 AND enabled = 1')->loadObjectList();
        $lang = JFactory::getLanguage();
        
        // Bind additional data from manifest and add to xml
        for ($i = 0; count($list) > $i; $i++)
        {
            switch ($list[$i]->type)
            {
                case 'component' :
                    $file = JFile::read(JPATH_ADMINISTRATOR.'/components/'.$list[$i]->element.'/'.substr($list[$i]->element, 4).'.xml');
                    $lang->load($list[$i]->element);
                    break;
                case 'module' : 
                    if ($list[$i]->client_id) 
                    {   
                        $file = JFile::read(JPATH_ADMINISTRATOR.'/modules/'.$list[$i]->element.'/'.$list[$i]->element.'.xml');
                        $lang->load($list[$i]->element);
                    }
                    else {
                        $file = JFile::read(JPATH_SITE.'/modules/'.$list[$i]->element.'/'.$list[$i]->element.'.xml');
                        $lang->load($list[$i]->element, JPATH_SITE);
                    }
                    break;
                case 'plugin' :
                    $file = JFile::read(JPATH_PLUGINS.'/'.$list[$i]->folder.'/'.$list[$i]->element.'/'.$list[$i]->element.'.xml');
                    $lang->load('plg_'.$list[$i]->folder.'_'.$list[$i]->element);
                    break;
                case 'template' : 
                    if ($list[$i]->client_id) {
                        $file = JFile::read(JPATH_ADMINISTRATOR.'/templates/'.$list[$i]->element.'/templateDetails.xml');
                        $lang->load('tpl_'.$list[$i]->element);
                    }
                    else {
                        $file = JFile::read(JPATH_SITE.'/templates/'.$list[$i]->element.'/templateDetails.xml');
                        $lang->load('tpl_'.$list[$i]->element, JPATH_SITE);
                    }
                    break;
            }
            $extension = simplexml_load_string($file);
            
            $list[$i]->version = $extension->version;
            $list[$i]->detailsurl = $extension->detailsurl;
        }
        
        return $list;
    }
}