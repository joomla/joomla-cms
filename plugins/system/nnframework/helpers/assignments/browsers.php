<?php
/**
 * NoNumber Framework Helper File: Assignments: Browsers
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
 * Assignments: Browsers
 */
class NNFrameworkAssignmentsBrowsers
{
	var $_version = '12.6.4';

	/**
	 * passBrowsers
	 */
	function passBrowsers(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$pass = 0;

		$selection = $main->makeArray($selection);

		if (!empty($selection)) {
			jimport('joomla.environment.browser');
			$browser = JBrowser::getInstance();
			$a = $browser->getAgentString();
			if (!(stripos($a, 'Chrome') === false)) {
				$a = preg_replace('#(Chrome/.*)Safari/[0-9\.]*#is', '\1', $a);
			} else if (!(stripos($a, 'Opera') === false)) {
				$a = preg_replace('#(Opera/.*)Version/#is', '\1Opera/', $a);
			}
			foreach ($selection as $sel) {
				if (!$sel){
					continue;
				}
				if ($sel == 'mobile') {
					if ($this->isMobile()) {
						$pass = 1;
						break;
					}
				} else if ($sel == 'searchbots' || $sel == 'crawlers') {
					if ($this->isSearchBot()) {
						$pass = 1;
						break;
					}
				} else {
					if (strpos($sel, '#') === 0) {
						if (preg_match($sel.'i', $a)) {
							$pass = 1;
							break;
						}
					} else {
						if (!(stripos($a, $sel) === false)) {
							$pass = 1;
							break;
						}
					}
				}
			}
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}
	/**
	 * passOS
	 */
	function passOS(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		return self::passBrowsers($main, $params, $selection, $assignment);
	}

	/**
	 * isMobile
	 */
	function isMobile()
	{
		/* taken from http://detectmobilebrowsers.com */

		$browser = JBrowser::getInstance();
		$a = $browser->getAgentString();

		$mobiles = array(
			'android.+mobile',
			'avantgo',
			'bada\/',
			'blackberry',
			'blazer',
			'compal',
			'elaine',
			'fennec',
			'hiptop',
			'iemobile',
			'ip(hone|od)',
			'iris',
			'kindle',
			'lge ',
			'maemo',
			'midp',
			'mmp',
			'netfront',
			'opera m(ob|in)i',
			'palm( os)?',
			'phone',
			'p(ixi|re)\/',
			'plucker',
			'pocket',
			'psp',
			'symbian',
			'treo',
			'up\.(browser|link)',
			'vodafone',
			'wap',
			'windows (ce|phone)',
			'xda',
			'xiino/i'
		);
		if (preg_match('#('.implode('|', $mobiles).')#i', $a)) {
			return 1;
		}

		$a = substr($a, 0, 4);
		$mobiles = array(
			'/1207',
			'6310',
			'6590',
			'3gso',
			'4thp',
			'50[1-6]i',
			'770s',
			'802s',
			'a wa',
			'abac',
			'ac(er|oo|s\-)',
			'ai(ko|rn)',
			'al(av|ca|co)',
			'amoi',
			'an(ex|ny|yw)',
			'aptu',
			'ar(ch|go)',
			'as(te|us)',
			'attw',
			'au(di|\-m|r |s )',
			'avan',
			'be(ck|ll|nq)',
			'bi(lb|rd)',
			'bl(ac|az)',
			'br[ev]w',
			'bumb',
			'bw\-[nu]',
			'c55\/',
			'capi',
			'ccwa',
			'cdm\-',
			'cell',
			'chtm',
			'cldc',
			'cmd\-',
			'co(mp|nd)',
			'craw',
			'da(it|ll|ng)',
			'dbte',
			'dc\-s',
			'devi',
			'dica',
			'dmob',
			'do[cp]o',
			'ds(12|\-d)',
			'el(49|ai)',
			'em(l2|ul)',
			'er(ic|k0)',
			'esl8',
			'ez([4-7]0|os|wa|ze)',
			'fetc',
			'fly[\-_]',
			'g1 u',
			'g560',
			'gene',
			'gf\-5',
			'g\-mo',
			'go(\.w|od)',
			'gr(ad|un)',
			'haie',
			'hcit',
			'hd\-[mpt]',
			'hei\-',
			'hi(pt|ta)',
			'hp( i|ip)',
			'hs\-c',
			'ht(c[\- _agpst]|tp)',
			'hu(aw|tc)',
			'i\-(20|go|ma)',
			'i230',
			'iac[ \-\/]',
			'ibro',
			'idea',
			'ig01',
			'ikom',
			'im1k',
			'inno',
			'ipaq',
			'iris',
			'ja[tv]a',
			'jbro',
			'jemu',
			'jigs',
			'kddi',
			'keji',
			'kgt[ \/]',
			'klon',
			'kpt ',
			'kwc\-',
			'kyo[ck]',
			'le(no|xi)',
			'lg( g|\/[klu]|50|54|\-[a-w])',
			'libw',
			'lynx',
			'm1\-w',
			'm3ga',
			'm50\/',
			'ma(te|ui|xo)',
			'mc(01|21|ca)',
			'm\-cr',
			'me(di|rc|ri)',
			'mi(o8|oa|ts)',
			'mmef',
			'mo(01|02|bi|de|do|t[\- ov]|zz)',
			'mt(50|p1|v )',
			'mwbp',
			'mywa',
			'n10[0-2]',
			'n20[2-3]',
			'n30[02]',
			'n50[025]',
			'n7(0[01]|10)',
			'ne([cm]\-|on|tf|wf|wg|wt)',
			'nok[6i]',
			'nzph',
			'o2im',
			'op(ti|wv)',
			'oran',
			'owg1',
			'p800',
			'pan[adt]',
			'pdxg',
			'pg(13|\-([1-8]|c))',
			'phil',
			'pire',
			'pl(ay|uc)',
			'pn\-2',
			'po(ck|rt|se)',
			'prox',
			'psio',
			'pt\-g',
			'qa\-a',
			'qc(07|12|21|32|60|\-[2-7]|i\-)',
			'qtek',
			'r380',
			'r600',
			'raks',
			'rim9',
			'ro(ve|zo)',
			's55\/',
			'sa(ge|ma|mm|ms|ny|va)',
			'sc(01|h\-|oo|p\-)',
			'sdk\/',
			'se(c[\-01]|47|mc|nd|ri)',
			'sgh\-',
			'shar',
			'sie[\-m]',
			'sk\-0',
			'sl(45|id)',
			'sm(al|ar|b3|it|t5)',
			'so(ft|ny)',
			'sp(01|h\-|v\-|v )',
			'sy(01|mb)',
			't2(18|50)',
			't6(00|10|18)',
			'ta(gt|lk)',
			'tcl\-',
			'tdg\-',
			'tel[im]',
			'tim\-',
			't\-mo',
			'to(pl|sh)',
			'ts(70|m\-|m3|m5)',
			'tx\-9',
			'up(\.b|g1|si)',
			'utst',
			'v400',
			'v750',
			'veri',
			'vi(rg|te)',
			'vk(40|5[0-3]|\-v)',
			'vm40',
			'voda',
			'vulc',
			'vx(52|53|60|61|70|80|81|83|85|98)',
			'w3c[\- ]',
			'webc',
			'whit',
			'wi(g |nc|nw)',
			'wmlb',
			'wonu',
			'x700',
			'yas\-',
			'your',
			'zeto',
			'zte\-'
		);
		if (preg_match('#('.implode('|', $mobiles).')#i', $a)) {
			return 1;
		}

		return 0;
	}

	/**
	 * isMobile
	 */
	function isSearchBot()
	{
		$browser = JBrowser::getInstance();
		$a = $browser->getAgentString();
		$crawlers = '(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves/Teoma|ia_archiver)';

		if (preg_match('#'.$crawlers.'#i', $a)) {
			return 1;
		}

		return 0;
	}
}