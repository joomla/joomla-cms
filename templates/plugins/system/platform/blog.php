<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

namespace HelixUltimate\Blog;

defined ('_JEXEC') or die();

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.registry.registry');

require_once __DIR__ . '/classes/image.php';

use HelixUltimate\Image\Image as Image;

class Blog
{

  // Upload File
  public static function upload_image()
  {

      $report = array();
      $report['status'] = false;
      $report['output'] = 'Invalid Token';
      \JSession::checkToken() or die(json_encode($report));

      $input = \JFactory::getApplication()->input;
      $image = $input->files->get('image');
      $index = htmlspecialchars($input->post->get('index', '', 'STRING'));
      $gallery = $input->post->get('gallery', false, 'BOOLEAN');

      $tplRegistry = new \JRegistry();
      $tplParams = $tplRegistry->loadString(self::getTemplate()->params);

      // User is not authorised
      if (!\JFactory::getUser()->authorise('core.create', 'com_media'))
      {
          $report['status'] = false;
          $report['output'] = \JText::_('You are not authorised to upload file.');
          echo json_encode($report);
          die;
      }

      if(count($image))
      {

          if ($image['error'] == UPLOAD_ERR_OK)
          {
              $error = false;

              $params = \JComponentHelper::getParams('com_media');
              $contentLength = (int) $_SERVER['CONTENT_LENGTH'];
              $mediaHelper = new \JHelperMedia;
              $postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));
              $memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));
          
              if (($postMaxSize > 0 && $contentLength > $postMaxSize) || ($memoryLimit != -1 && $contentLength > $memoryLimit))
              {
                  $report['status'] = false;
                  $report['output'] = \JText::_('Total size of upload exceeds the limit.');
                  $error = true;
                  die(json_encode($report));
              }

              $uploadMaxSize = $params->get('upload_maxsize', 0) * 1024 * 1024;
              $uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));

              if (($image['error'] == 1) || ($uploadMaxSize > 0 && $image['size'] > $uploadMaxSize) || ($uploadMaxFileSize > 0 && $image['size'] > $uploadMaxFileSize))
              {
                  $report['status'] = false;
                  $report['output'] = \JText::_('This file is too large to upload.');
                  $error = true;
              }

              if(!$error)
              {
                  $date = \JFactory::getDate();
                  $folder = \JHtml::_('date', $date, 'Y') . '/' . \JHtml::_('date', $date, 'm') . '/' . \JHtml::_('date', $date, 'd');

                  if(!file_exists( \JPATH_ROOT . '/images/' . $folder ))
                  {
                      \JFolder::create(\JPATH_ROOT . '/images/' . $folder, 0755);
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
                      $dest = \JPATH_ROOT . '/images/' . $folder . '/' . $image_name;
                      $src = 'images/' . $folder . '/'  . $image_name;
                      $data_src = 'images/' . $folder . '/'  . $image_name;
                  } while(file_exists($dest));

                  if(\JFile::upload($path, $dest)) {

                      $image_quality = $tplParams->get('image_crop_quality', '100');

                      if($tplParams->get('image_small', 0))
                      {
                          $sizes['small'] = explode('x', strtolower($tplParams->get('image_small_size', '100X100')));
                      }

                      if($tplParams->get('image_thumbnail', 1))
                      {
                          $sizes['thumbnail'] = explode('x', strtolower($tplParams->get('image_thumbnail_size', '200X200')));
                      }

                      if($tplParams->get('image_medium', 0))
                      {
                          $sizes['medium'] = explode('x', strtolower($tplParams->get('image_medium_size', '300X300')));
                      }
                      
                      if($tplParams->get('image_large', 0))
                      {
                          $sizes['large']  = explode('x', strtolower($tplParams->get('image_large_size', '600X600')));
                      }

                      if(count($sizes))
                      {
                          $sources = Image::createThumbs($dest, $sizes, $folder, $base_name, $ext, $image_quality);
                      }

                      if(\JFile::exists(\JPATH_ROOT . '/images/' . $folder . '/' . $base_name . '_thumbnail.' . $ext))
                      {
                          $src = 'images/' . $folder . '/'  . $base_name . '_thumbnail.' . $ext;
                      }

                      $report['status'] = true;
                      $report['index'] = $index;

                      if($gallery)
                      {
                          $report['output'] = '<a href="#" class="btn btn-mini btn-danger btn-helix-ultimate-remove-gallery-image"><span class="fa fa-times"></span></a><img src="'. \JURI::root(true) . '/' . $src . '" alt="">';
                          $report['data_src'] = $data_src;
                      }
                      else
                      {
                          $report['output'] = '<img src="'. \JURI::root(true) . '/' . $src . '" data-src="'. $data_src .'" alt="">';
                      }
                  }
              }
          }
      }
      else
      {
          $report['status'] = false;
          $report['output'] = \JText::_('Upload Failed!');
      }

      die(json_encode($report));
  }

  // Delete File
  public static function remove_image()
  {
      $report = array();
      $report['status'] = false;
      $report['output'] = 'Invalid Token';
      \JSession::checkToken() or die(json_encode($report));

      if (!\JFactory::getUser()->authorise('core.delete', 'com_media'))
      {
          $report['status'] = false;
          $report['output'] = \JText::_('You are not authorised to delete file.');
          echo json_encode($report);
          die;
      }

      $input = \JFactory::getApplication()->input;
      $src = $input->post->get('src', '', 'STRING');

      $path = \JPATH_ROOT . '/' . $src;

      if(\JFile::exists($path))
      {

          if(\JFile::delete($path))
          {

              $basename = basename($src);
              $small = \JPATH_ROOT . '/' . dirname($src) . '/' . \JFile::stripExt($basename) . '_small.' . \JFile::getExt($basename);
              $thumbnail = \JPATH_ROOT . '/' . dirname($src) . '/' . \JFile::stripExt($basename) . '_thumbnail.' . \JFile::getExt($basename);
              $medium = \JPATH_ROOT . '/' . dirname($src) . '/' . \JFile::stripExt($basename) . '_medium.' . \JFile::getExt($basename);
              $large = \JPATH_ROOT . '/' . dirname($src) . '/' . \JFile::stripExt($basename) . '_large.' . \JFile::getExt($basename);

              if(\JFile::exists($small))
              {
                  \JFile::delete($small);
              }

              if(\JFile::exists($thumbnail))
              {
                  \JFile::delete($thumbnail);
              }

              if(\JFile::exists($medium))
              {
                  \JFile::delete($medium);
              }

              if(\JFile::exists($large))
              {
                  \JFile::delete($large);
              }

              $report['status'] = true;
          }
          else
          {
              $report['status'] = false;
              $report['output'] = \JText::_('Delete failed');
          }
      }
      else 
      {
          $report['status'] = true;
      }

      die(json_encode($report));
  }

  private static function getTemplate()
    {

        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('template', 'params')));
        $query->from($db->quoteName('#__template_styles'));
        $query->where($db->quoteName('client_id') . ' = '. $db->quote(0));
        $query->where($db->quoteName('home') . ' = '. $db->quote(1));
        $db->setQuery($query);

        return $db->loadObject();

    }

}
