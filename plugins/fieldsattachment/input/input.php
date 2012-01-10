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

class plgfieldsattachment_input extends JPlugin
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
        function plgfieldsattachment_input(& $subject, $config)
	{
		parent::__construct($subject, $config);
                 
        }
	function construct( )
	{
            $name = "input";
            
            if(empty($this->params)){
                    $plugin = JPluginHelper::getPlugin('fieldsattachment', $name);
                    $this->params = new JParameter($plugin->params); 
                }
            
            $lang   =&JFactory::getLanguage();
            $lang->load( 'plg_fieldsattachment_input' );
            JPlugin::loadLanguage( 'plg_fieldsattachment_input' );
            
            $this->params->set( "name" , $name  );
	}
	  
        function getName()
        {  
                return  $this->params->get( 'name'  ) ;
        }

        function renderHelpConfig(  )
        { 
            $return = "" ;
            $form = $this->form->getFieldset("percha_input");
            $return .= JHtml::_('sliders.panel', JText::_( "JGLOBAL_FIELDSET_INPUT_OPTIONS"), "percha_".$this->params->get( 'name', "" ).'-params');
            $return .=   '<fieldset class="panelform" >
			<ul class="adminformlist" style="overflow:hidden;">';
           // foreach ($this->param as $name => $fieldset){
            foreach ($form as $field) {
                $return .=   "<li>".$field->label ." ". $field->input."</li>";
            }
             $return .='</ul> ';
            if(count($form)>0){
            $return .=  '<div><input type="button" value="'.JText::_("Update Config").'" onclick="controler_percha_input()" /></div>';
            }
            $return .=  ' </fieldset>';
            //$return .=  '<script src="'. JURI::base().'../plugins/fieldsattachment/input/controler.js" type="text/javascript"></script> ';
            
            
            return  $return;
        }



        function renderInput($articleid, $fieldsid, $value )
        {  
            return  '<input  name="field_'.$fieldsid.'" id="field_'.$fieldsid.'" type="text" size="150" value="'.$value.'" />';
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="input" ';
             if("input" == $valor)   $return .= 'selected="selected"';
             $return .= '>'.$name.'</option>';
             return $return ;
        }

        function getHTML($articleid, $fieldid)
        {
            $str = fieldattach::getInput($articleid, $fieldid);
            return $str;
        }

        function action()
        {

        }
       

}
