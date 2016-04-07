<?php

// Stage
  $result = array(
    'status'           => null,
    'message'          => null,
    'percent_complete' => 0,
    'bytes_read'       => 0,
    'bytes_extracted'  => 0,
    'files_extracted'  => 0,
    'config'           => $config
    );

// Validate
  if(
    !empty($com_installer_config)
    && !empty($com_installer_config['jpath_base'])
    && !empty($com_installer_config['package'])
    && !empty($com_installer_config['package']['dir'])
    ){

    // Saves the start time and memory usage.
      $startTime = microtime(1);
      $startMem  = memory_get_usage();

    // Prepare Environment
      define('_JEXEC', 1);
      if (file_exists(__DIR__ . '/defines.php'))
      {
        include_once __DIR__ . '/defines.php';
      }
      if (!defined('_JDEFINES'))
      {
        define('JPATH_BASE', $com_installer_config['jpath_base']);
        require_once JPATH_BASE . '/includes/defines.php';
      }
      require_once JPATH_BASE . '/includes/framework.php';
      require_once JPATH_BASE . '/includes/helper.php';
      require_once JPATH_BASE . '/includes/toolbar.php';

    // Set profiler start time and memory usage and mark afterLoad in the profiler.
      JDEBUG ? $_PROFILER->setStart($startTime, $startMem)->mark('afterLoad') : null;

    // Instantiate the application.
      $app = JFactory::getApplication('administrator');

    // Run Joomla Installer
      $installer = JInstaller::getInstance();
      if( !$installer->update($com_installer_config['package']['dir']) ){
        $result['status'] = 'error';
        $result['message'] = 'Joomla Installer Error';
      }
      else {
        $result['status'] = 'success';
        $result['percent_complete'] = 100;
      }

    // Store Message
      $app->setUserState('com_installer.messages', $installer->message);
      $app->setUserState('com_installer.extension_messages', $installer->get('extension_message'));

  }

// Complete
  echo json_encode( $result );