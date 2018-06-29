<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$input   = Factory::getApplication()->input;
$context = Factory::getApplication()->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD');
$parts   = FieldsHelper::extract($context);

if ($input->get('view') === 'fields' && $input->get('layout') === 'modal')
{
	if (!Factory::getUser()->authorise('core.create', $parts[0])
		|| !Factory::getUser()->authorise('core.edit', $parts[0]))
	{
		Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

		return;
	}
}

$controller = BaseController::getInstance('Fields');
$controller->execute($input->get('task'));
$controller->redirect();
