<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 *
 * @author yannick
 *
 */
class JLayoutFile extends JLayoutBase {

  protected $layoutId = '';

  public function __construct( $layoutId) {
    $this->layoutId = $layoutId;
  }

  public function render( $displayData) {

    $layoutOutput = '';
    $path = $this->_getPath();
    if(!empty($path) && file_exists( $path)) {
      ob_start();
      include $path;
      $layoutOutput = ob_get_contents();
      ob_end_clean();
    }

    return $layoutOutput;
  }

  /**
   * Finds real file path, and check overrides
   */
  protected function _getPath() {

    static $fullPath = null;

    if(is_null( $fullPath) && !empty($this->layoutId)) {
      $path = str_replace( '.', '/', $this->layoutId) . '.php';

      // 1rst look for overrides
      $overrideFile = JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/' . $path;
      if(file_exists( $overrideFile)) {
        $fullPath = $overrideFile;
      } else {
        $fullPath = JPATH_ROOT . '/layouts/' . $path;
        if(!file_exists( $fullPath)) {
          // file does not exists, store empty string to avoid new lookups
          $fullPath = '';
        }
      }

    }

    return $fullPath;
  }
}
