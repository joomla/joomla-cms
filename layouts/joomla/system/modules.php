<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

/* @var $displayData [] */
/* @var $position string */
/* @var $content string */
/* @var $attribs array */
/* @var $document \Joomla\CMS\Document\HtmlDocument */

extract($displayData);

$renderer = $document->loadRenderer('module');
$buffer   = '';

$app          = Factory::getApplication();
$user         = Factory::getUser();
$frontediting = ($app->isClient('site') && $app->get('frontediting', 1) && !$user->guest);
$menusEditing = ($app->get('frontediting', 1) == 2) && $user->authorise('core.edit', 'com_menus');

foreach (ModuleHelper::getModules($position) as $mod)
{
	$moduleHtml = $renderer->render($mod, $attribs, $content);

	if ($frontediting && trim($moduleHtml) != '' && $user->authorise('module.edit.frontend', 'com_modules.module.' . $mod->id))
	{
		$displayData = array('moduleHtml' => &$moduleHtml, 'module' => $mod, 'position' => $position, 'menusediting' => $menusEditing);
		LayoutHelper::render('joomla.edit.frontediting_modules', $displayData);
	}

	$buffer .= $moduleHtml;
}

$app->triggerEvent('onAfterRenderModules', [&$buffer, &$attribs]);

echo $buffer;
