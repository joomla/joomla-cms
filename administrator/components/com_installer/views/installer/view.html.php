<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once JPATH_ADMINISTRATOR . '/components/com_installer/views/default/view.php';
require_once JPATH_ADMINISTRATOR . '/components/com_installer/helpers/installer.php';

/**
 * Extension Manager Install View
 *
 * @since  1.5
 */
class InstallerViewInstaller extends InstallerViewDefault
{

  /**
   * [display description]
   * @param  [type] $tpl [description]
   * @return [type]      [description]
   */
  public function display($tpl = null)
  {

    // Stage
      $app = JFactory::getApplication();

    // Identify Installation Step
      $queue = $app->getUserState('com_installer.queue', array());
      $this->queue_count = ($queue ? count($queue) : 1);
      $this->queue_active = 1;
      if( $queue ){
        for( $i=0; $i<count($queue); $i++ ){
          $queueItem = $queue[$i];
          if( in_array($queueItem['status'], array('active')) ){
            $this->queue_active = $i + 1;
          }
        }
      }

    // Display
      $this->state = $this->get('state');
      parent::display($tpl);

  }

  /**
   * [addToolbar description]
   */
  protected function addToolbar()
  {
    JToolbarHelper::title(JText::_('COM_INSTALLER_HEADER_INSTALLER'), 'puzzle install');
  }

}
