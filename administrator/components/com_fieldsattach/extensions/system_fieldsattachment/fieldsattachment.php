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
/*$dir = dirname(__FILE__);
$dir = $dir.DS.'..'.DS.'..'.DS.'..'.DS;
JLoader::register('fieldsattachHelper',   $dir.'administrator/components/com_fieldsattach/helpers/fieldsattach.php');*/

// require helper file
$sitepath = JPATH_BASE ;
$sitepath = str_replace ("administrator", "", $sitepath); 
JLoader::register('fieldsattachHelper',   $sitepath.'administrator/components/com_fieldsattach/helpers/fieldsattach.php');
 
class plgSystemfieldsattachment extends JPlugin
{
        private $str ;
        private $path;
        public $array_fields  = array();
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
	function plgSystemfieldsattachment(& $subject, $config)
	{
		parent::__construct($subject, $config);
                
                $this->path= '..'.DS.'images'.DS.'documents';
                $mainframe = JFactory::getApplication();
		if ($mainframe->isAdmin()) {
 			$document = &JFactory::getDocument();
                	$document->addStyleSheet(   JURI::base().'../plugins/system/fieldsattachment/js/style.css' );
                    $dispatcher =& JDispatcher::getInstance();

                    JPluginHelper::importPlugin('fieldsattachment'); // very important
                    //select

                    $this->array_fields = $this->get_extensions() ;

                     foreach ($this->array_fields as $obj)
                    {
                        $function  = "plgfieldsattachment_".$obj."::construct();";
                        eval($function);
                         
                    } 
                   $this->params->set( 'array_fields', $this->array_fields );

                   //DELETE
                    //JError::raiseWarning( 100,  "DELETE".JRequest::getVar("cid")   ." task:".JRequest::getVar("task")  );
                    if(JRequest::getVar("task") == "articles.trash") { $this->deleteFields();}
                   return;
                }  
	}

        public function deleteFields()
        {
            $app = JFactory::getApplication();
            $db	= & JFactory::getDBO();
            $arrays = JRequest::getVar("cid");
            $ids = "";
            foreach ($arrays as $obj)
            { 
                $query = 'DELETE FROM  #__fieldsattach_values WHERE articleid= '.$obj ;
                $db->setQuery($query);
                $db->query();
                $app->enqueueMessage( JTEXT::_("Delete fields of ID ") . $obj )   ;

            } 

        }
        
        public function onContentBeforeDelete($context, &$article, $isNew)
	{
            
        }


        public function onContentBeforeSave($context, &$article, $isNew)
	{


            //IF TITLE THEN ACTIVE CONTENT =========================================================================================
            $db	= & JFactory::getDBO();
            $user =& JFactory::getUser(); 

            //-----------------------
            $query = 'SELECT  id,asset_id  FROM #__content WHERE created_by='.$user->get(id).' AND title IS NOT NULL AND state  = -2 AND id='.$article->id;
            $db->setQuery( $query );
            $result = $db->loadObject();
            $id = $result->id;
            
            if(!empty($id))
                {   
                 $article->state = 1;
                } 
            
        }
 

        /**
	* Injects Insert Tags input box and drop down menu to adminForm
	*
	* @access	public
	* @since	1.5
	*/
	public function onContentAfterSave($context, &$article, $isNew)
	{
            $app = JFactory::getApplication();
            $user =& JFactory::getUser();

            $sitepath = JPATH_BASE ;
            $sitepath = str_replace ("administrator", "", $sitepath);
            
            

             $fontend = false;
             if( $option=='com_content' && $user->get('id')>0 &&  $view == 'form' &&  $layout == 'edit'  ) $fontend = true; 

            //Crear directorio ==============================================================
             if (($option=='com_content' && $view=="article"   )||($fontend))
             {
                 $this->createDirectory($article->id);

                
             }

             //============================================================================
                //COPY AND SAVE LIKE COPY
                if(JRequest::getVar("id") != $article->id && (!empty( $article->id))   && ($article->id>0) && (JRequest::getVar("id")>0)  ){
                    $oldid = JRequest::getVar("id")  ;
                    $newid = $article->id;
                    //JError::raiseWarning( 100,  "COPY ALL FIELDSATTACHEDS".$article->id    );
                    $db	= & JFactory::getDBO();
                    //COPY __fieldsattach_values VALUES TABLE
                    $query = 'SELECT * FROM #__fieldsattach_values as a  WHERE a.articleid = '. $oldid ;
                    $db->setQuery( $query );
                    $results= $db->loadObjectList();
                    if($results){
                        foreach ($results as $result)
                        {
                            $query = 'SELECT * FROM #__fieldsattach_values as a  WHERE a.articleid = '. $newid.' AND a.fieldsid='.$result->fieldsid ;
                            $db->setQuery( $query );
                            $obj= $db->loadObject();
                            if($obj)
                            {
                                //update
                                 $query = 'UPDATE  #__fieldsattach_values SET value="'.$result->valor.'" WHERE id='.$result->id ;
                                 $db->setQuery($query);
                                 $db->query();
                            }else{
                                //insert
                                 $query = 'INSERT INTO #__fieldsattach_values(articleid,fieldsid,value) VALUES ('.$newid.',\''.  $result->fieldsid .'\',\''.$result->value.'\' )     ';
                                 $db->setQuery($query);
                                 $db->query();
                            }
                        }
                    }
                   
                     
                    //COPY  fieldsattach_images GALLERIES-----------------------------
                    $query = 'SELECT * FROM #__fieldsattach_images as a  WHERE a.articleid = '. $oldid ; 
                    $db->setQuery( $query );
                    $results= $db->loadObjectList();
                    if($results){ 
                        foreach ($results as $result)
                        {
                            $query = 'SELECT * FROM #__fieldsattach_images as a  WHERE a.articleid = '. $newid.' AND a.fieldsattachid='.$result->fieldsid ;
                            $db->setQuery( $query );
                            $obj= $db->loadObject();
                            if($obj)
                            {
                                //update
                                 $query = 'UPDATE  #__fieldsattach_images SET image1="'.$result->title.'", image1="'.$result->image1.'", image2="'.$result->image2.'", image3="'.$result->image3.'", description="'.$result->description.'", ordering="'.$result->ordering.'", published="'.$result->published.'"  WHERE id='.$result->id ;
                                 $db->setQuery($query);
                                 $db->query();

                            }else{
                                //insert
                                $query = 'INSERT INTO #__fieldsattach_images(articleid,fieldsattachid,title,  image1, image2, image3, description, ordering, published) VALUES ('.$newid.',\''.  $result->fieldsattachid .'\',\''.$result->title.'\',\''.$result->image1.'\',\''.$result->image2.'\',\''.$result->image3.'\',\''.$result->description.'\',\''.$result->ordering.'\',\''.$result->published.'\' )     ';
                                $db->setQuery($query);
                                 $db->query(); 
                            }
                        }
                    }

                     //COPY  FOLDER -----------------------------
                    $app = JFactory::getApplication();
                    $path = $this->path;
                    $path = str_replace ("../", "", $path);
                    $source = $sitepath . $path .DS.  $oldid.DS;
                    $dest = $sitepath.  $path .DS.  $newid.DS;
                    //JFolder::copy($source, $dest);
                    if(!JFolder::exists($dest))
                     { 
                        JFolder::create($dest); 
                     }
                     $files =  JFolder::files($source);
                    foreach ($files as $file)
                    {
                        //JFolder::copy($source.$file, $dest.$file  );
                       // if(copy($source.$file, $dest.$file)) $app->enqueueMessage( JTEXT::_("Copy file ok:") . $file )   ;
                        if(Jfile::copy($source.$file, $dest.$file)) $app->enqueueMessage( JTEXT::_("Copy file ok:") . $file )   ;
                        else JError::raiseWarning( 100, "Cannot copy the file: ".  $source.$file." to ".$dest.$file ); 
                    }
 
                }
                //END COPY AND SAVE============================================================================

            //Ver categorias del artículo ==============================================================
            $idscat = $this->recursivecat($article->catid);
            $db	= & JFactory::getDBO();

            $query = 'SELECT a.id, a.type, b.recursive, b.catid FROM #__fieldsattach as a INNER JOIN #__fieldsattach_groups as b ON a.groupid = b.id WHERE b.catid IN ('. $this->str .') AND a.published=1 AND b.published = 1 ORDER BY a.ordering, a.title';
            $db->setQuery( $query );
            $nameslst = $db->loadObjectList();  

            //***********************************************************************************************
            //Mirar cual de los grupos es RECURSIVO  ****************  ****************  ****************
            //***********************************************************************************************
            $cont = 0;
            foreach ($nameslst as $field)
            {
                //JError::raiseWarning( 100, $field->catid ." !=".$article->catid  );
                if( $field->catid != $article->catid )
                {
                    //Mirar si recursivamente si
                    if(!$field->recursive)
                        {
                            //echo "ELIMINO DE LA LISTA " ;
                            unset($nameslst[$cont]);
                        }
                }
                $cont++;
            }

            $fields_tmp0 = fieldsattachHelper::getfieldsForAll($article->id);
            $nameslst = array_merge($fields_tmp0, $nameslst );

            $fields_tmp2 = fieldsattachHelper::getfieldsForArticlesid($article->id, $nameslst);

            $nameslst = array_merge( $nameslst, $fields_tmp2 );
            
            //Si existen fields relacionados se mira uno a uno si tiene valores
            if(count($nameslst)>0){
                foreach($nameslst as $obj)
                {
                    $query = 'SELECT a.id , b.extras FROM #__fieldsattach_values as a INNER JOIN #__fieldsattach as b ON a.fieldsid = b.id WHERE a.articleid='.$article->id .' AND a.fieldsid ='. $obj->id ;
                   // echo $query;
                   
                    $db->setQuery($query);
                    $valueslst = $db->loadObject();
                     //JError::raiseWarning( 100, $query." - ".count($valueslst)  );
                     //JError::raiseWarning( 100, $valueslst  );
                    if(count($valueslst)==0)
                        {
                            //INSERT 
                            $valor = JRequest::getVar("field_". $obj->id, '', 'post', 'string', JREQUEST_ALLOWHTML);
                            //JError::raiseWarning( 100, "field_1 : ".JRequest::getVar("field_1"  ));
                            //JError::raiseWarning( 100, "value:". $_POST["field_". $obj->id]  );
                            if(is_array($valor))
                            {
                                $valortxt="";
                                for($i = 0; $i < count($valor); $i++ )
                                {

                                      $valortxt .=  $valor[$i].", ";
                                }
                                $valor = $valortxt;
                            }
                            //INSERT 
                            $query = 'INSERT INTO #__fieldsattach_values(articleid,fieldsid,value) VALUES ('.$article->id.',\''.  $obj->id .'\',\''.$valor.'\' )     ';
                            $db->setQuery($query);
                            $db->query();

                            //Select last id ----------------------------------
                            $query = 'SELECT  id  FROM #__fieldsattach_values AS a WHERE  a.articleid='.$article->id.' AND a.fieldsid='.$obj->id;
                            //echo $query;
                            $db->setQuery( $query );
                            $result = $db->loadObject();
                            $valueslst->id = $result->id;
                            //JError::raiseWarning( 100, $query  );
                            //JError::raiseWarning( 100, $valueslst  );
                            
                        }
                        else{
                            //UPDATE
                            //( 'yourfieldname', '', 'post', 'string', JREQUEST_ALLOWHTML );
                            //$valor = JRequest::getVar("field_". $obj->id, JREQUEST_ALLOWHTML);
                            $valor = JRequest::getVar("field_". $obj->id, '', 'post', 'string', JREQUEST_ALLOWHTML);
                             // JError::raiseWarning( 100, $valor  );
                            if(is_array($valor))
                            {
                                $valortxt="";
                                for($i = 0; $i < count($valor); $i++ )
                                { 
                                      $valortxt .=  $valor[$i].", ";
                                }
                                $valor = $valortxt;
                            }
                            $valor = str_replace('"','\"', $valor );
                            $query = 'UPDATE  #__fieldsattach_values SET value="'.$valor.'" WHERE id='.$valueslst->id ;
                            $db->setQuery($query);
                            // JError::raiseWarning( 100, $query  );
                            $db->query(); 
                        }

                        //Acción PLUGIN ========================================================
                        JPluginHelper::importPlugin('fieldsattachment'); // very important 
                        $query = 'SELECT *  FROM #__extensions as a WHERE a.element="'.$obj->type.'"  AND a.enabled= 1';
                        // JError::raiseWarning( 100, $obj->type." --- ". $query   );
                        $db->setQuery( $query );
                        $results = $db->loadObject();
                        if(!empty($results)){
                            
                            $function  = "plgfieldsattachment_".$obj->type."::action( ".$article->id.",".$obj->id.",".$valueslst->id.");";
                            //  JError::raiseWarning( 100,   $function   );
                            eval($function);
                        }
                } 
            }

	    return true;
        }

        

	/**
	* Injects Insert Tags input box and drop down menu to adminForm
	*
	* @access	public
	* @since	1.5
	*/
	function onAfterRender()
	{
               
                 $body = JResponse::getBody();
          	 $id = JRequest::getVar('id');
                 $idgroup= 0; 
                 $editor =& JFactory::getEditor();
		 if (!$id)
		 {
                        $cid = JRequest::getVar( 'cid' , array() , '' , 'array' );
                        @$id = $cid[0];

                        $view = JRequest::getVar('view');
                        if ($view =='article') $path = '';
                        else $path = '..'.DS;
		 }
		 $task = JRequest::getVar('task');
		 $option= JRequest::getVar('option');
                 $id= JRequest::getVar('id', JRequest::getVar('a_id'));

                 $view= JRequest::getVar('view');
                 $layout= JRequest::getVar('layout');
		 
		 //$tagsList = $this->getTags($id, $option);
		 //$masterTagList = $this->getMasterTagsList(); // Added by Duane Jeffers
                 $pos = strrpos(JPATH_BASE, "administrator"); 

                 $user =& JFactory::getUser();

                 $fontend = false;
                 if( $option=='com_content' && $user->get('id')>0 &&  $view == 'form' &&  $layout == 'edit'  ) $fontend = true;

                 $backend = false;
                 if( $option=='com_content' && !empty($pos) &&  $layout == 'edit') $backend = true;
  
                 if (($backend )|| ( $fontend ) )
		 {

                         if(empty($id))
                         {                             
                            $id = $this->getlastId();
                            //Redirect --------------------------------------------------
                            if($backend) {
                                if(!empty($id)){
                                    $url = JURI::base() ."index.php?option=com_content&task=article.edit&id=".$id;
                                    JApplication::redirect($url);
                                     
                                }
                            }   
                         }

                        //$plugin =  JPluginHelper::getPlugin( 'system', 'perchagooglemaps' );
                        //$params = new JParameter( $plugin->params ); 
                        //$params =& JComponentHelper::getParams('com_perchagooglemaps');
                        //$dir = $params->get("dir", "/images/xml/");
                        

                        $fields_tmp0 = fieldsattachHelper::getfieldsForAll($id);
 
                        $fields_tmp1 = $this->getfields($id);
                        $fields_tmp1 = array_merge($fields_tmp0, $fields_tmp1);
 
                        $fields_tmp2 = fieldsattachHelper::getfieldsForArticlesid($id, $fields_tmp1);

                        $fields = array_merge($fields_tmp1, $fields_tmp2);
                        //$fields = $fields_tmp0;
                         
                        if(count($fields)>0){ 
                        $sitepath  =  fieldsattachHelper::getabsoluteURL(); 

                       // if(JRequest::getVar("view")=="fieldsattachunidad") $str = '<script src="'.$sitepath.'plugins/system/fieldsattachment/js/fieldattachment.js" type="text/javascript"></script> ';
                         if($backend || $fontend)  $str = '<script src="'.$sitepath.'plugins/system/fieldsattachment/js/fieldattachment.js" type="text/javascript"></script> ';
                        
                         if($backend){ 
                            $str .= '<script src="'. $sitepath.'plugins/system/fieldsattachment/js/TabPane.js" type="text/javascript"></script> ';
                            $str .= "<script  type='text/javascript'> document.addEvent('domready', function() {
                            var tabPane = new TabPane('demo');
                            });</script>";
                         
                            $str .= '<div id="demo"><ul class="tabs">';
                         }else{
                              
                         }
                         $idgroup="";
                         //TABS RENDER ====================================================================
                         if(count($fields)>0){
                         foreach($fields as $field)
                            { 
                                if($field->idgroup != $idgroup){
                                     if($backend){
                                         $str .= '<li class="tab">'. $field->titlegroup.'</li>';
                                     }                                  
                                }
                                 $idgroup = $field->idgroup;
                            }
                         }
                          if($backend){
                            $str .= '</ul><div class="fieldscontent"> ';
                          }
                          //inputs RENDER ====================================================================
                          $idgroup=-1;
                          if(count($fields)>0){
                          foreach($fields as $field)
                            {  
                                    if($field->idgroup != $idgroup){
                                        if($idgroup > 0) {
                                            if($backend){
                                                $str .= ' </div><br />';
                                            }else{ $str .= '</div></fieldset>';}
                                        }
                                        if($backend){
                                            $str .= '<div id="'.$fields[0]->titlegroup.'-'.$field->idgroup.'" class="content"  >';
                                        }else
                                        {

                                        $str .= '<fieldset><legend>'. $field->titlegroup.'</legend>';
                                        $str .= '<div class="formelm-area">';
                                      
                                        }
                                    }
                                    $idgroup = $field->idgroup;
                                    $str .= '<div>';
                                    if($backend){
                                        $str.= '<br /><br /><div style="width:100%; margin:  5px 0  5px 0px; padding:3px  0  3px 5px; color:#165a8f; overflow:hidden;background-color:#eee;  border:#dcdcdc solid 1px;"><label for="field_'.$field->id.'" style=" font-weight:bold; font-size:14px;">';
                                    }else{ $str.= '<br /><br /><h3>';}
                                    $str .= $field->title;
                                    if($backend){
                                        $str .= '</label></div>';
                                    }else{ $str.= '</h3>';}

                                    //NEW GET PLUGIN ********************************************************************//
                                    JPluginHelper::importPlugin('fieldsattachment'); // very important
                                    //select
                                    $this->array_fields = $this->params->get( 'array_fields' );
                                    if(empty($this->array_fields)) $this->array_fields = $this->get_extensions() ;
                                    if(count($this->array_fields )>0){
                                        foreach ($this->array_fields as $obj)
                                        {
                                            $function  = "plgfieldsattachment_".$obj."::construct();";
                                            //echo $function."<br />";
                                            eval($function);
                                            $function  =  'plgfieldsattachment_'.$obj."::getName();";

                                            eval("\$plugin_name =".  $function."");
                                            //$str .= $field->type." == ".$plugin_name."<br />";
                                            eval($function);
                                            if ($field->type ==  $plugin_name ) {
                                                   //$value = fieldsattachHelper::getValue($id, $field->id);
                                                   $value = JRequest::getVar("field_".$field->id, $this->getfieldsvalue(  $field->id, $id));
                                                   //$value = ;
                                                   
                                                   //$str .= 'plgfieldsattachment_'.$obj;
                                                   $value = addslashes($value);
                                                   $function  =  'plgfieldsattachment_'.$obj.'::renderInput('.$id.','.$field->id.',"'.$value.'","'.$field->extras.'");';
                                                   //$str .= "DENTROOOOOOOOOOOOOOOOOOOOOOOOOO: ".$function."<br><br> field_".$field->id;
                                                   //echo $function."<br />";
                                                   //JError::raiseWarning( 100, "function render:". $function  );
                                                   $str .= '<div style=" width:100%; overflow:hidden" >';
                                                    eval("\$str .=".  $function."");
                                                    $str .= '</div>';
                                            }
                                         }
                                      }
                                     $str .= '</div>';
                                }
                                //END inputs RENDER ========================================================= 
                          }
                          if($backend){
                            $str .='</div>';
                          }else{
                              $str .='</div></fieldset>';
                              //$str .='</div></fieldset>';
                              
                           }
                          /*********************************/
                          if($backend){
                                $str .= '</div>';  $str .= '</div>';
                          }
                        
                            }else{
                            $str = "";
                            }
                        
                        $pos = strrpos(JPATH_BASE, "administrator");
 

                         if ( !empty($pos)  ){
                            $finds = explode('<div class="clr"></div>', $body);
                            if(!empty ($id)) $finds[5] = $str.$finds[5];
                            $body = implode('<div class="clr"></div>', $finds); if ( !empty($pos)  ){  }

                         }else
                         {
                            $finds = explode('</fieldset>', $body);
                            if(!empty ($id)) $finds[1] = $str.$finds[1];
                            $body = implode('</fieldset>', $finds); if ( !empty($pos)  ){  }
                             //
                         }
                        
                        
		 } 
                 //Añadir   enctype="multipart/form-data" 

                 $body = str_replace('method="post"',   'method="post" enctype="multipart/form-data" ' , $body);
                 
		 JResponse::setBody($body);

                
	}

        private function createDirectory($id)
        {
            $app = JFactory::getApplication(); 
            JError::raiseWarning( 100, "CREAR DIR::: ".  $this->path .DS.  $id );
            if(!JFolder::exists($this->path .DS. $id))
             {
                //echo "<br >CREATE PATH __ : ".$this->path .DS.  $article->id;
                JError::raiseWarning( 100,  $this->path .DS.  $id );
                JFolder::create($this->path .DS.  $id);
                $app->enqueueMessage( JTEXT::_("Folder created:").$this->path .DS. $id)   ;
             } 
        }

        private function  getlastId()
        {
            $db	= & JFactory::getDBO();
            $user =& JFactory::getUser();
            $mysqldate = date( 'Y-m-d H:i:s' );
            
            //-----------------------
            $query = 'SELECT  id  FROM #__content WHERE created_by='.$user->get(id).' AND introtext= "" '; 
            
            //echo $query;
            $db->setQuery( $query );
            $result = $db->loadObject();
            $id = $result->id;
            if(empty($id))
                {  
                $valor = "";
                $query = 'INSERT INTO #__content(title, catid, created_by, created, state) VALUES ("", 1, '.$user->get(id).',"'.$mysqldate.'", -2)     ';
                $db->setQuery($query);
                $db->query(); 
                //-----------------------
                $query = 'SELECT  id  FROM #__content   ';
                $query .= ' order by id DESC '; 
                //echo $query;
                $db->setQuery( $query );
                $result = $db->loadObject();
                $id = $result->id;

                //Crear directorio ==============================================================
                $this->createDirectory($id);
                }
            return $id;
        }

        private function  getfieldsForAll2()
        {
            $db	= & JFactory::getDBO(); 
            $query = 'SELECT a.id as idgroup, a.title as titlegroup, a.catid, a.language, a.recursive, b.* FROM #__fieldsattach_groups as a INNER JOIN #__fieldsattach as b ON a.id = b.groupid ';
            $query .= 'WHERE a.catid = 0 AND a.published=1 AND b.published = 1 ';
            //echo $elid->language."Language: ".$idioma;
            if (  ($elid->language == $idioma ) ) {
                  $query .= ' AND (a.language="'.$elid->language.'" OR a.language="*" ) AND (b.language="'.$elid->language.'" OR b.language="*") ' ;
                  // echo "filter::". $app->getLanguageFilter();
                  // echo "filter::". JRequest::getVar("language");
            }
            $query .='ORDER BY a.ordering, a.title, b.ordering';
            // echo $query;
            $db->setQuery( $query );
            $result = $db->loadObjectList();
            if($result) return $result;
        }

        private function getfields($id)
        {
             
            $result ="";
            $db	= & JFactory::getDBO(); 
            $query = 'SELECT a.catid, a.language FROM #__content as a WHERE a.id='. $id  ;
             
            $db->setQuery( $query );
            $elid = $db->loadObject();
            $idioma = $elid->language;

            $this->recursivecat($elid->catid, "");
            
            if(!empty($elid)){
                $db	= & JFactory::getDBO();

                $query = 'SELECT a.id as idgroup, a.title as titlegroup, a.catid, a.language, a.recursive, b.* FROM #__fieldsattach_groups as a INNER JOIN #__fieldsattach as b ON a.id = b.groupid ';
                $query .= 'WHERE a.catid IN ('. $this->str .') AND a.published=1 AND b.published = 1 ';
                //echo $elid->language."Language: ".$idioma;
                if (  ($elid->language == $idioma ) ) {
                      $query .= ' AND (a.language="'.$elid->language.'" OR a.language="*" ) AND (b.language="'.$elid->language.'" OR b.language="*") ' ;
                      // echo "filter::". $app->getLanguageFilter();
                      // echo "filter::". JRequest::getVar("language");
                }
                 $query .='ORDER BY a.ordering, a.title, b.ordering';
                // echo $query;
                $db->setQuery( $query );
                $result = $db->loadObjectList(); 

                //**********************************************************************************************
                //Mirar cual de los grupos es RECURSIVO  ************************************************
                //***********************************************************************************************
                $cont = 0;
                foreach ($result as $field)
                {
                    
                    if( $field->catid != $elid->catid )
                    {
                        //Mirar si recursivamente si
                        if(!$field->recursive)
                            {
                                //echo "ELIMINO DE LA LISTA " ;
                                unset($result[$cont]);
                            }
                    }
                    $cont++;
                }
                return $result;
            }

            
        }
        
        /**
	* Injects  HTML get fields for a id
	*
	* @access	public
	* @since	1.5
	*/
        private function getfieldsForArticlesid($id, $fields = null)
        {
	    $empty = array();
            //$id = ",".$id.",";
            $db	= & JFactory::getDBO();

            $query = 'SELECT a.id as idgroup, a.title as titlegroup, a.catid, a.language, a.recursive, b.*, a.articlesid FROM #__fieldsattach_groups as a INNER JOIN #__fieldsattach as b ON a.id = b.groupid ';
            //$query .= 'WHERE (a.articlesid LIKE "%,'. $id .',%" )  AND a.published=1 AND b.published = 1 ';
            $query .= 'WHERE  a.published=1 AND b.published = 1 ';
            
            if (  ($elid->language == $idioma ) ) {
                  $query .= ' AND (a.language="'.$elid->language.'" OR a.language="*" ) AND (b.language="'.$elid->language.'" OR b.language="*") ' ;
            }
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

        private function getfieldsvalue($fieldsid, $articleid)
        {
            $result ="";
            $db	= & JFactory::getDBO();
            $query = 'SELECT a.value FROM #__fieldsattach_values as a WHERE a.fieldsid='. $fieldsid.' AND a.articleid='.$articleid  ;
            //echo $query;
            $db->setQuery( $query );
            $elid = $db->loadObject();
            $return ="";
            if(!empty($elid))  $return =$elid->value;
            return $return ;
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
        

        function recursivecat($catid)
        {
            /*SELECT *
FROM  `jos_categories`
             * parent_id
             *
             * // Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('*');

		// From the hello table
		$query->from('#__fieldsattach_groups');
             */
                if(!empty($this->str)) $this->str .=  ",";
                $this->str .= $catid ;
                //echo "SUMO:".$str."<br>";
                $db	= & JFactory::getDBO();
                $query = 'SELECT parent_id FROM #__categories as a WHERE a.id='.$catid   ;
                //echo $query."<br>";
                $db->setQuery( $query );
                $tmp = $db->loadObject();
                
                if($tmp->parent_id>0) $this->recursivecat($tmp->parent_id);
                 
 

        }

        //IMAGE RESIZE FUNCTION FOLLOW ABOVE DIRECTIONS  
        private function resize($nombre,$archivo,$ancho,$alto,$id,$filter=NULL)
        {
            $path = JPATH_BASE ;
            $app = JFactory::getApplication();
             
            $arr1 = explode(".", $nombre );
            $tmp = $arr1[1];
             
            $nombre = $path."/".$this->path .DS. $id .DS. $nombre;
             $destarchivo = $this->path .DS. $id .DS. $archivo;
            $archivo =  $path."/".$this->path .DS. $id .DS. $archivo;
 
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
                     
                    
                    if($filter =="IMG_FILTER_NEGATE") $filter = 0;
                    if($filter =="IMG_FILTER_GRAYSCALE") $filter = 1;
                    if($filter =="IMG_FILTER_BRIGHTNESS") $filter = 2;
                    if($filter =="IMG_FILTER_CONTRAST") $filter = 3;
                    if($filter =="IMG_FILTER_COLORIZE") $filter = 4;
                    if($filter =="IMG_FILTER_EDGEDETECT") $filter = 5;
                    if($filter =="IMG_FILTER_EMBOSS") $filter = 6;
                    if($filter =="IMG_FILTER_GAUSSIAN_BLUR") $filter = 7;
                    if($filter =="IMG_FILTER_SELECTIVE_BLUR") $filter = 8;
                    if($filter =="IMG_FILTER_MEAN_REMOVAL") $filter = 9;
                    if($filter =="IMG_FILTER_SMOOTH") $filter = 10;
                    if($filter =="IMG_FILTER_PIXELATE") $filter = 11;
                    if(imagefilter($imagen, $filter, 50))
                    { 
                        $app->enqueueMessage( JTEXT::_("Apply filter:").$filter  );
                    }  else {
                        JError::raiseWarning( 100,  JTEXT::_("Apply filter ERROR:").$filter   );
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

        private function get_extensions()
        {
            $array_fields  = array();
            $db = &JFactory::getDBO(  );
            $query = 'SELECT *  FROM #__extensions as a WHERE a.folder = "fieldsattachment"  AND a.enabled= 1';
            $db->setQuery( $query );

            $results = $db->loadObjectList();
            foreach ($results as $obj)
            {
                $array_fields[count($array_fields)] = $obj->element;
            }
            return $array_fields;
        }


}
