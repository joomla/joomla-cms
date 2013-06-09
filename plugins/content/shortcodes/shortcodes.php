<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Shortcodes
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Shortcodes Content Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Shortcodes
 * @since       3.2
 */
class PlgContentShortcodes extends JPlugin
{
	/**
	 * Container for storing shortcode tags and their hook to call for the shortcode.
	 *
	 * @since   3.2
	 * @var     array
	 */
	protected $shortcode_tags = array();

	/**
	 * Plugin that loads module positions within content.
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available.
	 * @param   object   &$params   The article params.
	 * @param   integer  $page      The 'page' number.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Get the event dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Load the shortcode plugin group.
		JPluginHelper::importPlugin('shortcode');

		// Trigger the onShortcodePrepare event.
		$dispatcher->trigger('onShortcodePrepare', array($context, &$article, &$params));
	}

	/**
	 * Add hook for shortcode tag.
	 *
	 * @param   string    $tag   Shortcode tag to be searched in post content.
	 * @param   callable  $func  Hook to run when shortcode is found.
	 *
	 * @return  boolean   True on success.
	 *
	 * @since   3.2
	 */
	public function addShortcode($tag, $func)
	{
		if (is_callable($func))
		{
			$this->shortcode_tags[$tag] = $func;

			return true;
		}

		return false;
	}

	/**
	 * Whether the passed content contains the specified shortcode.
	 *
	 * @param   string  $content  Content to search for shortcodes.
	 * @param   string  $tag      Shortcode tag.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function hasShortcode($content, $tag)
	{
		if ($this->shortcodeExists($tag))
		{
			preg_match_all('/' . $this->getShortcodeRegex() . '/s', $content, $matches, PREG_SET_ORDER);

			if (empty($matches))
			{
				return false;
			}

			foreach ($matches as $shortcode)
			{
				if ($tag === $shortcode[2])
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Search content for shortcodes and filter shortcodes through their hooks.
	 *
	 * @param   string  $content  Content to search for shortcodes.
	 *
	 * @return  string  Content with shortcodes filtered out.
	 *
	 * @since   3.2
	 */
	public function doShortcode($content)
	{
		if (empty($this->shortcode_tags) || !is_array($this->shortcode_tags))
		{
			return $content;
		}

		$pattern = $this->getShortcodeRegex();

		return preg_replace_callback("/$pattern/s", array($this, 'doShortcodeTag'), $content);
	}

	/**
	 * Retrieve the shortcode regular expression for searching.
	 *
	 * @return  string  The shortcode search regular expression.
	 *
	 * @since   3.2
	 */
	public function getShortcodeRegex()
	{
		// Initialiase variables.
		$tagnames  = array_keys($this->shortcode_tags);
		$tagregexp = join('|', array_map('preg_quote', $tagnames));

		return '\\[(\\[?)' . "($tagregexp)" . '(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
	}

	/**
	 * Regular Expression callable for Shortcodes::doShortcode() for calling shortcode hook.
	 *
	 * @param   array  $matches  Regular expression match array.
	 *
	 * @return  mixed  False on failure.
	 *
	 * @since   3.2
	 */
	public function doShortcodeTag($matches)
	{
		// Allow [[foo]] syntax for escaping a tag.
		if ($matches[1] == '[' && $matches[6] == ']')
		{
			return substr($matches[0], 1, -1);
		}

		// Initialiase variables.
		$tag = $matches[2];
		$this->shortcodeParseAtts($matches[3]);

		// If using open & closing tags. Ex: [foo]content[/foo]
		if (isset($matches[5]))
		{
			return $matches[1] . call_user_func($this->shortcode_tags[$tag], $matches[5]) . $matches[6];
		}
		// Self-closing tag. Ex: [foo bar="baz"]
		else
		{
			return $matches[1] . call_user_func($this->shortcode_tags[$tag], null) . $matches[6];
		}
	}

	/**
	 * Retrieve all attributes from the shortcodes tag.
	 *
	 * @param   string   $text  Text to search for attributes.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function shortcodeParseAtts($text)
	{
		// Initialiase variables.
		$atts    = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text    = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);

		if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				if (!empty($match[1]))
				{
					$atts[strtolower($match[1])] = stripcslashes($match[2]);
				}
				elseif (!empty($match[3]))
				{
					$atts[strtolower($match[3])] = stripcslashes($match[4]);
				}
				elseif (!empty($match[5]))
				{
					$atts[strtolower($match[5])] = stripcslashes($match[6]);
				}
				elseif (isset($match[7]) and strlen($match[7]))
				{
					$atts[] = stripcslashes($match[7]);
				}
				elseif (isset($match[8]))
				{
					$atts[] = stripcslashes($match[8]);
				}
			}
		}

		// Merge tag attributes with default config.
		$this->params->loadArray($atts);

		return true;
	}

	/**
	 * Combine user attributes with known attributes and fill in defaults when needed.
	 *
	 * @param   array  $pairs  Entire list of supported attributes and their defaults.
	 * @param   array  $atts   User defined attributes in shortcode tag.
	 *
	 * @return  array  Combined and filtered attribute list.
	 *
	 * @since   3.2
	 */
	public function shortcodeAtts($pairs, $atts)
	{
		// Initialiase variables.
		$atts = (array) $atts;
		$out  = array();

		foreach ($pairs as $name => $default)
		{
			if (array_key_exists($name, $atts))
			{
				$out[$name] = $atts[$name];
			}
			else
			{
				$out[$name] = $default;
			}
		}

		return $out;
	}
}
