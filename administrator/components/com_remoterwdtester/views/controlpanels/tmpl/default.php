<?php
/**
 * @version     1.0.0
 * @package     com_remoterwdtester
 * @copyright   Copyright (C) Joostrap 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Philip Locke <fastnetwebdesign@gmail.com> - http://www.joostrap.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_remoterwdtester/assets/css/remoterwdtester.css');

$user	= JFactory::getUser();
$userId	= $user->get('id');


?>

<iframe src="<?php echo JURI::root(); ?>media/rwdtester/remote/control" width="100%" height="400px"></iframe>

     

		
