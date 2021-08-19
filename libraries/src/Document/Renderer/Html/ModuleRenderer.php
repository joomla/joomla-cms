<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;

/**
 * HTML document renderer for a single module
 *
 * @since  3.5
 */
class ModuleRenderer extends DocumentRenderer
{
	/**
	 * Renders a module script and returns the results as a string
	 *
	 * @param   string  $module   The name of the module to render
	 * @param   array   $attribs  Associative array of values
	 * @param   string  $content  If present, module information from the buffer will be used
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.5
	 */
	public function render($module, $attribs = array(), $content = null)
	{
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
			// Check if a raw style is requested, which should render only a content, without chrome style
			$isRawStyle = !empty($attribs['style']) && $attribs['style'] === 'raw';

			// Default to itemid creating method and workarounds on
			$cacheparams = new \stdClass;
			$cacheparams->cachemode = $cachemode;
			$cacheparams->class = ModuleHelper::class;
			$cacheparams->method = 'renderModule';
			$cacheparams->methodparams = array($module, $attribs);
			$cacheparams->cachesuffix = $isRawStyle ? 'style-raw' : '';

			$result = ModuleHelper::moduleCache($module, $params, $cacheparams);

			// For a raw style set the content to the module, so it will not be rendered again next time, see ModuleHelper::renderRawModule
			// It need to be done here because the cache controller does not keep reference to the module object
			if ($isRawStyle)
			{
				$module->content = $result;
				$module->contentRendered = true;
			}

			return $result;
		}

		return ModuleHelper::renderModule($module, $attribs);
	}
}
