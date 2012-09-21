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
 * Base class for rendering a display layout
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @since       3.0
 */
class JLayoutBase implements JLayout
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
  public function escape( $output)
  {
    return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
  }
  
  /**
   * Method to render the layout.
   *
   * @return  string  The necessary HTML to display the layout
   *
   * @since   3.0
   * @throws  RuntimeException
   */
  public function render( $displayData)
  {

    $layoutOutput = '';
    
    /*
     * 
     ob_start();
     
     ?>
     
     <div class="something">
       <?php echo $this->escape( $displayData->someText); ?>
     </div>
     
     <?php
     
     $layoutOutput = ob_get_contents();
     ob_end_clean();
     * 
     */
    return $layoutOutput;

  }

}
