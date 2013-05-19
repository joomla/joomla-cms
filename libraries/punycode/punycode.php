<?php
/**
 * Punycode Library
 *
 * The MIT License
 *
 * Copyright (c) 2011 Takehito Gondo
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Punycode
{
	private static $DECODE_TABLE = array(
		'a' =>  0, 'b' =>  1, 'c' =>  2, 'd' =>  3, 'e' =>  4, 'f' =>  5,
		'g' =>  6, 'h' =>  7, 'i' =>  8, 'j' =>  9, 'k' => 10, 'l' => 11,
		'm' => 12, 'n' => 13, 'o' => 14, 'p' => 15, 'q' => 16, 'r' => 17,
		's' => 18, 't' => 19, 'u' => 20, 'v' => 21, 'w' => 22, 'x' => 23,
		'y' => 24, 'z' => 25, '0' => 26, '1' => 27, '2' => 28, '3' => 29,
		'4' => 30, '5' => 31, '6' => 32, '7' => 33, '8' => 34, '9' => 35
	);
	private static $ENCODE_TABLE = array(
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
		'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
		'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
	);
	const BASE = 36;
	const TMIN = 1;
	const TMAX = 26;
	const SKEW = 38;
	const DAMP = 700;
	const INITIAL_BIAS = 72;
	const INITIAL_N = 0x80;
	const DELIMITER = '-';
	const PREFIX = 'xn--';
	const SUFFIX = '';

	private static $OPTIONS = array(
		'charset' => 'UTF-8'
	);

	public static function set_options($options)
	{
		self::$OPTIONS = array_merge(self::$OPTIONS, (array)$options);
	}

	private static function adapt($delta, $numpoints, $firsttime)
	{
		$delta = (int)($firsttime ? $delta / self::DAMP : $delta / 2);
		$delta += (int)($delta / $numpoints);
		$k = 0;
		while ($delta > (((self::BASE - self::TMIN) * self::TMAX) / 2)) {
			$delta = (int)($delta / (self::BASE - self::TMIN));
			$k += self::BASE;
		}
		return $k + (int)((self::BASE - self::TMIN + 1) * $delta / ($delta + self::SKEW));
	}

	private static function string_from_charcode($charcode)
	{
		return mb_convert_encoding('&#'.$charcode.';', self::$OPTIONS['charset'], 'HTML-ENTITIES');
	}

	private static function charcode_from_string( $string )
	{
		return (int)preg_replace('/^.*?([0-9]+).*$/', '$1', mb_convert_encoding($string, 'HTML-ENTITIES', self::$OPTIONS['charset']));
	}

	public static function urldecode($url)
	{
		return self::urlendecode($url, 'decode');
	}

	public static function urlencode($url)
	{
		return self::urlendecode($url, 'encode');
	}

	private static function urlendecode($url, $func)
	{
		$uri = parse_url($url);
		if (!empty($uri['host'])) {
			foreach (array_unique((array)explode('.', $uri['host'])) as $var) {
				if (($encoded = self::$func($var)) != $var) {
					$url = str_replace($var, $encoded, $url);
				}
			}
		}
		return $url;
	}

	public static function decode($input)
	{
		if (preg_match('/^'. self::PREFIX .'/', $input)) {
			return self::_decode(substr($input, strlen(self::PREFIX)));
		}
		return $input;
	}

	public static function encode($input)
	{
		if (preg_match('/[^\x00-\x7f]/', $input)) {
			return self::PREFIX . self::_encode($input);
		}
		return $input;
	}

	public static function _decode($input)
	{
		$n = self::INITIAL_N;
		$bias = self::INITIAL_BIAS;
		$i = 0;
		$output = null;

		if ($pos = (int)strrpos($input, self::DELIMITER)) {
			$output = substr($input, 0, $pos++);
		}
		$ilen = strlen($input);
		$olen = strlen($output);

		while ($pos < $ilen) {
			$oldi = $i;
			$w = 1;
			$k = 0;
			while ($k += self::BASE) {
				$i += ($digit = self::$DECODE_TABLE[ $input[$pos++] ]) * $w;
				$t = $k <= $bias ? self::TMIN : ($k >= $bias + self::TMAX ? self::TMAX : $k - $bias);
				if ($digit < $t) {
					break;
				}
				$w *= self::BASE - $t;
			}
			$bias = self::adapt($i - $oldi, ++$olen, $oldi == 0);
			$n += (int)($i / $olen);
			$i %= $olen;
			$output = mb_substr($output, 0, $i, self::$OPTIONS['charset']) . self::string_from_charcode($n) . mb_substr($output, $i, $olen - $i, self::$OPTIONS['charset']);
			++$i;
		}
		return $output;
	}

	public static function _encode($input)
	{
		$n = self::INITIAL_N;
		$bias = self::INITIAL_BIAS;
		$delta = 0;
		$output = null;

		$ilen = mb_strlen($input, self::$OPTIONS['charset']);
		$non_basic_codepoints = array();
		$codepoints = array();
		for ($b = 0; $b < $ilen; ++$b) {
			if (($code = ord($char = mb_substr($input, $b, 1, self::$OPTIONS['charset']))) < $n) {
				$output .= $char;
			} else if (!in_array($code = self::charcode_from_string($char), $non_basic_codepoints)) {
				$non_basic_codepoints[] = $code;
			}
			$codepoints[] = $code;
		}

		if (($b = strlen($output)) == $ilen) {
			return $output;
		}
		if ($h = $b) {
			$output .= self::DELIMITER;
		}

		$j = 0;
		sort($non_basic_codepoints);
		while ($h < $ilen) {
			$m = $non_basic_codepoints[$j++];
			$delta += ($m - $n) * ($h + 1);
			$n = $m;

			foreach ($codepoints as $c) {
				if ($c < $n) {
					++$delta;
				} else if ($c == $n) {
					$q = $delta;
					$k = 0;
					while ($k += self::BASE) {
						$t = $k <= $bias ? self::TMIN : ($k >= $bias + self::TMAX ? self::TMAX : $k - $bias);
						if ($q < $t) {
							break;
						}
						$output .= self::$ENCODE_TABLE[$t + (($q - $t) % (self::BASE - $t))];
						$q = (int)(($q - $t) / (self::BASE - $t));
					}
					$output .= self::$ENCODE_TABLE[$q];
					$bias = self::adapt($delta, $h + 1, $h == $b);
					$delta = 0;
					++$h;
				}
			}
			++$delta;
			++$n;
		}
		return $output;
	}
}
