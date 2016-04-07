<?php

/**
 * Akeeba Restore
 * A JSON-powered JPA, JPS and ZIP archive extraction library
 *
 * @copyright   2010-2014 Nicholas K. Dionysopoulos / Akeeba Ltd.
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */

/**
 * The Akeeba Kickstart Factory class
 * This class is reponssible for instanciating all Akeeba Kicsktart classes
 */
class AKFactory {
  /** @var array A list of instantiated objects */
  private $objectlist = array();

  /** @var array Simple hash data storage */
  private $varlist = array();

  /** Private constructor makes sure we can't directly instanciate the class */
  private function __construct() {}

  /**
   * Gets a single, internally used instance of the Factory
   * @param string $serialized_data [optional] Serialized data to spawn the instance from
   * @return AKFactory A reference to the unique Factory object instance
   */
  protected static function &getInstance( $serialized_data = null ) {
    static $myInstance;
    if(!is_object($myInstance) || !is_null($serialized_data))
      if(!is_null($serialized_data))
      {
        $myInstance = unserialize($serialized_data);
      }
      else
      {
        $myInstance = new self();
      }

    return $myInstance;
  }

  /**
   * Internal function which instanciates a class named $class_name.
   * The autoloader
   * @param object $class_name
   * @return
   */
  protected static function &getClassInstance($class_name) {
    $self = self::getInstance();
    if(!isset($self->objectlist[$class_name]))
    {
      $self->objectlist[$class_name] = new $class_name;
    }
    return $self->objectlist[$class_name];
  }

  // ========================================================================
  // Public factory interface
  // ========================================================================

  /**
   * Gets a serialized snapshot of the Factory for safekeeping (hibernate)
   * @return string The serialized snapshot of the Factory
   */
  public static function serialize() {
    $engine = self::getUnarchiver();
    $engine->shutdown();
    $serialized = serialize(self::getInstance());

    if(function_exists('base64_encode') && function_exists('base64_decode'))
    {
      $serialized = base64_encode($serialized);
    }
    return $serialized;
  }

  /**
   * Regenerates the full Factory state from a serialized snapshot (resume)
   * @param string $serialized_data The serialized snapshot to resume from
   */
  public static function unserialize($serialized_data) {
    if(function_exists('base64_encode') && function_exists('base64_decode'))
    {
      $serialized_data = base64_decode($serialized_data);
    }
    self::getInstance($serialized_data);
  }

  /**
   * Reset the internal factory state, freeing all previously created objects
   */
  public static function nuke()
  {
    $self = self::getInstance();
    foreach($self->objectlist as $key => $object)
    {
      $self->objectlist[$key] = null;
    }
    $self->objectlist = array();
  }

  // ========================================================================
  // Public hash data storage interface
  // ========================================================================

  public static function set($key, $value)
  {
    $self = self::getInstance();
    $self->varlist[$key] = $value;
  }

  public static function get($key, $default = null)
  {
    $self = self::getInstance();
    if( array_key_exists($key, $self->varlist) )
    {
      return $self->varlist[$key];
    }
    else
    {
      return $default;
    }
  }

  // ========================================================================
  // Akeeba Kickstart classes
  // ========================================================================

  /**
   * Gets the post processing engine
   * @param string $proc_engine
   */
  public static function &getPostProc($proc_engine = null)
  {
    static $class_name;
    if( empty($class_name) )
    {
      if(empty($proc_engine))
      {
        $proc_engine = self::get('kickstart.procengine','direct');
      }
      $class_name = 'AKPostproc'.ucfirst($proc_engine);
    }
    return self::getClassInstance($class_name);
  }

  /**
   * Gets the unarchiver engine
   */
  public static function &getUnarchiver( $configOverride = null )
  {
    static $class_name;

    // Reset Package Class
      if(!empty($configOverride)) {
      if($configOverride['reset']) {
        $class_name = null;
      }
    }

    // Idenfify Package Type
      if( empty($class_name) ) {
      $filetype = self::get('kickstart.setup.filetype', null);
        if(empty($filetype)) {
        $filename = self::get('kickstart.setup.sourcefile', null);
        $basename = basename($filename);
        $baseextension = strtoupper(substr($basename,-3));
          switch($baseextension) {
          case 'JPA':
            $filetype = 'JPA';
            break;
          case 'JPS':
            $filetype = 'JPS';
            break;
          case 'ZIP':
            $filetype = 'ZIP';
            break;
          case 'FOLDER':
              $filetype = 'FOLDER';
            break;
          default:
            die('Invalid archive type or extension in file '.$filename);
            break;
        }
      }
      $class_name = 'AKUnarchiver'.ucfirst($filetype);
    }

    // Stage Destination
    $destdir = self::get('kickstart.setup.destdir', null);
      if(empty($destdir)) {
      $destdir = KSROOTDIR;
    }

    // Package Handler
    $object = self::getClassInstance($class_name);
    if( $object->getState() == 'init')
    {
      $sourcePath = self::get('kickstart.setup.sourcepath', '');
      $sourceFile = self::get('kickstart.setup.sourcefile', '');
      if( !empty($sourcePath) ){
        $sourceFile = rtrim($sourcePath, '/\\') . '/' . $sourceFile;
      }
      // Initialize the object
      $config = array(
        'filename'            => $sourceFile,
        'restore_permissions' => self::get('kickstart.setup.restoreperms', 0),
        'post_proc'           => self::get('kickstart.procengine', 'direct'),
        'add_path'            => self::get('kickstart.setup.targetpath', $destdir),
        'rename_files'        => array('.htaccess' => 'htaccess.bak', 'php.ini' => 'php.ini.bak', 'web.config' => 'web.config.bak'),
        'skip_files'          => array(basename(__FILE__), 'kickstart.php', 'abiautomation.ini', 'htaccess.bak', 'php.ini.bak', 'cacert.pem'),
        'ignoredirectories'   => array('tmp', 'log', 'logs'),
      );
      if( !defined('KICKSTART') ){
        // In installer.php mode we have to exclude some more files
        $config['skip_files'][] = 'media/components/com_installer/standalone/installer.php';
        $config['skip_files'][] = 'media/components/com_installer/standalone/installer.config.php';
      }
      if( !empty($configOverride) ){
        foreach( $configOverride as $key => $value ){
          $config[$key] = $value;
        }
      }
      $object->setup($config);
    }

    return $object;
  }

  /**
   * Get the a reference to the Akeeba Engine's timer
   * @return AKCoreTimer
   */
  public static function &getTimer()
  {
    return self::getClassInstance('AKCoreTimer');
  }

}

