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
 
class plgfieldsattachment_selectmultiple extends JPlugin
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
            $name = "selectmultiple";
            if(empty($this->params)){
                    $plugin = JPluginHelper::getPlugin('fieldsattachment', $name);
                    $this->params = new JParameter($plugin->params); 
                }
            $this->params->set( "name" , $name  );

	    //Load the Plugin language file out of the administration
            $lang = & JFactory::getLanguage();
            $lang->load('plg_fieldsattachment_'.$this->params->get( "name" ), JPATH_ADMINISTRATOR);
	}
	  
        function getName()
        {  
                return $this->params->get( 'name', "" );
        } 

        function renderInput($articleid, $fieldsid, $value, $extras )
        {
            $tmp = $extras;
            //$str .= "<br> resultado1: ".$tmp;
            $lineas = explode(chr(13),  $tmp);
            //$str .= "<br> resultado2: ".$lineas[0];
            $str .= '<select name="field_'.$fieldsid.'" multiple="multiple">';
            foreach ($lineas as $linea)
            {

                $tmp = explode('|',  $linea);
                $title = $tmp[0];
                $valor = $tmp[1];
                $str .= '<option value="'.$valor.'" ';
                if($value == $valor) $str .= 'selected'; {}
                $str .= ' >';
                $str .= $title;
                $str .= '</option>';

            }
            $str .= '</select>';
            return  $str;
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="selectmultiple" ';
             if("selectmultiple" == $valor)   $return .= 'selected="selected" val:'. $valor;
             $return .= '>'.$name.'</option>';
             return $return ;
        }

        function getHTML($articleid, $fieldsid)
        {
		$valor = fieldattach::getValue( $articleid,  $fieldsids  );
        	$title = fieldattach::getName( $articleid,  $fieldsids  );
		$extras =	fieldattach::getExtra($fieldsids); 
	    //$str .= "<br> resultado1: ".$extras;
	    $lineas = explode(chr(13),  $extras);
	    //$str .= "<br> resultado2: ".$lineas[0]; 
	    $str .= '<select  multiple="multiple"  name="field_'.$field->id.'[]">';
	    foreach ($lineas as $linea)
	    {

	        $tmp = explode('|',  $linea);
	        $title = $tmp[0];
	        $valor = $tmp[1];
	        $str .= '<option value="'.$valor.'" ';
	        if($this->getfieldsvaluearray($fieldsids, $articleid, $valor)) $str .= 'selected'; {}
	        $str .= ' >';
	        $str .= $title;
	        $str .= '</option>';

	    }
            $str .= '</select>';

 
              return $str;
        }

	private function getfieldsvaluearray($fieldsid, $articleid, $value)
        {
            $result ="";
            $db	= & JFactory::getDBO();
            $query = 'SELECT a.value FROM #__fieldsattach_values as a WHERE a.fieldsid='. $fieldsid.' AND a.articleid='.$articleid  ;
            //echo "<br>";
            $db->setQuery( $query );
            $elid = $db->loadObject();
            $return ="";
            if(!empty($elid))
            { 
                $tmp = explode(",",$elid->value); 
                foreach($tmp as $obj)
                {
                    $obj = str_replace(" ","",$obj);
                    $value = str_replace(" ","",$value);
                    //echo "<br>".$obj ."==". $value." -> ".strcmp($obj, $value)." (".strlen($obj).")";
                    if(strcmp($obj, $value) == 0)
                    {
                        //echo "SIIIIIIIIIIIIIIIII" ;
                        return true;
                    }
                }
            }
            return false ;
        }

        function action()
        {

        }

	 
       

}
