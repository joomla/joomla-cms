<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use Joomla\CMS\Language\Text;
use RuntimeException;

/**
 * Locates the appropriate template file for a view
 */
class ViewTemplateFinder
{
	/** @var  View  The view we are attached to */
	protected $view;

	/** @var  Container  The container of the view, for quick reference */
	protected $container;

	/** @var  array  The layout template extensions to look for */
	protected $extensions = ['.blade.php', '.php'];

	/** @var  string  Default layout's name (default: "default") */
	protected $defaultLayout = 'default';

	/** @var  string  Default subtemplate name (default: empty) */
	protected $defaultTpl = '';

	/** @var  bool  Should I only look in the specified view (true) or also the pluralised/singularised (false) */
	protected $strictView = true;

	/** @var  bool  Should I only look for the defined subtemplate or also no subtemplate? */
	protected $strictTpl = true;

	/** @var  bool  Should  Should I only look for this layout or also the default layout? */
	protected $strictLayout = true;

	/** @var  string  Which application side prefix should I use by default (site, admin, auto, any) */
	protected $sidePrefix = 'auto';

	/**
	 * Public constructor. The config array can contain the following keys
	 * extensions       array
	 * defaultLayout    string
	 * defaultTpl       string
	 * strictView       bool
	 * strictTpl        bool
	 * strictLayout     bool
	 * sidePrefix       string
	 * For the descriptions of each key please see the same-named property of this class
	 *
	 * @param   View   $view    The view we are attached to
	 * @param   array  $config  The configuration for this view template finder
	 */
	function __construct(View $view, array $config = [])
	{
		$this->view      = $view;
		$this->container = $view->getContainer();

		if (isset($config['extensions']))
		{
			if (!is_array($config['extensions']))
			{
				$config['extensions'] = trim($config['extensions']);
				$config['extensions'] = explode(',', $config['extensions']);
				$config['extensions'] = array_map(function ($x) {
					return trim($x);
				}, $config['extensions']);
			}

			$this->setExtensions($config['extensions']);
		}

		if (isset($config['defaultLayout']))
		{
			$this->setDefaultLayout($config['defaultLayout']);
		}

		if (isset($config['defaultTpl']))
		{
			$this->setDefaultTpl($config['defaultTpl']);
		}

		if (isset($config['strictView']))
		{
			$config['strictView'] = in_array($config['strictView'], [true, 'true', 'yes', 'on', 1]);

			$this->setStrictView($config['strictView']);
		}

		if (isset($config['strictTpl']))
		{
			$config['strictTpl'] = in_array($config['strictTpl'], [true, 'true', 'yes', 'on', 1]);

			$this->setStrictTpl($config['strictTpl']);
		}

		if (isset($config['strictLayout']))
		{
			$config['strictLayout'] = in_array($config['strictLayout'], [true, 'true', 'yes', 'on', 1]);

			$this->setStrictLayout($config['strictLayout']);
		}

		if (isset($config['sidePrefix']))
		{
			$this->setSidePrefix($config['sidePrefix']);
		}
	}

	/**
	 * Returns a list of template URIs for a specific component, view, template and sub-template. The $parameters array
	 * can have any of the following keys:
	 * component        string  The name of the component, e.g. com_something
	 * view             string  The name of the view
	 * layout           string  The name of the layout
	 * tpl              string  The name of the subtemplate
	 * strictView       bool    Should I only look in the specified view, or should I look in the
	 * pluralised/singularised view as well? strictLayout     bool    Should I only look for this layout, or also for
	 * the default layout? strictTpl        bool    Should I only look for this subtemplate or also for no subtemplate?
	 * sidePrefix       string  The application side prefix (site, admin, auto, any)
	 *
	 * @param   array  $parameters  See above
	 *
	 * @return  array
	 */
	public function getViewTemplateUris(array $parameters)
	{
		// Merge the default parameters with the parameters given
		$parameters = array_merge([
			'component'    => $this->container->componentName,
			'view'         => $this->view->getName(),
			'layout'       => $this->defaultLayout,
			'tpl'          => $this->defaultTpl,
			'strictView'   => $this->strictView,
			'strictLayout' => $this->strictLayout,
			'strictTpl'    => $this->strictTpl,
			'sidePrefix'   => $this->sidePrefix,
		], $parameters);

		$uris = [];

		$component    = $parameters['component'];
		$view         = $parameters['view'];
		$layout       = $parameters['layout'];
		$tpl          = $parameters['tpl'];
		$strictView   = $parameters['strictView'];
		$strictLayout = $parameters['strictLayout'];
		$strictTpl    = $parameters['strictTpl'];
		$sidePrefix   = $parameters['sidePrefix'];

		$basePath = $sidePrefix . ':' . $component . '/' . $view . '/';

		$uris[] = $basePath . $layout . ($tpl ? "_$tpl" : '');

		if (!$strictTpl)
		{
			$uris[] = $basePath . $layout;
		}

		if (!$strictLayout)
		{
			$uris[] = $basePath . 'default' . ($tpl ? "_$tpl" : '');

			if (!$strictTpl)
			{
				$uris[] = $basePath . 'default';
			}
		}

		if (!$strictView)
		{
			$parameters['view']       = $this->container->inflector->isSingular($view) ? $this->container->inflector->pluralize($view) : $this->container->inflector->singularize($view);
			$parameters['strictView'] = true;

			$extraUris = $this->getViewTemplateUris($parameters);
			$uris      = array_merge($uris, $extraUris);
			unset ($extraUris);
		}

		return array_unique($uris);
	}

	/**
	 * Parses a template URI in the form of admin:component/view/layout to an array listing the application section
	 * (admin, site), component, view and template referenced therein.
	 *
	 * @param   string  $uri  The template path to parse
	 *
	 * @return  array  A hash array with the parsed path parts. Keys: admin, component, view, template
	 */
	public function parseTemplateUri($uri = '')
	{
		$parts = [
			'admin'     => 0,
			'component' => $this->container->componentName,
			'view'      => $this->view->getName(),
			'template'  => 'default',
		];

		if (substr($uri, 0, 5) == 'auto:')
		{
			$replacement = $this->container->platform->isBackend() ? 'admin:' : 'site:';
			$uri         = $replacement . substr($uri, 5);
		}

		if (substr($uri, 0, 6) == 'admin:')
		{
			$parts['admin'] = 1;
			$uri            = substr($uri, 6);
		}
		elseif (substr($uri, 0, 5) == 'site:')
		{
			$uri = substr($uri, 5);
		}
		elseif (substr($uri, 0, 4) == 'any:')
		{
			$parts['admin'] = -1;
			$uri            = substr($uri, 4);
		}

		if (empty($uri))
		{
			return $parts;
		}

		$uriParts  = explode('/', $uri, 3);
		$partCount = count($uriParts);

		if ($partCount >= 1)
		{
			$parts['component'] = $uriParts[0];
		}

		if ($partCount >= 2)
		{
			$parts['view'] = $uriParts[1];
		}

		if ($partCount >= 3)
		{
			$parts['template'] = $uriParts[2];
		}

		return $parts;
	}

	/**
	 * Resolves a view template URI (e.g. any:com_foobar/Items/cheese) to an absolute filesystem path
	 * (e.g. /var/www/html/administrator/components/com_foobar/View/Items/tmpl/cheese.php)
	 *
	 * @param   string  $uri             The view template URI to parse
	 * @param   string  $layoutTemplate  The layout template override of the View class
	 * @param   array   $extraPaths      Any extra lookup paths where we'll be looking for this view template
	 * @param   bool    $noOverride      If true we will not load Joomla! template overrides
	 *
	 * @return  string
	 *
	 * @throws RuntimeException
	 */
	public function resolveUriToPath($uri, $layoutTemplate = '', array $extraPaths = [], $noOverride = false)
	{
		// Parse the URI into its parts
		$parts = $this->parseTemplateUri($uri);

		// Get some useful values
		$isAdmin        = $this->container->platform->isBackend() ? 1 : 0;
		$componentPaths = $this->container->platform->getComponentBaseDirs($parts['component']);
		$templatePath   = $this->container->platform->getTemplateOverridePath($parts['component']);

		// Get the lookup paths
		$paths = [];

		// If we are on the correct side of the application or we have an "any:" URI look for a template override
		if (!$noOverride && (($parts['admin'] == -1) || ($parts['admin'] == $isAdmin)))
		{
			$paths[] = $templatePath . '/' . $parts['view'];
		}

		// Add the requested side of the application
		$requestedAdmin = ($parts['admin'] == -1) ? $isAdmin : $parts['admin'];

		$paths[] = ($requestedAdmin ? $componentPaths['admin'] : $componentPaths['site']) . '/ViewTemplates/' . $parts['view'];
		$paths[] = ($requestedAdmin ? $componentPaths['admin'] : $componentPaths['site']) . '/View/' . $parts['view'] . '/tmpl';

		// Add the other side of the application for "any:" URIs
		if ($parts['admin'] == -1)
		{
			$paths[] = ($requestedAdmin ? $componentPaths['site'] : $componentPaths['admin']) . '/ViewTemplates/' . $parts['view'];
			$paths[] = ($requestedAdmin ? $componentPaths['site'] : $componentPaths['admin']) . '/View/' . $parts['view'] . '/tmpl';
		}

		// Add extra paths
		if (!empty($extraPaths))
		{
			$paths = array_merge($paths, $extraPaths);
		}

		// Remove duplicate paths
		$paths = array_unique($paths);

		// Look for a template layout override
		if (!empty($layoutTemplate) && ($layoutTemplate != '_') && ($layoutTemplate != $parts['template']))
		{
			$apath = array_shift($paths);
			array_unshift($paths, str_replace($parts['template'], $layoutTemplate, $apath));
		}

		// Get the Joomla! version template suffixes
		$jVersionSuffixes = array_merge($this->container->platform->getTemplateSuffixes(), ['']);

		// Get the renderer name suffixes
		$rendererNameSuffixes = [
			'.' . $this->container->renderer->getInformation()->name,
			'',
		];

		$filesystem = $this->container->filesystem;

		foreach ($this->extensions as $extension)
		{
			foreach ($jVersionSuffixes as $JVersionSuffix)
			{
				foreach ($rendererNameSuffixes as $rendererNameSuffix)
				{
					$filenameToFind = $parts['template'] . $JVersionSuffix . $rendererNameSuffix . $extension;

					$fileName = $filesystem->pathFind($paths, $filenameToFind);

					if ($fileName)
					{
						return $fileName;
					}
				}
			}
		}

		/**
		 * If no view template was found for the component fall back to FOF's core Blade templates -- located in
		 * <libdir>/ViewTemplates/<viewName>/<templateName> -- and their template overrides.
		 */
		$paths   = [];
		$paths[] = $this->container->platform->getTemplateOverridePath('lib_fof30') . '/' . $parts['view'];
		$paths[] = realpath(__DIR__ . '/..') . '/ViewTemplates/' . $parts['view'];

		foreach ($jVersionSuffixes as $JVersionSuffix)
		{
			foreach ($rendererNameSuffixes as $rendererNameSuffix)
			{
				$filenameToFind = $parts['template'] . $JVersionSuffix . $rendererNameSuffix . '.blade.php';

				$fileName = $filesystem->pathFind($paths, $filenameToFind);

				if (!empty($fileName))
				{
					return $fileName;
				}
			}
		}

		throw new RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $uri), 500);
	}

	/**
	 * Get the list of view template extensions
	 *
	 * @return  array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}

	/**
	 * Set the list of view template extensions
	 *
	 * @param   array  $extensions
	 *
	 * @return  void
	 */
	public function setExtensions(array $extensions)
	{
		$this->extensions = $extensions;
	}

	/**
	 * Add an extension to the list of view template extensions
	 *
	 * @param   string  $extension
	 *
	 * @return  void
	 */
	public function addExtension($extension)
	{
		if (empty($extension))
		{
			return;
		}

		if (substr($extension, 0, 1) != '.')
		{
			$extension = '.' . $extension;
		}

		if (!in_array($extension, $this->extensions))
		{
			$this->extensions[] = $extension;
		}
	}

	/**
	 * Remove an extension from the list of view template extensions
	 *
	 * @param   string  $extension
	 *
	 * @return  void
	 */
	public function removeExtension($extension)
	{
		if (empty($extension))
		{
			return;
		}

		if (substr($extension, 0, 1) != '.')
		{
			$extension = '.' . $extension;
		}

		if (!in_array($extension, $this->extensions))
		{
			return;
		}

		$pos = array_search($extension, $this->extensions);
		unset ($this->extensions[$pos]);
	}

	/**
	 * Returns the default layout name
	 *
	 * @return  string
	 */
	public function getDefaultLayout()
	{
		return $this->defaultLayout;
	}

	/**
	 * Sets the default layout name
	 *
	 * @param   string  $defaultLayout
	 *
	 * @return  void
	 */
	public function setDefaultLayout($defaultLayout)
	{
		$this->defaultLayout = $defaultLayout;
	}

	/**
	 * Returns the default subtemplate name
	 *
	 * @return  string
	 */
	public function getDefaultTpl()
	{
		return $this->defaultTpl;
	}

	/**
	 * Sets the default subtemplate name
	 *
	 * @param   string  $defaultTpl
	 */
	public function setDefaultTpl($defaultTpl)
	{
		$this->defaultTpl = $defaultTpl;
	}

	/**
	 * Returns the "strict view" flag. When the flag is false we will look for the view template in both the
	 * singularised and pluralised view. If it's true we will only look for the view template in the view
	 * specified in getViewTemplateUris.
	 *
	 * @return  boolean
	 */
	public function isStrictView()
	{
		return $this->strictView;
	}

	/**
	 * Sets the "strict view" flag. When the flag is false we will look for the view template in both the
	 * singularised and pluralised view. If it's true we will only look for the view template in the view
	 * specified in getViewTemplateUris.
	 *
	 * @param   boolean  $strictView
	 *
	 * @return  void
	 */
	public function setStrictView($strictView)
	{
		$this->strictView = $strictView;
	}

	/**
	 * Returns the "strict template" flag. When the flag is false we will look for a view template with or without the
	 * subtemplate defined in getViewTemplateUris. If it's true we will only look for the subtemplate specified.
	 *
	 * @return boolean
	 */
	public function isStrictTpl()
	{
		return $this->strictTpl;
	}

	/**
	 * Sets the "strict template" flag. When the flag is false we will look for a view template with or without the
	 * subtemplate defined in getViewTemplateUris. If it's true we will only look for the subtemplate specified.
	 *
	 * @param   boolean  $strictTpl
	 *
	 * @return  void
	 */
	public function setStrictTpl($strictTpl)
	{
		$this->strictTpl = $strictTpl;
	}

	/**
	 * Returns the "strict layout" flag. When the flag is false we will look for a view template with both the specified
	 * and the default template name in getViewTemplateUris. When true we will only look for the specified view
	 * template.
	 *
	 * @return  boolean
	 */
	public function isStrictLayout()
	{
		return $this->strictLayout;
	}

	/**
	 * Sets the "strict layout" flag. When the flag is false we will look for a view template with both the specified
	 * and the default template name in getViewTemplateUris. When true we will only look for the specified view
	 * template.
	 *
	 * @param   boolean  $strictLayout
	 *
	 * @return  void
	 */
	public function setStrictLayout($strictLayout)
	{
		$this->strictLayout = $strictLayout;
	}

	/**
	 * Returns the application side prefix which will be used by default in getViewTemplateUris. It can be one of:
	 * site     Public front-end
	 * admin    Administrator back-end
	 * auto     Automatically figure out if it should be site or admin
	 * any      First look in the current application side, then look on the other side of the application
	 *
	 * @return  string
	 */
	public function getSidePrefix()
	{
		return $this->sidePrefix;
	}

	/**
	 * Sets the application side prefix which will be used by default in getViewTemplateUris. It can be one of:
	 * site     Public front-end
	 * admin    Administrator back-end
	 * auto     Automatically figure out if it should be site or admin
	 * any      First look in the current application side, then look on the other side of the application
	 *
	 * @param   string  $sidePrefix
	 *
	 * @return  void
	 */
	public function setSidePrefix($sidePrefix)
	{
		$sidePrefix = strtolower($sidePrefix);
		$sidePrefix = trim($sidePrefix);

		if (!in_array($sidePrefix, ['site', 'admin', 'auto', 'any']))
		{
			$sidePrefix = 'auto';
		}

		$this->sidePrefix = $sidePrefix;
	}


}
