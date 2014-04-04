<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument head renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererHead extends JDocumentRenderer
{
	/**
	 * Renders an external CSS tag.
	 * 
	 * @param   JDocumentAsset  $asset  Asset object to render.
	 *
	 * @return  string  Rendered CSS tag.
	 * 
	 * @since   3.3
	 */
	protected function cssExternal(JDocumentAsset $asset)
	{
		$lnEnd = $this->_doc->_getLineEnd();
		$tab = $this->_doc->_getTab();
		$usecdn = isset($this->_doc->cdn) ? $this->_doc->cdn : false;

		// Assemble the attributes.
		$attributes = array();
		$attributes[] = 'rel="stylesheet"';
		$attributes[] = 'href="' . $asset->getUrl($usecdn) . '"';
		$attributes[] = 'type="text/css"';

		$media = $asset->getAttribute('media');
		if ($media != '')
		{
			$attributes[] = 'media="' . $media . '"';
		}

		// Construct the tag.
		$buffer = $tab . '<link ' . implode(' ', $attributes) . '/>' . $lnEnd;

		return $buffer;
	}

	/**
	 * Renders an internal CSS tag.
	 *
	 * @param   JDocumentAsset  $asset  Asset object to render.
	 *
	 * @return  string  Rendered CSS tag.
	 * 
	 * @since   3.3
	 */
	protected function cssInternal(JDocumentAsset $asset)
	{
		// Get line endings
		$lnEnd = $this->_doc->_getLineEnd();
		$tab = $this->_doc->_getTab();
		
		// Assemble the attributes.
		$attributes = array();
		$attributes[] = 'type="text/css"';

		$media = $asset->getAttribute('media');
		if ($media != '')
		{
			$attributes[] = 'media="' . $media . '"';
		}

		// Construct the tag.
		$buffer = $tab . '<style ' . implode(' ', $attributes) . '>' . $lnEnd;

		// This is for full XHTML support.
		if ($this->_doc->_mime != 'text/html')
		{
			$buffer .= $tab . $tab . '<![CDATA[' . $lnEnd;
		}

		$buffer .= $asset->getContent() . $lnEnd;

		// See above note
		if ($this->_doc->_mime != 'text/html')
		{
			$buffer .= $tab . $tab . ']]>' . $lnEnd;
		}
		$buffer .= $tab . '</style>' . $lnEnd;

		return $buffer;
	}

	/**
	 * Find an asset given its MD5 content signature.
	 * 
	 * @param   array   $assets  Array of JDocumentAsset objects.
	 * @param   string  $id      Asset content MD5.
	 * 
	 * @return  JDocumentAsset  Asset found or null if not found.
	 * 
	 * @since   3.3
	 */
	protected function findAssetByMd5(array $assets, $md5)
	{
		foreach ($assets as $asset)
		{
			if ($asset->getAttribute('md5') == $md5)
			{
				return $asset;
			}
		}
	}
	
	/**
	 * Generate script language declarations.
	 * 
	 * @return  string  JavaScript function to declare language strings.
	 * 
	 * @since   3.3
	 */
	protected function generateLanguageDeclarations()
	{
		$lnEnd = $this->_doc->_getLineEnd();
		$tab = $this->_doc->_getTab();
		$buffer = '';

		if (count(JText::script()))
		{
			$buffer .= $tab . $tab . '(function() {' . $lnEnd;
			$buffer .= $tab . $tab . $tab . 'var strings = ' . json_encode(JText::script()) . ';' . $lnEnd;
			$buffer .= $tab . $tab . $tab . 'if (typeof Joomla == \'undefined\') {' . $lnEnd;
			$buffer .= $tab . $tab . $tab . $tab . 'Joomla = {};' . $lnEnd;
			$buffer .= $tab . $tab . $tab . $tab . 'Joomla.JText = strings;' . $lnEnd;
			$buffer .= $tab . $tab . $tab . '}' . $lnEnd;
			$buffer .= $tab . $tab . $tab . 'else {' . $lnEnd;
			$buffer .= $tab . $tab . $tab . $tab . 'Joomla.JText.load(strings);' . $lnEnd;
			$buffer .= $tab . $tab . $tab . '}' . $lnEnd;
			$buffer .= $tab . $tab . '})();' . $lnEnd;
		}

		return $buffer;
	}

	/**
	 * Wrap content in a Microsoft IE conditional expression.
	 * 
	 * @see http://msdn.microsoft.com/en-us/library/ms537512.ASPX
	 * 
	 * @param   string  $content    Content to be wrapped.
	 * @param   string  $condition  Conditional expression.
	 * 
	 * @return  string  Wrapped content.
	 * 
	 * @since   3.3
	 */
	protected function ieCondition($content, $condition)
	{
		$lnEnd = $this->_doc->_getLineEnd();

		$buffer = '<!--[if ' . $condition . ']>' . $lnEnd;
		$buffer .= $content;
		$buffer .= '<![endif]-->' . $lnEnd;

		return $buffer;
	}

	/**
	 * Import custom head tags.
	 * 
	 * @return  string  Custom head string.
	 * 
	 * @since   3.3
	 */
	protected function importCustom()
	{
		$lnEnd = $this->_doc->_getLineEnd();
		$tab = $this->_doc->_getTab();
		$buffer = '';

		foreach ($this->_doc->_custom as $custom)
		{
			$buffer .= $tab . $custom . $lnEnd;
		}

		return $buffer;
	}

	/**
	 * Import all old-style external CSS stylesheets.
	 * 
	 * @param   string  $type  Asset type (either 'css' or 'javascript').
	 * 
	 * @since   3.3
	 */
	protected function importExternal($assetType)
	{
		// Do we have any external scripts or stylesheets to import?
		if (!isset($this->_doc->media_assets[$assetType]['external']))
		{
			return;
		}

		// Import old-style external assets.
		foreach ($this->_doc->media_assets[$assetType]['external'] as $key => $value)
		{
			// Find existing asset object if it exists.
			$asset = $this->_doc->findAssetById(basename($value['url']));
			
			// If it doesn't exist, create it.
			if (is_null($asset))
			{
				$asset = new JDocumentAsset($assetType, basename($value['url']));
				$asset->setUrl($value['url']);

				$this->_doc->addAsset($asset);
			}

			// Update URL.
			if ($asset->getUrl() == '')
			{
				$asset->setUrl($value['url']);
			}

			// Copy the defer flag state.
			isset($value['defer']) ? $asset->setAttribute('defer', (boolean) $value['defer']) : null;
						
			// Copy the async flag state.
			isset($value['async']) ? $asset->setAttribute('async', (boolean) $value['async']) : null;
		}
	}
			
	/**
	 * Import all old-style internal scripts and stylesheets.
	 * 
	 * @param   string  $type  Asset type (either 'css' or 'javascript').
	 * 
	 * @since   3.3
	 */
	protected function importInternal($assetType)
	{
		// Do we have any internal scripts or stylesheets to import?
		if (!isset($this->_doc->media_assets[$assetType]['internal']))
		{
			return;
		}

		// Import old-style internal CSS assets.
		foreach ($this->_doc->media_assets[$assetType]['internal'] as $content)
		{
			// Find existing asset object if it exists.
			$asset = $this->findAssetByMd5($this->_doc->getAssets(), md5($content));
			
			// If it doesn't exist, create it; otherwise update it.
			if (is_null($asset))
			{
				$asset = new JDocumentAsset($assetType);
				$asset
					->setContent($content)
					->setId()
					;

				$this->_doc->addAsset($asset);
			}
			else
			{
				$asset->setContent($content);
			}
		}
	}
	
	/**
	 * Import all old-style metadata.
	 * 
	 * @return  string  Metadata tag string.
	 * 
	 * @since   3.3
	 */
	protected function importMetadata()
	{
		$document = $this->_doc;
		
		// Convert the tagids to titles
		if (isset($document->_metaTags['standard']['tags']))
		{
			$tagsHelper = new JHelperTags;
			$document->_metaTags['standard']['tags'] = implode(', ', $tagsHelper->getTagNames($document->_metaTags['standard']['tags']));
		}

		// Trigger the onBeforeCompileHead event
		JFactory::getApplication()->triggerEvent('onBeforeCompileHead');

		// Get line endings
		$lnEnd = $document->_getLineEnd();
		$tab = $document->_getTab();
		$tagEnd = ' />';
		$buffer = '';

		// Generate charset when using HTML5 (should happen first)
		if ($document->isHtml5())
		{
			$buffer .= $tab . '<meta charset="' . $document->getCharset() . '" />' . $lnEnd;
		}

		// Generate base tag (need to happen early)
		$base = $document->getBase();
		if (!empty($base))
		{
			$buffer .= $tab . '<base href="' . $document->getBase() . '" />' . $lnEnd;
		}

		// Generate META tags (needs to happen as early as possible in the head)
		foreach ($document->_metaTags as $type => $tag)
		{
			foreach ($tag as $name => $content)
			{
				if ($type == 'http-equiv' && !($document->isHtml5() && $name == 'content-type'))
				{
					$buffer .= $tab . '<meta http-equiv="' . $name . '" content="' . htmlspecialchars($content) . '" />' . $lnEnd;
				}
				elseif ($type == 'standard' && !empty($content))
				{
					$buffer .= $tab . '<meta name="' . $name . '" content="' . htmlspecialchars($content) . '" />' . $lnEnd;
				}
			}
		}

		// Don't add empty descriptions
		$documentDescription = $document->getDescription();
		if ($documentDescription)
		{
			$buffer .= $tab . '<meta name="description" content="' . htmlspecialchars($documentDescription) . '" />' . $lnEnd;
		}

		// Don't add empty generators
		$generator = $document->getGenerator();
		if ($generator)
		{
			$buffer .= $tab . '<meta name="generator" content="' . htmlspecialchars($generator) . '" />' . $lnEnd;
		}

		$buffer .= $tab . '<title>' . htmlspecialchars($document->getTitle(), ENT_COMPAT, 'UTF-8') . '</title>' . $lnEnd;

		// Generate link declarations
		foreach ($document->_links as $link => $linkAtrr)
		{
			$buffer .= $tab . '<link href="' . $link . '" ' . $linkAtrr['relType'] . '="' . $linkAtrr['relation'] . '"';
			if ($temp = JArrayHelper::toString($linkAtrr['attribs']))
			{
				$buffer .= ' ' . $temp;
			}
			$buffer .= ' />' . $lnEnd;
		}
		
		return $buffer;
	}
	
	/**
	 * Renders an external JavaScript tag.
	 * 
	 * @param   JDocumentAsset  $asset  Asset object to render.
	 *
	 * @return  string  Rendered JavaScript tag.
	 * 
	 * @since   3.3
	 */
	protected function javascriptExternal(JDocumentAsset $asset)
	{
		// Get line endings
		$lnEnd = $this->_doc->_getLineEnd();
		$tab = $this->_doc->_getTab();
		$usecdn = isset($this->_doc->cdn) ? $this->_doc->cdn : false;
		
		// Assemble the attributes.
		$attributes = array();
		$attributes[] = 'src="' . $asset->getUrl($usecdn) . '"';
		$attributes[] = 'type="text/javascript"';

		if ($asset->getAttribute('defer'))
		{
			$attributes[] = 'defer="defer"';
		}

		if ($asset->getAttribute('async'))
		{
			$attributes[] = 'async="async"';
		}

		// Construct the tag.
		$buffer = $tab . '<script ' . implode(' ', $attributes) . '></script>' . $lnEnd;

		return $buffer;
	}

	/**
	 * Renders an internal JavaScript tag.
	 *
	 * @param   JDocumentAsset  $asset  Asset object to render.
	 *
	 * @return  string  Rendered JavaScript tag.
	 * 
	 * @since   3.3
	 */
	protected function javascriptInternal(JDocumentAsset $asset)
	{
		// Get the content.
		$content = $asset->getContent();
		
		// Check we have something to ouput.
		if ($content == '')
		{
			return '';
		}
		
		// Get line endings
		$lnEnd = $this->_doc->_getLineEnd();
		$tab = $this->_doc->_getTab();
		
		// Construct the tag.
		$buffer = $tab . '<script type="text/javascript">' . $lnEnd;

		// This is for full XHTML support.
		if ($this->_doc->_mime != 'text/html')
		{
			$buffer .= $tab . $tab . '<![CDATA[' . $lnEnd;
		}

		$buffer .= $asset->getContent() . $lnEnd;

		// See above note
		if ($this->_doc->_mime != 'text/html')
		{
			$buffer .= $tab . $tab . ']]>' . $lnEnd;
		}
		$buffer .= $tab . '</script>' . $lnEnd;

		return $buffer;
	}

	/**
	 * Merge internal scripts and stylesheets where possible.
	 * 
	 * If internal assets of the same type are rendered adjacent to one another
	 * then they can be merged into one asset rather than being wrapped individually.
	 * 
	 * @param   array   $assets  Array of JDocumentAsset objects.
	 * @param   boolean $usecdn  True if CDN URLs should be used where available.
	 * 
	 * @since   3.3
	 */
	protected function mergeInternalAssets(array $assets, $usecdn = false)
	{
		$mergeable = null;
		foreach ($assets as $index => $asset)
		{
			if ($asset->getUrl($usecdn) == '')
			{
				if (is_null($mergeable))
				{
					// This asset is potentially mergeable.
					$mergeable = $index;
				}
				else
				{
					// Check that this asset and the previous one are of the same type.
					if ($asset->getType() == $assets[$mergeable]->getType())
					{
						// This asset and the previous one can be merged.
						$assets[$mergeable]->setContent($assets[$mergeable]->getContent() . "\n" . $asset->getContent());

						// Effectively delete the current asset.
						$asset->setType('deleted');
					}
				}
			}
			else
			{
				// Reset to not mergeable.
				$mergeable = null;
			}
		}
	}

	/**
	 * Move an asset into the head.
	 * Recursively moves its dependencies to the head too.
	 * 
	 * @param   JDocumentAsset  $asset  Asset to be moved to the head.
	 * 
	 * @since   3.3
	 */
	protected function moveAssetToHead(JDocumentAsset $asset)
	{
		// Set flag to indicate this asset must be in the head.
		$asset->setAttribute('head', true);
		
		// Check its dependencies and move those too.
		foreach ($asset->getDependencies() as $dependency)
		{
			$this->moveAssetToHead($dependency);
		}
	}

	/**
	 * Renders the document head and returns the results as a string
	 *
	 * @param   string  $name     Name argument from jdoc:include.
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  Not used.
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 * 
	 * @note    Unused arguments are retained to preserve backward compatibility.
	 */
	public function render($name, $params = array(), $content = null)
	{
		// Initialise flags.
		$this->_doc->cdn = $this->_doc->merge = $this->_doc->debug = false;

		// If options have been specified, process them.
		$options = isset($params['options']) ? $options = explode(',', $params['options']) : array();
		foreach ($options as $option)
		{
			// Set flag in document so we can access in bottom too.
			if (in_array($option, array('cdn', 'merge', 'debug')))
			{
				$this->_doc->$option = true;
			}
		}

		// Import meta data.
		$buffer = $this->importMetadata();

		// Import all old-style stylesheet assets.
		$this->importExternal('css');
		$this->importInternal('css');

		// Import all old-style JavaScript assets.
		$this->importExternal('javascript');
		$this->importInternal('javascript');
		
		// Apply fix-ups to all assets prior to sorting.
		foreach ($this->_doc->getAssets() as $asset)
		{
			// If an asset must go in the head, then all
			// its dependencies must go in the head too.
			// All CSS must go in the head.
			if ($asset->getAttribute('head', false) || $asset->getType() == 'css')
			{
				$this->moveAssetToHead($asset);
			}
		}

		// Add script language declarations.
		$scriptLangDeclarations = $this->generateLanguageDeclarations();
		if ($scriptLangDeclarations != '')
		{
			$scriptLang = new JDocumentAsset('javascript');
			$scriptLang->setContent($scriptLangDeclarations);
			
			// Must come after all other JavaScript for backwards compatibility.
			// If the assumption that it must load last is wrong,
			// then this chunk of code can be removed.
			foreach ($this->_doc->getAssets() as $asset)
			{
				if ($asset->getType() == 'javascript')
				{
					// If we find a script that is not loading in the head...
					if (!$asset->getAttribute('head', true))
					{
						// Then move the language declarations to the tail.
						$scriptLang->setAttribute('head', false);
					}
					
					// Add this script as a dependency (so we load after it).
					$scriptLang->addDependency($asset);
				}
			}
			
			// Add language declarations asset to the document.
			$this->_doc->addAsset($scriptLang);
		}

		// Sort the asset dependency graph into topological order.
		$assets = $this->sort($this->_doc->getAssets());
		$this->_doc->setAssets($assets);
		
		// Apply optimisations.
		// Merge internal scripts and stylesheets where possible.
		if ($this->_doc->merge)
		{
			$this->mergeInternalAssets($assets, $this->_doc->cdn);
		}
		
		// Render the assets.
		$buffer .= $this->renderAssets($assets, true, 'javascript');
		$buffer .= $this->renderAssets($assets, true, 'css');

		// Render custom head tags.
		$buffer .= $this->importCustom();

		return $buffer;
	}

	/**
	 * Renders all assets, subject to some filters.
	 * 
	 * @param   array    $assets  Array of JDocumentAsset objects to be rendered.
	 * @param   boolean  $head    True if only the head assets are to be rendered.
	 * @param   string   $type    Optional filter by asset type.
	 *
	 * @return  string  Rendered JavaScript tags.
	 * 
	 * @since   3.3
	 */
	protected function renderAssets(array $assets, $head, $type = '')
	{
		// Start new output buffer.
		ob_start();

		// Render all the assets.
		foreach ($assets as $asset)
		{
			// If the asset has the "drop" flag set, then skip it.
			if ($asset->getAttribute('drop'))
			{
				continue;
			}
			
			// If the asset is head-only then render in the head; otherwise render in the tail.
			if ($asset->getAttribute('head', true) == $head)
			{
				// Filter by type if required.
				$assetType = $asset->getType();
				if ($type == '' || $type == $assetType)
				{
					// Construct the asset type renderer method name.
					$usecdn = isset($this->_doc->cdn) ? $this->_doc->cdn : false;
					$assetMethod = $assetType . ($asset->getUrl($usecdn) == '' ? 'Internal' : 'External');
	
					// If the method exists, call it.
					if (method_exists($this, $assetMethod))
					{
						// If the asset has an MS IE condition, surround with comment.
						$ieCondition = $asset->getAttribute('ie');
						if ($ieCondition != '')
						{
							echo $this->ieCondition($this->$assertMethod($asset), $ieCondition);
						}
						else
						{
							echo $this->$assetMethod($asset);
						}
					}
				}
			}
		}

		// Get the buffer contents.
		$buffer = ob_get_contents();
		ob_end_clean();

		return $buffer;
	}

	/**
	 * Serialise the dependency graph (topological sort).
	 * 
	 * The assets should form a directed acyclic graph
	 * which we now sort into topological order.
	 * 
	 * @param   array  $assets  Array of JDocumentAsset objects to be sorted.
	 * 
	 * @return  array  Array of JDocumentAsset objects in topological order.
	 * 
	 * @throws  RuntimeException if a circular dependency is detected.
	 * 
	 * @since   3.3
	 */
	 protected function sort(array $assets)
	 {
		// Seed the queue with all assets that have no dependencies.
		$queue = array();
		foreach ($assets as $asset)
		{
			$dependencies = $asset->getDependencies();
			if (empty($dependencies))
			{
				$queue[] = $asset;
			}
		}
		
		// Serialise the dependency graph (topological sort).
		$output = array();
		while (!empty($queue))
		{
			// Get the next item from the queue.
			$current = array_shift($queue);

			// Add the item to the serialised array.
			$output[] = $current;

			// Look through all the assets.
			foreach ($assets as $asset)
			{
				// Look through all the dependencies of this asset.
				$dependencies = $asset->getDependencies();
				foreach ($dependencies as $dependency)
				{
					// If the asset depends on our current asset then
					// add it to the queue and remove the dependency.
					if ($dependency->getId() == $current->getId())
					{
						$remaining = $asset->removeDependency($current);
						if (empty($remaining))
						{
							$queue[] = $asset;
						}
					}
				}
			}
		}
		
		// Check that we have accounted for all the assets.
		// If there are any left then we have a problem!
		if (count($assets) != count($output))
		{
			throw new RuntimeException('Unresolved or circular dependency detected in asset dependencies');
		}
		
		return $output;
	 }

}
