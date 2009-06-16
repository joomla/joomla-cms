<?php
/**
 * @version     $Id: categories.php 2009-05-15 10:43:09Z bembelimen $
 * @package     Joomla!.Framework
 * @subpackage  Components.Categories
 * @license     GNU/GPL, see http://www.gnu.org/copyleft/gpl.html and LICENSE.php
 *
 * Starting point of com_categories
 *
 * Joomla! is free software. you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Joomla! is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Joomla!; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */


// Ensure, that the file was included by Joomla!
defined('_JEXEC') or jexit();

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
