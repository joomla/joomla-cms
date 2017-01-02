<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\Event\Event;

/**
 * CodeMirror Editor Plugin.
 *
 * @since  1.6
 */
class PlgEditorCodemirror extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  12.3
	 */
	protected $autoloadLanguage = true;

	/**
	 * Mapping of syntax to CodeMirror modes.
	 *
	 * @var array
	 */
	protected $modeAlias = array(
			'html' => 'htmlmixed',
			'ini'  => 'properties',
			'json' => array('name' => 'javascript', 'json' => true),
		);

	/**
	 * Initialises the Editor.
	 *
	 * @return  void
	 */
	public function onInit()
	{
		static $done = false;

		// Do this only once.
		if ($done)
		{
			return;
		}

		$done = true;

		// Most likely need this later
		$doc = JFactory::getDocument();

		// Codemirror shall have its own group of plugins to modify and extend its behavior
		$result = JPluginHelper::importPlugin('editors_codemirror');

		// At this point, params can be modified by a plugin before going to the layout renderer.
		JFactory::getApplication()->triggerEvent('onCodeMirrorBeforeInit', array(&$this->params));

		$displayData = (object) array('params'  => $this->params);

		// We need to do output buffering here because layouts may actually 'echo' things which we do not want.
		ob_start();
		JLayoutHelper::render('editors.codemirror.init', $displayData, __DIR__ . '/layouts');
		ob_end_clean();

		$font = $this->params->get('fontFamily', 0);
		$fontInfo = $this->getFontInfo($font);

		if (isset($fontInfo))
		{
			if (isset($fontInfo->url))
			{
				$doc->addStylesheet($fontInfo->url);
			}

			if (isset($fontInfo->css))
			{
				$displayData->fontFamily = $fontInfo->css . '!important';
			}
		}

		// We need to do output buffering here because layouts may actually 'echo' things which we do not want.
		ob_start();
		JLayoutHelper::render('editors.codemirror.styles', $displayData, __DIR__ . '/layouts');
		ob_end_clean();

		JFactory::getApplication()->triggerEvent('onCodeMirrorAfterInit', array(&$this->params));
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string  Javascript
	 */
	public function onSave($id)
	{
		return sprintf('document.getElementById(%1$s).value = Joomla.editors.instances[%1$s].getValue();', json_encode((string) $id));
	}

	/**
	 * Get the editor content.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string  Javascript
	 */
	public function onGetContent($id)
	{
		return sprintf('Joomla.editors.instances[%1$s].getValue();', json_encode((string) $id));
	}

	/**
	 * Set the editor content.
	 *
	 * @param   string  $id       The id of the editor field.
	 * @param   string  $content  The content to set.
	 *
	 * @return  string  Javascript
	 */
	public function onSetContent($id, $content)
	{
		return sprintf('Joomla.editors.instances[%1$s].setValue(%2$s);', json_encode((string) $id), json_encode((string) $content));
	}

	/**
	 * Adds the editor specific insert method.
	 *
	 * @return  boolean
	 */
	public function onGetInsertMethod()
	{
		static $done = false;

		// Do this only once.
		if ($done)
		{
			return true;
		}

		$done = true;

		JFactory::getDocument()->addScriptDeclaration("
		;function jInsertEditorText(text, editor) { Joomla.editors.instances[editor].replaceSelection(text); }
		");

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   int      $col      The number of columns for the textarea.
	 * @param   int      $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    Not used.
	 * @param   object   $author   Not used.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string  HTML
	 */
	public function onDisplay(
		$name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$id = empty($id) ? $name : $id;

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->displayButtons($id, $buttons, $asset, $author);

		// Only add "px" to width and height if they are not given as a percentage.
		$width .= is_numeric($width) ? 'px' : '';
		$height .= is_numeric($height) ? 'px' : '';

		// Options for the CodeMirror constructor.
		$options = new stdClass;

		// Should we focus on the editor on load?
		$options->autofocus = (boolean) $this->params->get('autoFocus', true);

		// Until there's a fix for the overflow problem, always wrap lines.
		$options->lineWrapping = true;

		// Add styling to the active line.
		$options->styleActiveLine = (boolean) $this->params->get('activeLine', true);

		// Add styling to the active line.
		if ($this->params->get('selectionMatches', false))
		{
			$options->highlightSelectionMatches = array(
					'showToken' => true,
					'annotateScrollbar' => true,
				);
		}

		// Do we use line numbering?
		if ($options->lineNumbers = (boolean) $this->params->get('lineNumbers', 0))
		{
			$options->gutters[] = 'CodeMirror-linenumbers';
		}

		// Do we use code folding?
		if ($options->foldGutter = (boolean) $this->params->get('codeFolding', 1))
		{
			$options->gutters[] = 'CodeMirror-foldgutter';
		}

		// Do we use a marker gutter?
		if ($options->markerGutter = (boolean) $this->params->get('markerGutter', $this->params->get('marker-gutter', 0)))
		{
			$options->gutters[] = 'CodeMirror-markergutter';
		}

		// Load the syntax mode.
		$syntax = $this->params->get('syntax', 'html');
		$options->mode = isset($this->modeAlias[$syntax]) ? $this->modeAlias[$syntax] : $syntax;

		// Load the theme if specified.
		if ($theme = $this->params->get('theme'))
		{
			$options->theme = $theme;
			JHtml::_('stylesheet', $this->params->get('basePath', 'media/editors/codemirror/') . 'theme/' . $theme . '.css', array('version' => 'auto'));
		}

		// Special options for tagged modes (xml/html).
		if (in_array($options->mode, array('xml', 'htmlmixed', 'htmlembedded', 'php')))
		{
			// Autogenerate closing tags (html/xml only).
			$options->autoCloseTags = (boolean) $this->params->get('autoCloseTags', true);

			// Highlight the matching tag when the cursor is in a tag (html/xml only).
			$options->matchTags = (boolean) $this->params->get('matchTags', true);
		}

		// Special options for non-tagged modes.
		if (!in_array($options->mode, array('xml', 'htmlmixed', 'htmlembedded')))
		{
			// Autogenerate closing brackets.
			$options->autoCloseBrackets = (boolean) $this->params->get('autoCloseBrackets', true);

			// Highlight the matching bracket.
			$options->matchBrackets = (boolean) $this->params->get('matchBrackets', true);
		}

		$options->scrollbarStyle = $this->params->get('scrollbarStyle', 'native');

		// Vim Keybindings.
		$options->vimMode = (boolean) $this->params->get('vimKeyBinding', 0);

		$displayData = (object) array(
				'options' => $options,
				'params'  => $this->params,
				'name'    => $name,
				'id'      => $id,
				'cols'    => $col,
				'rows'    => $row,
				'content' => $content,
				'buttons' => $buttons
			);

		// At this point, displayData can be modified by a plugin before going to the layout renderer.
		$results = JFactory::getApplication()->triggerEvent('onCodeMirrorBeforeDisplay', array(&$displayData));

		$results[] = JLayoutHelper::render('editors.codemirror.element', $displayData, __DIR__ . '/layouts', array('debug' => JDEBUG));

		foreach (JFactory::getApplication()->triggerEvent('onCodeMirrorAfterDisplay', array(&$displayData)) as $result)
		{
			$results[] = $result;
		}

		return implode("\n", $results);
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     Button name.
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 * @param   mixed   $asset    Unused.
	 * @param   mixed   $author   Unused.
	 *
	 * @return  string  HTML
	 */
	protected function displayButtons($name, $buttons, $asset, $author)
	{
		$return = '';

		$onGetInsertMethodEvent = new Event(
			'onGetInsertMethod',
			['name' => $name]
		);

		$rawResults = $this->getDispatcher()->dispatch('onGetInsertMethod', $onGetInsertMethodEvent);
		$results    = $rawResults['result'];

		if (is_array($results) && !empty($results))
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
			$buttonsEvent = new Event(
				'getButtons',
				[
					'name'    => $this->_name,
					'buttons' => $buttons,
				]
			);

			$buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
			$buttons       = $buttonsResult['result'];

			$return .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return $return;
	}

	/**
	 * Gets font info from the json data file
	 *
	 * @param   string  $font  A key from the $fonts array.
	 *
	 * @return  object
	 */
	protected function getFontInfo($font)
	{
		static $fonts;

		if (!$fonts)
		{
			$fonts = json_decode(file_get_contents(__DIR__ . '/fonts.json'), true);
		}

		return isset($fonts[$font]) ? (object) $fonts[$font] : null;
	}
}
