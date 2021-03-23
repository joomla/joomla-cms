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
use Joomla\Registry\Registry;

/* @var $displayData [] */
/* @var $module object */
/* @var $content string */
/* @var $attribs array */
extract($displayData);

if (!\is_object($module))
{
	$title = $attribs['title'] ?? null;

	$module = ModuleHelper::getModule($module, $title);

	if (!\is_object($module))
	{
		if (\is_null($content))
		{
			return '';
		}

		/**
		 * If module isn't found in the database but data has been pushed in the buffer
		 * we want to render it
		 */
		$tmp = $module;
		$module = new \stdClass;
		$module->params = null;
		$module->module = $tmp;
		$module->id = 0;
		$module->user = 0;
	}
}

// Set the module content
if (!\is_null($content))
{
	$module->content = $content;
}

// Get module parameters
$params = new Registry($module->params);

// Use parameters from template
if (isset($attribs['params']))
{
	$template_params = new Registry(html_entity_decode($attribs['params'], ENT_COMPAT, 'UTF-8'));
	$params->merge($template_params);
	$module = clone $module;
	$module->params = (string) $params;
}

// Set cachemode parameter or use JModuleHelper::moduleCache from within the module instead
$cachemode = $params->get('cachemode', 'static');

if ($params->get('cache', 0) == 1 && Factory::getApplication()->get('caching') >= 1 && $cachemode !== 'id' && $cachemode !== 'safeuri')
{
	// Default to itemid creating method and workarounds on
	$cacheparams = new \stdClass;
	$cacheparams->cachemode = $cachemode;
	$cacheparams->class = ModuleHelper::class;
	$cacheparams->method = 'renderModule';
	$cacheparams->methodparams = array($module, $attribs);

	echo ModuleHelper::ModuleCache($module, $params, $cacheparams);
}

echo ModuleHelper::renderModule($module, $attribs);
