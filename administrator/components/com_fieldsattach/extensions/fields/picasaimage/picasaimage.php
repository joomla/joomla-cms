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
JLoader::register('fieldsattachHelper',  'components/com_fieldsattach/helpers/fieldsattach.php');
 
class plgfieldsattachment_picasaimage extends JPlugin
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
            $name = "picasaimage";
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

        function renderHelpConfig(  )
        {
            $return = "" ;
            $form = $this->form->getFieldset("percha_picasaimage");
            $return .= JHtml::_('sliders.panel', JText::_( "JGLOBAL_FIELDSET_PICASAIMAGE_OPTIONS"), "percha_".$this->params->get( 'name', "" ).'-params');
            $return .=   '<fieldset class="panelform" >
			<ul class="adminformlist" style="overflow:hidden;">';
           // foreach ($this->param as $name => $fieldset){
            foreach ($form as $field) {
                $return .=   "<li>".$field->label ." ". $field->input."</li>";
            }
            $return .=  '<div><input type="button" value="'.JText::_("Update Config").'" onclick="controler_percha_picasaimage()" /></div>';
            $return .=  '</ul> </fieldset>';
            $return .=  '<script src="'. JURI::base().'../plugins/fieldsattachment/picasaimage/controler.js" type="text/javascript"></script> ';
                         
            
            return  $return;
        }



        function renderInput($articleid, $fieldsid, $value, $extras = null)
        {
             $imagepicasa = $value;


            $tmp = $extras;
            $galeria ="";
            //$str .= "<br> resultado1: ".$tmp;
            $lineas = explode(chr(13),  $tmp);
            //$str .= "<br> resultado2: ".$lineas[0];
            foreach ($lineas as $linea)
            {
                $tmp = explode('|',  $linea);
                if(!empty( $tmp[0])) $galeria = $tmp[0];

            }

            $str .='<div class="button2-left"><div class="image"><a class="modal-button" title="Image" href="'.$galeria.'" onclick="IeCursorFix(); return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">Select Image</a></div></div><br /><br /><br />';
            $str .= '<input name="field_'.$fieldsid.'" type="text" size="150" value="'.$imagepicasa.'" />';
            if(!empty($imagepicasa)) $str .= '<img src="'.$imagepicasa.'" alt="" />';

            return  $str;
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="picasaimage" ';
             if("picasaimage" == $valor)   $return .= 'selected="selected"';
             $return .= '>'.$name.'</option>';
             return $return ;
        }

        function getHTML()
        {
            return "";
        }

        function action()
        {

        }
       

}
