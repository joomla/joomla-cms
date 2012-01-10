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
 
 
class plgfieldsattachment_listunits extends JPlugin
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
            $name = "listunits";
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

        function renderInput($articleid, $fieldsid, $value, $extras = null )
        {
            //ADD JAVASSCRIPT FUNCTION *** 
            $str  = "<script> window.addEvent('domready', function(){addRow('".$fieldsid."')})</script>    ";

            //$doc =& JFactory::getDocument();
            //$doc->addScriptDeclaration( $str );

            //READ EXTRA INFO ***
            $tmp = $extras;
            $galeria ="";
            //$str .= "<br> resultado1: ".$tmp;
            $lineas = explode(chr(13),  $tmp);
            //$str .= "<br> resultado2: ".$lineas[0];
            foreach ($lineas as $linea)
            {
                $tmp = explode('|',  $linea);
                $contador = 0;
                $num_row = 0;


                $str .=  '<div style=" "><table id="table_insert_'.$fieldsid.'" >
                <tbody id="my_table_insert_'.$fieldsid.'"> ';

               if(count($tmp)>0){
                foreach ($tmp as $obj)
                    {
                     $value1="";
                     $str .= '<tr>';
                     $name  = str_replace (" ", "_", $obj);
                     $str .= '<td>'.$obj.'</td>';
                     $str .= '<td><input name="'.$name.'" class="json_'.$fieldsid.'" type="text" size="120" value="'.$value1.'" /></td>';
                     $str .= '</tr>';
                    }
                }


                $contador = 0;

                $str .= '</table>';
                $str .='<input type="button" id="addRow'.$fieldsid.'" class="field_'.$fieldsid.'" value="Add Row" /> ';
                $str .= '<br /><br /><br /><br /><table  id="table_result_'.$fieldsid.'" class="table_result_'.$fieldsid.'" cellspacing="0">';
                $str .= '<thead><tr>';
                if(count($tmp)>0){
                foreach ($tmp as $obj)
                    {
                     $value1="";
                      $str .= '<td style="  border:1px #eee solid;  font-size:14px; font-weight:bold; padding:7px; color:#333;">'.$obj.'</td>';

                     //$str .= '<td><input name="obj_'.$contador.'" type="text" size="40" value="'.$value.'" /></td>';
                     $contador++;
                    }
                    }
                // $str .= '<td><a href="#" id="delete'.$num_row.'" class="delete">'.JText::_("DELETE").'</a></td>';
                $str .= '<td style=" border:1px #eee solid;  font-size:13px; font-weight:bold; padding:7px;color:#333;">'.JText::_("DELETE").'</td>';
                $str .= '</tr></thead>';
                $str .='<tbody id="table_result_body_'.$fieldsid.'" class="my_body_insert field_'.$fieldsid.'">';
                $valor = $value ;
                //echo $valor."<br>";
                $json = explode("},", $valor);



                if(count($json)>0)
                {
                    $i = 0;
                    foreach ($json as $linea )
                    {
                        //$linea =  substr($linea, 0 , strlen($linea)-1);
                        $linea = str_replace("},", "", $linea);
                        $linea = str_replace("}", "", $linea);
                        $linea =   $linea. '}';
                        //echo  $linea;
                       // $jsonobj = json_decode('{"Modelo":"asd","Largo_mts":"sdafsfas","Acción":"dfasdf","Tramos":"","Plegado":"","ø_Base":"","Peso_g":"","Cajas":"","CÓDIGO":""}');
                        $jsonobj = json_decode( $linea );

                        if($i%2) {$color="#eee";} else{$color="#fff";}
                        $str .='<tr id="tr_'.$i.'" class="el_field_'.$fieldsid.'" >';
                        $delete = false;
                        foreach ($tmp as $obj)
                        {
                             $value="";
                             $name  = str_replace (" ", "_", $obj);
                             if(!empty($jsonobj->{$name})){
                                 $str .= '<td style="     font-size:11px;   padding:7px; color:#333; " class="'.$name.'">';
                                 $str .=  $jsonobj->{$name};
                                 $str .='</td>';
                                  $delete = true;
                             }
                        }
                        $i++;
                        if($delete) $str .='<td style="   font-size:11px;   padding:7px; color:#333;   "><a href="#" class="deleterow" >Delete</a></td>';

                        //$str .='<td>ss'. count($json);

                        JError::raiseWarning( 100, $obj );


                        $str .='</tr>';

                    }
                }

                $str .='</tbody>';
                $str .= '</table>';
                $str .= '</div>';
            }
            $valor = htmlspecialchars( $valor );
            $str .= '<input name="field_'.$fieldsid.'" id="field_'.$fieldsid.'"  class="alljson" type="hidden" size="150" value="'.$valor.'" />';
 
            return  $str;
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="listunits" ';
             if("listunits" == $valor)   $return .= 'selected="selected"';
             $return .= '>'.$name.'</option>';
             return $return ;
        } 
        
         function getHTML($articleid, $fieldid)
        {
            $str = fieldattach::getListUnits($articleid, $fieldid);
            return $str;
        }

        function action()
        {

        }
}
