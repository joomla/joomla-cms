<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

namespace HelixUltimate\Media;

defined ('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class Media
{

  public static function getFolders()
  {
    $media = array();
    $media['status'] = false;
    $media['output'] = \JText::_('JINVALID_TOKEN');
    \JSession::checkToken() or die(json_encode($media));

    $input 	= \JFactory::getApplication()->input;
    $path 	= $input->post->get('path', '/images', 'PATH');

    $images = \JFolder::files(JPATH_ROOT . $path, '.png|.jpg|.gif|.svg|.ico', false, true);
    $folders = \JFolder::folders(JPATH_ROOT . $path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', '_spmedia_thumbs'));

    $crumbs = explode('/', ltrim($path, '/'));
    $crumb_url = '';

    $breadcrumb = '<ul class="helix-ultimate-media-breadcrumb">';
    foreach($crumbs as $key => $crumb){
      $crumb_url .= '/' . $crumb;

      if(count($crumbs) == ($key + 1) ) {
        $breadcrumb .= '<li class="helix-ultimate-media-breadcrumb-item active" data-path="' . $crumb_url . '">' . preg_replace('/[-_]+/', ' ', $crumb) . '</li>';
      } else {
        $breadcrumb .= '<li class="helix-ultimate-media-breadcrumb-item" data-path="' . $crumb_url . '"><a href="#" data-path="' . $crumb_url . '">' . preg_replace('/[-_]+/', ' ', $crumb) . '</a></li>';
      }

    }
    $breadcrumb .= '</ul>';

    $media['breadcrumbs'] = $breadcrumb;
    $media['path'] = $path;
    $files = array();


    $media['images'] = $images;
    $media['folders'] = $folders;

    $output = '<div id="helix-ultimate-media-manager">';
    $output .= '<ul class="helix-ultimate-media clearfix">';

    if(count($folders)) {
      foreach ($folders as $folder) {
        $files[$folder] = array(
          'type' => 'folder',
          'folder' => $path . '/' . $folder,
          'name' => $folder
        );
      }
    }

    if(count($images)) {
      foreach ($images as $image) {
        $image 			= str_replace('\\', '/',$image);
      	$root_path 	= str_replace('\\', '/', JPATH_ROOT);
      	$path 			= str_replace($root_path . '/', '', $image);

        $files[basename($path)] = array(
          'type' => 'image',
          'path' => $path,
          'name' => basename($path),
          'preview' => \JURI::root() . $path
        );
      }
    }

    if(count($files)) {
      ksort($files);
      foreach ($files as $key => $file) {
        if($file['type'] == 'folder') {
          $output .= '<li class="helix-ultimate-media-folder" data-path="' . $file['folder'] .'">';
          $output .= '<div class="helix-ultimate-media-thumb">';
          $output .= '<svg width="160" height="160" viewBox="0 0 160 160"><g fill="none" fill-rule="evenodd"><path d="M77.955 53h50.04A3.002 3.002 0 0 1 131 56.007v58.988a4.008 4.008 0 0 1-4.003 4.005H39.003A4.002 4.002 0 0 1 35 114.995V45.99c0-2.206 1.79-3.99 3.997-3.99h26.002c1.666 0 3.667 1.166 4.49 2.605l3.341 5.848s1.281 2.544 5.12 2.544l.005.003z" fill="#71B9F4"></path><path d="M77.955 52h50.04A3.002 3.002 0 0 1 131 55.007v58.988a4.008 4.008 0 0 1-4.003 4.005H39.003A4.002 4.002 0 0 1 35 113.995V44.99c0-2.206 1.79-3.99 3.997-3.99h26.002c1.666 0 3.667 1.166 4.49 2.605l3.341 5.848s1.281 2.544 5.12 2.544l.005.003z" fill="#92CEFF"></path></g></svg>';
          $output .= '</div>';
          $output .= '<span class="helix-ultimate-media-select"><span class="fa fa-check"></span></span>';
          $output .= '<div class="helix-ultimate-media-label">'. $file['name'] .'</div>';
          $output .= '</li>';
        } else {
          $output .= '<li class="helix-ultimate-media-image" data-path="'. $file['path'] .'" data-preview="'. $file['preview'] .'">';
          $output .= '<div class="helix-ultimate-media-thumb">';
          $output .= '<img src="'. $file['preview'] .'" alt="">';
          $output .= '</div>';
          $output .= '<span class="helix-ultimate-media-select"><span class="fa fa-check"></span></span>';
          $output .= '<div class="helix-ultimate-media-label">'. $file['name'] .'</div>';
          $output .= '</li>';
        }
      }
    } else {
      //$output .= '<li class="helix-ultimate-media-folder-empty"></li>';
    }

    $output .= '</ul>';
    $output .= '</div>';

    $media['status'] = true;
    $media['output'] = $output;

    die(json_encode($media));
  }

  public static function deleteMedia() {
    $output = array();
    $output['status'] = false;
    $output['message'] = \JText::_('JINVALID_TOKEN');
    \JSession::checkToken() or die(json_encode($output));

    $input 	= \JFactory::getApplication()->input;
    $path 	= $input->post->get('path', '/images', 'PATH');
    $type	= $input->post->get('type', 'file', 'STRING');

    if($type == 'file') {
      if(\JFile::delete( JPATH_ROOT . '/' . $path)) {
        $output['status'] = true;
      } else {
        $output['message'] = "Unable to delete file";
        $output['status'] = false;
      }
    } else {
      if(\JFolder::delete( JPATH_ROOT . '/' . $path)) {
        $output['status'] = true;
      } else {
        $output['message'] = "Unable to delete folder";
        $output['status'] = false;
      }
    }

    die(json_encode($output));
  }

  public static function createFolder() {
    $output = array();
    $output['status'] = false;
    $output['message'] = \JText::_('JINVALID_TOKEN');
    \JSession::checkToken() or die(json_encode($output));

    $input 	= \JFactory::getApplication()->input;
    $path 	= $input->post->get('path', '/images', 'PATH');
    $folder_name 	= $input->post->get('folder_name', '', 'STRING');

    $absolute_path = JPATH_ROOT . $path . '/' . preg_replace('/\s+/', '-', $folder_name);

    if(\JFolder::exists($absolute_path)) {
      $output['message'] = "Folder is already exists.";
      $output['status'] = false;
    } else {
      if(\JFolder::create($absolute_path, 0755)) {
        $output['output'] = self::getFolders();
        $output['status'] = true;
      } else {
        $output['message'] = "Unable to create folder.";
        $output['status'] = false;
      }
    }

    die(json_encode($output));
  }

  public static function uploadMedia() {

    $user   = \JFactory::getUser();
    $input 	= \JFactory::getApplication()->input;
    $dir 	= $input->post->get('path', '/images', 'PATH');
    $index 	= $input->post->get('index', '', 'STRING');
    $file 	= $input->files->get('file');
    $authorised = $user->authorise('core.edit', 'com_templates');

    $report = array();
    $report['status'] = false;
    $report['message'] = \JText::_('JINVALID_TOKEN');
    $report['index'] = $index;

    \JSession::checkToken() or die(json_encode($report));

    if ($authorised !== true) {
      $report['status'] = false;
      $report['message'] = \JText::_('JERROR_ALERTNOAUTHOR');
      echo json_encode($report);
      die();
    }

    if(count($file)) {
      if ($file['error'] == UPLOAD_ERR_OK) {
        $error = false;
        $params = \JComponentHelper::getParams('com_media');
        $contentLength = (int) $_SERVER['CONTENT_LENGTH'];
        $mediaHelper = new \JHelperMedia;
        $postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));
        $memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));
        // Check for the total size of post back data.
        if (($postMaxSize > 0 && $contentLength > $postMaxSize) || ($memoryLimit != -1 && $contentLength > $memoryLimit)) {
          $report['status'] = false;
          $report['message'] = \JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_MEDIA_TOTAL_SIZE_EXCEEDS');
          $error = true;
          echo json_encode($report);
          die;
        }
        $uploadMaxSize = $params->get('upload_maxsize', 0) * 1024 * 1024;
        $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));
        if (($file['error'] == 1) || ($uploadMaxSize > 0 && $file['size'] > $uploadMaxSize) || ($uploadMaxFileSize > 0 && $file['size'] > $uploadMaxFileSize)) {
          $report['status'] = false;
          $report['message'] = \JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_MEDIA_LARGE');
          $error = true;
        }

        // File formats
        $accepted_file_formats = array('jpg', 'jpeg', 'png', 'gif', 'svg', 'ico');
        
        // Upload if no error found
        if(!$error) {

          $file_ext = strtolower(\JFile::getExt($file['name']));

          if(in_array($file_ext, $accepted_file_formats)) {

            $name = $file['name'];
            $source_path = $file['tmp_name'];
            $folder = ltrim($dir, '/');
            // Do no override existing file

            $media_file = preg_replace('#\s+#', "-", \JFile::makeSafe(basename(strtolower($name))));
            $i = 0;
            do {
              $base_name  = \JFile::stripExt($media_file) . ($i ? "$i" : "");
              $ext        = \JFile::getExt($media_file);
              $media_name = $base_name . '.' . $ext;
              $i++;
              $dest       = \JPATH_ROOT . '/' . $folder . '/' . $media_name;
              $src        = $folder . '/'  . $media_name;
            } while(file_exists($dest));
            // End Do not override

            if(\JFile::upload($source_path, $dest, false, true)) {

              $report['src'] = \JURI::root(true) . '/' . $src;
              $report['status'] = true;
              $report['title'] = $media_name;
              $report['path'] = $src;

              $output = '<div class="helix-ultimate-media-thumb">';
              $output .= '<img src="'. $report['src'] .'" alt="">';
              $output .= '</div>';
              $output .= '<span class="helix-ultimate-media-select"><span class="fa fa-check"></span></span>';
              $output .= '<div class="helix-ultimate-media-label">'. $report['title'] .'</div>';

              $report['output'] = $output;

            } else {
              $report['status'] = false;
              $report['message'] = \JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_UPLOAD_FAILED');
            }

          } else {
            $report['status'] = false;
            $report['message'] = \JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_FILE_NOT_SUPPORTED');
          }

        }
      }
    } else {
      $report['status'] = false;
      $report['message'] = \JText::_('File not found');
    }

    die(json_encode($report));
  }

}