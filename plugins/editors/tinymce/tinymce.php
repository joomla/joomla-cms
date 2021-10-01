<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;

/**
 * TinyMCE Editor Plugin
 *
 * @since  1.5
 */
class PlgEditorTinymce extends CMSPlugin
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
	 * @var    \Joomla\CMS\Application\CMSApplication
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
		$wa = $this->app->getDocument()->getWebAssetManager();

		if (!$wa->assetExists('script', 'tinymce'))
		{
			$wa->registerScript('tinymce', $this->_basePath . '/tinymce.min.js', [], ['defer' => true]);
		}

		if (!$wa->assetExists('script', 'plg_editors_tinymce'))
		{
			$wa->registerScript('plg_editors_tinymce', 'plg_editors_tinymce/tinymce.min.js', [], ['defer' => true], ['core', 'tinymce']);
		}

		$wa->useScript('tinymce')
			->useScript('plg_editors_tinymce');
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
		$doc     = Factory::getDocument();
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
		$editor .= LayoutHelper::render('joomla.tinymce.textarea', $textarea);

		if (!$this->app->client->mobile)
		{
			$editor .= LayoutHelper::render('joomla.tinymce.togglebutton');
		}

		$editor .= '</div>';

		// Prepare the instance specific options, actually the ext-buttons
		if (empty($options['tinyMCE'][$fieldName]['joomlaExtButtons']))
		{
			$btns = $this->tinyButtons($id, $buttons);

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

		$user     = Factory::getUser();
		$language = Factory::getLanguage();
		$theme    = 'silver';
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

		// load external plugins
		if (isset($extraOptions->external_plugins) && $extraOptions->external_plugins)
		{
			foreach (json_decode(json_encode($extraOptions->external_plugins), true) as $external)
			{
				// get the path for readability
				$path = $external['path'];

				// if we have a name and path, add it to the list
				if ($external['name'] != '' && $path != '')
				{
					if (substr($path, 0, 1) == '/')
					{
						// treat as a local path, so add the root
						$path = Uri::root() . substr($path, 1);
					}

					$externalPlugins[$external['name']] = $path;
				}
			}
		}

		// Merge the params
		$levelParams->loadObject($toolbarParams);
		$levelParams->loadObject($extraOptions);

		// Set the selected skin
		$skin = $levelParams->get($this->app->isClient('administrator') ? 'skin_admin' : 'skin', 'oxide');

		// Check that selected skin exists.
		$skin = Folder::exists(JPATH_ROOT . '/media/vendor/tinymce/skins/ui/' . $skin) ? $skin : 'oxide';

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
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('template'))
			->from($db->quoteName('#__template_styles'))
			->where(
				[
					$db->quoteName('client_id') . ' = 0',
					$db->quoteName('home') . ' = ' . $db->quote('1'),
				]
			);

		$db->setQuery($query);

		try
		{
			$template = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			$this->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

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
				$content_css = Uri::root(true) . '/templates/' . $template . '/css/' . $content_css_custom;

				// Issue warning notice if the file is not found (but pass name to $content_css anyway to avoid TinyMCE error
				if (!file_exists($templates_path . '/' . $template . '/css/' . $content_css_custom))
				{
					$msg = sprintf(Text::_('PLG_TINY_ERR_CUSTOMCSSFILENOTPRESENT'), $content_css_custom);
					Log::add($msg, Log::WARNING, 'jerror');
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
						Log::add(Text::_('PLG_TINY_ERR_EDITORCSSFILENOTPRESENT'), Log::WARNING, 'jerror');
					}
					else
					{
						$content_css = Uri::root(true) . '/templates/system/css/editor.css';
					}
				}
				else
				{
					$content_css = Uri::root(true) . '/templates/' . $template . '/css/editor.css';
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

			$blockedTags       = !empty($filter->blockedTags) ? $filter->blockedTags : array();
			$blockedAttributes = !empty($filter->blockedAttributes) ? $filter->blockedAttributes : array();
			$tagArray          = !empty($filter->tagsArray) ? $filter->tagsArray : array();
			$attrArray         = !empty($filter->attrArray) ? $filter->attrArray : array();

			$invalid_elements  = implode(',', array_merge($blockedTags, $blockedAttributes, $tagArray, $attrArray));

			// Valid elements are all entries listed as allowed in com_config, which are now missing in the filter blocked properties
			$default_filter = InputFilter::getInstance();
			$valid_elements = implode(',', array_diff($default_filter->blockedTags, $blockedTags));

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

		if (is_numeric($html_width))
		{
			$html_width .= 'px';
		}

		if (is_numeric($html_height))
		{
			$html_height .= 'px';
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
			'importcss',
			'quickbars',
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

		$menubar         = (array) $levelParams->get('menu', []);
		$toolbar1        = (array) $levelParams->get('toolbar1', []);
		$toolbar2        = (array) $levelParams->get('toolbar2', []);

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
		$templates = [];

		if (!empty($allButtons['template']))
		{
			// Do we have a custom content_template_path
			$template_path = $levelParams->get('content_template_path');
			$template_path = $template_path ? '/templates/' . $template_path : '/media/vendor/tinymce/templates';

			$filepaths = Folder::exists(JPATH_ROOT . $template_path)
				? Folder::files(JPATH_ROOT . $template_path, '\.(html|txt)$', false, true)
				: [];

			foreach ($filepaths as $filepath)
			{
				$fileinfo      = pathinfo($filepath);
				$filename      = $fileinfo['filename'];
				$full_filename = $fileinfo['basename'];

				if ($filename === 'index')
				{
					continue;
				}

				$title       = $filename;
				$title_upper = strtoupper($filename);
				$description = ' ';

				if ($language->hasKey('PLG_TINY_TEMPLATE_' . $title_upper . '_TITLE'))
				{
					$title = Text::_('PLG_TINY_TEMPLATE_' . $title_upper . '_TITLE');
				}

				if ($language->hasKey('PLG_TINY_TEMPLATE_' . $title_upper . '_DESC'))
				{
					$description = Text::_('PLG_TINY_TEMPLATE_' . $title_upper . '_DESC');
				}

				$templates[] = array(
					'title' => $title,
					'description' => $description,
					'url' => Uri::root(true) . $template_path . '/' . $full_filename,
				);
			}
		}

		// Check for extra plugins, from the setoptions form
		foreach (array('wordcount' => 1, 'advlist' => 1, 'autosave' => 1, 'textpattern' => 0) as $pName => $def)
		{
			if ($levelParams->get($pName, $def))
			{
				$plugins[] = $pName;
			}
		}

		// Drag and drop Images always FALSE, reverting this allows for inlining the images
		$allowImgPaste = false;
		$dragdrop      = $levelParams->get('drag_drop', 1);

		if ($dragdrop && $user->authorise('core.create', 'com_media'))
		{
			$externalPlugins['jdragndrop'] = HTMLHelper::_('script', 'plg_editors_tinymce/plugins/dragdrop/plugin.min.js', ['relative' => true, 'version' => 'auto', 'pathOnly' => true]);
			$uploadUrl                     = Uri::base(false) . 'index.php?option=com_media&format=json&task=api.files';

			if ($this->app->isClient('site'))
			{
				$uploadUrl = htmlentities($uploadUrl, null, 'UTF-8', null);
			}

			Text::script('PLG_TINY_ERR_UNSUPPORTEDBROWSER');
			Text::script('ERROR');
			Text::script('PLG_TINY_DND_ADDITIONALDATA');
			Text::script('PLG_TINY_DND_ALTTEXT');
			Text::script('PLG_TINY_DND_LAZYLOADED');
			Text::script('PLG_TINY_DND_EMPTY_ALT');

			$scriptOptions['parentUploadFolder'] = $levelParams->get('path', '');
			$scriptOptions['csrfToken']          = Session::getFormToken();
			$scriptOptions['uploadUri']          = $uploadUrl;

			// @TODO have a way to select the adapter, similar to $levelParams->get('path', '');
			$scriptOptions['comMediaAdapter']    = 'local-images:';
		}

		// Convert pt to px in dropdown
		$scriptOptions['fontsize_formats'] = '8px 10px 12px 14px 18px 24px 36px';

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
			$toolbar1  = array_merge($toolbar1, explode($separator, $custom_button));
		}

		// Merge the two toolbars for backwards compatibility
		$toolbar = array_merge($toolbar1, $toolbar2);

		// Build the final options set
		$scriptOptions   = array_merge(
			$scriptOptions,
			array(
				'suffix'   => '.min',
				'baseURL'  => Uri::root(true) . '/media/vendor/tinymce',
				'directionality' => $text_direction,
				'language' => $langPrefix,
				'autosave_restore_when_empty' => false,
				'skin'     => $skin,
				'theme'    => $theme,
				'schema'   => 'html5',

				// Toolbars
				'menubar'  => empty($menubar)  ? false : implode(' ', array_unique($menubar)),
				'toolbar' => empty($toolbar) ? null  : 'jxtdbuttons ' . implode(' ', $toolbar),

				'plugins'  => implode(',', array_unique($plugins)),

				// Quickbars
				'quickbars_image_toolbar'     => false,
				'quickbars_insert_toolbar'    => false,
				'quickbars_selection_toolbar' => 'bold italic underline | H2 H3 | link blockquote',

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
				'document_base_url'  => Uri::root(true) . '/',
				'paste_data_images'  => $allowImgPaste,
				'image_caption'      => true,
				'importcss_append'   => true,
				'height'             => $html_height,
				'width'              => $html_width,
				'elementpath'        => (bool) $levelParams->get('element_path', true),
				'resize'             => $resizing,
				'templates'          => $templates,
				'external_plugins'   => empty($externalPlugins) ? null  : $externalPlugins,
				'contextmenu'        => (bool) $levelParams->get('contextmenu', true) ? null : false,
				'toolbar_sticky'     => true,
				'toolbar_mode'       => $levelParams->get('toolbar_mode', 'sliding'),

				// Image plugin options
				'a11y_advanced_options' => true,
				'image_advtab'          => (bool) $levelParams->get('image_advtab', false),
				'image_title'           => true,

				// Drag and drop specific
				'dndEnabled' => $dragdrop,

				// Disable TinyMCE Branding
				'branding'   => false,
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

		$options['tinyMCE']['default'] = $scriptOptions;

		$doc->addScriptOptions('plg_editor_tinymce', $options);

		return $editor;
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
			Text::script('PLG_TINY_CORE_BUTTONS');

			// Init the arrays for the buttons
			$btnsNames = [];

			// Build the script
			foreach ($buttons as $i => $button)
			{
				$button->id = $name . '_' . $button->name . '_modal';

				echo LayoutHelper::render('joomla.editors.buttons.modal', $button);

				if ($button->get('name'))
				{
					// Set some vars
					$btnName = $button->get('text');
					$modalId = $name . '_' . $button->name;
					$onclick = $button->get('onclick') ?: null;
					$icon    = $button->get('icon');

					if ($button->get('link') !== '#')
					{
						$href = Uri::base() . $button->get('link');
					}
					else
					{
						$href = null;
					}

					$coreButton = [];

					$coreButton['name']    = $btnName;
					$coreButton['href']    = $href;
					$coreButton['id']      = $modalId;
					$coreButton['icon']    = $icon;
					$coreButton['click']   = $onclick;
					$coreButton['iconSVG'] = $button->get('iconSVG');

					// The array with the toolbar buttons
					$btnsNames[] = $coreButton;
				}
			}

			sort($btnsNames);

			return ['names'  => $btnsNames];
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
		$config     = ComponentHelper::getParams('com_config');
		$user       = Factory::getUser();
		$userGroups = Access::getGroupsByUser($user->get('id'));

		$filters = $config->get('filters');

		$forbiddenListTags        = array();
		$forbiddenListAttributes  = array();

		$customListTags       = array();
		$customListAttributes = array();

		$allowedListTags        = array();
		$allowedListAttributes  = array();

		$allowedList  = false;
		$forbiddenList  = false;
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
				// Forbidden or allowed lists.
				// Preprocess the tags and attributes.
				$tags           = explode(',', $filterData->filter_tags);
				$attributes     = explode(',', $filterData->filter_attributes);
				$tempTags       = [];
				$tempAttributes = [];

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

				// Collect the list of forbidden or allowed tags and attributes.
				// Each list is cumulative.
				// "BL" is deprecated in Joomla! 4, will be removed in Joomla! 5
				if (in_array($filterType, ['BL', 'FL']))
				{
					$forbiddenList           = true;
					$forbiddenListTags       = array_merge($forbiddenListTags, $tempTags);
					$forbiddenListAttributes = array_merge($forbiddenListAttributes, $tempAttributes);
				}
				// "CBL" is deprecated in Joomla! 4, will be removed in Joomla! 5
				elseif (in_array($filterType, ['CBL', 'CFL']))
				{
					// Only set to true if Tags or Attributes were added
					if ($tempTags || $tempAttributes)
					{
						$customList           = true;
						$customListTags       = array_merge($customListTags, $tempTags);
						$customListAttributes = array_merge($customListAttributes, $tempAttributes);
					}
				}
				// "WL" is deprecated in Joomla! 4, will be removed in Joomla! 5
				elseif (in_array($filterType, ['WL', 'AL']))
				{
					$allowedList           = true;
					$allowedListTags       = array_merge($allowedListTags, $tempTags);
					$allowedListAttributes = array_merge($allowedListAttributes, $tempAttributes);
				}
			}
		}

		// Remove duplicates before processing (because the forbidden list uses both sets of arrays).
		$forbiddenListTags        = array_unique($forbiddenListTags);
		$forbiddenListAttributes  = array_unique($forbiddenListAttributes);
		$customListTags       = array_unique($customListTags);
		$customListAttributes = array_unique($customListAttributes);
		$allowedListTags        = array_unique($allowedListTags);
		$allowedListAttributes  = array_unique($allowedListAttributes);

		// Unfiltered assumes first priority.
		if ($unfiltered)
		{
			// Dont apply filtering.
			return false;
		}
		else
		{
			// Custom forbidden list precedes Default forbidden list.
			if ($customList)
			{
				$filter = InputFilter::getInstance([], [], 1, 1);

				// Override filter's default forbidden tags and attributes
				if ($customListTags)
				{
					$filter->blockedTags = $customListTags;
				}

				if ($customListAttributes)
				{
					$filter->blockedAttributes = $customListAttributes;
				}
			}
			// Forbidden list takes second precedence.
			elseif ($forbiddenList)
			{
				// Remove the allowed tags and attributes from the forbidden list.
				$forbiddenListTags       = array_diff($forbiddenListTags, $allowedListTags);
				$forbiddenListAttributes = array_diff($forbiddenListAttributes, $allowedListAttributes);

				$filter = InputFilter::getInstance($forbiddenListTags, $forbiddenListAttributes, 1, 1);

				// Remove allowed tags from filter's default forbidden list
				if ($allowedListTags)
				{
					$filter->blockedTags = array_diff($filter->blockedTags, $allowedListTags);
				}

				// Remove allowed attributes from filter's default forbidden list
				if ($allowedListAttributes)
				{
					$filter->blockedAttributes = array_diff($filter->blockedAttributes, $allowedListAttributes);
				}
			}
			// Allowed list take third precedence.
			elseif ($allowedList)
			{
				// Turn off XSS auto clean
				$filter = InputFilter::getInstance($allowedListTags, $allowedListAttributes, 0, 0, 0);
			}
			// No HTML takes last place.
			else
			{
				$filter = InputFilter::getInstance();
			}

			return $filter;
		}
	}

	/**
	 * Return list of known TinyMCE buttons
	 * @see https://www.tiny.cloud/docs/demo/full-featured/
	 * @see https://www.tiny.cloud/apps/#core-plugins
	 *
	 * @return array
	 *
	 * @since 3.7.0
	 */
	public static function getKnownButtons()
	{
		$buttons = [

			// General buttons
			'|'              => array('label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_SEPARATOR'), 'text' => '|'),

			'undo'           => array('label' => 'Undo'),
			'redo'           => array('label' => 'Redo'),

			'bold'           => array('label' => 'Bold'),
			'italic'         => array('label' => 'Italic'),
			'underline'      => array('label' => 'Underline'),
			'strikethrough'  => array('label' => 'Strikethrough'),
			'styleselect'    => array('label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_STYLESELECT'), 'text' => 'Formats'),
			'formatselect'   => array('label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_FORMATSELECT'), 'text' => 'Paragraph'),
			'fontselect'     => array('label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_FONTSELECT'), 'text' => 'Font Family'),
			'fontsizeselect' => array('label' => Text::_('PLG_TINY_TOOLBAR_BUTTON_FONTSIZESELECT'), 'text' => 'Font Sizes'),

			'alignleft'     => array('label' => 'Align left'),
			'aligncenter'   => array('label' => 'Align center'),
			'alignright'    => array('label' => 'Align right'),
			'alignjustify'  => array('label' => 'Justify'),
			'lineheight'    => array('label' => 'Line height'),

			'outdent'       => array('label' => 'Decrease indent'),
			'indent'        => array('label' => 'Increase indent'),

			'forecolor'     => array('label' => 'Text colour'),
			'backcolor'     => array('label' => 'Background text colour'),

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
			'image'          => array('label' => 'Insert/edit image', 'plugin' => 'image'),
			'pagebreak'      => array('label' => 'Page break', 'plugin' => 'pagebreak'),
			'print'          => array('label' => 'Print', 'plugin' => 'print'),
			'preview'        => array('label' => 'Preview', 'plugin' => 'preview'),
			'fullscreen'     => array('label' => 'Fullscreen', 'plugin' => 'fullscreen'),
			'template'       => array('label' => 'Insert template', 'plugin' => 'template'),
			'searchreplace'  => array('label' => 'Find and replace', 'plugin' => 'searchreplace'),
			'insertdatetime' => array('label' => 'Insert date/time', 'plugin' => 'insertdatetime'),
			'help'           => array('label' => 'Help', 'plugin' => 'help'),
			// 'spellchecker'   => array('label' => 'Spellcheck', 'plugin' => 'spellchecker'),
		];

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
		$preset = [];

		$preset['simple'] = [
			'menu' => [],
			'toolbar1' => [
				'bold', 'underline', 'strikethrough', '|',
				'undo', 'redo', '|',
				'bullist', 'numlist', '|',
				'pastetext', 'jxtdbuttons',
			],
			'toolbar2' => [],
		];

		$preset['medium'] = array(
			'menu' => array('edit', 'insert', 'view', 'format', 'table', 'tools', 'help'),
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
				'charmap', 'pastetext', 'preview', 'jxtdbuttons',
			),
			'toolbar2' => array(),
		);

		$preset['advanced'] = array(
			'menu'     => array('edit', 'insert', 'view', 'format', 'table', 'tools', 'help'),
			'toolbar1' => array(
				'bold', 'italic', 'underline', 'strikethrough', '|',
				'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
				'lineheight', '|',
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
				'print', 'preview', 'codesample', 'insertdatetime', 'removeformat', 'jxtdbuttons',
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
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = :folder')
			->where($db->quoteName('element') . ' = :element')
			->bind(':folder', $this->_type)
			->bind(':element', $this->_name);
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Array helper function to remove specific arrays by key-value
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
