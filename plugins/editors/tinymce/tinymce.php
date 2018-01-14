<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Event\Event;

/**
 * TinyMCE Editor Plugin
 *
 * @since  1.5
 */
class PlgEditorTinymce extends JPlugin
{
	/**
	 * Base path for editor files
	 *
	 * @since  3.5
	 */
	protected $_basePath = 'media/vendor/tinymce';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the application object
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app = null;

	/**
	 * Initialises the Editor.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onInit()
	{
		JHtml::_('behavior.core');
		JHtml::_('script', $this->_basePath . '/tinymce.min.js', array('version' => 'auto'));
		JHtml::_('script', 'editors/tinymce/tinymce.min.js', array('version' => 'auto', 'relative' => true));
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The name of the editor area.
	 * @param   string   $content  The content of the field.
	 * @param   string   $width    The width of the editor area.
	 * @param   string   $height   The height of the editor area.
	 * @param   int      $col      The number of columns for the editor area.
	 * @param   int      $row      The number of rows for the editor area.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea. If not supplied the name is used.
	 * @param   string   $asset    The object asset
	 * @param   object   $author   The author.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string
	 */
	public function onDisplay(
		$name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$app = JFactory::getApplication();

		if (empty($id))
		{
			$id = $name;
		}

		$id            = preg_replace('/(\s|[^A-Za-z0-9_])+/', '_', $id);
		$nameGroup     = explode('[', preg_replace('/\[\]|\]/', '', $name));
		$fieldName     = end($nameGroup);
		$scriptOptions = array();
		$externalPlugins = array();

		// Check for existing options
		$doc     = JFactory::getDocument();
		$options = $doc->getScriptOptions('plg_editor_tinymce');

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width))
		{
			$width .= 'px';
		}

		if (is_numeric($height))
		{
			$height .= 'px';
		}

		// Data object for the layout
		$textarea = new stdClass;
		$textarea->name    = $name;
		$textarea->id      = $id;
		$textarea->class   = 'mce_editable joomla-editor-tinymce';
		$textarea->cols    = $col;
		$textarea->rows    = $row;
		$textarea->width   = $width;
		$textarea->height  = $height;
		$textarea->content = $content;

		// Set editor to readonly mode
		$textarea->readonly = !empty($params['readonly']);

		// Render Editor markup
		$editor = '<div class="js-editor-tinymce">';
		$editor .= JLayoutHelper::render('joomla.tinymce.textarea', $textarea);
		$editor .= $this->_toogleButton($id);
		$editor .= '</div>';

		// Prepare the instance specific options, actually the ext-buttons
		if (empty($options['tinyMCE'][$fieldName]['joomlaExtButtons']))
		{
			$btns = $this->tinyButtons($id, $buttons);

			if (!empty($btns['names']))
			{
				JHtml::_('script', 'editors/tinymce/tiny-close.min.js', array('version' => 'auto', 'relative' => true), array('defer' => 'defer'));
			}

			// Set editor to readonly mode
			if (!empty($params['readonly']))
			{
				$options['tinyMCE'][$fieldName]['readonly'] = 1;
			}

			$options['tinyMCE'][$fieldName]['joomlaMergeDefaults'] = true;
			$options['tinyMCE'][$fieldName]['joomlaExtButtons']    = $btns;

			$doc->addScriptOptions('plg_editor_tinymce', $options, false);
		}

		// Setup Default (common) options for the Editor script

		// Check whether we already have them
		if (!empty($options['tinyMCE']['default']))
		{
			return $editor;
		}

		$user     = JFactory::getUser();
		$language = JFactory::getLanguage();
		$theme    = 'modern';
		$ugroups  = array_combine($user->getAuthorisedGroups(), $user->getAuthorisedGroups());

		// Prepare the parameters
		$levelParams      = new Joomla\Registry\Registry;
		$extraOptions     = new stdClass;
		$toolbarParams    = new stdClass;
		$extraOptionsAll  = $this->params->get('configuration.setoptions', array());
		$toolbarParamsAll = $this->params->get('configuration.toolbars', array());

		// Get configuration depend from User group
		foreach ($extraOptionsAll as $set => $val)
		{
			$val->access = empty($val->access) ? array() : $val->access;

			// Check whether User in one of allowed group
			foreach ($val->access as $group)
			{
				if (isset($ugroups[$group]))
				{
					$extraOptions  = $val;
					$toolbarParams = $toolbarParamsAll->$set;
				}
			}
		}

		// Merge the params
		$levelParams->loadObject($toolbarParams);
		$levelParams->loadObject($extraOptions);

		// List the skins
		$skindirs = glob(JPATH_ROOT . '/media/vendor/tinymce/skins' . '/*', GLOB_ONLYDIR);

		// Set the selected skin
		$skin = 'lightgray';
		$side = $app->isClient('administrator') ? 'skin_admin' : 'skin';

		if ((int) $levelParams->get($side, 0) < count($skindirs))
		{
			$skin = basename($skindirs[(int) $levelParams->get($side, 0)]);
		}

		$langMode   = $levelParams->get('lang_mode', 1);
		$langPrefix = $levelParams->get('lang_code', 'en');

		if ($langMode)
		{
			if (file_exists(JPATH_ROOT . '/media/vendor/tinymce/langs/' . $language->getTag() . '.js'))
			{
				$langPrefix = $language->getTag();
			}
			elseif (file_exists(JPATH_ROOT . '/media/vendor/tinymce/langs/' . substr($language->getTag(), 0, strpos($language->getTag(), '-')) . '.js'))
			{
				$langPrefix = substr($language->getTag(), 0, strpos($language->getTag(), '-'));
			}
			else
			{
				$langPrefix = 'en';
			}
		}

		$text_direction = 'ltr';

		if ($language->isRtl())
		{
			$text_direction = 'rtl';
		}

		$use_content_css    = $levelParams->get('content_css', 1);
		$content_css_custom = $levelParams->get('content_css_custom', '');

		/*
		 * Lets get the default template for the site application
		 */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('template')
			->from('#__template_styles')
			->where('client_id=0 AND home=' . $db->quote('1'));

		$db->setQuery($query);

		try
		{
			$template = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return '';
		}

		$content_css    = null;
		$templates_path = JPATH_SITE . '/templates';

		// Loading of css file for 'styles' dropdown
		if ($content_css_custom)
		{
			// If URL, just pass it to $content_css
			if (strpos($content_css_custom, 'http') !== false)
			{
				$content_css = $content_css_custom;
			}

			// If it is not a URL, assume it is a file name in the current template folder
			else
			{
				$content_css = JUri::root(true) . '/templates/' . $template . '/css/' . $content_css_custom;

				// Issue warning notice if the file is not found (but pass name to $content_css anyway to avoid TinyMCE error
				if (!file_exists($templates_path . '/' . $template . '/css/' . $content_css_custom))
				{
					$msg = sprintf(JText::_('PLG_TINY_ERR_CUSTOMCSSFILENOTPRESENT'), $content_css_custom);
					JLog::add($msg, JLog::WARNING, 'jerror');
				}
			}
		}
		else
		{
			// Process when use_content_css is Yes and no custom file given
			if ($use_content_css)
			{
				// First check templates folder for default template
				// if no editor.css file in templates folder, check system template folder
				if (!file_exists($templates_path . '/' . $template . '/css/editor.css'))
				{
					// If no editor.css file in system folder, show alert
					if (!file_exists($templates_path . '/system/css/editor.css'))
					{
						JLog::add(JText::_('PLG_TINY_ERR_EDITORCSSFILENOTPRESENT'), JLog::WARNING, 'jerror');
					}
					else
					{
						$content_css = JUri::root(true) . '/templates/system/css/editor.css';
					}
				}
				else
				{
					$content_css = JUri::root(true) . '/templates/' . $template . '/css/editor.css';
				}
			}
		}

		$ignore_filter = false;

		// Text filtering
		if ($levelParams->get('use_config_textfilters', 0))
		{
			// Use filters from com_config
			$filter = static::getGlobalFilters();

			$ignore_filter = $filter === false;

			$tagBlacklist  = !empty($filter->tagBlacklist) ? $filter->tagBlacklist : array();
			$attrBlacklist = !empty($filter->attrBlacklist) ? $filter->attrBlacklist : array();
			$tagArray      = !empty($filter->tagArray) ? $filter->tagArray : array();
			$attrArray     = !empty($filter->attrArray) ? $filter->attrArray : array();

			$invalid_elements  = implode(',', array_merge($tagBlacklist, $attrBlacklist, $tagArray, $attrArray));

			// Valid elements are all whitelist entries in com_config, which are now missing in the tagBlacklist
			$default_filter = JFilterInput::getInstance();
			$valid_elements = implode(',', array_diff($default_filter->tagBlacklist, $tagBlacklist));

			$extended_elements = '';
		}
		else
		{
			// Use filters from TinyMCE params
			$invalid_elements  = trim($levelParams->get('invalid_elements', 'script,applet,iframe'));
			$extended_elements = trim($levelParams->get('extended_elements', ''));
			$valid_elements    = trim($levelParams->get('valid_elements', ''));
		}

		$html_height = $this->params->get('html_height', '550');
		$html_width  = $this->params->get('html_width', '');

		if ($html_width == 750)
		{
			$html_width = '';
		}

		// The param is true for vertical resizing only, false or both
		$resizing          = (bool) $levelParams->get('resizing', true);
		$resize_horizontal = (bool) $levelParams->get('resize_horizontal', true);

		if ($resizing && $resize_horizontal)
		{
			$resizing = 'both';
		}

		// Set of always available plugins
		$plugins  = array(
			'autolink',
			'lists',
			'save',
			'colorpicker',
			'importcss',
		);

		// Allowed elements
		$elements = array(
			'hr[id|title|alt|class|width|size|noshade]',
		);

		if ($extended_elements)
		{
			$elements = array_merge($elements, explode(',', $extended_elements));
		}

		// Prepare the toolbar/menubar
		$knownButtons = static::getKnownButtons();

		// Check if there no value at all
		if (!$levelParams->get('menu') && !$levelParams->get('toolbar1') && !$levelParams->get('toolbar2'))
		{
			// Get from preset
			$presets = static::getToolbarPreset();

			/*
			 * Predefine group as:
			 * Set 0: for Administrator, Editor, Super Users (4,7,8)
			 * Set 1: for Registered, Manager (2,6), all else are public
			 */
			switch (true)
			{
				case isset($ugroups[4]) || isset($ugroups[7]) || isset($ugroups[8]):
					$preset = $presets['advanced'];
					break;

				case isset($ugroups[2]) || isset($ugroups[6]):
					$preset = $presets['medium'];
					break;

				default:
					$preset = $presets['simple'];
			}

			$levelParams->loadArray($preset);
		}

		$menubar         = (array) $levelParams->get('menu', array());
		$toolbar1        = (array) $levelParams->get('toolbar1', array());
		$toolbar2        = (array) $levelParams->get('toolbar2', array());

		// Make an easy way to check which button is enabled
		$allButtons = array_merge($toolbar1, $toolbar2);
		$allButtons = array_combine($allButtons, $allButtons);

		// Check for button-specific plugins
		foreach ($allButtons as $btnName)
		{
			if (!empty($knownButtons[$btnName]['plugin']))
			{
				$plugins[] = $knownButtons[$btnName]['plugin'];
			}
		}

		// Template
		$templates = array();

		if (!empty($allButtons['template']))
		{
			foreach (glob(JPATH_ROOT . '/media/vendor/tinymce/templates/*.html') as $filename)
			{
				$filename = basename($filename, '.html');

				if ($filename !== 'index')
				{
					$lang        = JFactory::getLanguage();
					$title       = $filename;
					$description = ' ';

					if ($lang->hasKey('PLG_TINY_TEMPLATE_' . strtoupper($filename) . '_TITLE'))
					{
						$title = JText::_('PLG_TINY_TEMPLATE_' . strtoupper($filename) . '_TITLE');
					}

					if ($lang->hasKey('PLG_TINY_TEMPLATE_' . strtoupper($filename) . '_DESC'))
					{
						$description = JText::_('PLG_TINY_TEMPLATE_' . strtoupper($filename) . '_DESC');
					}

					$templates[] = array(
						'title' => $title,
						'description' => $description,
						'url' => JUri::root(true) . '/media/vendor/tinymce/templates/' . $filename . '.html',
					);
				}
			}
		}

		// Check for extra plugins, from the setoptions form
		foreach (array('wordcount' => 1, 'advlist' => 1, 'autosave' => 1, 'contextmenu' => 1) as $pName => $def)
		{
			if ($levelParams->get($pName, $def))
			{
				$plugins[] = $pName;
			}
		}

		// Drag and drop Images
		$allowImgPaste = false;
		$dragdrop      = $levelParams->get('drag_drop', 1);

		if ($dragdrop && $user->authorise('core.create', 'com_media'))
		{
			$externalPlugins['jdragndrop'] = JUri::root() . 'media/editors/tinymce/js/plugins/dragdrop/plugin.min.js';

			$allowImgPaste = true;
			$isSubDir      = '';
			$session       = JFactory::getSession();
			$uploadUrl     = JUri::base() . 'index.php?option=com_media&task=file.upload&tmpl=component&'
				. $session->getName() . '=' . $session->getId()
				. '&' . JSession::getFormToken() . '=1'
				. '&asset=image&format=json';

			if ($app->isClient('site'))
			{
				$uploadUrl = htmlentities($uploadUrl, null, 'UTF-8', null);
			}

			// Is Joomla installed in subdirectory
			if (JUri::root(true) !== '/')
			{
				$isSubDir = JUri::root(true);
			}

			// Get specific path
			$tempPath = $levelParams->get('path', '');

			if (!empty($tempPath))
			{
				// Remove the root images path
				$tempPath = str_replace(JComponentHelper::getParams('com_media')->get('image_path') . '/', '', $tempPath);
			}

			JText::script('PLG_TINY_ERR_UNSUPPORTEDBROWSER');

			$scriptOptions['setCustomDir']    = $isSubDir;
			$scriptOptions['mediaUploadPath'] = $tempPath;
			$scriptOptions['uploadUri']       = $uploadUrl;
		}

		// User custom plugins and buttons
		$custom_plugin = trim($levelParams->get('custom_plugin', ''));
		$custom_button = trim($levelParams->get('custom_button', ''));

		if ($custom_plugin)
		{
			$separator = strpos($custom_plugin, ',') !== false ? ',' : ' ';
			$plugins   = array_merge($plugins, explode($separator, $custom_plugin));
		}

		if ($custom_button)
		{
			$separator = strpos($custom_button, ',') !== false ? ',' : ' ';
			$toolbar2  = array_merge($toolbar2, explode($separator, $custom_button));
			$toolbar2  = array_merge($toolbar2, $btns['native']);
		}

		$toolbar2  = array_merge($toolbar2, $btns['native']);

		// Merge the custom plugins paths
		$externalPlugins  = array_merge($externalPlugins, $btns['paths']);

		// Build the final options set
		$scriptOptions = array_merge(
			$scriptOptions,
			array(
				'suffix'  => '.min',
				'baseURL' => JUri::root(true) . '/media/vendor/tinymce',
				'directionality' => $text_direction,
				'language' => $langPrefix,
				'autosave_restore_when_empty' => false,
				'skin'   => $skin,
				'theme'  => $theme,
				'schema' => 'html5',

				// Toolbars
				'menubar'  => empty($menubar)  ? false : implode(' ', array_unique($menubar)),
				'toolbar1' => empty($toolbar1) ? null  : implode(' ', $toolbar1),
				'toolbar2' => empty($toolbar2) ? null  : implode(' ', $toolbar2),

				'plugins'  => implode(',', array_unique($plugins)),

				// Cleanup/Output
				'inline_styles'    => true,
				'gecko_spellcheck' => true,
				'entity_encoding'  => $levelParams->get('entity_encoding', 'raw'),
				'verify_html'      => !$ignore_filter,

				'valid_elements'          => $valid_elements,
				'extended_valid_elements' => implode(',', $elements),
				'invalid_elements'        => $invalid_elements,

				// URL
				'relative_urls'      => (bool) $levelParams->get('relative_urls', true),
				'remove_script_host' => false,

				// Layout
				'content_css'        => $content_css,
				'document_base_url'  => JUri::root(true) . '/',
				'paste_data_images'  => $allowImgPaste,
				'importcss_append'   => true,
				'image_title'        => true,
				'height'             => $html_height,
				'width'              => $html_width,
				'resize'             => $resizing,
				'templates'          => $templates,
				'image_advtab'       => (bool) $levelParams->get('image_advtab', false),
				'external_plugins'   => empty($externalPlugins) ? null  : $externalPlugins,

				// Drag and drop specific
				'dndEnabled' => $dragdrop,

				// Disable TinyMCE Branding
				'branding'	=> false,
			)
		);

		if ($levelParams->get('newlines'))
		{
			// Break
			$scriptOptions['force_br_newlines'] = true;
			$scriptOptions['force_p_newlines']  = false;
			$scriptOptions['forced_root_block'] = '';
		}
		else
		{
			// Paragraph
			$scriptOptions['force_br_newlines'] = false;
			$scriptOptions['force_p_newlines']  = true;
			$scriptOptions['forced_root_block'] = 'p';
		}

		$scriptOptions['rel_list'] = array(
			array('title' => 'None', 'value' => ''),
			array('title' => 'Alternate', 'value' => 'alternate'),
			array('title' => 'Author', 'value' => 'author'),
			array('title' => 'Bookmark', 'value' => 'bookmark'),
			array('title' => 'Help', 'value' => 'help'),
			array('title' => 'License', 'value' => 'license'),
			array('title' => 'Lightbox', 'value' => 'lightbox'),
			array('title' => 'Next', 'value' => 'next'),
			array('title' => 'No Follow', 'value' => 'nofollow'),
			array('title' => 'No Referrer', 'value' => 'noreferrer'),
			array('title' => 'Prefetch', 'value' => 'prefetch'),
			array('title' => 'Prev', 'value' => 'prev'),
			array('title' => 'Search', 'value' => 'search'),
			array('title' => 'Tag', 'value' => 'tag'),
		);

		/**
		 * Shrink the buttons if not on a mobile or if mobile view is off.
		 * If mobile view is on force into simple mode and enlarge the buttons
		 */
		if (!$this->app->client->mobile)
		{
			$scriptOptions['toolbar_items_size'] = 'small';
		}
		elseif ($levelParams->get('mobile', 0))
		{
			$scriptOptions['menubar'] = false;
			unset($scriptOptions['toolbar2']);
		}

		$options['tinyMCE']['default'] = $scriptOptions;

		$doc->addScriptOptions('plg_editor_tinymce', $options);

		return $editor;
	}

	/**
	 * Get the toggle editor button
	 *
	 * @param   string  $name  Editor name
	 *
	 * @return  string
	 */
	private function _toogleButton($name)
	{
		return JLayoutHelper::render('joomla.tinymce.togglebutton', $name);
	}

	/**
	 * Get the XTD buttons and render them inside tinyMCE
	 *
	 * @param   string  $name      the id of the editor field
	 * @param   string  $excluded  the buttons that should be hidden
	 *
	 * @return array
	 */
	private function tinyButtons($name, $excluded)
	{
		// Get the available buttons
		$buttonsEvent = new Event(
			'getButtons',
			[
				'editor'  => $name,
				'buttons' => $excluded,
			]
		);

		$buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
		$buttons       = $buttonsResult['result'];

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			// Init the arrays for the buttons
			$tinyBtns  = array();
			$btnsNames = array();
			$btnNative = array();
			$externalPlugins = array();

			// Build the script
			foreach ($buttons as $i => $button)
			{
				if ($button->get('name'))
				{
					switch ($button->get('name'))
					{
						case 'pictures':
							$externalPlugins[JText::_('PLG_IMAGE_BUTTON_IMAGE')] = JUri::root() . 'media/editors/tinymce/js/plugins/media/media.js';
							$btnNative[] = str_replace(' ', '', $button->get('text'));
							\JFactory::getDocument()->addScriptOptions('xtd-' . strtolower(JText::_('PLG_IMAGE_BUTTON_IMAGE')), $button->get('options'));
							break;
						default:
							// Set some vars
							$name    = 'button-' . $i . str_replace(' ', '', $button->get('text'));
							$title   = $button->get('text');
							$onclick = $button->get('onclick') ?: null;
							$options = $button->get('options');
							$icon    = $button->get('name');

							if ($button->get('link') !== '#')
							{
								$href = JUri::base() . $button->get('link');
							}
							else
							{
								$href = null;
							}

							// We do some hack here to set the correct icon for 3PD buttons
							$icon = 'none icon-' . $icon;

							// Now we can built the script
							$tempConstructor = '!(function(){';
							$tempConstructor .= "editor.addButton(\"" . $name . "\", {
								text: \"" . $title . "\",
								title: \"" . $title . "\",
								icon: \"" . $icon . "\",
								onclick: function () {";

							if ($href || $button->get('modal'))
							{
								$tempConstructor .= "
								var modalOptions = {
									title  : \"" . $title . "\",
									url : '" . $href . "',
									buttons: [{
										text   : \"Close\",
										onclick: \"close\"
									}]
								}
								modalOptions.width = parseInt(" . intval($options['width']) . ", 10);
								modalOptions.height = parseInt(" . intval($options['height']) . ", 10);
								editor.windowManager.open(modalOptions);";

								if ($onclick && ($button->get('modal') || $href))
								{
									$tempConstructor .= "\r\n
										" . $onclick . '
									';
								}
							}
							else
							{
								$tempConstructor .= "\r\n
								" . $onclick . '
								';
							}

							$tempConstructor .= '
					}
				});
			})();';

							// The array with the toolbar buttons
							$btnsNames[] = $name . ' | ';

							// The array with code for each button
							$tinyBtns[] = $tempConstructor;
							break;
					}
				}
			}

			return array(
				'names'  => $btnsNames,
				'script' => $tinyBtns,
				'native' => $btnNative,
				'paths'  => $externalPlugins
			);
		}
	}

	/**
	 * Get the global text filters to arbitrary text as per settings for current user groups
	 *
	 * @return  JFilterInput
	 *
	 * @since   3.6
	 */
	protected static function getGlobalFilters()
	{
		// Filter settings
		$config     = JComponentHelper::getParams('com_config');
		$user       = JFactory::getUser();
		$userGroups = JAccess::getGroupsByUser($user->get('id'));

		$filters = $config->get('filters');

		$blackListTags       = array();
		$blackListAttributes = array();

		$customListTags       = array();
		$customListAttributes = array();

		$whiteListTags       = array();
		$whiteListAttributes = array();

		$whiteList  = false;
		$blackList  = false;
		$customList = false;
		$unfiltered = false;

		// Cycle through each of the user groups the user is in.
		// Remember they are included in the public group as well.
		foreach ($userGroups as $groupId)
		{
			// May have added a group but not saved the filters.
			if (!isset($filters->$groupId))
			{
				continue;
			}

			// Each group the user is in could have different filtering properties.
			$filterData = $filters->$groupId;
			$filterType = strtoupper($filterData->filter_type);

			if ($filterType === 'NH')
			{
				// Maximum HTML filtering.
			}
			elseif ($filterType === 'NONE')
			{
				// No HTML filtering.
				$unfiltered = true;
			}
			else
			{
				// Blacklist or whitelist.
				// Preprocess the tags and attributes.
				$tags           = explode(',', $filterData->filter_tags);
				$attributes     = explode(',', $filterData->filter_attributes);
				$tempTags       = array();
				$tempAttributes = array();

				foreach ($tags as $tag)
				{
					$tag = trim($tag);

					if ($tag)
					{
						$tempTags[] = $tag;
					}
				}

				foreach ($attributes as $attribute)
				{
					$attribute = trim($attribute);

					if ($attribute)
					{
						$tempAttributes[] = $attribute;
					}
				}

				// Collect the blacklist or whitelist tags and attributes.
				// Each list is cummulative.
				if ($filterType === 'BL')
				{
					$blackList           = true;
					$blackListTags       = array_merge($blackListTags, $tempTags);
					$blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
				}
				elseif ($filterType === 'CBL')
				{
					// Only set to true if Tags or Attributes were added
					if ($tempTags || $tempAttributes)
					{
						$customList           = true;
						$customListTags       = array_merge($customListTags, $tempTags);
						$customListAttributes = array_merge($customListAttributes, $tempAttributes);
					}
				}
				elseif ($filterType === 'WL')
				{
					$whiteList           = true;
					$whiteListTags       = array_merge($whiteListTags, $tempTags);
					$whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
				}
			}
		}

		// Remove duplicates before processing (because the blacklist uses both sets of arrays).
		$blackListTags        = array_unique($blackListTags);
		$blackListAttributes  = array_unique($blackListAttributes);
		$customListTags       = array_unique($customListTags);
		$customListAttributes = array_unique($customListAttributes);
		$whiteListTags        = array_unique($whiteListTags);
		$whiteListAttributes  = array_unique($whiteListAttributes);

		// Unfiltered assumes first priority.
		if ($unfiltered)
		{
			// Dont apply filtering.
			return false;
		}
		else
		{
			// Custom blacklist precedes Default blacklist
			if ($customList)
			{
				$filter = JFilterInput::getInstance(array(), array(), 1, 1);

				// Override filter's default blacklist tags and attributes
				if ($customListTags)
				{
					$filter->tagBlacklist = $customListTags;
				}

				if ($customListAttributes)
				{
					$filter->attrBlacklist = $customListAttributes;
				}
			}
			// Blacklists take second precedence.
			elseif ($blackList)
			{
				// Remove the white-listed tags and attributes from the black-list.
				$blackListTags       = array_diff($blackListTags, $whiteListTags);
				$blackListAttributes = array_diff($blackListAttributes, $whiteListAttributes);

				$filter = JFilterInput::getInstance($blackListTags, $blackListAttributes, 1, 1);

				// Remove whitelisted tags from filter's default blacklist
				if ($whiteListTags)
				{
					$filter->tagBlacklist = array_diff($filter->tagBlacklist, $whiteListTags);
				}

				// Remove whitelisted attributes from filter's default blacklist
				if ($whiteListAttributes)
				{
					$filter->attrBlacklist = array_diff($filter->attrBlacklist, $whiteListAttributes);
				}
			}
			// Whitelists take third precedence.
			elseif ($whiteList)
			{
				// Turn off XSS auto clean
				$filter = JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
			}
			// No HTML takes last place.
			else
			{
				$filter = JFilterInput::getInstance();
			}

			return $filter;
		}
	}

	/**
	 * Return list of known TinyMCE buttons
	 *
	 * @return array
	 *
	 * @since 3.7.0
	 */
	public static function getKnownButtons()
	{
		// See https://www.tinymce.com/docs/demo/full-featured/
		// And https://www.tinymce.com/docs/plugins/
		$buttons = array(

			// General buttons
			'|'              => array('label' => JText::_('PLG_TINY_TOOLBAR_BUTTON_SEPARATOR'), 'text' => '|'),

			'undo'           => array('label' => 'Undo'),
			'redo'           => array('label' => 'Redo'),

			'bold'           => array('label' => 'Bold'),
			'italic'         => array('label' => 'Italic'),
			'underline'      => array('label' => 'Underline'),
			'strikethrough'  => array('label' => 'Strikethrough'),
			'styleselect'    => array('label' => JText::_('PLG_TINY_TOOLBAR_BUTTON_STYLESELECT'), 'text' => 'Formats'),
			'formatselect'   => array('label' => JText::_('PLG_TINY_TOOLBAR_BUTTON_FORMATSELECT'), 'text' => 'Paragraph'),
			'fontselect'     => array('label' => JText::_('PLG_TINY_TOOLBAR_BUTTON_FONTSELECT'), 'text' => 'Font Family'),
			'fontsizeselect' => array('label' => JText::_('PLG_TINY_TOOLBAR_BUTTON_FONTSIZESELECT'), 'text' => 'Font Sizes'),

			'alignleft'     => array('label' => 'Align left'),
			'aligncenter'   => array('label' => 'Align center'),
			'alignright'    => array('label' => 'Align right'),
			'alignjustify'  => array('label' => 'Justify'),

			'outdent'       => array('label' => 'Decrease indent'),
			'indent'        => array('label' => 'Increase indent'),

			'bullist'       => array('label' => 'Bullet list'),
			'numlist'       => array('label' => 'Numbered list'),

			'link'          => array('label' => 'Insert/edit link', 'plugin' => 'link'),
			'unlink'        => array('label' => 'Remove link', 'plugin' => 'link'),

			'subscript'     => array('label' => 'Subscript'),
			'superscript'   => array('label' => 'Superscript'),
			'blockquote'    => array('label' => 'Blockquote'),

			'cut'           => array('label' => 'Cut'),
			'copy'          => array('label' => 'Copy'),
			'paste'         => array('label' => 'Paste', 'plugin' => 'paste'),
			'pastetext'     => array('label' => 'Paste as text', 'plugin' => 'paste'),
			'removeformat'  => array('label' => 'Clear formatting'),

			// Buttons from the plugins
			'forecolor'      => array('label' => 'Text color', 'plugin' => 'textcolor'),
			'backcolor'      => array('label' => 'Background color', 'plugin' => 'textcolor'),
			'anchor'         => array('label' => 'Anchor', 'plugin' => 'anchor'),
			'hr'             => array('label' => 'Horizontal line', 'plugin' => 'hr'),
			'ltr'            => array('label' => 'Left to right', 'plugin' => 'directionality'),
			'rtl'            => array('label' => 'Right to left', 'plugin' => 'directionality'),
			'code'           => array('label' => 'Source code', 'plugin' => 'code'),
			'codesample'     => array('label' => 'Insert/Edit code sample', 'plugin' => 'codesample'),
			'table'          => array('label' => 'Table', 'plugin' => 'table'),
			'charmap'        => array('label' => 'Special character', 'plugin' => 'charmap'),
			'visualchars'    => array('label' => 'Show invisible characters', 'plugin' => 'visualchars'),
			'visualblocks'   => array('label' => 'Show blocks', 'plugin' => 'visualblocks'),
			'nonbreaking'    => array('label' => 'Nonbreaking space', 'plugin' => 'nonbreaking'),
			'emoticons'      => array('label' => 'Emoticons', 'plugin' => 'emoticons'),
			'media'          => array('label' => 'Insert/edit video', 'plugin' => 'media'),
			'pagebreak'      => array('label' => 'Page break', 'plugin' => 'pagebreak'),
			'print'          => array('label' => 'Print', 'plugin' => 'print'),
			'preview'        => array('label' => 'Preview', 'plugin' => 'preview'),
			'fullscreen'     => array('label' => 'Fullscreen', 'plugin' => 'fullscreen'),
			'template'       => array('label' => 'Insert template', 'plugin' => 'template'),
			'searchreplace'  => array('label' => 'Find and replace', 'plugin' => 'searchreplace'),
			'insertdatetime' => array('label' => 'Insert date/time', 'plugin' => 'insertdatetime'),
			// 'spellchecker'   => array('label' => 'Spellcheck', 'plugin' => 'spellchecker'),
		);

		return $buttons;
	}

	/**
	 * Return toolbar presets
	 *
	 * @return array
	 *
	 * @since 3.7.0
	 */
	public static function getToolbarPreset()
	{
		$preset = array();

		$preset['simple'] = array(
			'menu' => array(),
			'toolbar1' => array(
				'bold', 'underline', 'strikethrough', '|',
				'undo', 'redo', '|',
				'bullist', 'numlist', '|',
				'pastetext'
			),
			'toolbar2' => array(),
		);

		$preset['medium'] = array(
			'menu' => array('edit', 'insert', 'view', 'format', 'table', 'tools'),
			'toolbar1' => array(
				'bold', 'italic', 'underline', 'strikethrough', '|',
				'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
				'formatselect', '|',
				'bullist', 'numlist', '|',
				'outdent', 'indent', '|',
				'undo', 'redo', '|',
				'link', 'unlink', 'anchor', 'code', '|',
				'hr', 'table', '|',
				'subscript', 'superscript', '|',
				'charmap', 'pastetext' , 'preview'
			),
			'toolbar2' => array(),
		);

		$preset['advanced'] = array(
			'menu'     => array('edit', 'insert', 'view', 'format', 'table', 'tools'),
			'toolbar1' => array(
				'bold', 'italic', 'underline', 'strikethrough', '|',
				'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
				'styleselect', '|',
				'formatselect', 'fontselect', 'fontsizeselect', '|',
				'searchreplace', '|',
				'bullist', 'numlist', '|',
				'outdent', 'indent', '|',
				'undo', 'redo', '|',
				'link', 'unlink', 'anchor', 'image', '|',
				'code', '|',
				'forecolor', 'backcolor', '|',
				'fullscreen', '|',
				'table', '|',
				'subscript', 'superscript', '|',
				'charmap', 'emoticons', 'media', 'hr', 'ltr', 'rtl', '|',
				'cut', 'copy', 'paste', 'pastetext', '|',
				'visualchars', 'visualblocks', 'nonbreaking', 'blockquote', 'template', '|',
				'print', 'preview', 'codesample', 'insertdatetime', 'removeformat',
			),
			'toolbar2' => array(),
		);

		return $preset;
	}

	/**
	 * Gets the plugin extension id.
	 *
	 * @return  int  The plugin id.
	 *
	 * @since   3.7.0
	 */
	private function getPluginId()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote($this->_type))
			->where($db->quoteName('element') . ' = ' . $db->quote($this->_name));
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Array helper funtion to remove specific arrays by key-value
	 *
	 * @param   array   $array  the parent array
	 * @param   string  $key    the key
	 * @param   string  $value  the value
	 *
	 * @return  array
	 */
	private function removeElementWithValue($array, $key, $value)
	{
		foreach ($array as $subKey => $subArray)
		{
			if ($subArray[$key] == $value)
			{
				unset($array[$subKey]);
			}
		}
		return $array;
	}
}
