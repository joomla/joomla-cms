<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.registry.registry');
jimport('joomla.image.image');

require_once __DIR__ . '/classes/image.php';

class plgAjaxHelix3 extends JPlugin
{

  function onAjaxHelix3()
  {
    $input = JFactory::getApplication()->input;
    $action = $input->post->get('action', '', 'STRING');

    if($action=='upload_image') {
      $this->upload_image();
      return;
    } else if($action=='remove_image') {
      $this->remove_image();
      return;
    }

    if ($_POST['data']) {
      $data = json_decode(json_encode($_POST['data']), true);;
      $action = $data['action'];
      $layoutName = '';

      if (isset($data['layoutName'])) {
        $layoutName = $data['layoutName'];
      }

      $template  = self::getTemplate()->template;
      $layoutPath = JPATH_SITE.'/templates/'.$template.'/layout/';

      $filepath = $layoutPath.$layoutName;

      $report = array();
      $report['action'] = 'none';
      $report['status'] = 'false';

      switch ($action) {
        case 'remove':
        if (file_exists($filepath)) {
          unlink($filepath);
          $report['action'] = 'remove';
          $report['status'] = 'true';
        }
        $report['layout'] = JFolder::files($layoutPath, '.json');
        break;

        case 'save':
        if ($layoutName) {
          $layoutName = strtolower(str_replace(' ','-',$layoutName));
        }
        $content = $data['content'];

        if ($content && $layoutName) {
          $file = fopen($layoutPath.$layoutName.'.json', 'wb');
          fwrite($file, $content);
          fclose($file);
        }
        $report['layout'] = JFolder::files($layoutPath, '.json');
        break;

        case 'load':
        if (file_exists($filepath)) {
          $content = file_get_contents($filepath);
        }

        if (isset($content) && $content) {
          echo $layoutHtml = self::loadNewLayout(json_decode($content));
        }
        die();
        break;

        case 'resetLayout':
        if($layoutName){
          echo self::resetMenuLayout($layoutName);
        }
        die();
        break;

        // Upload Image
        case 'upload_image':
        echo "Joomla";
        die();
        break;

        default:
        break;

        case 'voting':

        if (JSession::checkToken()) {
          return json_encode($report);
        }

        $rate = -1;
        $pk = 0;

        if (isset($data['user_rating'])) {
          $rate = (int)$data['user_rating'];
        }

        if (isset($data['id'])) {
          $id = str_replace('post_vote_','',$data['id']);
          $pk = (int)$id;
        }

        if ($rate >= 1 && $rate <= 5 && $id > 0)
        {
          $userIP = $_SERVER['REMOTE_ADDR'];

          $db    = JFactory::getDbo();
          $query = $db->getQuery(true);

          $query->select('*')
          ->from($db->quoteName('#__content_rating'))
          ->where($db->quoteName('content_id') . ' = ' . (int) $pk);

          $db->setQuery($query);

          try
          {
            $rating = $db->loadObject();
          }
          catch (RuntimeException $e)
          {
            return json_encode($report);
          }

          if (!$rating)
          {
            $query = $db->getQuery(true);

            $query->insert($db->quoteName('#__content_rating'))
            ->columns(array($db->quoteName('content_id'), $db->quoteName('lastip'), $db->quoteName('rating_sum'), $db->quoteName('rating_count')))
            ->values((int) $pk . ', ' . $db->quote($userIP) . ',' . (int) $rate . ', 1');

            $db->setQuery($query);

            try
            {
              $db->execute();

              $data = self::getItemRating($pk);
              $rating = $data->rating;

              $report['action'] = $rating;
              $report['status'] = 'true';

              return json_encode($report);
            }
            catch (RuntimeException $e)
            {
              return json_encode($report);;
            }
          }
          else
          {
            if ($userIP != ($rating->lastip))
            {
              $query = $db->getQuery(true);

              $query->update($db->quoteName('#__content_rating'))
              ->set($db->quoteName('rating_count') . ' = rating_count + 1')
              ->set($db->quoteName('rating_sum') . ' = rating_sum + ' . (int) $rate)
              ->set($db->quoteName('lastip') . ' = ' . $db->quote($userIP))
              ->where($db->quoteName('content_id') . ' = ' . (int) $pk);

              $db->setQuery($query);

              try
              {
                $db->execute();

                $data = self::getItemRating($pk);
                $rating = $data->rating;

                $report['action'] = $rating;
                $report['status'] = 'true';

                return json_encode($report);
              }
              catch (RuntimeException $e)
              {
                return json_encode($report);
              }
            }
            else
            {

              $report['status'] = 'invalid';
              return json_encode($report);
            }
          }
        }
        $report['action'] = 'failed';
        $report['status'] = 'false';
        return json_encode($report);
        break;

        //Font variant
        case 'fontVariants':

        $template_path = JPATH_SITE . '/templates/' . self::getTemplate()->template . '/webfonts/webfonts.json';
        $plugin_path   = JPATH_PLUGINS . '/system/helix3/assets/webfonts/webfonts.json';

        if(JFile::exists( $template_path )) {
          $json = JFile::read( $template_path );
        } else {
          $json = JFile::read( $plugin_path );
        }

        $webfonts   = json_decode($json);
        $items      = $webfonts->items;

        $output = array();
        $fontVariants = '';
        $fontSubsets = '';
        foreach ($items as $item) {
          if($item->family==$layoutName) {

            //Variants
            foreach ($item->variants as $variant) {
              $fontVariants .= '<option value="'. $variant .'">' . $variant . '</option>';
            }

            //Subsets
            foreach ($item->subsets as $subset) {
              $fontSubsets .= '<option value="'. $subset .'">' . $subset . '</option>';
            }
          }
        }

        $output['variants'] = $fontVariants;
        $output['subsets']  = $fontSubsets;

        return json_encode($output);

        break;

        //Font variant
        case 'updateFonts':

        jimport( 'joomla.filesystem.folder' );
        jimport('joomla.http.http');

        $template_path = JPATH_SITE . '/templates/' . self::getTemplate()->template . '/webfonts';

        if(!JFolder::exists( $template_path )) {
          JFolder::create( $template_path, 0755 );
        }

        $tplRegistry = new JRegistry();
        $tplParams = $tplRegistry->loadString(self::getTemplate()->params);
        $gfont_api = $tplParams->get('gfont_api', 'AIzaSyBVybAjpiMHzNyEm3ncA_RZ4WETKsLElDg');
        $url  = 'https://www.googleapis.com/webfonts/v1/webfonts?key='. $gfont_api;
        $http = new JHttp();
        $str  = $http->get($url);

        if($str->code == 200) {
          // if successfully updated
          if ( JFile::write( $template_path . '/webfonts.json', $str->body )) {
            echo "<p class='font-update-success'>Google Webfonts list successfully updated! Please refresh your browser.</p>";
          } else {
            echo "<p class='font-update-failed'>Google Webfonts update failed. Please make sure that your template folder is writable.</p>";
          }
        } elseif($str->code == 403) {
          // If got error
          $decode_msg = json_decode($str->body);
          if(isset(json_decode($str->body)->error->message) && $get_msg = json_decode($str->body)->error->message) {
            echo "<p class='font-update-failed'>". $get_msg ."</p>";
          }
        }

        die();

        break;

        //Template setting import
        case 'import':

        $template_id = filter_var( $data['template_id'], FILTER_VALIDATE_INT );

        if ( !$template_id ) {
          die();
          break;
        }

        $settings   = $data['settings'];

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $fields = array(
          $db->quoteName( 'params' ) . ' = ' . $db->quote( $settings )
        );

        $conditions = array(
          $db->quoteName( 'id' ) . ' = '. $db->quote( $template_id ),
          $db->quoteName('client_id') . ' = 0'
        );

        $query->update($db->quoteName('#__template_styles'))->set($fields)->where($conditions);

        $db->setQuery($query);

        $db->execute();

        die();
        break;

      }

      return json_encode($report);
    }

  }

  static public function getItemRating($pk = 0){
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('ROUND(rating_sum / rating_count, 0) AS rating, rating_count')
    ->from($db->quoteName('#__content_rating'))
    ->where($db->quoteName('content_id') . ' = ' . (int) $pk);

    $db->setQuery($query);
    $data = $db->loadObject();

    return $data;
  }

  static public function resetMenuLayout($current_menu_id = 0){

    if (!$current_menu_id) {
      return;
    }

    $items  = self::menuItems();
    $item   = array();

    if (isset($items[$current_menu_id]) && !empty($items[$current_menu_id])) {
      $item = $items[$current_menu_id];
    }

    $menuItems = new JMenuSite;

    $no_child = true;
    $count = 0;
    $x_key = 0;
    $y_key = 0;
    $check_child = 0;
    $item_array = array();

    foreach ($item as $key => $id){
      $status = 0;
      if (isset($items[$id]) && is_array($items[$id])){
        $no_child = false;
        $count = $count + 1;
        $check_child = $check_child+1;
        $status = 1;
      }

      if ($check_child === 2){
        $y_key = 0;
        $x_key = $x_key + 1;
        $check_child = 1;
      }

      $item_array[$x_key][$y_key] = array($id,$status);
      $y_key = $y_key + 1;
    }

    if ($no_child === true){
      $count = 1;
    }

    if($count > 4 && $count != 6){
      $count = 4;
    }

    ob_start();

    if($no_child === true)
    {
      echo '<div class="menu-section">';
      echo '<span class="row-move"><i class="fa fa-bars"></i></span>';
      echo '<div class="spmenu sp-row">';
      echo '<div class="column sp-col-md-12" data-column="12">';
      echo '<div class="column-items-wrap">';
      if (!empty($item)) {
        echo '<h4 style="display:none" data-current_child="'.$current_menu_id.'" >'.$menuItems->getItem($current_menu_id)->title.'</h4>';
        echo '<ul class="child-menu-items">';

        foreach ($item as $key => $id)
        {
          echo '<li>'.$menuItems->getItem($id)->title.'</li>';
        }
        echo '</ul>';
      }
      echo '<div class="modules-container">';
      echo '</div>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
    }
    else
    {
      echo '<div class="menu-section">';
      echo '<span class="row-move"><i class="fa fa-bars"></i></span>';
      echo '<div class="spmenu sp-row">';

      $columnNumber = 12 / $count;
      foreach ($item_array as $key => $item_array)
      {
        echo '<div class="column sp-col-md-'.$columnNumber.'" data-column="'.$columnNumber.'">';
        echo '<div class="column-items-wrap">';

        foreach ($item_array as $key => $item)
        {
          $id = $item[0];
          echo '<h4 data-current_child="'.$id.'" >'.$menuItems->getItem($id)->title.'</h4>';
          if ($item[1])
          {
            echo '<ul class="child-menu-items">';
            echo self::create_menu($id);
            echo '</ul>';
          }
        }
        echo '<div class="modules-container"></div>';
        echo '</div>';
        echo '</div>';
      }
      echo '</div>';
      echo '</div>';
    }

    $output = ob_get_contents();
    ob_clean();

    return $output;
  }

  static public function create_menu($current_menu_id)
  {
    $items = self::menuItems();
    $menus = new JMenuSite;

    if (isset($items[$current_menu_id]))
    {
      $item = $items[$current_menu_id];
      foreach ($item as $key => $item_id)
      {
        echo '<li>';
        echo $menus->getItem($item_id)->title;
        echo '</li>';
      }
    }
  }

  static public function menuItems()
  {
    $menus = new JMenuSite;
    $menus = $menus->getMenu();
    $new = array();
    foreach ($menus as $item) {
      $new[$item->parent_id][] = $item->id;
    }
    return $new;
  }

  static public function loadNewLayout($layout_data = null){

    $lang = JFactory::getLanguage();
    $lang->load('tpl_' . self::getTemplate()->template, JPATH_SITE, $lang->getName(), true);

    ob_start();

    $colGrid = array(
      '12'        => '12',
      '66'        => '6,6',
      '444'       => '4,4,4',
      '3333'      => '3,3,3,3',
      '48'        => '4,8',
      '39'        => '3,9',
      '363'       => '3,6,3',
      '264'       => '2,6,4',
      '210'       => '2,10',
      '57'        => '5,7',
      '237'       => '2,3,7',
      '255'       => '2,5,5',
      '282'       => '2,8,2',
      '2442'      => '2,4,4,2',
    );

    if ($layout_data) {
      foreach ($layout_data as $row) {
        $rowSettings = self::getSettings($row->settings);
        $name = JText::_('HELIX_SECTION_TITLE');

        if (isset($row->settings->name)) {
          $name = $row->settings->name;
        }
        ?>
        <div class="layoutbuilder-section" <?php echo $rowSettings; ?>>
          <div class="settings-section clearfix">
            <div class="settings-left pull-left">
              <a class="row-move" href="#"><i class="fa fa-arrows"></i></a>
              <strong class="section-title"><?php echo $name; ?></strong>
            </div>
            <div class="settings-right pull-right">
              <ul class="button-group">
                <li>
                  <a class="btn btn-small add-columns" href="#"><i class="fa fa-columns"></i> <?php echo JText::_('HELIX_ADD_COLUMNS'); ?></a>
                  <ul class="column-list">
                    <?php
                    $_active = '';
                    foreach ($colGrid as $key => $grid){
                      if($key == $row->layout){
                        $_active = 'active';
                      }
                      echo '<li><a href="#" class="column-layout column-layout-' .$key. ' '.$_active.'" data-layout="'.$grid.'"></a></li>';
                      $_active ='';
                    } ?>
                    <?php
                    $active = '';
                    $customLayout = '';
                    if (!isset($colGrid[$row->layout])) {
                      $active = 'active';
                      $split = str_split($row->layout);
                      $customLayout = implode(',',$split);
                    }
                    ?>
                    <li><a href="#" class="hasTooltip column-layout-custom column-layout custom <?php echo $active; ?>" data-layout="<?php echo $customLayout; ?>" data-type='custom' data-original-title="<strong>Custom Layout</strong>"></a></li>
                  </ul>
                </li>
                <li><a class="btn btn-small add-row" href="#"><i class="fa fa-bars"></i> <?php echo JText::_('HELIX_ADD_ROW'); ?></a></li>
                <li><a class="btn btn-small row-ops-set" href="#"><i class="fa fa-gears"></i> <?php echo JText::_('HELIX_SETTINGS'); ?></a></li>
                <li><a class="btn btn-danger btn-small remove-row" href="#"><i class="fa fa-times"></i> <?php echo JText::_('HELIX_REMOVE'); ?></a></li>
              </ul>
            </div>
          </div>
          <div class="row ui-sortable">
            <?php foreach ($row->attr as $column) { $colSettings = self::getSettings($column->settings); ?>
              <div class="<?php echo $column->className; ?>" <?php echo $colSettings; ?>>
                <div class="column">
                  <?php if (isset($column->settings->column_type) && $column->settings->column_type) {
                    echo '<h6 class="col-title pull-left">Component</h6>';
                  }else{
                    if (!isset($column->settings->name)) {
                      $column->settings->name = 'none';
                    }
                    echo '<h6 class="col-title pull-left">'.$column->settings->name.'</h6>';
                  }
                  ?>
                  <a class="col-ops-set pull-right" href="#" ><i class="fa fa-gears"></i></a>
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
          <?php
        }
      }
      $items = ob_get_contents();
      ob_end_clean();

      return $items;

    }

    static public function getSettings($config = null){
      $data = '';
      if (count($config)) {
        foreach ($config as $key => $value) {
          $data .= ' data-'.$key.'="'.$value.'"';
        }
      }
      return $data;
    }

    //Get template name
    private static function getTemplate() {

      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $query->select($db->quoteName(array('template', 'params')));
      $query->from($db->quoteName('#__template_styles'));
      $query->where($db->quoteName('client_id') . ' = '. $db->quote(0));
      $query->where($db->quoteName('home') . ' = '. $db->quote(1));
      $db->setQuery($query);

      return $db->loadObject();
    }

    // Upload File
    private function upload_image() {
      $input = JFactory::getApplication()->input;
      $image = $input->files->get('image');
      $imageonly = $input->post->get('imageonly', false, 'BOOLEAN');

      $tplRegistry = new JRegistry();
      $tplParams = $tplRegistry->loadString(self::getTemplate()->params);

      $report = array();

      // User is not authorised
      if (!JFactory::getUser()->authorise('core.create', 'com_media'))
      {
        $report['status'] = false;
        $report['output'] = JText::_('You are not authorised to upload file.');
        echo json_encode($report);
        die;
      }

      if(count($image)) {

        if ($image['error'] == UPLOAD_ERR_OK) {

          $error = false;

          $params = JComponentHelper::getParams('com_media');

          // Total length of post back data in bytes.
          $contentLength = (int) $_SERVER['CONTENT_LENGTH'];

          // Instantiate the media helper
          $mediaHelper = new JHelperMedia;

          // Maximum allowed size of post back data in MB.
          $postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));

          // Maximum allowed size of script execution in MB.
          $memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));

          // Check for the total size of post back data.
          if (($postMaxSize > 0 && $contentLength > $postMaxSize) || ($memoryLimit != -1 && $contentLength > $memoryLimit)) {
            $report['status'] = false;
            $report['output'] = JText::_('Total size of upload exceeds the limit.');
            $error = true;
            echo json_encode($report);
            die;
          }

          $uploadMaxSize = $params->get('upload_maxsize', 0) * 1024 * 1024;
          $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));

          if (($image['error'] == 1) || ($uploadMaxSize > 0 && $image['size'] > $uploadMaxSize) || ($uploadMaxFileSize > 0 && $image['size'] > $uploadMaxFileSize))
          {
            $report['status'] = false;
            $report['output'] = JText::_('This file is too large to upload.');
            $error = true;
          }

          // Upload if no error found
          if(!$error) {
            // Organised folder structure
            $date = JFactory::getDate();
            $folder = JHtml::_('date', $date, 'Y') . '/' . JHtml::_('date', $date, 'm') . '/' . JHtml::_('date', $date, 'd');

            if(!file_exists( JPATH_ROOT . '/images/' . $folder )) {
              JFolder::create(JPATH_ROOT . '/images/' . $folder, 0755);
            }

            $name = $image['name'];
            $path = $image['tmp_name'];

            // Do no override existing file
            $file = pathinfo($name);
            $i = 0;
            do {
              $base_name  = $file['filename'] . ($i ? "$i" : "");
              $ext        = $file['extension'];
              $image_name = $base_name . "." . $ext;
              $i++;
              $dest = JPATH_ROOT . '/images/' . $folder . '/' . $image_name;
              $src = 'images/' . $folder . '/'  . $image_name;
              $data_src = 'images/' . $folder . '/'  . $image_name;
            } while(file_exists($dest));
            // End Do not override

            if(JFile::upload($path, $dest)) {

              $sizes = array();

              if($tplParams->get('image_small', 0)) {
                $sizes['small'] = explode('x', strtolower($tplParams->get('image_small_size', '100X100')));
              }

              if($tplParams->get('image_thumbnail', 1)) {
                $sizes['thumbnail'] = explode('x', strtolower($tplParams->get('image_thumbnail_size', '200X200')));
              }

              if($tplParams->get('image_medium', 0)) {
                $sizes['medium'] = explode('x', strtolower($tplParams->get('image_medium_size', '300X300')));
              }

              if($tplParams->get('image_large', 0)) {
                $sizes['large']  = explode('x', strtolower($tplParams->get('image_large_size', '600X600')));
              }

              $sources = Helix3Image::createThumbs($dest, $sizes, $folder, $base_name, $ext);

              if(file_exists(JPATH_ROOT . '/images/' . $folder . '/' . $base_name . '_thumbnail.' . $ext)) {
                $src = 'images/' . $folder . '/'  . $base_name . '_thumbnail.' . $ext;
              }

              $report['status'] = true;

              if($imageonly) {
                $report['output'] = '<img src="'. JURI::root(true) . '/' . $src . '" data-src="'. $data_src .'" alt="">';
              } else {
                $report['output'] = '<li data-src="'. $data_src .'"><a href="#" class="btn btn-mini btn-danger btn-remove-image">Delete</a><img src="'. JURI::root(true) . '/' . $src . '" alt=""></li>';
              }
            }
          }
        }
      } else {
        $report['status'] = false;
        $report['output'] = JText::_('Upload Failed!');
      }

      echo json_encode($report);

      die;
    }

    // Delete File
    private function remove_image() {
      $report = array();

      if (!JFactory::getUser()->authorise('core.delete', 'com_media'))
      {
        $report['status'] = false;
        $report['output'] = JText::_('You are not authorised to delete file.');
        echo json_encode($report);
        die;
      }

      $input = JFactory::getApplication()->input;
      $src = $input->post->get('src', '', 'STRING');

      $path = JPATH_ROOT . '/' . $src;

      if(file_exists($path)) {

        if(JFile::delete($path)) {

          $basename = basename($src);
          $small = JPATH_ROOT . '/' . dirname($src) . '/' . JFile::stripExt($basename) . '_small.' . JFile::getExt($basename);
          $thumbnail = JPATH_ROOT . '/' . dirname($src) . '/' . JFile::stripExt($basename) . '_thumbnail.' . JFile::getExt($basename);
          $medium = JPATH_ROOT . '/' . dirname($src) . '/' . JFile::stripExt($basename) . '_medium.' . JFile::getExt($basename);
          $large = JPATH_ROOT . '/' . dirname($src) . '/' . JFile::stripExt($basename) . '_large.' . JFile::getExt($basename);

          if(file_exists($small)) {
            JFile::delete($small);
          }

          if(file_exists($thumbnail)) {
            JFile::delete($thumbnail);
          }

          if(file_exists($medium)) {
            JFile::delete($medium);
          }

          if(file_exists($large)) {
            JFile::delete($large);
          }

          $report['status'] = true;
        } else {
          $report['status'] = false;
          $report['output'] = JText::_('Delete failed');
        }
      } else {
        $report['status'] = true;
      }

      echo json_encode($report);

      die;
    }

  }
