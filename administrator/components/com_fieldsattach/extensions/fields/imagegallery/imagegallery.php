<?php
/**
 * @version		$Id: fieldsattachement.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

// require helper file
$sitepath = JPATH_BASE ;
$sitepath = str_replace ("administrator", "", $sitepath); 
JLoader::register('fieldattach',  $sitepath.'components/com_fieldsattach/helpers/fieldattach.php'); 
JLoader::register('fieldsattachHelper',   $sitepath.'administrator/components/com_fieldsattach/helpers/fieldsattach.php');
 
class plgfieldsattachment_imagegallery extends JPlugin
{
        /**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
         
	function construct( )
	{
            $name = "imagegallery"; 
            if(empty($this->params)){
                    
                    $plugin = JPluginHelper::getPlugin('fieldsattachment', $name);
                    $this->params = new JParameter($plugin->params); 
                }
                 
            $this->params->set( "name" , $name  );

	    $lang   =&JFactory::getLanguage();
            $lang->load( 'plg_fieldsattachment_input' );
            JPlugin::loadLanguage( 'plg_fieldsattachment_'.$name );
	}
	  
        function getName()
        {  
                return $this->params->get( 'name', "" );
        }
        
        function renderInput($articleid, $fieldsid, $value, $extras=null )
        {
            $sitepath  =  fieldsattachHelper::getabsoluteURL();
            $str_gallery = fieldsattachHelper::getGallery($articleid, $fieldsid);
            $str  =  '<div style=" position:relative; width:150px;  overflow: hidden;"><div  class="button2-left" ><div class="image" ><a class="modal-button" title="Article" href="'.$sitepath.'/administrator/index.php?option=com_fieldsattach&view=fieldsattachimages&tmpl=component&articleid='.$articleid.'&fieldsattachid='.$fieldsid.'&reset=1" onclick="IeCursorFix(); return false;" rel="{handler: \'iframe\', size: {x: 980, y: 500}}">'. JText::_("Gallery administrator").'</a></div></div></div>';
            $str .= $str_gallery;
            return  $str  ;
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="imagegallery" ';
             if("imagegallery" == $valor)   $return .= 'selected="selected"';
             $return .= '>'.$name.'</option>';
             return $return ;
        }

        function getHTML($articleid, $fieldsid )
        {
            $str =   fieldattach::getImageGallery($articleid, $fieldsid); 
            return $str;
        }

        function action()
        {

        }

         

}
