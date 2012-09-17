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
class JLayoutBase implements JLayout
{
  
  public function escape( $output) {
    return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
  }
  
  /**
   * Render a layout
   *
   * @return  string  The necessary HTML to display the layout
   *
   * @since   1.7
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
