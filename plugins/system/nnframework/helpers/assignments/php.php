<?php
/**
 * NoNumber Framework Helper File: Assignments: PHP
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Assignments: PHP
 */
class NNFrameworkAssignmentsPHP
{
	var $_version = '12.6.4';

	/**
	 * passPHP
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passPHP(&$main, &$params, $selection = array(), $assignment = 'all', $article = 0)
	{
		if (!is_array($selection)) {
			$selection = array($selection);
		}

		$pass = 0;
		foreach ($selection as $php) {
			// replace \n with newline and other fix stuff
			$php = str_replace('\|', '|', $php);
			$php = preg_replace('#(?<!\\\)\\\n#', "\n", $php);
			$php = str_replace('[:REGEX_ENTER:]', '\n', $php);

			if (trim($php) == '') {
				$pass = 1;
				break;
			}

			if (!$article && !(strpos($php, '$article') === false) && $main->_params->option == 'com_content' && $main->_params->view == 'article') {
				require_once JPATH_SITE.'/components/com_content/models/article.php';
				$model = JModel::getInstance('article', 'contentModel');
				$article = $model->getItem($main->_params->id);
			}
			if (!isset($Itemid)) {
				$Itemid = JRequest::getInt('Itemid');
			}
			if (!isset($mainframe)) {
				$mainframe = (strpos($php, '$mainframe') === false) ? '' : JFactory::getApplication();
			}
			if (!isset($app)) {
				$app = (strpos($php, '$app') === false) ? '' : JFactory::getApplication();
			}
			if (!isset($database)) {
				$database = (strpos($php, '$database') === false) ? '' : JFactory::getDBO();
			}
			if (!isset($db)) {
				$db = (strpos($php, '$db') === false) ? '' : JFactory::getDBO();
			}
			if (!isset($user)) {
				$user = (strpos($php, '$user') === false) ? '' : JFactory::getUser();
			}

			$vars = '$article,$Itemid,$mainframe,$app,$database,$db,$user';

			$val = '$temp_PHP_Val = create_function( \''.$vars.'\', $php.\';\' );';
			$val .= ' $pass = ( $temp_PHP_Val('.$vars.') ) ? 1 : 0; unset( $temp_PHP_Val );';
			@eval($val);

			if ($pass) {
				break;
			}
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}
}