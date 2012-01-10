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
JLoader::register('fieldsattachHelper',   $sitepath.'administrator/components/com_fieldsattach/helpers/fieldsattach.php');
 
class plgfieldsattachment_image extends JPlugin
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
            $name = "image";
            if(empty($this->params)){
                    $plugin = JPluginHelper::getPlugin('fieldsattachment', $name);
                    $this->params = new JParameter($plugin->params); 
                }
            $this->params->set( "name" , $name  );

            /*$sitepath = JURI::base() ;
            $pos = strrpos($sitepath, "administrator");
            if(!empty($pos)){$sitepath  = JURI::base().'..'.DS;}*/

            $sitepath  =  fieldsattachHelper::getabsoluteURL();
            
            $this->params->set( "path" , $sitepath .'images'.DS.'documents' );

            $documentpath  =  fieldsattachHelper::getabsolutePATH(); 
            $this->params->set( "documentpath" , $documentpath.DS.'images'.DS.'documents'    );

            $lang   =&JFactory::getLanguage();
            $lang->load( 'plg_fieldsattachment_input' );
            JPlugin::loadLanguage( 'plg_fieldsattachment_'.$name );
              
	}
	  
        function getName()
        {  
                return $this->params->get( 'name', "" );
        } 

        function renderInput($articleid, $fieldsid, $value, $extras = null )
        {
            $file = $value;
            $selectable="";
            if(!empty($extras))
            {
                //$lineas = explode('":"',  $field->params);
                //$tmp = substr($lineas[1], 0, strlen($lineas[1])-2);
                $tmp = $extras;
                $lineas = explode(chr(13),  $tmp);
                //$str .= "<br> resultado2: ".$lineas[0];
                $str .= '<tr><td><br /><br /><strong>Config</strong><br />';

                foreach ($lineas as $linea)
                {
                    $tmp = explode('|',  $linea);
                    if(!empty( $tmp[0])) $width = $tmp[0];
                    if(!empty( $tmp[1])) $height = $tmp[1];
                    if(!empty( $tmp[2])) $filter = $tmp[2];
                    if(!empty( $tmp[3])) $selectable = $tmp[3];
                    if(!empty( $width )) $str .= 'Size:'.$width;
                    else $str .= 'Size:-- ';
                    if(!empty( $height )) $str .= 'X'.$height;
                    else $str .= 'X --';
                    if(!empty( $filter )) $str .=  '<br />Filter:'.$filter ;
                    if(!empty( $selectable )) $str .=  '<br />Selectable: True ';
                     $str .=  '</td></tr>';
                }
            } 
           
           //$str .= $this->path .DS. $id.DS. $file;
            //$path = $this->params->get( "documentpath" );
            $path = $this->params->get( "path" );
            
            
          //  $str .= "<br>PATH:: ".$path;
            //$file_absolute =  $path .DS. $articleid .DS.  $file;
           //  $str .= "<br>PATH  file_absolute:: ".$file_absolute;
            

            $file_url = $path.DS. $articleid .DS.  $file;
            
            $documentpath = $this->params->get( "documentpath" );
            $file_absolute = $documentpath.DS. $articleid .DS.  $file;

            if($selectable=="selectable")
            {
                $file_url  =  fieldsattachHelper::getabsoluteURL().$file;
                
            }
            //$str .= "<br>".$file_absolute." -> ". file_exists( '/media/Iomega_HDD/trabajos/dalmau/web3/images/documents/60/1003_LLEida1.jpg' )  ;
            
            $str .= '<table>';
            if ( (file_exists( $file_absolute )  && (!empty($file)))||($selectable && !empty($file)))
              { 
                //Name file
                $str .= '<tr><td><img src="'. $file_url.'" ';
                if(!empty( $width )) $str .= 'width="'.$width.'"' ;
                if(!empty( $height )) $str .= 'height="'.$height.'"';
                $str .= ' alt=" "/></td></tr>';
                //Delete
                if($selectable=="selectable")
                {
                    $str.= '<tr><td><div style="overflow:hidden;"><label for="field_'.$fieldsid.'_delete1">';
                    $str .= JTEXT::_("Checkbox for delete file");
                    $str .= '</labe>';
                    $str .= '<input name="field_'.$fieldsid.'_delete1" type="checkbox" onclick="javascript: $(\'field_'.$fieldsid.'\').value= \'\' ;"   /></td></tr> ';
                
                    
                }else{
                    $str.= '<tr><td><div style="overflow:hidden;"><label for="field_'.$fieldsid.'_delete">';
                    $str .= JTEXT::_("Checkbox for delete file");
                    $str .= '</labe>';
                    $str .= '<input name="field_'.$fieldsid.'_delete" type="checkbox"   /></td></tr> '; 
                } 
              }  

                if($selectable=="selectable")
                {
                   
                    $str .= '<tr><td><input name="field_'.$fieldsid.'" id="field_'.$fieldsid.'" type="text" size="150" value="'.$value.'" /> ';

                    $str .= '<tr><td><div class="button2-left">
                        <div class="blank">
                                <a class="modal" title="Select Image" href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=140&amp;author=&amp;fieldid=field_'.$fieldsid.'&amp;folder=" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">
                                        Select Image</a>
                        </div>
                        </div>   ';
                        $str .='</td></tr> ';

                }else{
                           $str .= '<tr><td><input name="field_'.$fieldsid.'" id="field_'.$fieldsid.'" type="hidden" size="150" value="'.$value.'" /> ';

                    $str .= '<input name="field_'.$fieldsid.'_upload" id="field_'.$fieldsid.'_upload" type="file" size="150"  /></td>';
                }
                $str .= '</table><script>function jInsertFieldValue(txt, field){ $(field).value= txt ;}</script>';

            return  $str;
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="image" ';
             if("image" == $valor)   $return .= 'selected="selected"';
             $return .= '>'.$name.'</option>';
             return $return ;
        }

        function getHTML($articleid, $fieldsid)
        {
            $str  ='<div id="cel_'.$articleid.'" class="field_'.$fieldsid.'">'.fieldattach::getImg($articleid, $fieldsid).'</div>';
            return $str;
        }

        function action( $articleid, $fieldsid, $fieldsvalueid)
        {
           $path = $this->params->get( "path" );
           $documentpath = $this->params->get( "documentpath");
           if(empty($path))
           { 

                $sitepath  =  fieldsattachHelper::getabsoluteURL();

                $this->params->set( "path" , $sitepath .'images'.DS.'documents' );
                $documentpath=  JPATH_INSTALLATION.DS.'..'.DS.'images'.DS.'documents';
           }
            
           
           $file = "field_". $fieldsid."_upload";
           fieldsattachHelper::deleteFile($file, $articleid, $fieldsid, $fieldsvalueid, $documentpath);
            $nombreficherofinal = fieldsattachHelper::uploadFile($file, $articleid, $fieldsid, $fieldsvalueid, $documentpath);

            $width =0;
            $height = 0;
            $filter = "";
            $selectable="";
            $nombrefichero="";

            if(!empty($nombreficherofinal)){ 

                    $db = JFactory::getDbo();
                    $query = 'SELECT a.extras FROM #__fieldsattach as a WHERE a.id='.$fieldsid.'';
                    
                    $db->setQuery( $query );
                    $results = $db->loadObject();
                    $tmp ="";
                    //JError::raiseWarning( 100, $obj->type." --- ". $query   );
                    if(!empty($results)){
                           $tmp = $results->extras;
                            //JError::raiseWarning( 100,  " --- ". $results->extras   );
                    } 
                    //$str .= "<br> resultado1: ".$tmp;
                     $lineas = explode(chr(13),  $tmp);
                    //$str .= "<br> resultado2: ".$lineas[0];
                    $str .= '<div>';
                    foreach ($lineas as $linea)
                    {
                        $tmp = explode('|',  $linea);
                        $width = $tmp[0];
                        $height = $tmp[1];
                        $filter = $tmp[2];
                        $selectable= $tmp[3];
                        //echo $width.'ssX'.$height.'<br>';

                        $nombrefichero = $_FILES[$file]["name"];
                    } 
                    // $app = JFactory::getApplication();
                    // $app->enqueueMessage(  "---width:".$width  );
                  

                   fieldsattachHelper::resize($nombreficherofinal, $nombreficherofinal, $width, $height, $articleid, $documentpath, $filter);

                }
        }
       

}
