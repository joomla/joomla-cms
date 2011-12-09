<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Joomla! Debug plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.debug
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
		if (JDEBUG
		|| JFactory::getApplication()->getCfg('debug_lang'))
		{
			JFactory::getConfig()->set('gzip', 0);
			ob_start();
			ob_implicit_flush(false);
		}

		$this->linkFormat = ini_get('xdebug.file_link_format');
	}

	/**
	 * Show the debug info
	 */
	public function __destruct()
	{
		// Do not render if debugging or language debug is not enabled
		if (!JDEBUG
		&& ! JFactory::getApplication()->getCfg('debug_lang'))
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

		// Some "eyeprotecting" CSS
		$html .= '<style>
		#system-debug { background-color: #fff; border: 1px dashed silver;}
		div.dbgHeader { background-color: #cccccc; border: 1px solid #eee; font-size: 16px; }
		div.dbgHeader:hover { background-color: #fff; border: 1px solid silver; cursor: pointer; }
		div.dbgContainer { padding: 0.5em; }
		span.dbgCommand { color: blue; }
		span.dbgTable { color: green; }
		b.dbgOperator { color: orange; }
		#system-debug h1 { background-color: black; color: lime; padding: 0.3em; font-family: monospace; margin: 0; }
		</style>';

		// Some "mousewheel protecting" JS
		$html .= "<script>function toggleContainer(name) {
			var e = document.getElementById(name);// MooTools might not be available ;)
			e.style.display =(e.style.display == 'none') ? 'block' : 'none';
		}</script>";

		$html .= '<div id="system-debug" class="profiler">';

		$html .= '<h1>'.JText::_('PLG_DEBUG_TITLE').'</h1>';

		if (JDEBUG)
		{
			if (JError::getErrors())
			{
				$html .= $this->display('errors');
			}

			//$html .= print_r($data[$l], 1);

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
				$html .= $this->display('language_files_in_error');
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

		echo str_replace('</body>', $html.'</body>', $contents);
	}

	/**
	 * General display method.
	 *
	 * @param   string  $item  The item to display
	 *
	 * @return string
	 */
	protected function display($item)
	{
		$title = JText::_('PLG_DEBUG_'.strtoupper($item));

		$fncName = 'display'.ucfirst(str_replace('_', '', $item));

		if ( ! method_exists($this, $fncName))
		{
			return __METHOD__.' -- Unknown method: '.$fncName.'<br />';
		}

		$html = '';

		$js = "toggleContainer('dbgContainer".$item."');";

		$html .= '<div class="dbgHeader" onclick="'.$js.'">'.$title.'</div>';

		$style = ' style="display: none;"';//@todo set with js.. ?

		$html .= '<div '.$style.' class="dbgContainer" id="dbgContainer'.$item.'">';
		$html .= $this->$fncName();
		$html .= '</div>';

		return $html;
	}

	/**
	 * Display session information.
	 *
	 * Called recursive.
	 *
	 * @param  string   $key      A session key
	 * @param  mixed    $session  The session array, initially null
	 * @param  integer  $id       The id is used for JS toggling the div
	 *
	 * @return string
	 */
	protected function displaySession($key = '', $session = null, $id = 0)
	{
		if( ! $session) $session = $_SESSION;

		static $html = '';

		if( ! is_array($session))
		{
			$html .= $key.' &rArr;'.$session.PHP_EOL;
		}
		else
		{
			foreach ($session as $sKey => $entries)
			{
				if(is_array($entries))
				{
					$js = "toggleContainer('dbgContainer_session".$id."');";

					$html .= '<div class="dbgHeader" onclick="'.$js.'">'.$sKey.'</div>';

					$style = ' style="display: none;"';//@todo set with js.. ?

					$html .= '<div '.$style.' class="dbgContainer" id="dbgContainer_session'.$id.'">';
					$id ++;
					$this->displaySession($sKey, $entries, $id);
					$html .= '</div>';
					continue;
				}

				$html .= $sKey.' &rArr; '.$entries.'<br />';
			}
		}

		return $html;
	}

	/**
	 * Display errors.
	 *
	 * @return string
	 */
	protected function displayErrors()
	{
		$html = '';

		$html .= '<ol>';

		while ($error = JError::getError(true))
		{
			$col =(E_WARNING == $error->get('level')) ? 'red' : 'orange';

			$html .= '<li>';
			$html .= '<b style="color: '.$col.'">'.$error->getMessage().'</b><br />';

			$info = $error->get('info');

			if ($info)
			{
				$html .= '<pre>'.print_r($info, true).'</pre><br />';
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
	 * @return string
	 */
	protected function displayProfileInformation()
	{
		$html = '';

		foreach (JProfiler::getInstance('Application')->getBuffer() as $mark)
		{
			$html .= '<div>'.$mark.'</div>';
		}

		return $html;
	}

	/**
	 * Display memory usage
	 *
	 * @return string
	 */
	protected function displayMemoryUsage()
	{
		$html = '';

		$bytes = JProfiler::getInstance('Application')->getMemory();

		$html .= JHtml::_('number.bytes', $bytes);
		$html .= ' ('.number_format($bytes).' Bytes)';

		return $html;
	}

	/**
	 * Display logged queries.
	 *
	 * @return string
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

		$html .= '<h4>'.JText::sprintf('PLG_DEBUG_QUERIES_LOGGED',  $db->getCount()).'</h4>';

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

			$html .= '<li>'.$text.'</li>';
		}

		$html .= '</ol>';

		if ( ! $this->params->get('query_types', 1))
		{
			return $html;
		}

		// Get the totals for the query types:
		$totalSelectQueryTypes = count($selectQueryTypeTicker);
		$totalOtherQueryTypes = count($otherQueryTypeTicker);
		$totalQueryTypes = $totalSelectQueryTypes + $totalOtherQueryTypes;

		$html .= '<h4>'.JText::sprintf('PLG_DEBUG_QUERY_TYPES_LOGGED', $totalQueryTypes) . '</h4>';

		if ($totalSelectQueryTypes)
		{
			$html .= '<h5>'.JText::sprintf('PLG_DEBUG_SELECT_QUERIES').'</h5>';

			arsort($selectQueryTypeTicker);

			$html .= '<ol>';

			foreach ($selectQueryTypeTicker as $query => $occurrences)
			{
				$html .= '<li>'.JText::sprintf('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES'
				, $this->highlightQuery($query), $occurrences).'</li>';
			}

			$html .= '</ol>';
		}

		if ($totalOtherQueryTypes)
		{
			$html .= '<h5>'.JText::sprintf('PLG_DEBUG_OTHER_QUERIES').'</h5>';

			arsort($otherQueryTypeTicker);

			$html .= '<ol>';

			foreach ($otherQueryTypeTicker as $query => $occurrences)
			{
				$html .= '<li>'.JText::sprintf('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES'
				, $this->highlightQuery($query), $occurrences).'</li>';
			}
			$html .= '</ol>';
		}

		return $html;
	}

	/**
	 * Displays errors in language files.
	 *
	 * @return string
	 */
	protected function displayLanguageFilesInError()
	{
		$html = '';

		$errorfiles = JFactory::getLanguage()->getErrorFiles();

		if ( ! count($errorfiles))
		{
			return JText::_('JNONE');
		}

		$html .= '<ul>';

		foreach ($errorfiles as $file => $error)
		{
			$html .= '<li>'.$this->formatLink($file)
			.str_replace($file, '', $error).'</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Display loaded language files.
	 *
	 * @return string
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
	 * @return string
	 */
	protected function displayUntranslatedStrings()
	{
		$stripFirst	= $this->params->get('strip-first');
		$stripPref	= $this->params->get('strip-prefix');
		$stripSuff	= $this->params->get('strip-suffix');

		$orphans = JFactory::getLanguage()->getOrphans();

		if ( ! count($orphans))
		{
			return JText::_('JNONE');
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
						$guess = trim(preg_replace(chr(1).'^'.$stripPref.chr(1).'i', '', $guess));
					}

					if ($stripSuff)
					{
						$guess = trim(preg_replace(chr(1).$stripSuff.'$'.chr(1).'i', '', $guess));
					}
				}

				$key = trim(strtoupper($key));
				$key = preg_replace('#\s+#', '_', $key);
				$key = preg_replace('#\W#', '', $key);

				// Prepare the text
				$guesses[$file][] = $key.'="'.$guess.'"';
			}
		}

		$html = '';

		foreach ($guesses as $file => $keys)
		{
			$html .= "\n\n# ".($file ? $this->formatLink($file) : JText::_('PLG_DEBUG_UNKNOWN_FILE'))."\n\n";
			$html .= implode("\n", $keys);
		}

		return '<pre>'.$html.'</pre>';
	}

	/**
	 * Simple highlight for SQL queries.
	 *
	 * @param   string  $sql  The query to highlight
	 *
	 * @return string
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
		'/('.JFactory::getDbo()->getPrefix().'[a-z_0-9]+)/'
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

			for ($i = count($backtrace) - 1; $i >= 0 ; $i--)
			{
				$link = '&#160;';

				if (isset($backtrace[$i]['file']))
				{
					$link = $this->formatLink($backtrace[$i]['file'], $backtrace[$i]['line']);
				}

				$html .= '<tr>';
				$html .= '<td>'.$j.'</td>';

				if (isset($backtrace[$i]['class']))
				{
					$html .= '<td>'.$backtrace[$i]['class'].$backtrace[$i]['type'].$backtrace[$i]['function'].'()</td>';
				}
				else
				{
					$html .= '<td>'.$backtrace[$i]['function'].'()</td>';
				}

				$html .= '<td>'.$link.'</td>';

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
	 * @return string
	 */
	protected function formatLink($file, $line = '')
	{
		$link = str_replace(JPATH_ROOT, 'JROOT', $file);
		$link .=($line) ? ':'.$line : '';

		if ($this->linkFormat)
		{
			$href = $this->linkFormat;
			$href = str_replace('%f', $file, $href);
			$href = str_replace('%l', $line, $href);

			$html = '<a href="'.$href.'">'.$link.'</a>';
		}
		else
		{
			$html = $link;
		}

		return $html;
	}

}
