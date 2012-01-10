<?php
/**
 * @version		$Id: fieldattach.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die;
 // require helper file
//JLoader::register('fieldsattachHelper',  'components/com_fieldsattach/helpers/fieldsattach.php');
/**
 * FIELDSATTACH component helper.
 */
abstract class fieldsattachHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
		JSubMenuHelper::addEntry(JText::_('COM_FIELDSATTACH_SUBMENU_MESSAGES'), 'index.php?option=com_fieldsattach', $submenu == 'fieldsattachs');
		JSubMenuHelper::addEntry(JText::_('COM_FIELDSATTACH_SUBMENU_GROUPS'), 'index.php?option=com_fieldsattach&view=fieldsattachgroups', $submenu == 'fieldsattachgroups');
		
                JSubMenuHelper::addEntry(JText::_('COM_FIELDSATTACH_SUBMENU_UNIDADES'), 'index.php?option=com_fieldsattach&view=fieldsattachunidades', $submenu == 'fieldsattachunidades');
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-fieldsattach {background-image: url(../media/com_fieldsattach/images/tux-48x48.png);}');
		
              /*  if ($submenu == 'fieldsattachs')
		{ 
			$document->setTitle(JText::_('COM_FIELDATTACH_MANAGER_FIELDATTACHS')."sssssssssssssss");
		}
                if ($submenu == 'fieldsattachunidades')
		{
			$document->setTitle(JText::_('COM_FIELDATTACH_MANAGER_FIELDATTACHUNIDADES'));
		}*/
	}
	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($messageId)) {
			$assetName = 'com_fieldsattach';
		}
		else {
			$assetName = 'com_fieldsattach.message.'.(int) $messageId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

        /**
	 * Get a list of the user groups for filtering.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 * @since	1.6
	 */
	static function getGroups()
	{
		$db = JFactory::getDbo();
 
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text FROM #__fieldsattach_groups as a ORDER BY  a.title'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		foreach ($options as &$option) {
			$option->text = $option->text;
                        $option->value =  $option->value  ;
		}

		return $options;
	}
        
         /**
	 * Get a list of the user groups for filtering.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 * @since	1.6
	 */
	public function getGallery($articleid, $fieldsattachid )
	{
		

                $db = JFactory::getDbo();

                $query = $db->getQuery(true);
                 // Select some fields
		$query->select('*');

		// From the hello table
		$query->from('#__fieldsattach_images');
                $query->where("articleid = ".$articleid." AND fieldsattachid=".$fieldsattachid);

                $query->order("ordering");

               

               // $db = JFactory::getDbo();

		 $db->setQuery($query);
		 $rows= $db->loadObjectList();

                 $str = "<ul>";
                 $sitepath  =  fieldsattachHelper::getabsoluteURL();
               foreach ($rows as $row)
                {
                  $str.= '<li style="width:150px; height:150px; margin: 10px 10px 0 0; overflow:hidden; float:left;"><img src="'.$sitepath.''.$row->image1.'" alt="'.$row->title.'" /></li>';

                }
                $str .= "</ul>";


		return $str;
	}

         /**
	 * Get Form XML for edit parameters and HELP	 *
	 * @return	string	An array of JHtml FORM elements.
	 * @since	1.6
	 */
        function getForm($name)
        {
             jimport( 'joomla.form.form' );
            $return = "" ;
            //Load XML FORM ==================================================
            //$file = dirname(__FILE__) . DS . "form.xml";
            $file = JPATH_PLUGINS.DS.'fieldsattachment'.DS.$name.DS.'form.xml';
            // echo "FILEWWWW:".$file;
            $form = $this->form->loadfile( $file ); // to load in our own version of login.xml
            if($form){

                $form = $this->form->getFieldset("parametros_".$name);
                $return .= JHtml::_('sliders.panel', JText::_( "JGLOBAL_FIELDSET_HELP_AND_OPTIONS"), "percha_".$name.'-params');
                $return .=   '<fieldset class="panelform" >
                            <ul class="adminformlist" style="overflow:hidden;">';
                // foreach ($this->param as $name => $fieldset){
                foreach ($form as $field) {
                    $return .=   "<li>".$field->label ." ". $field->input."</li>";
                }
                $return .='</ul> ';
                if(count($form)>1){
                $return .=  '<div><input type="button" value="'.JText::_("Update Config").'" onclick="controler_percha_'.$name.'()" /></div>';
                }
                $return .=  '</fieldset>';
                $return .=  '<script src="'. JURI::base().'../plugins/fieldsattachment/'.$name.'/controler.js" type="text/javascript"></script> ';
            }
            return $return;
        }

        /**
	 * UPLOAD A FILE	 *
	 * @return	nothing	 
	 * @since	1.6
	 */
        function uploadFile($file, $articleid, $fieldsid,  $fieldsvalueid,  $path = null)
        {
            if(!empty($_FILES[$file]['tmp_name'])){
            $SafeFile = $_FILES[$file]['name'];
            
            $SafeFile = str_replace("#", "No.", $SafeFile);
            $SafeFile = str_replace("$", "Dollar", $SafeFile);
            $SafeFile = str_replace("%", "Percent", $SafeFile);
            $SafeFile = str_replace("^", "", $SafeFile);
            $SafeFile = str_replace("&", "and", $SafeFile);
            $SafeFile = str_replace("*", "", $SafeFile);
            $SafeFile = str_replace("?", "", $SafeFile);

            // JError::raiseWarning( 100, $file. " NAMETMP:".$SafeFile." ID:: ". $articleid. " ->  fieldsid ".$fieldsid ." PATH:".$path  );
            //JError::raiseWarning( 100,   $path .DS. $articleid .DS.  $_FILES[$file]["name"] );

            if(!JFile::upload($_FILES[$file]['tmp_name'] , $path .DS. $articleid .DS.  $_FILES[$file]["name"]))
            {
                JError::raiseWarning( 100,  JTEXT::_("Uploda image Error")   );
            }else
            {
                $app = JFactory::getApplication();
                $app->enqueueMessage( JTEXT::_("Uploda image OK")  );
                $nombreficherofinal = $_FILES[$file]["name"];
                if (file_exists( $path .DS. $articleid .DS. $nombreficherofinal))
                {

                    //$nombreficherofinal = $fieldsid."_".$nombreficherofinal;
                    $app->enqueueMessage( JTEXT::_("Name image changed " ). $nombreficherofinal  );
                    //JError::raiseWarning( 100, $_FILES[$file]["name"]. " ". JTEXT::_("already exists. "). " -> Name changed ".$nombreficherofinal   );
                    JFile::move($path .DS. $articleid .DS.$_FILES[$file]["name"], $path .DS. $articleid .DS.$nombreficherofinal);
                } 
                //UPDATE
                $db	= & JFactory::getDBO();
                $query = 'UPDATE  #__fieldsattach_values SET value="'. $nombreficherofinal.'" WHERE id='.$fieldsvalueid ;
                $db->setQuery($query); 
                $db->query();

                return $nombreficherofinal;
            }
            } 
        }

        function deleteFile($file, $articleid, $fieldsid,  $fieldsvalueid,  $path = null)
        {
            $deletefile = JRequest::getVar("field_". $fieldsid.'_delete');
            $file = JRequest::getVar("field_". $fieldsid);

            if($deletefile){
                     
                     //echo $this->path .DS. $file ;
                     $deleted= false;
                     if(empty($selectable)){ 
                         if(!JFile::delete( $path .DS. $articleid .DS.  $file) )
                         { 
                              JError::raiseWarning( 100,  JTEXT::_("Delete file Error")." ".$path   );
                              
                         } else
                         {
                             $deleted = true;
                         }
                     } 
                     if((!empty($selectable)||($deleted)))
                        {
                         
                            //UPDATE
                            $db	= & JFactory::getDBO();
                            $query = 'UPDATE  #__fieldsattach_values SET value="" WHERE fieldsid='.$fieldsid. ' AND articleid='.$articleid ;
                            $db->setQuery($query);
                            $db->query();
                            $app = JFactory::getApplication();
                            $app->enqueueMessage( JTEXT::_("Delete image")   );
                            

                        }

                    }
        }

         //IMAGE RESIZE FUNCTION FOLLOW ABOVE DIRECTIONS
        public function resize($nombre,$archivo,$ancho,$alto,$id, $path, $filter=NULL)
        {
            $path_absolute = JPATH_BASE ;
            $app = JFactory::getApplication();

            $arr1 = explode(".", $nombre );
            $tmp = $arr1[1];

            //$nombre = $path_absolute."/".$path .DS. $id .DS. $nombre;
            $nombre =  $path .DS. $id .DS. $nombre;
            $destarchivo = $path .DS. $id .DS. $archivo;
            //$archivo =  $path_absolute."/".$path .DS. $id .DS. $archivo;
            $archivo =  $path .DS. $id .DS. $archivo;

            //$app->enqueueMessage( JTEXT::_("Name file:  ").$nombre);
            //$app->enqueueMessage( JTEXT::_("New name:  ").$archivo);

            if(!file_exists($archivo)){
                JError::raiseWarning( 100, JTEXT::_("Not file exist ")  );
            }

            if (preg_match('/jpg|jpeg|JPG/',$archivo))
                {
                $imagen=imagecreatefromjpeg($archivo);
                }
            if (preg_match('/png|PNG/',$archivo))
                {
                $imagen=imagecreatefrompng($archivo);
                }
            if (preg_match('/gif|GIF/',$archivo))
                {
                $imagen=imagecreatefromgif($archivo);
                }

            $x=imageSX($imagen);
            $y=imageSY($imagen);
            if (!empty($ancho)) $w = $ancho; else $w = 0;
            if (!empty($alto)) $h = $alto; else $h = 0;

            $app->enqueueMessage( JTEXT::_("ORIGINAL: ")." width:".$x." height:".$y  );

            if($h > 0) { $ratio = ($y / $h); $w = round($x / $ratio);}
            else { $ratio = ($x / $w); $h = round($y / $ratio);}


            if(!empty($filter))
            {


                    if($filter =="IMG_FILTER_NEGATE") $filter_num = 0;
                    if($filter =="IMG_FILTER_GRAYSCALE") $filter_num = 1;
                    if($filter =="IMG_FILTER_BRIGHTNESS") $filter_num = 2;
                    if($filter =="IMG_FILTER_CONTRAST") $filter_num = 3;
                    if($filter =="IMG_FILTER_COLORIZE") $filter_num = 4;
                    if($filter =="IMG_FILTER_EDGEDETECT") $filter_num = 5;
                    if($filter =="IMG_FILTER_EMBOSS") $filter_num = 6;
                    if($filter =="IMG_FILTER_GAUSSIAN_BLUR") $filter_num = 7;
                    if($filter =="IMG_FILTER_SELECTIVE_BLUR") $filter_num = 8;
                    if($filter =="IMG_FILTER_MEAN_REMOVAL") $filter_num = 9;
                    if($filter =="IMG_FILTER_SMOOTH") $filter_num = 10;
                    if($filter =="IMG_FILTER_PIXELATE") $filter_num = 11;
                    if(imagefilter($imagen, $filter_num, 50))
                    {
                        $app->enqueueMessage( JTEXT::_("Apply filter:").$filter_num  );
                    }  else {
                        JError::raiseWarning( 100,  JTEXT::_("Apply filter ERROR:").$filter_num   );
                    }

            }

            // intentamos escalar la imagen original a la medida que nos interesa

             if(($w==0)||($h==0)) {$w=$x; $h=$y;}
            //$destino=ImageCreateTrueColor($w,$h);
             $app->enqueueMessage( JTEXT::_("IMAGE RESIZE: ")." width:".$w." height:".$h  );
            $destino = ImageCreateTrueColor($w,$h)
            or JError::raiseWarning( 100, JTEXT::_("Not created image  ")  );

            imagecopyresampled($destino,$imagen,0,0,0,0,$w,$h,$x,$y);

            if(!file_exists($archivo)){
                JError::raiseWarning( 100, JTEXT::_("Not file exist ")  );
            }else{
                //JFile::delete( $archivo );
                //$app->enqueueMessage( JTEXT::_("DELETE FILE   ").$archivo  );
            }

            $created = false;
            if (preg_match("/png/",$archivo))
                {
                $created = imagepng($destino,$archivo);
                }
            if (preg_match("/gif/",$archivo))
                {
                $created = imagegif($destino,$archivo);
                }
            else
                {
                $created = imagejpeg($destino,$archivo);

                }

             if($created){   $app->enqueueMessage( JTEXT::_("CREATE IMAGE OK   ").$archivo  );}
                else{JError::raiseWarning( 100, JTEXT::_("I can't create the image: ".$archivo)  );}

            imagedestroy($destino);
            imagedestroy($imagen);
        }

         //GET URL absolute
        public function getabsoluteURL()
        {
            $sitepath = JURI::base() ;
            $pos = strrpos($sitepath, "administrator");
            if(!empty($pos)){
                   // $sitepath  = JURI::base().'..'.DS;
                   // echo $sitepath."<br>";
                    $sitepath = str_replace ("administrator/", "", $sitepath);
                   // echo $sitepath."<br>";
                    } 
            return $sitepath;
        }

         //GET PATH absolute
        public function getabsolutePATH()
        {
            $sitepath = JPATH_BASE ;
            echo "";
            $pos = strrpos($sitepath, "administrator");
            if(!empty($pos)){
              //  echo "<br>ENTRAAAAAAAAAAAAAAAAAAAAAA: ".$sitepath;
                $sitepath = str_replace ("/administrator", "", $sitepath);
                //echo "<br>sale: ".$sitepath;
            }
            return  $sitepath;
        }

        /**
	* Arrauy    get fields for a id
	*
	* @access	public
	* @since	1.5
	*/
        public function  getfieldsForAll($id)
        {
           
            $db	= & JFactory::getDBO();
            $query = 'SELECT a.catid, a.language FROM #__content as a WHERE a.id='. $id  ;

            $db->setQuery( $query );
            $elid = $db->loadObject();
            $idioma = $elid->language;

            $empty = array();
            $db	= & JFactory::getDBO();
            $query = 'SELECT a.id as idgroup, a.title as titlegroup, a.catid, a.language, a.recursive, b.* FROM #__fieldsattach_groups as a INNER JOIN #__fieldsattach as b ON a.id = b.groupid ';
            $query .= 'WHERE a.catid = 0 AND a.published=1 AND b.published = 1 ';
            //echo $elid->language."Language: ".$idioma;
            $query .= ' AND (a.language="'.$elid->language.'" OR a.language="*" ) AND (b.language="'.$elid->language.'" OR b.language="*") ' ;
                  // echo "filter::". $app->getLanguageFilter();
                  // echo "filter::". JRequest::getVar("language");

            $query .='ORDER BY a.ordering, a.title, b.ordering';
            // echo $query;
            $db->setQuery( $query );
            $result = $db->loadObjectList();
            if($result) return $result;
            else return $empty  ;
        }

        /**
	* Array  HTML get fields for a id
	*
	* @access	public
	* @since	1.5
	*/
        public function getfieldsForArticlesid($id, $fields = null)
        {

             $db	= & JFactory::getDBO();
            $query = 'SELECT a.catid, a.language FROM #__content as a WHERE a.id='. $id  ;

            $db->setQuery( $query );
            $elid = $db->loadObject();
            $idioma = $elid->language;
            
	    $empty = array();
            //$id = ",".$id.",";
            $db	= & JFactory::getDBO();

            $query = 'SELECT a.id as idgroup, a.title as titlegroup, a.catid, a.language, a.recursive, b.*, a.articlesid FROM #__fieldsattach_groups as a INNER JOIN #__fieldsattach as b ON a.id = b.groupid ';
            //$query .= 'WHERE (a.articlesid LIKE "%,'. $id .',%" )  AND a.published=1 AND b.published = 1 ';
            $query .= 'WHERE  a.published=1 AND b.published = 1 ';

             $query .= ' AND (a.language="'.$elid->language.'" OR a.language="*" ) AND (b.language="'.$elid->language.'" OR b.language="*") ' ;

             $query .='ORDER BY a.ordering, a.title, b.ordering';
            //echo $query;
            $db->setQuery( $query );

            //(a.articlesid LIKE "%,'. $id .',%" )  AND
            $results = $db->loadObjectList();

            //echo "<br>count: " . count($results);
            $cont =0;
            if($results)
            {
                foreach($results as $result)
                {
                    $taula =  explode(",", $result->articlesid);
                    //echo "<br>srting:: ". $result->id;
                    //echo "<br>contar taula:: ". count($taula);
                    $trobat = false;
                    foreach ($taula as $theid)
                    {
                        //echo "<br>buscando: " . $theid;
                        if($theid == $id){
                            $trobat = true;
                           // echo "<br>trobat: " . $theid;
                            break;
                        }
                        else{
                            $trobat = false;
                            }
                    }
                    if(! $trobat){
                        unset($results[$cont]);
                    }else{
                        //Find in the fields,   exist?
                        if($fields){
                            foreach($fields as $obj)
                            {
                                if($result->id == $obj->id) unset($results[$cont]);
                            }
                        }
                    }
                    $cont++;

                }
                return $results;
            }
	 return $empty;


        }



}
