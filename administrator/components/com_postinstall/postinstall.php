<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

<<<<<<< HEAD
defined('_JEXEC') or die();
=======
defined('_JEXEC') or die;
>>>>>>> refs/heads/projects-master

// Load the RAD layer
if (!defined('FOF_INCLUDED'))
{
<<<<<<< HEAD
	require_once JPATH_LIBRARIES.'/fof/include.php';
}

// Dispatch the component
FOFDispatcher::getTmpInstance('com_postinstall')->dispatch();
=======
	require_once JPATH_LIBRARIES . '/fof/include.php';
}

// Dispatch the component
FOFDispatcher::getTmpInstance('com_postinstall')->dispatch();
>>>>>>> refs/heads/projects-master
