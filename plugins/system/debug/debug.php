<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Joomla! Debug plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 * @since       1.5
 */
class plgSystemDebug extends JPlugin
{
	protected $linkFormat = '';

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since 1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Log the deprecated API.
		if ($this->params->get('log-deprecated'))
		{
			JLog::addLogger(array('text_file' => 'deprecated.php'), JLog::ALL, array('deprecated'));
		}

		// Only if debugging or language debug is enabled
		if (JDEBUG || JFactory::getApplication()->getCfg('debug_lang'))
		{
			JFactory::getConfig()->set('gzip', 0);
			ob_start();
			ob_implicit_flush(false);
		}

		$this->linkFormat = ini_get('xdebug.file_link_format');
	}

	/**
	 * Add the CSS for debug. We can't do this in the constructor because
	 * stuff breaks.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterDispatch()
	{
		// Only if debugging or language debug is enabled
		if (JDEBUG || JFactory::getApplication()->getCfg('debug_lang'))
		{
			JHtml::_('stylesheet', 'cms/debug.css', array(), true);
		}
	}

	/**
	 * Show the debug info
	 *
	 * @since  1.6
	 */
	public function __destruct()
	{
		// Do not render if debugging or language debug is not enabled
		if (!JDEBUG && !JFactory::getApplication()->getCfg('debug_lang'))
		{
			return;
		}

		// Load the language
		$this->loadLanguage();

		// Capture output
		$contents = ob_get_contents();
		ob_end_clean();

		// No debug for Safari and Chrome redirection
		if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'webkit') !== false
			&& substr($contents, 0, 50) == '<html><head><meta http-equiv="refresh" content="0;')
		{
			echo $contents;
			return;
		}

		// Only render for HTML output
		if ('html' !== JFactory::getDocument()->getType())
		{
			echo $contents;
			return;
		}

		// If the user is not allowed to view the output then end here
		$filterGroups = (array) $this->params->get('filter_groups', null);

		if (!empty($filterGroups))
		{
			$userGroups = JFactory::getUser()->get('groups');

			if (!array_intersect($filterGroups, $userGroups))
			{
				echo $contents;
				return;
			}
		}

		// Load language file
		$this->loadLanguage('plg_system_debug');

		$html = '';

		// Some "mousewheel protecting" JS
		$html .= "<script>function toggleContainer(name) {
			var e = document.getElementById(name);// MooTools might not be available ;)
			e.style.display = (e.style.display == 'none') ? 'block' : 'none';
		}</script>";

		$html .= '<div id="system-debug" class="profiler">';

		$html .= '<h1>' . JText::_('PLG_DEBUG_TITLE') . '</h1>';

		if (JDEBUG)
		{
			if (JError::getErrors())
			{
				$html .= $this->display('errors');
			}

			$html .= $this->display('session');

			if ($this->params->get('profile', 1))
			{
				$html .= $this->display('profile_information');
			}

			if ($this->params->get('memory', 1))
			{
				$html .= $this->display('memory_usage');
			}

			if ($this->params->get('queries', 1))
			{
				$html .= $this->display('queries');
			}
		}

		if (JFactory::getApplication()->getCfg('debug_lang'))
		{
			if ($this->params->get('language_errorfiles', 1))
			{
				$languageErrors = JFactory::getLanguage()->getErrorFiles();
				$html .= $this->display('language_files_in_error', $languageErrors);
			}

			if ($this->params->get('language_files', 1))
			{
				$html .= $this->display('language_files_loaded');
			}

			if ($this->params->get('language_strings'))
			{
				$html .= $this->display('untranslated_strings');
			}
		}

		$html .= '</div>';

		echo str_replace('</body>', $html . '</body>', $contents);
	}

	/**
	 * General display method.
	 *
	 * @param   string  $item    The item to display
	 * @param   array   $errors  Errors occured during execution
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function display($item, array $errors = array())
	{
		$title = JText::_('PLG_DEBUG_' . strtoupper($item));

		$status = '';

		if (count($errors))
		{
			$status = ' dbgerror';
		}

		$fncName = 'display' . ucfirst(str_replace('_', '', $item));

		if (!method_exists($this, $fncName))
		{
			return __METHOD__ . ' -- Unknown method: ' . $fncName . '<br />';
		}

		$html = '';

		$js = "toggleContainer('dbgContainer" . $item . "');";

		$class = 'dbgHeader' . $status;

		$html .= '<div class="' . $class . '" onclick="' . $js . '"><a href="javascript:void(0);"><h3>' . $title . '</h3></a></div>';

		// @todo set with js.. ?
		$style = ' style="display: none;"';

		$html .= '<div ' . $style . ' class="dbgContainer" id="dbgContainer' . $item . '">';
		$html .= $this->$fncName();
		$html .= '</div>';

		return $html;
	}

	/**
	 * Display session information.
	 *
	 * Called recursive.
	 *
	 * @param   string   $key      A session key
	 * @param   mixed    $session  The session array, initially null
	 * @param   integer  $id       The id is used for JS toggling the div
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displaySession($key = '', $session = null, $id = 0)
	{
		if (!$session)
		{
			$session = $_SESSION;
		}

		static $html = '';
		static $id;

		if (!is_array($session))
		{
			$html .= $key . ' &rArr;' . $session . PHP_EOL;
		}
		else
		{
			foreach ($session as $sKey => $entries)
			{
				$display = true;

				if (is_array($entries) && $entries)
				{
					$display = false;
				}

				if (is_object($entries))
				{
					$o = JArrayHelper::fromObject($entries);

					if ($o)
					{
						$entries = $o;
						$display = false;
					}
				}

				if (!$display)
				{
					$js = "toggleContainer('dbgContainer_session" . $id . "');";

					$html .= '<div class="dbgHeader" onclick="' . $js . '"><a href="javascript:void(0);"><h3>' . $sKey . '</h3></a></div>';

					// @todo set with js.. ?
					$style = ' style="display: none;"';

					$html .= '<div ' . $style . ' class="dbgContainer" id="dbgContainer_session' . $id . '">';
					$id ++;

					// Recurse...
					$this->displaySession($sKey, $entries, $id);

					$html .= '</div>';

					continue;
				}

				if (is_array($entries))
				{
					$entries = implode($entries);
				}

				$html .= '<code>';
				$html .= $sKey . ' &rArr; ' . $entries . '<br />';
				$html .= '</code>';
			}
		}

		return $html;
	}

	/**
	 * Display errors.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displayErrors()
	{
		$html = '';

		$html .= '<ol>';

		while ($error = JError::getError(true))
		{
			$col = (E_WARNING == $error->get('level')) ? 'red' : 'orange';

			$html .= '<li>';
			$html .= '<b style="color: ' . $col . '">' . $error->getMessage() . '</b><br />';

			$info = $error->get('info');

			if ($info)
			{
				$html .= '<pre>' . print_r($info, true) . '</pre><br />';
			}

			$html .= $this->renderBacktrace($error);
			$html .= '</li>';
		}

		$html .= '</ol>';

		return $html;
	}

	/**
	 * Display profile information.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displayProfileInformation()
	{
		$html = '';

		foreach (JProfiler::getInstance('Application')->getBuffer() as $mark)
		{
			$html .= '<div>' . $mark . '</div>';
		}

		return $html;
	}

	/**
	 * Display memory usage
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displayMemoryUsage()
	{
		$html = '';

		$bytes = JProfiler::getInstance('Application')->getMemory();

		$html .= '<code>';
		$html .= JHtml::_('number.bytes', $bytes);
		$html .= ' (' . number_format($bytes) . ' Bytes)';
		$html .= '</code>';

		return $html;
	}

	/**
	 * Display logged queries.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displayQueries()
	{
		$db	= JFactory::getDbo();

		$log = $db->getLog();

		if ( ! $log)
		{
			return;
		}

		$html = '';

		$html .= '<h4>' . JText::sprintf('PLG_DEBUG_QUERIES_LOGGED',  $db->getCount()) . '</h4>';

		$html .= '<ol>';

		$selectQueryTypeTicker = array();
		$otherQueryTypeTicker = array();

		foreach ($log as $k => $sql)
		{
			// Start Query Type Ticker Additions
			$fromStart = stripos($sql, 'from');
			$whereStart = stripos($sql, 'where', $fromStart);

			if ($whereStart === false)
			{
				$whereStart = stripos($sql, 'order by', $fromStart);
			}

			if ($whereStart === false)
			{
				$whereStart = strlen($sql) - 1;
			}

			$fromString = substr($sql, 0, $whereStart);
			$fromString = str_replace("\t", " ", $fromString);
			$fromString = str_replace("\n", " ", $fromString);
			$fromString = trim($fromString);

			// Initialize the select/other query type counts the first time:
			if (!isset($selectQueryTypeTicker[$fromString]))
			{
				$selectQueryTypeTicker[$fromString] = 0;
			}

			if (!isset($otherQueryTypeTicker[$fromString]))
			{
				$otherQueryTypeTicker[$fromString] = 0;
			}

			// Increment the count:
			if (stripos($sql, 'select') === 0)
			{
				$selectQueryTypeTicker[$fromString] = $selectQueryTypeTicker[$fromString] + 1;
				unset($otherQueryTypeTicker[$fromString]);
			}
			else
			{
				$otherQueryTypeTicker[$fromString] = $otherQueryTypeTicker[$fromString] + 1;
				unset($selectQueryTypeTicker[$fromString]);
			}

			$text = $this->highlightQuery($sql);

			$html .= '<li><code>' . $text . '</code></li>';
		}

		$html .= '</ol>';

		if (!$this->params->get('query_types', 1))
		{
			return $html;
		}

		// Get the totals for the query types:
		$totalSelectQueryTypes = count($selectQueryTypeTicker);
		$totalOtherQueryTypes = count($otherQueryTypeTicker);
		$totalQueryTypes = $totalSelectQueryTypes + $totalOtherQueryTypes;

		$html .= '<h4>' . JText::sprintf('PLG_DEBUG_QUERY_TYPES_LOGGED', $totalQueryTypes) . '</h4>';

		if ($totalSelectQueryTypes)
		{
			$html .= '<h5>' . JText::sprintf('PLG_DEBUG_SELECT_QUERIES') . '</h5>';

			arsort($selectQueryTypeTicker);

			$html .= '<ol>';

			foreach ($selectQueryTypeTicker as $query => $occurrences)
			{
				$html .= '<li><code>'
				. JText::sprintf('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES', $this->highlightQuery($query), $occurrences)
				. '</code></li>';
			}

			$html .= '</ol>';
		}

		if ($totalOtherQueryTypes)
		{
			$html .= '<h5>' . JText::sprintf('PLG_DEBUG_OTHER_QUERIES') . '</h5>';

			arsort($otherQueryTypeTicker);

			$html .= '<ol>';

			foreach ($otherQueryTypeTicker as $query => $occurrences)
			{
				$html .= '<li><code>'
				. JText::sprintf('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES', $this->highlightQuery($query), $occurrences)
				. '</code></li>';
			}
			$html .= '</ol>';
		}

		return $html;
	}

	/**
	 * Displays errors in language files.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displayLanguageFilesInError()
	{
		$html = '';

		$errorfiles = JFactory::getLanguage()->getErrorFiles();

		if (!count($errorfiles))
		{
			$html .= '<p>' . JText::_('JNONE') . '</p>';

			return $html;
		}

		$html .= '<ul>';

		foreach ($errorfiles as $file => $error)
		{
			$html .= '<li>' . $this->formatLink($file) . str_replace($file, '', $error) . '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Display loaded language files.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displayLanguageFilesLoaded()
	{
		$html = '';

		$html .= '<ul>';

		foreach (JFactory::getLanguage()->getPaths() as $extension => $files)
		{
			foreach ($files as $file => $status)
			{
				$html .= '<li>';

				$html .= ($status)
				? JText::_('PLG_DEBUG_LANG_LOADED')
				: JText::_('PLG_DEBUG_LANG_NOT_LOADED');

				$html .= ' : ';
				$html .= $this->formatLink($file);
				$html .= '</li>';
			}
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Display untranslated language strings.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function displayUntranslatedStrings()
	{
		$stripFirst	= $this->params->get('strip-first');
		$stripPref	= $this->params->get('strip-prefix');
		$stripSuff	= $this->params->get('strip-suffix');

		$orphans = JFactory::getLanguage()->getOrphans();

		$html = '';

		if ( ! count($orphans))
		{
			$html .= '<p>' . JText::_('JNONE') . '</p>';

			return $html;
		}

		ksort($orphans, SORT_STRING);

		$guesses = array();

		foreach ($orphans as $key => $occurance)
		{
			if (is_array($occurance) && isset($occurance[0]))
			{
				$info = $occurance[0];
				$file = ($info['file']) ? $info['file'] : '';

				if (!isset($guesses[$file]))
				{
					$guesses[$file] = array();
				}

				// Prepare the key

				if (($pos = strpos($info['string'], '=')) > 0)
				{
					$parts	= explode('=', $info['string']);
					$key	= $parts[0];
					$guess	= $parts[1];
				}
				else
				{
					$guess = str_replace('_', ' ', $info['string']);

					if ($stripFirst)
					{
						$parts = explode(' ', $guess);
						if (count($parts) > 1)
						{
							array_shift($parts);
							$guess = implode(' ', $parts);
						}
					}

					$guess = trim($guess);

					if ($stripPref)
					{
						$guess = trim(preg_replace(chr(1) . '^' . $stripPref . chr(1) . 'i', '', $guess));
					}

					if ($stripSuff)
					{
						$guess = trim(preg_replace(chr(1) . $stripSuff . '$' . chr(1) . 'i', '', $guess));
					}
				}

				$key = trim(strtoupper($key));
				$key = preg_replace('#\s+#', '_', $key);
				$key = preg_replace('#\W#', '', $key);

				// Prepare the text
				$guesses[$file][] = $key . '="' . $guess . '"';
			}
		}

		foreach ($guesses as $file => $keys)
		{
			$html .= "\n\n# " . ($file ? $this->formatLink($file) : JText::_('PLG_DEBUG_UNKNOWN_FILE')) . "\n\n";
			$html .= implode("\n", $keys);
		}

		return '<pre>' . $html . '</pre>';
	}

	/**
	 * Simple highlight for SQL queries.
	 *
	 * @param   string  $sql  The query to highlight
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function highlightQuery($sql)
	{
		$newlineKeywords = '#\b(FROM|LEFT|INNER|OUTER|WHERE|SET|VALUES|ORDER|GROUP|HAVING|LIMIT|ON|AND|CASE)\b#i';

		$sql = htmlspecialchars($sql, ENT_QUOTES);

		$sql = preg_replace($newlineKeywords, '<br />&#160;&#160;\\0', $sql);

		$regex = array(

		// Tables are identified by the prefix
		'/(=)/'
		=> '<b class="dbgOperator">$1</b>',

		// All uppercase words have a special meaning
		'/(?<!\w|>)([A-Z_]{2,})(?!\w)/x'
		=> '<span class="dbgCommand">$1</span>',

		// Tables are identified by the prefix
		'/(' . JFactory::getDbo()->getPrefix() . '[a-z_0-9]+)/'
		=> '<span class="dbgTable">$1</span>'

		);

		$sql = preg_replace(array_keys($regex), array_values($regex), $sql);

		$sql = str_replace('*', '<b style="color: red;">*</b>', $sql);

		return $sql;
	}

	/**
	 * Render the backtrace.
	 *
	 * Stolen from JError to prevent it's removal.
	 *
	 * @param   integer  $error  The error
	 *
	 * @return  string  Contents of the backtrace
	 *
	 * @since   2.5
	 */
	protected function renderBacktrace($error)
	{
		$backtrace = $error->getTrace();

		$html = '';

		if (is_array($backtrace))
		{
			$j = 1;

			$html .= '<table cellpadding="0" cellspacing="0">';

			$html .= '<tr>';
			$html .= '<td colspan="3"><strong>Call stack</strong></td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<th>#</th>';
			$html .= '<th>Function</th>';
			$html .= '<th>Location</th>';
			$html .= '</tr>';

			for ($i = count($backtrace) - 1; $i >= 0; $i--)
			{
				$link = '&#160;';

				if (isset($backtrace[$i]['file']))
				{
					$link = $this->formatLink($backtrace[$i]['file'], $backtrace[$i]['line']);
				}

				$html .= '<tr>';
				$html .= '<td>' . $j . '</td>';

				if (isset($backtrace[$i]['class']))
				{
					$html .= '<td>' . $backtrace[$i]['class'] . $backtrace[$i]['type'] . $backtrace[$i]['function'] . '()</td>';
				}
				else
				{
					$html .= '<td>' . $backtrace[$i]['function'] . '()</td>';
				}

				$html .= '<td>' . $link . '</td>';

				$html .= '</tr>';
				$j++;
			}

			$html .= '</table>';
		}

		return $html;
	}

	/**
	 * Replaces the Joomla! root with "JROOT" to improve readability.
	 * Formats a link with a special value xdebug.file_link_format
	 * from the php.ini file.
	 *
	 * @param   string  $file  The full path to the file.
	 * @param   string  $line  The line number.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	protected function formatLink($file, $line = '')
	{
		$link = str_replace(JPATH_ROOT, 'JROOT', $file);
		$link .= ($line) ? ':' . $line : '';

		if ($this->linkFormat)
		{
			$href = $this->linkFormat;
			$href = str_replace('%f', $file, $href);
			$href = str_replace('%l', $line, $href);

			$html = '<a href="' . $href . '">' . $link . '</a>';
		}
		else
		{
			$html = $link;
		}

		return $html;
	}

}
