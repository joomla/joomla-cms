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
$dir = dirname(__FILE__);
$dir = $dir.DS.'..'.DS.'..'.DS.'..'.DS;
JLoader::register('fieldsattachHelper',   $dir.'administrator/components/com_fieldsattach/helpers/fieldsattach.php');
 
class plgfieldsattachment_checkbox extends JPlugin
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
            $name = "checkbox";
            if(empty($this->params)){
                    $plugin = JPluginHelper::getPlugin('fieldsattachment', $name);
                    $this->params = new JParameter($plugin->params); 
                }
            $this->params->set( "name" , $name  );
	}
	  
        function getName()
        {  
                return $this->params->get( 'name', "" );
        }
 


        function renderInput($articleid, $fieldsid, $value, $extras=null )
        {  
            $tmp = explode("|", $extras);
            $nombre = $tmp[0];
            $valor = $tmp[1];
            //$str .= "<br> resultado1: ".$tmp;
            $str .=  '<div style="float:left;"><label for="field_'.$fieldsid.'">'.$nombre.'</label><input name="field_'.$fieldsid.'" type="checkbox"  value="'.$valor.'" ';
            if($value == $valor) $str .= 'checked'; {}
            $str .= '/></div><div style="float:left;"></div>'  ;
            return  $str;
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="checkbox" ';
             if("checkbox" == $valor)   $return .= 'selected="selected"';
             $return .= '>'.$name.'</option>';
             return $return ;
        }

        function getHTML($articleid, $fieldid)
        {
            $valor = fieldattach::getValue($articleid, $fieldid);
	    if(!empty($valor))    $str = '<div class="field_'.$fieldsid.'">'.fieldattach::getName($articleid, $fieldid).'</div>';
            return $str;
        }

        function action()
        {
            
        }
       

}
