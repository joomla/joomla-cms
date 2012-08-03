<?php
/**
 * NoNumber Framework Helper File: Protect
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
 * Functions
 */
class NNProtect
{
	public static $_version = '12.6.4';

	/* check if page should be protected for certain extensions
	*/
	public static function isProtectedPage($ext = '', $hastags = 0)
	{
		// return if disabled via url
		// return if current page is raw format
		// return if current page is NoNumber QuickPage
		// return if current page is a JoomFish or Josetta page
		return (
			($ext && JRequest::getCmd('disable_'.$ext))
			|| JRequest::getCmd('format') == 'raw'
			|| ($hastags && (
				JRequest::getInt('nn_qp')
				|| in_array(JRequest::getCmd('option'), array('com_joomfishplus', 'com_josetta'))
			))
		);
	}

	/* check if page is an admin page
	*/
	public static function isAdmin($block_login = 0)
	{
		$options = array('com_acymailing');
		if ($block_login) {
			$options[] = 'com_login';
		}
		$app = JFactory::getApplication();
		return (
			$app->isAdmin()
			&& !in_array(JRequest::getCmd('option'), $options)
			&& JRequest::getCmd('task') != 'preview'
		);
	}

	/* check if page is an edit page
	*/
	public static function isEditPage()
	{
		return (
			in_array(JRequest::getCmd('task'), array('edit', 'form', 'submission'))
			|| in_array(JRequest::getCmd('do'), array('edit', 'form'))
			|| in_array(JRequest::getCmd('view'), array('edit', 'form'))
			|| in_array(JRequest::getCmd('layout'), array('edit', 'form', 'write'))
			|| in_array(JRequest::getCmd('option'), array('com_contentsubmit', 'com_cckjseblod'))
		);
	}

	/* the regular expression to mach the edit form
	*/
	public static function getFormRegex($regex_format = 0)
	{
		$regex = '(<'.'form [^>]*(id|name)="(adminForm|postform|submissionForm|default_action_user)")';

		if ($regex_format) {
			$regex = '#'.$regex.'#si';
		}

		return $regex;
	}

	/* protect complete adminForm (to prevent articles from being created when editing articles and such)
	*/
	public static function protectForm(&$string, $tags = array(), $protected = array())
	{
		if (!self::isEditPage()) {
			return;
		}

		if (!is_array($tags)) {
			$tags = array($tags);
		}

		if (empty ($protected)) {
			$protected = array();
			foreach ($tags as $i => $tag) {
				$protected[$i] = base64_encode($tag);
			}
		}

		$string = preg_replace(self::getFormRegex(1), '<!-- TMP_START_EDITOR -->\1', $string);
		$string = explode('<!-- TMP_START_EDITOR -->', $string);

		foreach ($string as $i => $str) {
			if (!empty($str) != '' && fmod($i, 2)) {
				$pass = 0;
				foreach ($tags as $tag) {
					if (!(strpos($str, $tag) === false)) {
						$pass = 1;
						break;
					}
				}
				if ($pass) {
					$str = explode('</form>', $str, 2);
					// protect tags only inside form fields
					if (preg_match_all('#(<textarea[^>]*>.*?<\/textarea>|<input[^>]*>)#si', $str['0'], $matches, PREG_SET_ORDER) > 0) {
						foreach ($matches as $match) {
							$field = str_replace($tags, $protected, $match['0']);
							$str['0'] = str_replace($match['0'], $field, $str['0']);
						}
					}
					$string[$i] = implode('</form>', $str);
				}
			}
		}

		$string = implode('', $string);
	}

	/* replace any protected tags to original
	*/
	public static function unprotectForm(&$string, $tags = array(), $protected = array())
	{
		if (!is_array($tags)) {
			$tags = array($tags);
		}

		if (empty ($protected)) {
			$protected = array();
			foreach ($tags as $i => $tag) {
				$protected[$i] = base64_encode($tag);
			}
		}

		$string = str_replace($protected, $tags, $string);
	}
}