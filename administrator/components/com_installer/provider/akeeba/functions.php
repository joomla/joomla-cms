<?php

if( !function_exists('inspect') ){
  function inspect(){
    echo '<pre>' . print_r(func_get_args(), true) . '</pre>';
  }
}

// fnmatch not available on non-POSIX systems
// Thanks to soywiz@php.net for this usefull alternative function [http://gr2.php.net/fnmatch]
if (!function_exists('fnmatch'))
{
  function fnmatch($pattern, $string)
  {
    return @preg_match(
      '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
        array('*' => '.*', '?' => '.?')) . '$/i', $string
    );
  }
}

// Unicode-safe binary data length function
if (!function_exists('akstringlen'))
{
  if (function_exists('mb_strlen'))
  {
    function akstringlen($string)
    {
      return mb_strlen($string, '8bit');
    }
  }
  else
  {
    function akstringlen($string)
    {
      return strlen($string);
    }
  }
}

/**
 * Gets a query parameter from GET or POST data
 *
 * @param $key
 * @param $default
 */
function getQueryParam($key, $default = null)
{
  $value = $default;

  if (array_key_exists($key, $_REQUEST))
  {
    $value = $_REQUEST[$key];
  }

  if (get_magic_quotes_gpc() && !is_null($value))
  {
    $value = stripslashes($value);
  }

  return $value;
}

// Debugging function
function debugMsg($msg){
  if (!defined('KSDEBUG')){
    return;
  }
  $fp = fopen('debug.txt', 'at');
  fwrite($fp, (is_string($msg) ? $msg : print_r($msg,true)) . "\n");
  fclose($fp);
}

// ------------ lixlpixel recursive PHP functions -------------
// recursive_remove_directory( directory to delete, empty )
// expects path to directory and optional TRUE / FALSE to empty
// of course PHP has to have the rights to delete the directory
// you specify and all files and folders inside the directory
// ------------------------------------------------------------
function recursive_remove_directory($directory)
{
  // if the path has a slash at the end we remove it here
  if(substr($directory,-1) == '/')
  {
    $directory = substr($directory,0,-1);
  }
  // if the path is not valid or is not a directory ...
  if(!file_exists($directory) || !is_dir($directory))
  {
    // ... we return false and exit the function
    return FALSE;
  // ... if the path is not readable
  }elseif(!is_readable($directory))
  {
    // ... we return false and exit the function
    return FALSE;
  // ... else if the path is readable
  }else{
    // we open the directory
    $handle = opendir($directory);
    $postproc = AKFactory::getPostProc();
    // and scan through the items inside
    while (FALSE !== ($item = readdir($handle)))
    {
      // if the filepointer is not the current directory
      // or the parent directory
      if($item != '.' && $item != '..')
      {
        // we build the new path to delete
        $path = $directory.'/'.$item;
        // if the new path is a directory
        if(is_dir($path))
        {
          // we call this function with the new path
          recursive_remove_directory($path);
        // if the new path is a file
        }else{
          // we remove the file
          $postproc->unlink($path);
        }
      }
    }
    // close the directory
    closedir($handle);
    // try to delete the now empty directory
    if(!$postproc->rmdir($directory))
    {
      // return false if not possible
      return FALSE;
    }
    // return success
    return TRUE;
  }
}
