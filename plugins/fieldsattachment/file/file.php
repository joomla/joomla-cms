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

 
class plgfieldsattachment_file extends JPlugin
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
            $name = "file";
            if(empty($this->params)){
                    $plugin = JPluginHelper::getPlugin('fieldsattachment', $name);
                    $this->params = new JParameter($plugin->params); 
                }
            $this->params->set( "name" , $name  );
            //$this->params->set( "path" , '..'.DS.'images'.DS.'documents'  );

           /* $sitepath = JURI::base() ;
            $pos = strrpos($sitepath, "administrator");
            if(!empty($pos)){$sitepath  = JURI::base().'..'.DS;}*/

            $sitepath  =  fieldsattachHelper::getabsoluteURL();

            $this->params->set( "path" , $sitepath .'images'.DS.'documents' );
           // $this->params->set( "sitepath" , $sitepath );

            $this->params->set( "documentpath" , JPATH_INSTALLATION.DS.'..'.DS.'images'.DS.'documents'    ); 

            $lang   =&JFactory::getLanguage();
            $lang->load( 'plg_fieldsattachment_input' );
            JPlugin::loadLanguage( 'plg_fieldsattachment_'.$name );
	}
	  
        function getName()
        {  
                return $this->params->get( 'name', "" );
        }

         



        function renderInput($articleid, $fieldsid, $value )
        {
             //Mirar si existe fichero
            $file = $value;
            $path = $this->params->get( "path" );
            $documentpath = $this->params->get( "documentpath" );
            $documentpath = $this->params->get( "documentpath");
             $documentpath = $this->params->get( "documentpath");
           if(empty($path))
           {
                /*$sitepath = JURI::base() ;
                $pos = strrpos($sitepath, "administrator");
                if(!empty($pos)){$sitepath  = JURI::base().'..'.DS;}*/

                $sitepath  =  fieldsattachHelper::getabsoluteURL();

                $this->params->set( "path" , $sitepath .'images'.DS.'documents' );
                $documentpath=  JPATH_INSTALLATION.DS.'..'.DS.'images'.DS.'documents';
           }
           $file_absolute = $documentpath.DS. $articleid .DS.  $file;

            //$files =  JPATH_SITE .DS."images".DS."documents".DS. $articleid .DS. $file;
            $str .=  '<table>'  ;

            
            
            
            if (JFile::exists( $file_absolute ) && (!empty($file)))
              {
                $str .= '<tr><td><div><a href="'. $path .DS. $articleid  .DS.  $file.'">'.$file.'</a></div></td></tr><tr><td valing="top">';
                $str.= '<label for="field_'.$fieldsid.'_delete">';
                $str .= JTEXT::_("Checkbox for delete file");
                $str .= '</labe>';
                $str .= '<input name="field_'.$fieldsid.'_delete" id="field_'.$fieldsid.'_delete" type="checkbox"   /></td></tr>';

              }
            $str .= '<tr><td><input name="field_'.$fieldsid.'" id="field_'.$fieldsid.'" type="hidden" size="150" value="'.$value.'" /> ';

            $str .= '<input name="field_'.$fieldsid.'_upload" id="field_'.$fieldsid.'_upload" type="file" size="150"  /></td></tr>';
             //$str .= $this->path .DS. $file;
            //$str .= JPATH_SITE .DS."images".DS."documents".DS. $id .DS. $file;

              $str .= '</table>';
            return  $str;
        }

        function getoptionConfig($valor)
        {
             $name = $this->params->get( 'name'  );
             $return ='<option value="file" ';
             if("file" == $valor)   $return .= 'selected="selected"';
             $return .= '>'.$name.'</option>';
             return $return ;
        }

        function getHTML($articleid, $fieldsid)
        {
            $str  = fieldattach::getFileDownload($articleid, $fieldsid);
            return $str;
        }

        function action( $articleid, $fieldsid, $fieldsvalueid)
        {
           $path = $this->params->get( "path" );
           $file = "field_". $fieldsid."_upload";
           $documentpath = $this->params->get( "documentpath");
           if(empty($path))
           {
                /*$sitepath = JURI::base() ;
                $pos = strrpos($sitepath, "administrator");
                if(!empty($pos)){$sitepath  = JURI::base().'..'.DS;}*/

                $sitepath  =  fieldsattachHelper::getabsoluteURL();

                $this->params->set( "path" , $sitepath .'images'.DS.'documents' );
                $documentpath=  JPATH_INSTALLATION.DS.'..'.DS.'images'.DS.'documents';
           }
           
           fieldsattachHelper::deleteFile($file, $articleid, $fieldsid, $fieldsvalueid, $documentpath);
           fieldsattachHelper::uploadFile($file, $articleid, $fieldsid, $fieldsvalueid, $documentpath);
        }
       

}
