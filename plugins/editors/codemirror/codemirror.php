<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * CodeMirror Editor Plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	Editors.codemirror
 * @since		1.6
 */
class plgEditorCodemirror extends JPlugin
{
	/**
	 * Base path for editor files.
	 *
	 * @var string
	 */
	protected $basePath = 'media/editors/codemirror/';

	/**
	 * Mapping of syntax to CodeMirror modes.
	 *
	 * @var array
	 */
	protected $modeMap = array(
			'html' => 'htmlmixed',
			'ini'  => 'properties'
		);

	/**
	 * Mapping of CodeMirror dependencies.
	 *
	 * @var array
	 */
	protected $dependency = array(
			'addon' => array(
					'edit.matchtags' => array('fold.xml-fold')
				),
			'mode'  => array(
					'htmlmixed'    => array('xml', 'css', 'javascript'),
					'htmlembedded' => array('htmlmixed'),
					'php'          => array('clike', 'htmlembedded')
				)
		);

	/**
	 * Initialises the Editor.
	 *
	 * @return	string	JavaScript Initialization string.
	 */
	public function onInit()
	{
		JHtml::_('behavior.framework');
		JHtml::_('script', $this->basePath . 'lib/codemirror.js');
		JHtml::_('stylesheet', $this->basePath . 'lib/codemirror.css');

		return '';
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param	string	$id	The id of the editor field.
	 *
	 * @return string Javascript
	 */
	public function onSave($id)
	{
		return sprintf('document.getElementById(%1$s).value = Joomla.editors.instances[%1$s].getValue();', json_encode((string) $id));
	}

	/**
	 * Get the editor content.
	 *
	 * @param	string	$id	The id of the editor field.
	 *
	 * @return string Javascript
	 */
	public function onGetContent($id)
	{
		return sprintf('Joomla.editors.instances[%1$s].getValue();', json_encode((string) $id));
	}

	/**
	 * Set the editor content.
	 *
	 * @param	string	$id			The id of the editor field.
	 * @param	string	$content	The content to set.
	 *
	 * @return string Javascript
	 */
	public function onSetContent($id, $content)
	{
		return sprintf('Joomla.editors.instances[%1$s].setValue(%2$s);', json_encode((string) $id), json_encode((string) $content));
	}

	/**
	 * Adds the editor specific insert method.
	 *
	 * @return boolean
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

		$js = ";function jInsertEditorText(text, editor) { Joomla.editors.instances[editor].replaceSelection(text); }\n";
		JFactory::getDocument()->addScriptDeclaration($js);

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param	string	$name		The control name.
	 * @param	string	$html		The contents of the text area.
	 * @param	string	$width		The width of the text area (px or %).
	 * @param	string	$height		The height of the text area (px or %).
	 * @param	int		$col		The number of columns for the textarea.
	 * @param	int		$row		The number of rows for the textarea.
	 * @param	boolean	$buttons	True and the editor buttons will be displayed.
	 * @param	string	$id			An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param	string	$asset
	 * @param	object	$author
	 * @param	array	$params		Associative array of editor parameters.
	 *
	 * @return string HTML
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$id = empty($id) ? $name : $id;

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->displayButtons($id, $buttons, $asset, $author);

		// Only add "px" to width and height if they are not given as a percentage.
		$width .= is_numeric($width) ? 'px' : '';
		$height .= is_numeric($height) ? 'px' : '';

		// Options for the CodeMirror constructor.
		$options = new stdClass;

		// Extra styles to add to the document.
		$extraStyles = array('.CodeMirror {' . implode(' ', $this->getEditorStyles()) . '} ');

		// Until there's a fix for the overflow problem, always wrap lines.
		$options->lineWrapping = true;

		$options->lineNumbers = (boolean) $this->params->get('linenumbers', 0);

		// Load the syntax mode.
		$syntax = JFactory::getApplication()->getUserState('editor.source.syntax', 'html');
		$options->mode = isset($this->modeMap[$syntax]) ? $this->modeMap[$syntax] : $syntax;
		$this->loadFile($options->mode, 'mode');

		// Load the theme if specified.
		$theme = $this->params->get('theme');
		if ($theme)
		{
			$options->theme = $theme;
			$this->loadFile($options->theme, 'theme');
		}

		// Add styling to the active line (any mode).
		if ($this->params->get('activeline'))
		{
			$this->loadFile('selection.active-line', 'addon');
			$options->styleActiveLine = true;

			$color = $this->params->get('activeline_color', '#a4c2eb');
			$r = hexdec($color{1} . $color{2});
			$g = hexdec($color{3} . $color{4});
			$b = hexdec($color{5} . $color{6});
			$extraStyles[] = '.CodeMirror-activeline-background {background:rgba(' . $r . ', ' . $g . ', ' . $b . ', .5);}';
		}

		// Special options for tagged modes (xml/html).
		if (in_array($options->mode, array('xml', 'htmlmixed', 'htmlembedded', 'php')))
		{
			// Autogenerate closing tags (html/xml only).
			if ($this->params->get('autoclose', true))
			{
				$this->loadFile('edit.closetag', 'addon');
				$options->autoCloseTags = true;
			}

			// Hilight the matching tag when the cursor is in a tag (html/xml only).
			if ($this->params->get('hilight_match', true))
			{
				$this->loadFile('edit.matchtags', 'addon');
				$options->matchTags = true;

				$color = $this->params->get('hilight_match_color', '#fa542f');
				$r = hexdec($color{1} . $color{2});
				$g = hexdec($color{3} . $color{4});
				$b = hexdec($color{5} . $color{6});
				$extraStyles[] = '.CodeMirror-matchingtag {background:rgba(' . $r . ', ' . $g . ', ' . $b . ', .5);}';
			}
		}

		if (!in_array($options->mode, array('xml', 'htmlmixed', 'htmlembedded')))
		{
			// Autogenerate closing brackets.
			if ($this->params->get('autoclose', true))
			{
				$this->loadFile('edit.closebrackets', 'addon');
				$options->autoCloseBrackets = true;
			}

			// Hilight the matching tag when the cursor is in a tag (html/xml only).
			if ($this->params->get('hilight_match', true))
			{
				$this->loadFile('edit.matchbrackets', 'addon');
				$options->matchBrackets = true;
			}
		}

		$keybinding = $this->params->get('keybinding', 0);
		if ($keybinding)
		{
			$this->loadFile($keybinding, 'keymap');
			$options->keyMap = $keybinding;
			$options->{$keybinding . 'Mode'} = true;
		}

		JFactory::getDocument()->addStyleDeclaration(implode("\n", $extraStyles));

		$html = array();
		$html[]	= "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = '(function(id, options) {';
		$html[] = '    Joomla.editors.instances[id] = CodeMirror.fromTextArea(document.getElementById(id), options);';
		$html[] = '})(' . json_encode($id) . ', ' . json_encode($options) . ');';
		$html[] = '</script>';

		return implode("\n", $html);
	}

	/**
	 * Loads a CodeMirror supplementary file.
	 *
	 * @param  string  $file  The file (witout extension) to be included (In case of addons, it should be folder.file).
	 * @param  string  $type  The type of item.
	 */
	protected function loadFile($file, $type)
	{
		static $loaded = array(
				'addon'  => array(),
				'keymap' => array(),
				'mode'   => array(),
				'theme'  => array()
			);

		// If $type is not a valid type or $file is already loaded, return;
		if (!in_array($type, array_keys($loaded)) || in_array($file, $loaded[$type]))
		{
			return;
		}

		// Add the file to the loaded list.
		$loaded[$type][] = $file;

		$path = $this->getPath($file, $type);

		JHtml::_(($type == 'theme') ? 'stylesheet' : 'script', $path);

		if (isset($this->dependency[$type]) && isset($this->dependency[$type][$file]))
		{
			foreach ($this->dependency[$type][$file] as $required)
			{
				$this->loadFile($required, $type);
			}
		}

	}

	/**
	 * Gets the path to any supplementary file used by CodeMirror.
	 *
	 * @param   string  $file  The file to be included.
	 * @param   string  $type  The type of item.
	 *
	 * @return  string
	 */
	protected function getPath($file, $type)
	{
		$paths = array(
				'addon'  => 'addon/%1$s/%2$s.js',
				'keymap' => 'keymap/%1$s.js',
				'mode'   => 'mode/%1$s/%1$s.js',
				'theme'  => 'theme/%1$s.css'
			);

		$parts = ($type == 'addon') ? explode('.', $file, 2) : (array) $file;

		return $this->basePath . vsprintf($paths[$type], $parts);
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param string $name
	 * @param mixed $buttons [array with button objects | boolean true to display buttons]
	 *
	 * @return string HTML
	 */
	protected function displayButtons($name, $buttons, $asset, $author)
	{
		// Load modal popup behavior
		JHtml::_('behavior.modal', 'a.modal-button');

		$args['name'] = $name;
		$args['event'] = 'onGetInsertMethod';

		$html = array();
		$results[] = $this->update($args);

		foreach ($results as $result)
		{
			if (is_string($result) && trim($result))
			{
				$html[] = $result;
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons, $asset, $author);

			// This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			$html[] = '<div id="editor-xtd-buttons">';

			$format =  '<div class="button2-left"><div class="%6$s"><a %1$s title="%2$s" %3$s %4$s rel="%5$s">%6$s</a></div></div>';

			foreach ($results as $button)
			{
				// Results should be an object
				if ($button->get('name'))
				{
					$modal		= ($button->get('modal')) ? ' class="modal-button btn"' : null;
					$href		= ($button->get('link')) ? ' href="'.JURI::base().$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$title      = ($button->get('title')) ? $button->get('title') : $button->get('text');

					$html[] = sprintf($format,
								$modal, $title, $href, $onclick,
								$button->get('options'),
								$button->get('name'),
								$button->get('text')
							);
				}
			}

			$html[] = '</div>';
		}

		return implode("\n", $html);
	}

	/**
	 * Gets the url of a font stylesheet (from google web fonts) based on param values
	 *
	 * @return	string	$styleSheet a url (or empty string)
	 */
	protected function loadFontStyleSheet($font)
	{
		$loaded = array();

		// Load only once.
		if (in_array($font, $loaded))
		{
			return;
		}

		$loaded[] = $font;

		$google = '//fonts.googleapis.com/css?';

		$styleSheets = array(
			'anonymous_pro'   => 'family=Anonymous+Pro',
			'cousine'         => 'family=Cousine',
			'cutive_mono'     => 'family=Cutive+Mono',
			'droid_sans_mono' => 'family=Droid+Sans+Mono',
			'inconsolata'     => 'family=Inconsolata',
			'lekton'          => 'family=Lekton',
			'nova_mono'       => 'family=Nova+Mono',
			'oxygen_mono'     => 'family=Oxygen+Mono',
			'press_start_2p'  => 'family=Press+Start+2P',
			'pt_mono'         => 'family=PT+Mono',
			'share_tech_mono' => 'family=Share+Tech+Mono',
			'source_code_pro' => 'family=Source+Code+Pro',
			'ubuntu_mono'     => 'family=Ubuntu+Mono',
			'vt323'           => 'family=VT323'
		);

		$url = isset($styleSheets[$font]) ? $google . $styleSheets[$font] : '';

		if ($url)
		{
			JHtml::_('stylesheet', $url);
		}
}

	/**
	 * Gets style declarations for using the selected font, size, and line-height from params
	 * returning as array for json encoding
	 *
	 * @return	array
	 */
	protected function getEditorStyles()
	{
		$font = $this->params->get('font_family', 0);

		if ($font)
		{
			$this->loadFontStyleSheet($font);
		}

		$fonts = array(
			'anonymous_pro'   => '\'Anonymous Pro\', monospace',
			'cousine'         => 'Cousine, monospace',
			'cutive_mono'     => '\'Cutive Mono\', monospace',
			'droid_sans_mono' => '\'Droid Sans Mono\', monospace',
			'inconsolata'     => 'Inconsolata, monospace',
			'lekton'          => 'Tekton, monospace',
			'nova_mono'       => '\'Nova Mono\', monospace',
			'oxygen_mono'     => '\'Oxygen Mono\', monospace',
			'press_start_2p'  => '\'Press Start 2P\', monospace',
			'pt_mono'         => '\'PT Mono\', monospace',
			'share_tech_mono' => '\'Share Tech Mono\', monospace',
			'source_code_pro' => '\'Source Code Pro\', monospace',
			'ubuntu_mono'     => '\'Ubuntu Mono\', monospace',
			'vt323'           => 'VT323, monospace'
		);

		$styles = array(
			'font-family: ' . (isset($fonts[$font]) ? $fonts[$font] : 'monospace') .';',
			'font-size: '   . $this->params->get('font_size', 10) . 'px;',
			'line-height: ' . $this->params->get('line_height', 1.2) . 'em;',
			'border: '      . '1px solid #ccc;'
		);

		$min_height = $this->params->get('min_height', 0);
		if ($min_height && is_numeric($min_height))
		{
			$styles[] = 'min-height: ' . $min_height . 'px;';
		}

		return $styles;
	}

}
