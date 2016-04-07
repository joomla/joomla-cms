<?php

/**
 *
 *  The logic for this is based on the current Akeeba integration
 *  and needs to be reviewed / written for generalize forked installations
 *  where standard event calls are intercepted by the provider, and the
 *  package dictates all package specfic pre/post procedures.
 *
 */

abstract class JInstallerStandaloneProvider extends JObject {

  /**
   * [__construct description]
   * @param [type] $config [description]
   */
  public function __construct( $config ){
    foreach( $config AS $key => $val ){
      $this->set( $key, $config[$key] );
    }
  }

  /**
   * [reset description]
   * @return [type] [description]
   */
  public function reset(){}

  /**
   * [buildInstaller description]
   * @return [type] [description]
   */
  public function buildInstaller(){}

}
