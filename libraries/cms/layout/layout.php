<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Interface to handle display layout
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @since       3.0
 */
interface JLayout
{

  /**
   * Method to escape output.
   *
   * @param   string  $output  The output to escape.
   *
   * @return  string  The escaped output.
   *
   * @since   3.0
   */
  public function escape( $output);
  
  /**
   * Method to render the layout.
   *
   * @return  string  The rendered layout.
   *
   * @since   3.0
   * @throws  RuntimeException
   */
  public function render( $displayData);
  
}
