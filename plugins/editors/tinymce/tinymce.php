<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * TinyMCE Editor Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       1.5
 */
class PlgEditorTinymce extends JPlugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'media/editors/tinymce';

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
	 * @return  string  JavaScript Initialization string
	 *
	 * @since   1.5
	 */
	public function onInit()
	{
		$language = JFactory::getLanguage();
		$mode     = (int) $this->params->get('mode', 1);
		$theme    = 'modern';
		$skin     = $this->params->get('skin', '0');

		switch ($skin)
		{
			case '0':
			default:
				$skin = 'skin : "lightgray",';
		}

		$entity_encoding = $this->params->get('entity_encoding', 'raw');
		$langMode        = $this->params->get('lang_mode', 0);
		$langPrefix      = $this->params->get('lang_code', 'en');

		if ($langMode)
		{
			if (file_exists(JPATH_ROOT . "/media/editors/tinymce/langs/" . $language->getTag() . ".js"))
			{
				$langPrefix = $language->getTag();
			}
			elseif (file_exists(JPATH_ROOT . "/media/editors/tinymce/langs/" . substr($language->getTag(), 0, strpos($language->getTag(), '-')) . ".js"))
			{
				$langPrefix = substr($language->getTag(), 0, strpos($language->getTag(), '-'));
			}
			else
			{
				$langPrefix = "en";
			}
		}

		$text_direction = 'ltr';

		if ($language->isRTL())
		{
			$text_direction = 'rtl';
		}

		$use_content_css    = $this->params->get('content_css', 1);
		$content_css_custom = $this->params->get('content_css_custom', '');

		/*
		 * Lets get the default template for the site application
		 */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('template')
			->from('#__template_styles')
			->where('client_id=0 AND home=' . $db->quote('1'));

		$db->setQuery($query);
		$template = $db->loadResult();

		$content_css    = '';
		$templates_path = JPATH_SITE . '/templates';

		// Loading of css file for 'styles' dropdown
		if ($content_css_custom)
		{
			// If URL, just pass it to $content_css
			if (strpos($content_css_custom, 'http') !== false)
			{
				$content_css = 'content_css : "' . $content_css_custom . '",';
			}

			// If it is not a URL, assume it is a file name in the current template folder
			else
			{
				$content_css = 'content_css : "' . JUri::root() . 'templates/' . $template . '/css/' . $content_css_custom . '",';

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
						$content_css = 'content_css : "' . JUri::root() . 'templates/system/css/editor.css",';
					}
				}
				else
				{
					$content_css = 'content_css : "' . JUri::root() . 'templates/' . $template . '/css/editor.css",';
				}
			}
		}

		$relative_urls = $this->params->get('relative_urls', '1');

		if ($relative_urls)
		{
			// Relative
			$relative_urls = "true";
		}
		else
		{
			// Absolute
			$relative_urls = "false";
		}

		$newlines = $this->params->get('newlines', 0);

		if ($newlines)
		{
			// Break
			$forcenewline = "force_br_newlines : true, force_p_newlines : false, forced_root_block : '',";
		}
		else
		{
			// Paragraph
			$forcenewline = "force_br_newlines : false, force_p_newlines : true, forced_root_block : 'p',";
		}

		$invalid_elements  = $this->params->get('invalid_elements', 'script,applet,iframe');
		$extended_elements = $this->params->get('extended_elements', '');
		$valid_elements    = $this->params->get('valid_elements', '');

		// Advanced Options
		$html_height = $this->params->get('html_height', '550');
		$html_width  = $this->params->get('html_width', '');

		if ($html_width == 750)
		{
			$html_width = '';
		}

		// Image advanced options
		$image_advtab = $this->params->get('image_advtab', 1);

		if ($image_advtab)
		{
			$image_advtab = "true";
		}
		else
		{
			$image_advtab = "false";
		}

		// The param is true false, so we turn true to both rather than showing vertical resize only
		$resizing = $this->params->get('resizing', '1');

		if ($resizing || $resizing == 'true')
		{
			$resizing = 'resize: "both",';
		}
		else
		{
			$resizing = 'resize: false,';
		}

		$toolbar1_add   = array();
		$toolbar2_add   = array();
		$toolbar3_add   = array();
		$toolbar4_add   = array();
		$elements       = array();
		$plugins        = array('autolink', 'lists', 'image', 'charmap', 'print', 'preview', 'anchor', 'pagebreak', 'code', 'save', 'textcolor', 'colorpicker', 'importcss');
		$toolbar1_add[] = 'bold';
		$toolbar1_add[] = 'italic';
		$toolbar1_add[] = 'underline';
		$toolbar1_add[] = 'strikethrough';

		// Alignment buttons
		$alignment = $this->params->get('alignment', 1);

		if ($alignment)
		{
			$toolbar1_add[] = '|';
			$toolbar1_add[] = 'alignleft';
			$toolbar1_add[] = 'aligncenter';
			$toolbar1_add[] = 'alignright';
			$toolbar1_add[] = 'alignjustify';
		}

		$toolbar1_add[] = '|';
		$toolbar1_add[] = 'styleselect';
		$toolbar1_add[] = '|';
		$toolbar1_add[] = 'formatselect';

		// Fonts
		$fonts = $this->params->get('fonts', 1);

		if ($fonts)
		{
			$toolbar1_add[] = 'fontselect';
			$toolbar1_add[] = 'fontsizeselect';
		}

		// Search & replace
		$searchreplace = $this->params->get('searchreplace', 1);

		if ($searchreplace)
		{
			$plugins[]      = 'searchreplace';
			$toolbar2_add[] = 'searchreplace';
		}

		$toolbar2_add[] = '|';
		$toolbar2_add[] = 'bullist';
		$toolbar2_add[] = 'numlist';
		$toolbar2_add[] = '|';
		$toolbar2_add[] = 'outdent';
		$toolbar2_add[] = 'indent';
		$toolbar2_add[] = '|';
		$toolbar2_add[] = 'undo';
		$toolbar2_add[] = 'redo';
		$toolbar2_add[] = '|';

		// Insert date and/or time plugin
		$insertdate = $this->params->get('insertdate', 1);

		if ($insertdate)
		{
			$plugins[]      = 'insertdatetime';
			$toolbar4_add[] = 'inserttime';
		}

		// Link plugin
		$link = $this->params->get('link', 1);

		if ($link)
		{
			$plugins[]      = 'link';
			$toolbar2_add[] = 'link';
			$toolbar2_add[] = 'unlink';
		}

		$toolbar2_add[] = 'anchor';
		$toolbar2_add[] = 'image';
		$toolbar2_add[] = '|';
		$toolbar2_add[] = 'code';

		// Colours
		$colours = $this->params->get('colours', 1);

		if ($colours)
		{
			$toolbar2_add[] = '|';
			$toolbar2_add[] = 'forecolor,backcolor';
		}

		// Fullscreen
		$fullscreen = $this->params->get('fullscreen', 1);

		if ($fullscreen)
		{
			$plugins[]      = 'fullscreen';
			$toolbar2_add[] = '|';
			$toolbar2_add[] = 'fullscreen';
		}

		// Table
		$table = $this->params->get('table', 1);

		if ($table)
		{
			$plugins[]      = 'table';
			$toolbar3_add[] = 'table';
			$toolbar3_add[] = '|';
		}

		$toolbar3_add[] = 'subscript';
		$toolbar3_add[] = 'superscript';
		$toolbar3_add[] = '|';
		$toolbar3_add[] = 'charmap';

		// Emotions
		$smilies = $this->params->get('smilies', 1);

		if ($smilies)
		{
			$plugins[]      = 'emoticons';
			$toolbar3_add[] = 'emoticons';
		}

		// Media plugin
		$media = $this->params->get('media', 1);

		if ($media)
		{
			$plugins[]      = 'media';
			$toolbar3_add[] = 'media';
		}

		// Horizontal line
		$hr = $this->params->get('hr', 1);

		if ($hr)
		{
			$plugins[]      = 'hr';
			$elements[]     = 'hr[id|title|alt|class|width|size|noshade]';
			$toolbar3_add[] = 'hr';
		}
		else
		{
			$elements[] = 'hr[id|class|title|alt]';
		}

		// RTL/LTR buttons
		$directionality = $this->params->get('directionality', 1);

		if ($directionality)
		{
			$plugins[] = 'directionality';
			$toolbar3_add[] = 'ltr rtl';
		}

		if ($extended_elements != "")
		{
			$elements = explode(',', $extended_elements);
		}

		$toolbar4_add[] = 'cut';
		$toolbar4_add[] = 'copy';

		// Paste
		$paste = $this->params->get('paste', 1);

		if ($paste)
		{
			$plugins[]      = 'paste';
			$toolbar4_add[] = 'paste';
		}

		$toolbar4_add[] = '|';

		// Visualchars
		$visualchars = $this->params->get('visualchars', 1);

		if ($visualchars)
		{
			$plugins[]      = 'visualchars';
			$toolbar4_add[] = 'visualchars';
		}

		// Visualblocks
		$visualblocks = $this->params->get('visualblocks', 1);

		if ($visualblocks)
		{
			$plugins[]      = 'visualblocks';
			$toolbar4_add[] = 'visualblocks';
		}

		// Non-breaking
		$nonbreaking = $this->params->get('nonbreaking', 1);

		if ($nonbreaking)
		{
			$plugins[]      = 'nonbreaking';
			$toolbar4_add[] = 'nonbreaking';
		}

		// Blockquote
		$blockquote = $this->params->get('blockquote', 1);

		if ($blockquote)
		{
			$toolbar4_add[] = 'blockquote';
		}

		// Template
		$template = $this->params->get('template', 1);

		if ($template)
		{
			$plugins[]      = 'template';
			$toolbar4_add[] = 'template';

			// Note this check for the template_list.js file will be removed in Joomla 4.0
			if (is_file(JPATH_ROOT . "/media/editors/tinymce/templates/template_list.js"))
			{
				// If using the legacy file we need to include and input the files the new way
				$str = file_get_contents(JPATH_ROOT . "/media/editors/tinymce/templates/template_list.js");

				// Find from one [ to the last ]
				preg_match_all('/\[.*\]/', $str, $matches);

				$templates = "templates: [";

				// Set variables
				foreach ($matches['0'] as $match)
				{
					preg_match_all('/\".*\"/', $match, $values);
					$result = trim($values["0"]["0"], '"');
					$final_result = explode(',', $result);
					$templates .= "{title: '" . trim($final_result['0'], ' " ') . "', description: '"
						. trim($final_result['2'], ' " ') . "', url: '" . JUri::root() . trim($final_result['1'], ' " ') . "'},";
				}

				$templates .= "],";
			}
			else
			{
				$templates = "templates: [
					{title: 'Layout', description: 'HTMLLayout', url:'" . JUri::root() . "media/editors/tinymce/templates/layout1.html'},
					{title: 'Simple snippet', description: 'Simple HTML snippet', url:'" . JUri::root() . "media/editors/tinymce/templates/snippet1.html'}
				],";
			}
		}
		else
		{
			$templates = '';
		}

		// Print
		$print = $this->params->get('print', 1);

		if ($print)
		{
			$plugins[] = 'print';
			$toolbar4_add[] = '|';
			$toolbar4_add[] = 'print';
			$toolbar4_add[] = 'preview';
		}

		// Spellchecker
		$spell = $this->params->get('spell', 0);

		if ($spell)
		{
			$plugins[]      = 'spellchecker';
			$toolbar4_add[] = '|';
			$toolbar4_add[] = 'spellchecker';
		}

		// Wordcount
		$wordcount = $this->params->get('wordcount', 1);

		if ($wordcount)
		{
			$plugins[] = 'wordcount';
		}

		// Advlist
		$advlist = $this->params->get('advlist', 1);

		if ($advlist)
		{
			$plugins[] = 'advlist';
		}

		// Autosave
		$autosave = $this->params->get('autosave', 1);

		if ($autosave)
		{
			$plugins[] = 'autosave';
		}

		// Context menu
		$contextmenu = $this->params->get('contextmenu', 1);

		if ($contextmenu)
		{
			$plugins[] = 'contextmenu';
		}

		$custom_plugin = $this->params->get('custom_plugin', '');

		if ($custom_plugin != "")
		{
			$plugins[] = $custom_plugin;
		}

		$custom_button = $this->params->get('custom_button', '');

		if ($custom_button != "")
		{
			$toolbar4_add[] = $custom_button;
		}

		// Prepare config variables
		$plugins  = implode(',', $plugins);
		$elements = implode(',', $elements);

		// Prepare config variables
		$toolbar1 = implode(' ', $toolbar1_add);
		$toolbar2 = implode(' ', $toolbar2_add);
		$toolbar3 = implode(' ', $toolbar3_add);
		$toolbar4 = implode(' ', $toolbar4_add);

		// See if mobileVersion is activated
		$mobileVersion = $this->params->get('mobile', 0);

		$load = "\t<script type=\"text/javascript\" src=\"" .
				JUri::root() . $this->_basePath .
				"/tinymce.min.js\"></script>\n";

		/**
		 * Shrink the buttons if not on a mobile or if mobile view is off.
		 * If mobile view is on force into simple mode and enlarge the buttons
		**/
		if (!$this->app->client->mobile)
		{
			$smallButtons = 'toolbar_items_size: "small",';
		}
		elseif ($mobileVersion == false)
		{
			$smallButtons = '';
		}
		else
		{
			$smallButtons = '';
			$mode         = 0;
		}

		switch ($mode)
		{
			case 0: /* Simple mode*/
				$return = $load .
				"\t<script type=\"text/javascript\">
					tinymce.init({
						// General
						directionality: \"$text_direction\",
						selector: \"textarea.mce_editable\",
						language : \"$langPrefix\",
						mode : \"specific_textareas\",
						autosave_restore_when_empty: false,
						$skin
						theme : \"$theme\",
						schema: \"html5\",
						menubar: false,
						toolbar1: \"bold italics underline strikethrough | undo redo | bullist numlist\",
						// Cleanup/Output
						inline_styles : true,
						gecko_spellcheck : true,
						entity_encoding : \"$entity_encoding\",
						$forcenewline
						$smallButtons
						// URL
						relative_urls : $relative_urls,
						remove_script_host : false,
						// Layout
						$content_css
						document_base_url : \"" . JUri::root() . "\"
					});
				</script>";
			break;

			case 1:
			default: /* Advanced mode*/
				$toolbar1 = "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | formatselect | bullist numlist";
				$toolbar2 = "outdent indent | undo redo | link unlink anchor image code | hr table | subscript superscript | charmap";
				$return = $load .
				"\t<script type=\"text/javascript\">
				tinyMCE.init({
					// General
					directionality: \"$text_direction\",
					language : \"$langPrefix\",
					mode : \"specific_textareas\",
					autosave_restore_when_empty: false,
					$skin
					theme : \"$theme\",
					schema: \"html5\",
					selector: \"textarea.mce_editable\",
					// Cleanup/Output
					inline_styles : true,
					gecko_spellcheck : true,
					entity_encoding : \"$entity_encoding\",
					valid_elements : \"$valid_elements\",
					extended_valid_elements : \"$elements\",
					$forcenewline
					$smallButtons
					invalid_elements : \"$invalid_elements\",
					// Plugins
					plugins : \"table link image code hr charmap autolink lists importcss\",
					// Toolbar
					toolbar1: \"$toolbar1\",
					toolbar2: \"$toolbar2\",
					removed_menuitems: \"newdocument\",
					// URL
					relative_urls : $relative_urls,
					remove_script_host : false,
					document_base_url : \"" . JUri::root() . "\",
					// Layout
					$content_css
					importcss_append: true,
					// Advanced Options
					$resizing
					height : \"$html_height\",
					width : \"$html_width\",

				});
				</script>";
			break;

			case 2: /* Extended mode*/
				$return = $load .
				"\t<script type=\"text/javascript\">
				tinyMCE.init({
					// General
					directionality: \"$text_direction\",
					language : \"$langPrefix\",
					mode : \"specific_textareas\",
					autosave_restore_when_empty: false,
					$skin
					theme : \"$theme\",
					schema: \"html5\",
					selector: \"textarea.mce_editable\",
					// Cleanup/Output
					inline_styles : true,
					gecko_spellcheck : true,
					entity_encoding : \"$entity_encoding\",
					valid_elements : \"$valid_elements\",
					extended_valid_elements : \"$elements\",
					$forcenewline
					$smallButtons
					invalid_elements : \"$invalid_elements\",
					// Plugins
					plugins : \"$plugins\",
					// Toolbar
					toolbar1: \"$toolbar1\",
					toolbar2: \"$toolbar2\",
					toolbar3: \"$toolbar3\",
					toolbar4: \"$toolbar4\",
					removed_menuitems: \"newdocument\",
					// URL
					relative_urls : $relative_urls,
					remove_script_host : false,
					document_base_url : \"" . JUri::root() . "\",
					rel_list : [
						{title: 'Alternate', value: 'alternate'},
						{title: 'Author', value: 'author'},
						{title: 'Bookmark', value: 'bookmark'},
						{title: 'Help', value: 'help'},
						{title: 'License', value: 'license'},
						{title: 'Lightbox', value: 'lightbox'},
						{title: 'Next', value: 'next'},
						{title: 'No Follow', value: 'nofollow'},
						{title: 'No Referrer', value: 'noreferrer'},
						{title: 'Prefetch', value: 'prefetch'},
						{title: 'Prev', value: 'prev'},
						{title: 'Search', value: 'search'},
						{title: 'Tag', value: 'tag'}
					],
					//Templates
					" . $templates . "
					// Layout
					$content_css
					importcss_append: true,
					// Advanced Options
					$resizing
					image_advtab: $image_advtab,
					height : \"$html_height\",
					width : \"$html_width\",

				});
				</script>";
			break;
		}

		return $return;
	}

	/**
	 * TinyMCE WYSIWYG Editor - get the editor content
	 *
	 * @param   string  $editor  The name of the editor
	 *
	 * @return  string
	 */
	public function onGetContent($editor)
	{
		return 'tinyMCE.get(\'' . $editor . '\').getContent();';
	}

	/**
	 * TinyMCE WYSIWYG Editor - set the editor content
	 *
	 * @param   string  $editor  The name of the editor
	 * @param   string  $html    The html to place in the editor
	 *
	 * @return  string
	 */
	public function onSetContent($editor, $html)
	{
		return 'tinyMCE.get(\'' . $editor . '\').setContent(' . $html . ');';
	}

	/**
	 * TinyMCE WYSIWYG Editor - copy editor content to form field
	 *
	 * @param   string  $editor  The name of the editor
	 *
	 * @return  string
	 */
	public function onSave($editor)
	{
		return 'if (tinyMCE.get("' . $editor . '").isHidden()) {tinyMCE.get("' . $editor . '").show()}; tinyMCE.get("' . $editor . '").save();';
	}

	/**
	 * Inserts html code into the editor
	 *
	 * @param   string  $name  The name of the editor
	 *
	 * @return  boolean
	 */
	public function onGetInsertMethod($name)
	{
		$doc = JFactory::getDocument();

		$js = "
			function isBrowserIE()
			{
				return navigator.appName==\"Microsoft Internet Explorer\";
			}

			function jInsertEditorText( text, editor )
			{
				tinyMCE.execCommand('mceInsertContent', false, text);
			}

			var global_ie_bookmark = false;

			function IeCursorFix()
			{
				if (isBrowserIE())
				{
					tinyMCE.execCommand('mceInsertContent', false, '');
					global_ie_bookmark = tinyMCE.activeEditor.selection.getBookmark(false);
				}
				return true;
			}";

		$doc->addScriptDeclaration($js);

		return true;
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
	 *
	 * @return  string
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null)
	{
		if (empty($id))
		{
			$id = $name;
		}

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
		$textarea->cols    = $col;
		$textarea->rows    = $row;
		$textarea->width   = $width;
		$textarea->height  = $height;
		$textarea->content = $content;

		$editor = '<div class="editor">';
		$editor .= JLayoutHelper::render('joomla.tinymce.textarea', $textarea);
		$editor .= $this->_displayButtons($id, $buttons, $asset, $author);
		$editor .= $this->_toogleButton($id);
		$editor .= '</div>';

		return $editor;
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     The editor name
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 * @param   string  $asset    The object asset
	 * @param   object  $author   The author.
	 *
	 * @return  string HTML
	 */
	private function _displayButtons($name, $buttons, $asset, $author)
	{
		$return = '';

		$args = array(
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		);

		$results = (array) $this->update($args);

		if ($results)
		{
			foreach ($results as $result)
			{
				if (is_string($result) && trim($result))
				{
					$return .= $result;
				}
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);
			$return .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return $return;
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
}
