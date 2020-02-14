<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.controllerform');
jimport( 'joomla.application.component.helper' );
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filter.output');
jimport('joomla.filter.filteroutput');
require_once JPATH_ROOT . '/administrator/components/com_sppagebuilder/helpers/image.php';

class SppagebuilderControllerMedia extends JControllerForm {

  // Upload File
  public function upload_media() {
    $model  = $this->getModel();
    $user = JFactory::getUser();
    $input  = JFactory::getApplication()->input;

    if(isset($_FILES['file']) && $_FILES['file']) {
      $file  = $_FILES['file'];

      $dir  = $input->post->get('folder', '', 'PATH');
      $report = array();

      $authorised = $user->authorise('core.edit', 'com_sppagebuilder') || $user->authorise('core.edit.own', 'com_sppagebuilder');
      if ($authorised !== true) {
        $report['status'] = false;
        $report['output'] = JText::_('JERROR_ALERTNOAUTHOR');
        echo json_encode($report);
        die();
      }

      if(count((array) $file)) {
        if ($file['error'] == UPLOAD_ERR_OK) {
          $error = false;
          $params = JComponentHelper::getParams('com_media');
          $contentLength = (int) $_SERVER['CONTENT_LENGTH'];
          $mediaHelper = new JHelperMedia;
          $postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));
          $memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));
          // Check for the total size of post back data.
          if (($postMaxSize > 0 && $contentLength > $postMaxSize) || ($memoryLimit != -1 && $contentLength > $memoryLimit)) {
            $report['status'] = false;
            $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_MEDIA_TOTAL_SIZE_EXCEEDS');
            $error = true;
            echo json_encode($report);
            die;
          }
          $uploadMaxSize = $params->get('upload_maxsize', 0) * 1024 * 1024;
          $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));
          if (($file['error'] == 1) || ($uploadMaxSize > 0 && $file['size'] > $uploadMaxSize) || ($uploadMaxFileSize > 0 && $file['size'] > $uploadMaxFileSize)) {
            $report['status'] = false;
            $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_MEDIA_LARGE');
            $error = true;
          }

          // File formats
          $accepted_file_formats = array(
            'image' => array('jpg', 'jpeg', 'png', 'gif', 'svg'),
            'video' => array('mp4', 'mov', 'wmv', 'avi', 'mpg', 'ogv', '3gp', '3g2'),
            'audio' => array('mp3', 'm4a', 'ogg', 'wav'),
            'attachment' => array('pdf', 'doc', 'docx', 'key', 'ppt', 'pptx', 'pps', 'ppsx', 'odt', 'xls', 'xlsx', 'zip')
          );

          // Upload if no error found
          if(!$error) {
            $date = JFactory::getDate();

            $file_ext = strtolower(JFile::getExt($file['name']));

            if(self::in_array($file_ext, $accepted_file_formats)) {
              $media_type = self::array_search($file_ext, $accepted_file_formats);

              if( $media_type == 'image' ) {
                $folder_root = 'images/';
              } elseif ( $media_type == 'video' ) {
                $folder_root = 'media/videos/';
              } elseif ( $media_type == 'audio' ) {
                $folder_root = 'media/audios/';
              } elseif ( $media_type == 'attachment' ) {
                $folder_root = 'media/attachments/';
              }

              $report['type'] = $media_type;

              $folder = $folder_root . JHtml::_('date', $date, 'Y') . '/' . JHtml::_('date', $date, 'm') . '/' . JHtml::_('date', $date, 'd');

              if($dir != '') {
                $folder = ltrim($dir, '/');
              }

              if(!JFolder::exists( JPATH_ROOT . '/' . $folder )) {
                JFolder::create(JPATH_ROOT . '/' . $folder, 0755);
              }

              if($media_type == 'image') {
                if(!JFolder::exists( JPATH_ROOT . '/' . $folder . '/_spmedia_thumbs' )) {
                  JFolder::create(JPATH_ROOT . '/' . $folder . '/_spmedia_thumbs', 0755);
                }
              }

              $name = $file['name'];
              $path = $file['tmp_name'];
              // Do no override existing file

              $media_file = preg_replace('#\s+#', "-", JFile::makeSafe(basename(strtolower($name))));
              $i = 0;
              do {
                $base_name  = JFile::stripExt($media_file) . ($i ? "$i" : "");
                $ext        = JFile::getExt($media_file);
                $media_name = $base_name . '.' . $ext;
                $i++;
                $dest       = JPATH_ROOT . '/' . $folder . '/' . $media_name;
                $src        = $folder . '/'  . $media_name;
              } while(file_exists($dest));
              // End Do not override

              if(JFile::upload($path, $dest, false, true)) {

                $thumb = '';

                if($media_type == 'image') {
                  if(strtolower($ext) == 'svg') {
                    $report['src'] = JURI::root(true) . '/' . $src;
                  } else {
                    $image = new SppagebuilderHelperImage($dest);
                    if( ($image->width > 300) || ($image->height > 225) ) {
                      $image->createThumb(array('300', '300'), '_spmedia_thumbs', $base_name, $ext);
                      $report['src'] = JURI::root(true) . '/' . $folder . '/_spmedia_thumbs/' . $base_name . '.' . $ext;
                      $thumb = $folder . '/_spmedia_thumbs/'  . $base_name . '.' . $ext;
                      $report['thumb'] = $thumb;
                    } else {
                      $report['src'] = JURI::root(true) . '/' . $src;
                      $report['thumb'] = $src;
                    }
                  }
                }

                $insertid = $model->insertMedia($base_name, $src, $thumb, $media_type);
                $report['status'] = true;
                $report['title'] = $base_name;
                $report['id'] = $insertid;
                $report['path'] = $src;

                $layout_path = JPATH_ROOT . '/administrator/components/com_sppagebuilder/layouts';
                $format_layout = new JLayoutFile('media.format', $layout_path);
                $report['output'] = $format_layout->render( array( 'media'=>$model->getMediaByID($insertid), 'innerHTML' => true ));

              } else {
                $report['status'] = false;
                $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_UPLOAD_FAILED');
              }

            } else {
              $report['status'] = false;
              $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_FILE_NOT_SUPPORTED');
            }

          }
        }
      } else {
        $report['status'] = false;
        $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_UPLOAD_FAILED');
      }
    } else {
      $report['status'] = false;
      $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_UPLOAD_FAILED');
    }

    echo json_encode($report);
    die();
  }


  // Delete File
  public function delete_media() {
    $model  = $this->getModel();
    $user = JFactory::getUser();
    $input  = JFactory::getApplication()->input;
    $m_type = $input->post->get('m_type', NULL, 'STRING');
    
    if($m_type == 'path') {
      $report = array();
      $report['status'] = true;
      $path = htmlspecialchars($input->post->get('path', NULL, 'STRING'));
      $src = JPATH_ROOT . '/' . $path;

      if(JFile::exists($src)) {
        if(!JFile::delete($src)) {
          $report['status'] = false;
          $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_DELETE_FAILED');
          echo json_encode($report);
          die;
        }
      } else {
        $report['status'] = true;
      }

      echo json_encode($report);

    } else {
      $id = $input->post->get('id', NULL, 'INT');

      if(!is_numeric($id)) {
        $report['status'] = false;
        $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_DELETE_FAILED');
        echo json_encode($report);
        die;
      }
  
      $media = $model->getMediaByID($id);

      $authorised = $user->authorise('core.edit', 'com_sppagebuilder') || ($user->authorise('core.edit.own', 'com_sppagebuilder') && ($media->created_by == $user->id));
      if ($authorised !== true) {
        $report['status'] = false;
        $report['output'] = JText::_('JERROR_ALERTNOAUTHOR');
        echo json_encode($report);
        die();
      }

      $src = JPATH_ROOT . '/' . $media->path;

      $report = array();
      $report['status'] = false;

      if(isset($media->thumb) && $media->thumb) {
        if(JFile::exists(JPATH_ROOT . '/' . $media->thumb)) {
          JFile::delete(JPATH_ROOT . '/' . $media->thumb); // Delete thumb
        }
      }

      if(JFile::exists($src)) {
        if(!JFile::delete($src)) {
          $report['status'] = false;
          $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_DELETE_FAILED');
          echo json_encode($report);
          die;
        }
      } else {
        $report['status'] = true;
      }

      // Remove from database
      $media = $model->removeMediaByID($id);
      $report['status'] = true;

      echo json_encode($report);
    }

    
    die;
  }


  private static function in_array($needle, $haystack) {

    $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($haystack));

    foreach($it AS $element) {
      if($element == $needle) {
        return true;
      }
    }

    return false;
  }


  private static function array_search($needle, $haystack) {

    foreach($haystack as $key=>$value) {
      $current_key=$key;
      if($needle===$value OR (is_array($value) && self::array_search($needle,$value) !== false)) {
        return $current_key;
      }
    }
    return false;
  }

  // Create folder
  public function create_folder() {
    $input  = JFactory::getApplication()->input;
    $folder = $input->post->get('folder', '', 'STRING');
      
    $dirname = dirname($folder);
    $basename = JFilterOutput::stringURLSafe(basename($folder));
    $folder = $dirname . '/'. $basename;

    $report = array();
    $report['status'] = false;
    $fullname = JPATH_ROOT . $folder;

    if(!JFolder::exists(JPATH_ROOT . $folder)) {
      if(JFolder::create(JPATH_ROOT . $folder, 0755)) {
        $report['status'] = true;

        $folder_info['name'] = basename($folder);
        $folder_info['relname'] = $folder;
        $folder_info['fullname'] = $fullname;
        $report['output'] = $folder_info;

      } else {
        $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_FOLDER_CREATION_FAILED');
      }
    } else {
      $report['output'] = JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_FOLDER_EXISTS');
    }

    echo json_encode($report);
    die;
  }
}
