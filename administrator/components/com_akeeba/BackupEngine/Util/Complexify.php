<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Platform;
use RuntimeException;

/**
 * PHP port of http://github.com/danpalmer/jquery.complexify.js
 * Retrieved from https://github.com/mcrumley/php-complexify/blob/master/src/Complexify/Complexify.php
 * Error reporting is based on https://github.com/kislyuk/node-complexify
 */
class Complexify
{
	private static $MIN_COMPLEXITY = 66;

	private static $MAX_COMPLEXITY = 120; //  25 chars, all charsets

	private static $CHARSETS = [
		// Commonly Used
		////////////////////
		[0x0020, 0x0020], // Space
		[0x0030, 0x0039], // Numbers
		[0x0041, 0x005A], // Uppercase
		[0x0061, 0x007A], // Lowercase
		[0x0021, 0x002F], // Punctuation
		[0x003A, 0x0040], // Punctuation
		[0x005B, 0x0060], // Punctuation
		[0x007B, 0x007E], // Punctuation
		// Everything Else
		////////////////////
		[0x0080, 0x00FF], // Latin-1 Supplement
		[0x0100, 0x017F], // Latin Extended-A
		[0x0180, 0x024F], // Latin Extended-B
		[0x0250, 0x02AF], // IPA Extensions
		[0x02B0, 0x02FF], // Spacing Modifier Letters
		[0x0300, 0x036F], // Combining Diacritical Marks
		[0x0370, 0x03FF], // Greek
		[0x0400, 0x04FF], // Cyrillic
		[0x0530, 0x058F], // Armenian
		[0x0590, 0x05FF], // Hebrew
		[0x0600, 0x06FF], // Arabic
		[0x0700, 0x074F], // Syriac
		[0x0780, 0x07BF], // Thaana
		[0x0900, 0x097F], // Devanagari
		[0x0980, 0x09FF], // Bengali
		[0x0A00, 0x0A7F], // Gurmukhi
		[0x0A80, 0x0AFF], // Gujarati
		[0x0B00, 0x0B7F], // Oriya
		[0x0B80, 0x0BFF], // Tamil
		[0x0C00, 0x0C7F], // Telugu
		[0x0C80, 0x0CFF], // Kannada
		[0x0D00, 0x0D7F], // Malayalam
		[0x0D80, 0x0DFF], // Sinhala
		[0x0E00, 0x0E7F], // Thai
		[0x0E80, 0x0EFF], // Lao
		[0x0F00, 0x0FFF], // Tibetan
		[0x1000, 0x109F], // Myanmar
		[0x10A0, 0x10FF], // Georgian
		[0x1100, 0x11FF], // Hangul Jamo
		[0x1200, 0x137F], // Ethiopic
		[0x13A0, 0x13FF], // Cherokee
		[0x1400, 0x167F], // Unified Canadian Aboriginal Syllabics
		[0x1680, 0x169F], // Ogham
		[0x16A0, 0x16FF], // Runic
		[0x1780, 0x17FF], // Khmer
		[0x1800, 0x18AF], // Mongolian
		[0x1E00, 0x1EFF], // Latin Extended Additional
		[0x1F00, 0x1FFF], // Greek Extended
		[0x2000, 0x206F], // General Punctuation
		[0x2070, 0x209F], // Superscripts and Subscripts
		[0x20A0, 0x20CF], // Currency Symbols
		[0x20D0, 0x20FF], // Combining Marks for Symbols
		[0x2100, 0x214F], // Letterlike Symbols
		[0x2150, 0x218F], // Number Forms
		[0x2190, 0x21FF], // Arrows
		[0x2200, 0x22FF], // Mathematical Operators
		[0x2300, 0x23FF], // Miscellaneous Technical
		[0x2400, 0x243F], // Control Pictures
		[0x2440, 0x245F], // Optical Character Recognition
		[0x2460, 0x24FF], // Enclosed Alphanumerics
		[0x2500, 0x257F], // Box Drawing
		[0x2580, 0x259F], // Block Elements
		[0x25A0, 0x25FF], // Geometric Shapes
		[0x2600, 0x26FF], // Miscellaneous Symbols
		[0x2700, 0x27BF], // Dingbats
		[0x2800, 0x28FF], // Braille Patterns
		[0x2E80, 0x2EFF], // CJK Radicals Supplement
		[0x2F00, 0x2FDF], // Kangxi Radicals
		[0x2FF0, 0x2FFF], // Ideographic Description Characters
		[0x3000, 0x303F], // CJK Symbols and Punctuation
		[0x3040, 0x309F], // Hiragana
		[0x30A0, 0x30FF], // Katakana
		[0x3100, 0x312F], // Bopomofo
		[0x3130, 0x318F], // Hangul Compatibility Jamo
		[0x3190, 0x319F], // Kanbun
		[0x31A0, 0x31BF], // Bopomofo Extended
		[0x3200, 0x32FF], // Enclosed CJK Letters and Months
		[0x3300, 0x33FF], // CJK Compatibility
		[0x3400, 0x4DB5], // CJK Unified Ideographs Extension A
		[0x4E00, 0x9FFF], // CJK Unified Ideographs
		[0xA000, 0xA48F], // Yi Syllables
		[0xA490, 0xA4CF], // Yi Radicals
		[0xAC00, 0xD7A3], // Hangul Syllables
		[0xD800, 0xDB7F], // High Surrogates
		[0xDB80, 0xDBFF], // High Private Use Surrogates
		[0xDC00, 0xDFFF], // Low Surrogates
		[0xE000, 0xF8FF], // Private Use
		[0xF900, 0xFAFF], // CJK Compatibility Ideographs
		[0xFB00, 0xFB4F], // Alphabetic Presentation Forms
		[0xFB50, 0xFDFF], // Arabic Presentation Forms-A
		[0xFE20, 0xFE2F], // Combining Half Marks
		[0xFE30, 0xFE4F], // CJK Compatibility Forms
		[0xFE50, 0xFE6F], // Small Form Variants
		[0xFE70, 0xFEFE], // Arabic Presentation Forms-B
		[0xFEFF, 0xFEFF], // Specials
		[0xFF00, 0xFFEF], // Halfwidth and Fullwidth Forms
		[0xFFF0, 0xFFFD]  // Specials
	];

	// Generated from 500 worst passwords and 370 Banned Twitter lists found at
	// @source http://www.skullsecurity.org/wiki/index.php/Passwords
	private static $BANLIST = [
		'0', '1111', '1212', '1234', '1313', '2000', '2112', '2222',
		'3333', '4128', '4321', '4444', '5150', '5555', '6666', '6969', '7777', 'aaaa',
		'alex', 'asdf', 'baby', 'bear', 'beer', 'bill', 'blue', 'cock', 'cool', 'cunt',
		'dave', 'dick', 'eric', 'fire', 'fish', 'ford', 'fred', 'fuck', 'girl', 'golf',
		'jack', 'jake', 'john', 'king', 'love', 'mark', 'matt', 'mike', 'mine', 'pass',
		'paul', 'porn', 'rock', 'sexy', 'shit', 'slut', 'star', 'test', 'time', 'tits',
		'wolf', 'xxxx', '11111', '12345', 'angel', 'apple', 'beach', 'billy', 'bitch',
		'black', 'boobs', 'booty', 'brian', 'bubba', 'buddy', 'chevy', 'chris', 'cream',
		'david', 'dirty', 'eagle', 'enjoy', 'enter', 'frank', 'girls', 'great', 'green',
		'happy', 'hello', 'horny', 'house', 'james', 'japan', 'jason', 'juice', 'kelly',
		'kevin', 'kitty', 'lover', 'lucky', 'magic', 'money', 'movie', 'music', 'naked',
		'ou812', 'paris', 'penis', 'peter', 'porno', 'power', 'pussy', 'qwert', 'sammy',
		'scott', 'smith', 'stars', 'steve', 'super', 'teens', 'tiger', 'video', 'viper',
		'white', 'women', 'xxxxx', 'young', '111111', '112233', '121212', '123123',
		'123456', '131313', '232323', '654321', '666666', '696969', '777777', '987654',
		'aaaaaa', 'abc123', 'abcdef', 'access', 'action', 'albert', 'alexis', 'amanda',
		'andrea', 'andrew', 'angela', 'angels', 'animal', 'apollo', 'apples', 'arthur',
		'asdfgh', 'ashley', 'august', 'austin', 'badboy', 'bailey', 'banana', 'barney',
		'batman', 'beaver', 'beavis', 'bigdog', 'birdie', 'biteme', 'blazer', 'blonde',
		'blowme', 'bonnie', 'booboo', 'booger', 'boomer', 'boston', 'brandy', 'braves',
		'brazil', 'bronco', 'buster', 'butter', 'calvin', 'camaro', 'canada', 'carlos',
		'carter', 'casper', 'cheese', 'coffee', 'compaq', 'cookie', 'cooper', 'cowboy',
		'dakota', 'dallas', 'daniel', 'debbie', 'dennis', 'diablo', 'doctor', 'doggie',
		'donald', 'dragon', 'dreams', 'driver', 'eagle1', 'eagles', 'edward', 'erotic',
		'falcon', 'fender', 'flower', 'flyers', 'freddy', 'fucked', 'fucker', 'fuckme',
		'gators', 'gemini', 'george', 'giants', 'ginger', 'golden', 'golfer', 'gordon',
		'guitar', 'gunner', 'hammer', 'hannah', 'harley', 'helpme', 'hentai', 'hockey',
		'horney', 'hotdog', 'hunter', 'iceman', 'iwantu', 'jackie', 'jaguar', 'jasper',
		'jeremy', 'johnny', 'jordan', 'joseph', 'joshua', 'junior', 'justin', 'killer',
		'knight', 'ladies', 'lakers', 'lauren', 'legend', 'little', 'london', 'lovers',
		'maddog', 'maggie', 'magnum', 'marine', 'martin', 'marvin', 'master', 'matrix',
		'member', 'merlin', 'mickey', 'miller', 'monica', 'monkey', 'morgan', 'mother',
		'muffin', 'murphy', 'nascar', 'nathan', 'nicole', 'nipple', 'oliver', 'orange',
		'parker', 'peanut', 'pepper', 'player', 'please', 'pookie', 'prince', 'purple',
		'qazwsx', 'qwerty', 'rabbit', 'rachel', 'racing', 'ranger', 'redsox', 'robert',
		'rocket', 'runner', 'russia', 'samson', 'sandra', 'saturn', 'scooby', 'secret',
		'sexsex', 'shadow', 'shaved', 'sierra', 'silver', 'skippy', 'slayer', 'smokey',
		'snoopy', 'soccer', 'sophie', 'spanky', 'sparky', 'spider', 'squirt', 'steven',
		'sticky', 'stupid', 'suckit', 'summer', 'surfer', 'sydney', 'taylor', 'tennis',
		'teresa', 'tester', 'theman', 'thomas', 'tigers', 'tigger', 'tomcat', 'topgun',
		'toyota', 'travis', 'tucker', 'turtle', 'united', 'vagina', 'victor', 'viking',
		'voodoo', 'walter', 'willie', 'wilson', 'winner', 'winter', 'wizard', 'xavier',
		'xxxxxx', 'yamaha', 'yankee', 'yellow', 'zxcvbn', 'zzzzzz', '1234567', '7777777',
		'8675309', 'abgrtyu', 'amateur', 'anthony', 'arsenal', 'asshole', 'bigcock',
		'bigdick', 'bigtits', 'bitches', 'blondes', 'blowjob', 'bond007', 'brandon',
		'broncos', 'bulldog', 'cameron', 'captain', 'charles', 'charlie', 'chelsea',
		'chester', 'chicago', 'chicken', 'college', 'cowboys', 'crystal', 'cumming',
		'cumshot', 'diamond', 'dolphin', 'extreme', 'ferrari', 'fishing', 'florida',
		'forever', 'freedom', 'fucking', 'fuckyou', 'gandalf', 'gateway', 'gregory',
		'heather', 'hooters', 'hunting', 'jackson', 'jasmine', 'jessica', 'johnson',
		'leather', 'letmein', 'madison', 'matthew', 'maxwell', 'melissa', 'michael',
		'monster', 'mustang', 'naughty', 'ncc1701', 'newyork', 'nipples', 'packers',
		'panther', 'panties', 'patrick', 'peaches', 'phantom', 'phoenix', 'porsche',
		'private', 'pussies', 'raiders', 'rainbow', 'rangers', 'rebecca', 'richard',
		'rosebud', 'scooter', 'scorpio', 'shannon', 'success', 'testing', 'thunder',
		'thx1138', 'tiffany', 'trouble', 'twitter', 'voyager', 'warrior', 'welcome',
		'william', 'winston', 'yankees', 'zxcvbnm', '11111111', '12345678', 'access14',
		'baseball', 'bigdaddy', 'butthead', 'cocacola', 'computer', 'corvette',
		'danielle', 'dolphins', 'einstein', 'firebird', 'football', 'hardcore',
		'iloveyou', 'internet', 'jennifer', 'marlboro', 'maverick', 'mercedes',
		'michelle', 'midnight', 'mistress', 'mountain', 'nicholas', 'password',
		'princess', 'qwertyui', 'redskins', 'redwings', 'rush2112', 'samantha',
		'scorpion', 'srinivas', 'startrek', 'starwars', 'steelers', 'sunshine',
		'superman', 'swimming', 'trustno1', 'victoria', 'whatever', 'xxxxxxxx',
		'password1', 'password12', 'password123',
	];

	private $minimumChars = 8;

	private $strengthScaleFactor = 1;

	private $bannedPasswords = [];

	private $banMode = 'strict'; // (strict|loose)

	private $encoding = 'UTF-8';

	/**
	 * Constructor
	 *
	 * @param   array  $options  Override default options using an associative array of options
	 *
	 * Options:
	 *  - minimumChars: Minimum password length (default: 8)
	 *  - strengthScaleFactor: Required password strength multiplier (default: 1)
	 *  - bannedPasswords: Custom list of banned passwords (default: long list of common passwords)
	 *  - banMode: Use strict or loose comparisons for banned passwords. "strict" = don't allow a substring of a banned
	 *  password, "loose" = only ban exact matches (default: strict)
	 *  - encoding: Character set encoding of the password (default: UTF-8)
	 */
	public function __construct(array $options = [])
	{
		$this->bannedPasswords = self::$BANLIST;

		foreach ($options as $opt => $val)
		{
			if ($opt === 'banmode')
			{
				trigger_error('The lowercase banmode option is deprecated. Use banMode instead.', E_USER_DEPRECATED);
				$opt = 'banMode';
			}

			$this->{$opt} = $val;
		}
	}

	/**
	 * Checks if a password is strong enough for use on a live site. Used to check the front-end Secret Word.
	 *
	 * @param   string  $password         The password to check
	 * @param   bool    $throwExceptions  Throw an exception if the password is not strong enough?
	 *
	 * @return  bool
	 */
	public static function isStrongEnough($password, $throwExceptions = true)
	{
		$complexify = new self();

		$res = (object) [
			'valid'      => strlen($password) >= 32,
			'complexity' => 50,
			'errors'     => (strlen($password) >= 32) ? [] : ['tooshort'],
		];

		if (function_exists('mb_strlen') && function_exists('mb_convert_encoding') &&
			function_exists('mb_substr') && function_exists('mb_convert_case'))
		{
			$res = $complexify->evaluateSecurity($password);
		}


		if ($res->valid)
		{
			return true;
		}

		if (!$throwExceptions)
		{
			return false;
		}

		$error = count($res->errors) ? array_shift($res->errors) : 'toosimple';

		$errorMessage = Platform::getInstance()->translate('COM_AKEEBA_CPANEL_ERR_FESECRETWORD_' . $error);

		throw new RuntimeException($errorMessage, 403);
	}

	/**
	 * Check the complexity of a password
	 *
	 * @param   string  $password  The password to check
	 *
	 * @return  object  StdClass object with properties "valid", "complexity", and "error"
	 *  - valid: TRUE if the password is complex enough, FALSE if it is not
	 *  - complexity: The complexity of the password as a percent
	 *  - errors: Array containing descriptions of what made the password fail. Possible values are: banned, toosimple,
	 *  tooshort
	 */
	public function evaluateSecurity($password)
	{
		$complexity = 0;
		$error      = [];

		// Reset complexity to 0 when banned password is found
		if (!$this->inBanlist($password))
		{
			// Add character complexity
			foreach (self::$CHARSETS as $charset)
			{
				$complexity += $this->additionalComplexityForCharset($password, $charset);
			}
		}
		else
		{
			array_push($error, 'banned');
			$complexity = 1;
		}

		// Use natural log to produce linear scale
		$complexity = log($complexity ** mb_strlen($password, $this->encoding)) * (1 / $this->strengthScaleFactor);

		if ($complexity <= self::$MIN_COMPLEXITY)
		{
			array_push($error, 'toosimple');
		}

		if (mb_strlen($password, $this->encoding) < $this->minimumChars)
		{
			array_push($error, 'tooshort');
		}

		// Scale to percentage, so it can be used for a progress bar
		$complexity = ($complexity / self::$MAX_COMPLEXITY) * 100;
		$complexity = ($complexity > 100) ? 100 : $complexity;

		return (object) ['valid' => (is_array($error) || $error instanceof \Countable ? count($error) : 0) === 0, 'complexity' => $complexity, 'errors' => $error];
	}

	/**
	 * Determine the complexity added from a character set if it is used in a string
	 *
	 * @param   string  $str       String to check
	 * @param   int  [2]    $charset  Array of unicode code points representing the lower and upper bound of the
	 *                             character range
	 *
	 * @return  int  0 if there are no characters from the character set, size of the character set if there are any
	 *               characters used in the string
	 */
	private function additionalComplexityForCharset($str, $charset)
	{
		$len = mb_strlen($str, $this->encoding);
		for ($i = 0; $i < $len; $i++)
		{
			$c =
				unpack('Nord', mb_convert_encoding(mb_substr($str, $i, 1, $this->encoding), 'UCS-4BE', $this->encoding));
			if ($charset[0] <= $c['ord'] && $c['ord'] <= $charset[1])
			{
				return $charset[1] - $charset[0] + 1;
			}
		}

		return 0;
	}

	/**
	 * Check if a string is in the banned password list
	 *
	 * @param   string  $str  String to check
	 *
	 * @return  bool  TRUE if $str is a banned password, or if it is a substring of a banned password and
	 *                $this->banMode is 'strict'
	 */
	private function inBanlist($str)
	{
		if ($str == '')
		{
			return false;
		}

		$str = mb_convert_case($str, MB_CASE_LOWER, $this->encoding);

		if ($this->banMode === 'strict')
		{
			for ($i = 0; $i < count($this->bannedPasswords); $i++)
			{
				if (mb_strpos($this->bannedPasswords[$i], $str, 0, $this->encoding) !== false)
				{
					return true;
				}
			}

			return false;
		}

		return in_array($str, $this->bannedPasswords);
	}
}
