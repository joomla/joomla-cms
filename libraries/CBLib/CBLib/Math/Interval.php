<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/17/14 1:56 PM $
* @package CBLib\Math
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Math;

defined('CBLIB') or die();

/**
 * CBLib\Math\Interval Class implementation
 *
 * Partly inspired with thanks from Laravel and Symfony
 */
class Interval
{
	/**
	 * Tests if a given number belongs to a given math interval.
	 *
	 * An interval can represent a finite set of numbers:
	 *
	 *  {1,2,3,4}
	 *
	 * An interval can represent numbers between two numbers:
	 *
	 *  [1, +Inf]
	 *  ]-1,2[
	 *
	 * The left delimiter can be [ (inclusive) or ] (exclusive).
	 * The right delimiter can be [ (exclusive) or ] (inclusive).
	 * Beside numbers, you can use -Inf and +Inf for the infinite.
	 *
	 * @see    http://en.wikipedia.org/wiki/Interval_%28mathematics%29
	 *         http://en.wikipedia.org/wiki/ISO_31-11
	 *
	 * @param integer $number   A number
	 * @param string  $interval An interval
	 *
	 * @return Boolean
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function test($number, $interval)
	{
		$interval = trim($interval);

		if (!preg_match('/^'.self::getIntervalRegexp().'$/x', $interval, $matches)) {
			throw new \InvalidArgumentException(sprintf('"%s" is not a valid interval.', $interval));
		}

		if ($matches[1]) {
			foreach (explode(',', $matches[2]) as $n) {
				if ($number == $n) {
					return true;
				}
			}
		} else {
			$leftNumber = self::convertNumber($matches['left']);
			$rightNumber = self::convertNumber($matches['right']);

			return
				('[' === $matches['left_delimiter'] ? $number >= $leftNumber : $number > $leftNumber)
				&& (']' === $matches['right_delimiter'] ? $number <= $rightNumber : $number < $rightNumber)
				;
		}

		return false;
	}

	/**
	 * Returns a Regexp that matches valid intervals.
	 *
	 * @return string A Regexp (without the delimiters)
	 */
	public static function getIntervalRegexp()
	{
		return <<<EOF
        ({\s*
            (\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)
        \s*})

            |

        (?P<left_delimiter>[\[\]])
            \s*
            (?P<left>-Inf|\-?\d+(\.\d+)?)
            \s*,\s*
            (?P<right>\+?Inf|\-?\d+(\.\d+)?)
            \s*
        (?P<right_delimiter>[\[\]])
EOF;
	}

	/**
	 * Converts string into float and '-Inf' and '+Inf'/'Inf' strings into minimum or maximum (float)
	 *
	 * @param  string  $number
	 * @return float
	 */
	private static function convertNumber($number)
	{
		if ('-Inf' === $number) {
			return log(0);
		} elseif ('+Inf' === $number || 'Inf' === $number) {
			return -log(0);
		}

		return (float) $number;
	}
} 