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

// no direct access
defined('_JEXEC') or die;


 // require helper file
JLoader::register('fieldsattachHelper',  JPATH_INSTALLATION.DS.'..'.DS.'administrator/components/com_fieldsattach/helpers/fieldsattach.php');
 
 
class fieldattach
{

        /**
	 * Return the value of field
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	value to field.
	 * @since	1.6
	 */
	public function getName($articleid, $fieldsids)
	{
	    $db = &JFactory::getDBO(  );
	    $query = 'SELECT  b.title  FROM #__fieldsattach_values as a INNER JOIN #__fieldsattach as b ON  b.id = a.fieldsid  WHERE a.fieldsid IN ('.$fieldsids.') AND (b.language="'. JRequest::getVar("language", "*").'" OR b.language="*" ) AND a.articleid= '.$articleid;
	    //echo $query."<br>";
            $db->setQuery( $query );
	    $result = $db->loadObject();
            $str = "";
            if(!empty($result->title)) $str = $result->title;
	     return $str;
	}
        /**
	 * Return the value of field
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	value to field.
	 * @since	1.6
	 */
	public function getValue($articleid, $fieldsids)
	{
	    $db = &JFactory::getDBO(  ); 
	    $query = 'SELECT  a.value  FROM #__fieldsattach_values as a INNER JOIN #__fieldsattach as b ON  b.id = a.fieldsid  WHERE a.fieldsid IN ('.$fieldsids.') AND (b.language="'. JRequest::getVar("language", "*").'" OR b.language="*" ) AND a.articleid= '.$articleid;
	    //echo $query."<br/>";
            $db->setQuery( $query );
	    $result = $db->loadObject();
            $str = "";
            if(!empty($result->value)) $str = $result->value;
            //echo "VALOR: ".$str."<br/>";
	    return $str;
	}

         /**
	 * Return a input HTML tag
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of input
	 * @since	1.6
	 */
	public function getInput($id, $fieldsids)
        {
          $html ='';
          $valor = fieldattach::getValue( $id,  $fieldsids  );
          $title = fieldattach::getName( $id,  $fieldsids  );
          if(!empty($valor))
          {
              $html .= '<div id="cel_'.$fieldsids.'" class="'.$field->type.'">';
              if(fieldattach::getShowTitle(   $fieldsids  ))  $html .= '<span class="title">'.$title.' </span>';
              $html .= '<span class="value">'.$valor.'</span></div>';
          }
          return $html;
        }

        /**
	 * Return a image HTML tag
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of image
	 * @since	1.6
	 */
	public function getImg($id, $fieldsids, $title=null)
	{
	    $html =  '';
            $db = &JFactory::getDBO(  );
	    $query = 'SELECT  a.value  FROM #__fieldsattach_values as a INNER JOIN #__fieldsattach as b ON  b.id = a.fieldsid  WHERE a.fieldsid IN ('.$fieldsids.') AND (b.language="'. JRequest::getVar("language", "*").'" OR b.language="*") AND a.articleid= '.$id;
	   
            $db->setQuery( $query );
	    $result = $db->loadObject();

            $file = $result->value; 

            if(!empty($result->value)) {
                if (JFile::exists( JPATH_SITE .DS."images".DS."documents".DS. $id .DS. $file)  )
                {
                $html =  '<img src="images/documents/'.$id.'/'.$result->value.'" title = "'.$title.'" alt="'.$title.'" />' ;
                }else{
                    if (JFile::exists( JPATH_SITE .DS.$result->value)  ){
                        $html =  '<img src="'.$result->value.'" title = "'.$title.'" alt="'.$title.'" />' ;
                    } 
                }
            }
	    return $html;
	}
         /**
	 * Return a html of   select
	 *
	 * @param	$articleid	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of  select
	 * @since	1.6
	 */
	public function getSelect($articleid, $fieldsids)
        {
              $html ='';
              $valor = fieldattach::getValue( $articleid,  $fieldsids  );
              $title = fieldattach::getName( $articleid,  $fieldsids  );
               
              if(!empty($valor))
              {
                  $valorselects = fieldattach::getValueSelect( $fieldsids , $valor );
                  $html .= '<div id="cel_'.$fieldsids.'" class=" ">';
                  if(fieldattach::getShowTitle(   $fieldsids  )) $html .= '<span class="title">'.$title.' </span>';
                  $html .= '<span class="value">'.$valorselects.'</span></div>';
              }
              return $html;
        }

        
        /**
	 * Return the value of field
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	value to field.
	 * @since	1.6
	 */
	public function getValueSelect( $fieldsids, $valor )
	{
	    $db = &JFactory::getDBO(  );
	    $query = 'SELECT  a.extras  FROM #__fieldsattach as a   WHERE a.id  = '.$fieldsids.'    ';
	    //echo $query."<br/>";
            $db->setQuery( $query );
	    $result = $db->loadObject();
            $tmp = '';$str = "" ;
            if(!empty($result->extras)) $tmp = $result->extras;
              //echo  "<br/>extras: ".$tmp;
            $lineas = explode(chr(13),  $tmp);
            if(count($lineas)>0)
            {
                foreach ($lineas as $linea)
                {
                   // echo  "<br/>".$linea. "->".$valor;
                    $pos = strrpos($linea,  $valor);
                    if ($pos === false) {
                       
                    }else{
                        $tmp = explode('|',  $linea);
                        $str = $tmp[0]; 
                        
                        }
                }
            }
           
	    return $str;
	}

        /**
	 * Return a html of   file download
	 *
	 * @param	$articleid	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of  file download
	 * @since	1.6
	 */
	public function getFileDownload($articleid, $fieldsids)
        {
            $html =  '';
            $db = &JFactory::getDBO(  );
	    $query = 'SELECT  a.value  FROM #__fieldsattach_values as a INNER JOIN #__fieldsattach as b ON  b.id = a.fieldsid  WHERE a.fieldsid IN ('.$fieldsids.') AND (b.language="'. JRequest::getVar("language", "*").'" OR b.language="*") AND a.articleid= '.$articleid;

            $db->setQuery( $query );
	    $result = $db->loadObject(); 
            if(!empty($result->value)) { 
                if (JFile::exists( JPATH_SITE .DS."images".DS."documents".DS. $articleid .DS. $result->value)  )
                {
                $html .=  '<a href="images/documents/'.$id.'/'.$result->value.'" title = "'.$title.'" alt="'.$title.'" class="downloads" />'.JText::_('Download').'</a>';

                }
            }
	    return $html;
        }
        
         /**
	 * Return a html of multiple select
	 *
	 * @param	$articleid	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of multiple select
	 * @since	1.6
	 */
	public function getSelectmultiple($articleid, $fieldsids)
        {
              $html ='';
              $valor = fieldattach::getValue( $articleid,  $fieldsids  );
              if(!empty($valor))
              {
                    $html .= '<div id="cel_'.$field->id.'" class="'.$field->type.'">';
                    if(fieldattach::getShowTitle(   $fieldsids  )) $str .= '<span class="title">'.$field->title.' </span>';
                    $tmp = explode(",",$valor);
                    $conta = 0;
                    foreach($tmp as $obj)
                    {
                        $conta++;
                        if(!empty($obj)) $html .= '<span class="value num_'.$conta.'">'.$obj.'</span>';

                    }
                    $html .= '</div>';

              }
              return $html;
        }
        /**
	 * Return a image gallery HTML tag
	 *
	 * @param	$articleid	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html gallery   list
	 * @since	1.6
	 */
	public function getImageGallery($articleid, $fieldsids)
	{
	    $html =  '<ul class="gallery">';
            $db = &JFactory::getDBO(  );
	    $query = 'SELECT  a.* FROM #__fieldsattach_images as a  WHERE a.fieldsattachid = '.$fieldsids.' AND a.articleid= '.$articleid;

            $db->setQuery( $query );
	    $result = $db->loadObjectList();
            $firs_link = '';
            $cont = 0;

            $sitepath  =  fieldsattachHelper::getabsoluteURL();

            if(!empty($result)){
                foreach ($result as $obj){
                    //if (JFile::exists( JPATH_SITE .DS."images".DS."documents".DS. $articleid .DS. $result->value)  ) 
                    $html .=  '<li>' ;
                    if (JFile::exists( JPATH_SITE .DS. $obj->image2)  )
                    {
                        $html .=  '<a href="'.$sitepath.''.$obj->image1.'" id="imgFiche" class="nyroModal" title="'.$obj->title.'" rel="gal_'.$articleid.'">';
                        $html .=  '<img src="'.$sitepath.''.$obj->image2.'"  alt="'.$obj->title.'" />';
                    }else{$html .=  '<img src="'.$sitepath.''.$obj->image1.'"  alt="'.$obj->title.'" />';}

                    if (JFile::exists( JPATH_SITE .DS. $obj->image2)  )
                    {
                        $html .=  '</a>';
                    }
                    $html .=  '</li>'; 
                    $cont++;
                }
            }
            $html .=  '</ul>';

	    return $html;
	}

        /**
	 * Return a image HTML tag
	 *
	 * @param	$articleid	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of image
	 * @since	1.6
	 */
	public function getFirstImageGallery($articleid, $fieldsids, $title=null)
	{
	    $html =  '<div class="gallery">';
            $db = &JFactory::getDBO(  );
	    $query = 'SELECT  a.* FROM #__fieldsattach_images as a  WHERE a.fieldsattachid = '.$fieldsids.' AND a.articleid= '.$articleid;
             
            $db->setQuery( $query );
	    $result = $db->loadObjectList();
            $firs_link = '';
            $cont = 0;
            if(!empty($result)){
                foreach ($result as $obj){

                    $html .=  '<a href="'.JURI::base().''.$obj->image1.'" id="imgFiche" class="nyroModal" title="'.$obj->title.'" rel="gal_'.$articleid.'">';
                    if($cont==0){
                      

                        $html .=  '<img src="'.JURI::base().''.$obj->image1.'"  alt="'.$obj->title.'" />';

                        }
                    else{ $html .= ''; }
                    $html .=  '</a>';

                    if($cont==1){   $firs_link = JURI::base().''.$obj->image1 ;}
                    
                    $cont++;
                }
            }
            $html .=  '</div>';
            if(!empty($firs_link)){
                $html .=  '<div class="vergallery">';
                $html .=  '<a href="'.$firs_link.'" id="imgFiche" class="nyroModal" title="'.$obj->title.'" rel="gal_'.$articleid.'">';
                $html .=  JText::_("Ver Imagenes");
                $html .=  '</a>';
                $html .=  '</div>';
            }
	    return $html;
	}
/**
	 * Return a image HTML tag
	 *
	 * @param	$articleid	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of image
	 * @since	1.6
	 */
	public function getVideoGallery($articleid, $fieldsids)
	{
            $html = '';
            $db = &JFactory::getDBO(  );
	    $query = 'SELECT  a.value  FROM #__fieldsattach_values as a INNER JOIN #__fieldsattach as b ON  b.id = a.fieldsid  WHERE a.fieldsid IN ('.$fieldsids.') AND (b.language="'. JRequest::getVar("language", "*").'" OR b.language="*" ) AND a.articleid= '.$articleid;
	    //echo $query;
            $db->setQuery( $query );
	    $result = $db->loadObject();
	    if(!empty($result->value))
            {
                $html .=  '<div class="vervideogallery">';
                $html .= '<a href="http://www.youtube.com/watch?v='.$result->value.'" class="nyroModal"  >'.JText::_("Ver Video").'</a><br />';
                $html .=  '</div>';
            }
            
            return $html;
        }

        /**
	 * Return a image VIMEO IFRAME
	 *
	 * @param	$articleid	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	vidmeo IFRAME
	 * @since	1.6
	 */
	public function getVimeoVideo($articleid, $fieldsids)
	{
            $extrainfo = fieldattach::getExtra($fieldsids);
            $width="300";
            $height="300";

            if((count($extrainfo) >= 1)&&(!empty($extrainfo[0]))) $width= $extrainfo[0];
            if((count($extrainfo) >= 2)&&(!empty($extrainfo[1]))) $height= $extrainfo[1];

            $code = fieldattach::getValue(  $articleid, $fieldsids);
            if(!empty($code)){
                $html  = '<div id="cel_'.$fieldsids.'" class="vimeo">';
                $html .= '<iframe src="http://player.vimeo.com/video/'.$code.'" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>';
                $html .= '</div>';
            }
            return $html;
        }

        /**
	 * Return a image YOUTUBE OBJECT
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	object video
	 * @since	1.6
	 */
	public function getYoutubeVideo($articleid, $fieldsids)
	{
            $extrainfo = fieldattach::getExtra($fieldsids);
            $width="300";
            $height="300";
            if((count($extrainfo) >= 1)&&(!empty($extrainfo[0]))) $width= $extrainfo[0];
            if((count($extrainfo) >= 2)&&(!empty($extrainfo[1]))) $height= $extrainfo[1];


            $code = fieldattach::getValue(  $articleid,  $fieldsids);
            if(!empty($code)){
             $html .= '<div id="cel_'.$fieldsids.'" class="youtube">';
             $html .=  '<object width="'.$width.'" height="'.$height.'">
               <param name="movie" value="http://www.youtube.com/v/'. $code.'&amp;hl=en_US&amp;fs=1&amp;"></param>
               <param name="allowFullScreen" value="true"></param>
               <param name="allowscriptaccess" value="always"></param>
               <embed
                  src="http://www.youtube.com/v/'.$code.'&amp;hl=en_US&amp;fs=1&amp;"
                  type="application/x-shockwave-flash"
                  allowscriptaccess="always"
                  allowfullscreen="true"
                  width="'.$width.'"
                  height="'.$height.'">
               </embed>
            </object>
            ';

              $html .= '</div>';
            }
            return $html;
        }
        /**
	 * Return a table HTML with a list of units
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of table
	 * @since	1.6
	 */
        public function getExtra($fieldsids)
	{
            $db = &JFactory::getDBO(  );
	    $query = 'SELECT a.* FROM #__fieldsattach as a  WHERE a.id = '.$fieldsids;


            $db->setQuery( $query );
	    $result  = $db->loadObject();
            $extrainfo = explode("|",$result->extras);
            return $extrainfo;
        }

         /**
	 * Return a table HTML with a list of units
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of table
	 * @since	1.6
	 */
        public function getShowTitle($fieldsids)
	{
            $db = &JFactory::getDBO(  );
	    $query = 'SELECT a.* FROM #__fieldsattach as a  WHERE a.id = '.$fieldsids;


            $db->setQuery( $query );
	    $result  = $db->loadObject();
             
            return $result->showtitle;
        }

       /**
	 * Return a table HTML with a list of units
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
	 *
	 * @return	html of table 
	 * @since	1.6
	 */
	public function getListUnits($articleid, $fieldsids)
	{
            $str ='';
            $extrainfo = fieldattach::getExtra($fieldsids);
            $str .='<table><thead><tr>';
            foreach ($extrainfo as $result )
                {
                     $str .='<th>'.$result.'</th>';
                } 
            $str .='</tr></thead>';
            $valor = fieldattach::getValue($articleid, $fieldsids);
            $json = explode("},", $valor);

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
                $str .='<tr>';
                foreach ($extrainfo as $obj )
                {
                     $str .='<td>'.  $jsonobj->{$obj} .'</td>';
                }
                $str .='</tr>';
            }


            $str .='</table>';
            

            /*$valor = fieldattach::getValue($this->item->id, "22");
            $json = explode("},", $valor);

            if(count($json)>0)
            {
                ?>
                <thead>
                    <tr>
                        <th class="first"><?php echo JText::_("Modelo");?></th>
                        <th><?php echo JText::_("Bobina");?></th>
                        <th><?php echo JText::_("Peso gr.");?></th>
                        <th><?php echo JText::_("Capacidad m");?></th>
                        <th><?php echo JText::_("Capacidad ømm");?></th>
                        <th><?php echo JText::_("Ratio");?></th>
                        <th><?php echo JText::_("Código");?></th>
                    </tr>
                    <?php
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
                    ?>
                   <tr>
                       <td  class="first"><?php echo $jsonobj->{"Modelo"}?></td>
                       <td><?php echo $jsonobj->{"Bobina"}?></td>
                       <td><?php echo $jsonobj->{"Peso_gr."}?></td>
                       <td><?php echo $jsonobj->{"Capacidad_m"}?></td>
                       <td><?php echo $jsonobj->{"Capacidad_ømm"}?></td>
                       <td><?php echo $jsonobj->{"Ratio"}?></td>
                       <td><?php echo $jsonobj->{"Código"}?></td>
                   </tr>
                    <?php
                }
            }*/
            return $str;
        }

        /**
	 * Create two images for a button.
	 *
	 * @param	$id	 id of article
         *              $fieldsids  id of field
         *              $width  width of resize
         *              $height height of resize
	 *
	 * @return	value to field.
	 * @since	1.6
	 */
        public function creteButtonImage($id, $fieldsids, $width, $height)
        {
            $db = &JFactory::getDBO(  );
            $path=  'images'.DS.'documents';

            $query = 'SELECT  a.value  FROM #__fieldsattach_values as a WHERE fieldsid='.$fieldsids.' AND articleid= '.$id;

            $db->setQuery( $query );
            $result = $db->loadObject();

            $ancho = $width;
            $alto = $height;

            $nombre = JPATH_BASE. DS .$path. DS . $id. DS . $result->value;
            $nombre = JPATH_BASE. DS ."images". DS . "documents" . DS . $id . DS .  $result->value;
            $archivo = $path. DS . $id. DS . "btn_1" ;
            $archivo_on = $path. DS . $id. DS . "btn_1_on" ;


           // echo "<br>".$nombre."<br>";
            if (preg_match('/jpg|jpeg|JPG/',$nombre))
                {
                $imagen=imagecreatefromjpeg($nombre);
                $archivo .=".jpg";$archivo_on .=".jpg";
                }
            if (preg_match('/png|PNG/',$nombre))
                {
                $imagen=imagecreatefrompng($nombre);
                 $archivo .=".png";$archivo_on .=".png";

                }
            if (preg_match('/gif|GIF/',$nombre))
                {
                $imagen=imagecreatefromgif($nombre);
                $archivo .=".gif";$archivo_on .=".gif";
                }

            $x=imageSX($imagen);
            $y=imageSY($imagen);
            $w=$ancho;
            $h=$alto;
            if ($x > $y)
            {
            $w=$ancho;
            $h=$y*($alto/$x);
            }

        if ($x < $y)
            {
            $w=$x*($ancho/$y);
            $h=$alto;
            }

        if ($x == $y)
            {
            $w=$ancho;
            $h=$alto;
            }

            //Crear imagen sin filtro
            $destino_on=ImageCreateTrueColor($w,$h);
            imagecopyresampled($destino_on,$imagen,0,0,0,0,$w,$h,$x,$y);

            if(imagefilter($imagen, 1, 50))
            {
                $app = JFactory::getApplication();
                $app->enqueueMessage( JTEXT::_("Apply filter:")  );
            }  else {
                JError::raiseWarning( 100,  JTEXT::_("Apply filter ERROR:").$filter   );
            }

            $destino=ImageCreateTrueColor($w,$h);
           // echo "<br>destini: ".$destino;
            imagecopyresampled($destino,$imagen,0,0,0,0,$w,$h,$x,$y);


            //echo " archivo:: ".$nombre;

            $tmp = JPATH_BASE. DS . $archivo;
            $tmp2 = JPATH_BASE. DS . $archivo_on;

            if (preg_match("/png/",$tmp))
                {
                imagepng($destino,$tmp);
                imagepng($destino_on,$tmp2);
                }
            if (preg_match("/gif/",$tmp))
                {
                imagegif($destino,$tmp);
                imagepng($destino_on,$tmp2);
                }
            else
                {
                imagejpeg($destino,$tmp);
                imagepng($destino_on,$tmp2);
                }

            imagedestroy($destino);
           /* imagedestroy($imagen);*/

            return '<img src='. $archivo.' alt =" " id="imagen_'.$id.'"/>';
        }

}
