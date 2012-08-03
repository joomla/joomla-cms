<?php
/**
 * NoNumber Framework Helper File: Tags
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
class NNTags
{
	public static $_version = '12.6.4';

	public static function getTagValues($tag = '', $keys = array('title'), $separator = '|', $equal = ':', $limit = 0)
	{
		$s = '[[S]]';
		$e = '[[E]]';

		$tag = str_replace(array($separator, $equal), array($s, $e), $tag);
		$tag = str_replace(array('\\'.$s, '\\'.$e), array($separator, $equal), $tag);

		if ($limit) {
			$vals = explode($s, $tag, (int) $limit);
		} else {
			$vals = explode($s, $tag);
		}

		$t = new stdClass();
		$t->params = array();
		$unnamed = array();
		foreach($vals as $val) {
			$keyval = explode($e, $val);
			if (isset($keyval['1'])) {
				$t->{$keyval['0']} = $keyval['1'];
			} else {
				$unnamed[] = $keyval['0'];
			}
		}

		foreach($unnamed as $i => $val) {
			if ( isset($keys[$i])) {
				$t->{$keys[$i]} = $val;
			} else {
				$t->params[] = $val;
			}
		}

		return $t;
	}

		public static function setSurroundingTags($pre, $post, $tags = 0)
	{
		if ($tags == 0) {
			$tags = array('div', 'p', 'span', 'pre', 'a',
				'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
				'strong', 'b', 'em', 'i', 'u', 'big', 'small', 'font'
			);
		}
		$a = explode('<', $pre);
		$b = explode('</', $post);
		if (count($b) > 1 && count($a) > 1) {
			$a = array_reverse($a);
			$a_pre = array_pop($a);
			$b_pre = array_shift($b);
			$a_tags = $a;
			foreach ($a_tags as $i => $a_tag) {
				$a[$i] = '<'.trim($a_tag);
				$a_tags[$i] = preg_replace('#^([a-z0-9]+).*$#', '\1', trim($a_tag));
			}
			$b_tags = $b;
			foreach ($b_tags as $i => $b_tag) {
				$b[$i] = '</'.trim($b_tag);
				$b_tags[$i] = preg_replace('#^([a-z0-9]+).*$#', '\1', trim($b_tag));
			}
			foreach ($b_tags as $i => $b_tag) {
				if ($b_tag && in_array($b_tag, $tags)) {
					foreach ($a_tags as $j => $a_tag) {
						if ($b_tag == $a_tag) {
							$a_tags[$i] = '';
							$b[$i] = trim(preg_replace('#^</'.$b_tag.'.*?>#', '', $b[$i]));
							$a[$j] = trim(preg_replace('#^<'.$a_tag.'.*?>#', '', $a[$j]));
							break;
						}
					}
				}
			}
			foreach ($a_tags as $i => $tag) {
				if ($tag && in_array($tag, $tags)) {
					array_unshift($b, trim($a[$i]));
					$a[$i] = '';
				}
			}
			$a = array_reverse($a);
			list($pre, $post) = array(implode('', $a), implode('', $b));
		}
		return array(trim($pre), trim($post));
	}
}