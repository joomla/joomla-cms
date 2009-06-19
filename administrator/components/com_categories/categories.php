<?php
/**
 * @version     $Id$
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

// load the User object
$user = JFactory::getUser();

// Let's check, if the user is authorized to edit the categories
if (!$user->authorize('core.categories.manage')) :

    // load the application
    $app = JFactory::getApplication();
    // forbit access
    $app->redirect('index.php', JText::_('ALERTNOTAUTH'));

endif;

// include the controller

include_once(JPATH_COMPONENT.DS.'controller.php');

// let's load a specific controller
$controller = JRequest::getWord( 'controller' );

// is the specific controller requested?
if( $controller ) :

    // then set the path
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';

    // does the path exists?
    if (file_exists($path)) :

       // then include it
       include_once $path;

    // otherwise
    else :

        // use the standard controller
        $controller = '';

    endif;

endif;

$classname = 'CategoriesController'.$controller;
$controller	= new $classname();

// perform the requested task
$controller->execute( JRequest::getWord( 'task' ) );

// last but not least redirect, if set by the controller
$controller->redirect();

?>
