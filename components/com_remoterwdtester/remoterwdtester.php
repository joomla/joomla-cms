<?php
/**
 * @version     1.0.0
 * @package     com_remoterwdtester
 * @copyright   Copyright (C) Joostrap 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Philip Locke <fastnetwebdesign@gmail.com> - http://www.joostrap.com
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= JControllerLegacy::getInstance('Remoterwdtester');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
