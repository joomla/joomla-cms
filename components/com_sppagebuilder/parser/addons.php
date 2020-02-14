<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

abstract class SppagebuilderAddons {

  public function __construct( $addon )
  {

    if ( !$addon ) {
      return false;
    }

    $this->addon = $addon;
  }
}
