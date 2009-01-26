<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Joomla! Debug plugin
 *
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemDebug extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since	1.6
	 */
	function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		//load the translation
		$this->loadLanguage();
	}

	/**
	* Converting the site URL to fit to the HTTP request
	*
	*/
	function onAfterRender()
	{
		global $_PROFILER;

		// Do not render if debugging is not enabled
		if(!JDEBUG) { return; }

		$document	=& JFactory::getDocument();
		$doctype	= $document->getType();

		// Only render for HTML output
		if ($doctype !== 'html') { return; }

		$profiler	=& $_PROFILER;

		ob_start();
		echo '<div id="system-debug" class="profiler">';
		$errors = JError::getErrors();
		if(!empty($errors)) {
			echo '<h4>'.JText::_('Errors').'</h4><ol>';
			while($error = JError::getError(true)) {
				echo '<li>'.$error->getMessage().'<br /><h4>'.JText::_('Info').'</h4><pre>'.print_r($error->get('info'), true).'</pre><br /><h4>'.JText::_('Backtrace').'</h4>'.JError::renderBacktrace($error).'</li>';
			}
			echo '</ol>';
		}
		if ($this->params->get('profile', 1)) {
			echo '<h4>'.JText::_('Profile Information').'</h4>';
			foreach ($profiler->getBuffer() as $mark) {
				echo '<div>'.$mark.'</div>';
			}
		}

		if ($this->params->get('memory', 1)) {
			echo '<h4>'.JText::_('Memory Usage').'</h4>';
			echo $profiler->getMemory();
		}

		if ($this->params->get('queries', 1))
		{

			$newlineKeywords = '#(FROM|LEFT|INNER|OUTER|WHERE|SET|VALUES|ORDER|GROUP|HAVING|LIMIT|ON|AND)#i';

			$db	=& JFactory::getDBO();

			echo '<h4>'.JText::sprintf('Queries logged',  $db->getTicker()).'</h4>';

			if ($log = $db->getLog())
			{
				echo '<ol>';
				foreach ($log as $k=>$sql)
				{
					$text = preg_replace($newlineKeywords, '<br />&nbsp;&nbsp;\\0', $sql);
					echo '<li>'.$text.'</li>';
				}
				echo '</ol>';
			}
		}

		$lang = &JFactory::getLanguage();
		if ($this->params->get('language_files', 1))
		{
			echo '<h4>'.JText::_('Language Files Loaded').'</h4>';
			echo '<ul>';
			$extensions	= $lang->getPaths();
			foreach ($extensions as $extension => $files)
			{
				foreach ($files as $file => $status)
				{
					echo "<li>$file $status</li>";
				}
			}
			echo '</ul>';
		}

		$langStrings = $this->params->get('language_strings', -1);
		if ($langStrings < 0 OR $langStrings == 1) {
			echo '<h4>'.JText::_('Untranslated Strings Diagnostic').'</h4>';
			echo '<pre>';
			$orphans = $lang->getOrphans();
			if (count($orphans))
			{
				ksort($orphans, SORT_STRING);
				foreach ($orphans as $key => $occurance) {
					foreach ($occurance as $i => $info) {
						$class	= @$info['class'];
						$func	= @$info['function'];
						$file	= @$info['file'];
						$line	= @$info['line'];
						echo strtoupper($key)."\t$class::$func()\t[$file:$line]\n";
					}
				}
			}
			else {
				echo JText::_('None');
			}
			echo '</pre>';
		}
		if ($langStrings < 0 OR $langStrings == 2) {
			echo '<h4>'.JText::_('Untranslated Strings Designer').'</h4>';
			echo '<pre>';
			$orphans = $lang->getOrphans();
			if (count($orphans))
			{
				ksort($orphans, SORT_STRING);
				$guesses = array();
				foreach ($orphans as $key => $occurance)
				{
					if (is_array($occurance) AND isset($occurance[0])) {
						$info = &$occurance[0];
						$file = @$info['file'];
						if (!isset($guesses[$file])) {
							$guesses[$file] = array();
						}

						$pos = strpos($info['string'], '_');
						$guess = str_replace('_', ' ', substr($info['string'], $pos > 0 ? $pos + 1 : 0));
						if ($strip = $this->params->get('language_prefix')) {
							$guess = trim(preg_replace(chr(1).'^'.$strip.chr(1), '', $guess));
						}
						$guesses[$file][] = trim(strtoupper($key)).'='.$guess;
					}
				}
				foreach ($guesses as $file => $keys) {
					echo "\n\n# ".($file ? $file : JText::_('Unknown file'))."\n\n";
					echo implode("\n", $keys);
				}
			}
			else {
				echo JText::_('None');
			}
			echo '</pre>';
		}
		echo '</div>';

		$debug = ob_get_clean();

		$body = JResponse::getBody();
		$body = str_replace('</body>', $debug.'</body>', $body);
		JResponse::setBody($body);
	}

}
