<?php
/**
 * Element: Browsers
 * Displays a multiselectbox of different browsers
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
 * Browsers Element
 */
class nnFieldBrowsers
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params)
	{
		$this->params = $params;

		$size = (int) $this->def('size');
		$groups = explode(',', $this->def('groups'));

		if (!is_array($value)) {
			$value = explode(',', $value);
		}

		$matches = array();
		/* Browsers */
		if (empty($groups) || in_array('os', $groups)) {
			$matches[] = array('Windows', 'Windows');
			$matches[] = array('- Windows 7', 'Windows nt 6.1');
			$matches[] = array('- Windows Vista', 'Windows nt 6.0');
			$matches[] = array('- Windows Server 2003', 'Windows nt 5.2');
			$matches[] = array('- Windows XP', 'Windows nt 5.1');
			$matches[] = array('- Windows 2000 sp1', 'Windows nt 5.01');
			$matches[] = array('- Windows 2000', 'Windows nt 5.0');
			$matches[] = array('- Windows NT 4.0', 'Windows nt 4.0');
			$matches[] = array('- Windows Me', 'Win 9x 4.9');
			$matches[] = array('- Windows 98', 'Windows 98');
			$matches[] = array('- Windows 95', 'Windows 95');
			$matches[] = array('- Windows CE', 'Windows ce');
			$matches[] = '';
			$matches[] = array('Mac OS', '#(Mac OS|Mac_PowerPC|Macintosh)#');
			$matches[] = array('- Mac OSX', 'Mac OS X');
			$matches[] = array('- - Mac OSX Lion', 'Mac OS X 10.7');
			$matches[] = array('- - Mac OSX Snow Leopard', 'Mac OS X 10.6');
			$matches[] = array('- - Mac OSX Leopard', 'Mac OS X 10.5');
			$matches[] = array('- - Mac OSX Tiger', 'Mac OS X 10.4');
			$matches[] = array('- - Mac OSX Panther', 'Mac OS X 10.3');
			$matches[] = array('- - Mac OSX Jaguar', 'Mac OS X 10.2');
			$matches[] = array('- - Mac OSX Puma', 'Mac OS X 10.1');
			$matches[] = array('- - Mac OSX Cheetah', 'Mac OS X 10.0');
			$matches[] = array('- - Mac OS (classic)', '#(Mac_PowerPC|Macintosh)#');
			$matches[] = '';
			$matches[] = array('Linux', '#(Linux|X11)#');
			$matches[] = '';
			$matches[] = JText::_('NN_OTHERS');
			$matches[] = array('Open BSD', 'OpenBSD');
			$matches[] = array('Sun OS', 'SunOS');
			$matches[] = array('QNX', 'QNX');
			$matches[] = array('BeOS', 'BeOS');
			$matches[] = array('OS/2', 'OS/2');
		}
		if (empty($groups) || in_array('browsers', $groups)) {
			$matches[] = array('Chrome', 'Chrome');
			$matches[] = array('- Chrome 22', 'Chrome/22.');
			$matches[] = array('- Chrome 21', 'Chrome/21.');
			$matches[] = array('- Chrome 20', 'Chrome/20.');
			$matches[] = array('- Chrome 19', 'Chrome/19.');
			$matches[] = array('- Chrome 18', 'Chrome/18.');
			$matches[] = array('- Chrome 17', 'Chrome/17.');
			$matches[] = array('- Chrome 16', 'Chrome/16.');
			$matches[] = array('- Chrome 15', 'Chrome/15.');
			$matches[] = array('- Chrome 14', 'Chrome/14.');
			$matches[] = array('- Chrome 13', 'Chrome/13.');
			$matches[] = array('- Chrome 12', 'Chrome/12.');
			$matches[] = array('- Chrome 11', 'Chrome/11.');
			$matches[] = array('- Chrome 10', 'Chrome/10.');
			$matches[] = array('- Chrome 1-9', '#Chrome/[1-9]\.#');
			$matches[] = '';
			$matches[] = array('Firefox', 'Firefox');
			$matches[] = array('- Firefox 15', 'Firefox/15.');
			$matches[] = array('- Firefox 14', 'Firefox/14.');
			$matches[] = array('- Firefox 13', 'Firefox/13.');
			$matches[] = array('- Firefox 12', 'Firefox/12.');
			$matches[] = array('- Firefox 11', 'Firefox/11.');
			$matches[] = array('- Firefox 10', 'Firefox/10.');
			$matches[] = array('- Firefox 1-9', '#Firefox/[1-9]\.#');
			$matches[] = '';
			$matches[] = array('Internet Explorer', 'MSIE');
			$matches[] = array('- Internet Explorer 10', 'MSIE 10.');
			$matches[] = array('- Internet Explorer 9', 'MSIE 9.');
			$matches[] = array('- Internet Explorer 8', 'MSIE 8.');
			$matches[] = array('- Internet Explorer 7', 'MSIE 7.');
			$matches[] = array('- Internet Explorer 1-6', '#MSIE [1-6]\.#');
			$matches[] = '';
			$matches[] = array('Opera', 'Opera');
			$matches[] = array('- Opera 1-9', '#Opera/[1-9]\.#');
			$matches[] = array('- Opera 12', 'Opera/12.');
			$matches[] = array('- - Opera 12.5', 'Opera/12.5');
			$matches[] = array('- - Opera 12.0', 'Opera/12.0');
			$matches[] = array('- Opera 11', 'Opera/11.');
			$matches[] = array('- - Opera 11.5', 'Opera/11.5');
			$matches[] = array('- - Opera 11.0', 'Opera/11.0');
			$matches[] = array('- Opera 10', 'Opera/10.');
			$matches[] = array('- - Opera 10.5', 'Opera/10.5');
			$matches[] = array('- - Opera 10.0', 'Opera/10.0');
			$matches[] = '';
			$matches[] = array('Safari', 'Safari');
			$matches[] = array('- Safari 4', '#Version/4\..*Safari/#');
			$matches[] = array('- Safari 5', '#Version/5\..*Safari/#');
			$matches[] = array('- Safari 1-3', '#Version/[1-3]\..*Safari/#');
		}

		/* Mobile browsers */
		if (empty($groups)) {
			$matches[] = '';
			$matches[] = '';
			$matches[] = JText::_('NN_MOBILE_BROWSERS');
		}
		if (empty($groups) || in_array('mobile', $groups)) {
			$matches[] = array(JText::_('JALL'), 'mobile');
			$matches[] = array('- Android', 'Android');
			$matches[] = array('- Blackberry', 'Blackberry');
			$matches[] = array('- IE Mobile', 'IEMobile');
			$matches[] = array('- iPad', 'iPad');
			$matches[] = array('- iPhone', 'iPhone');
			$matches[] = array('- iPod Touch', 'iPod');
			$matches[] = array('- NetFront', 'NetFront');
			$matches[] = array('- Nokia', 'NokiaBrowser');
			$matches[] = array('- Opera Mini', 'Opera Mini');
			$matches[] = array('- Opera Mobile', 'Opera Mobi');
			$matches[] = array('- UC Browser', 'UC Browser');
		}

		/* Web crawlers */
		if (empty($groups)) {
			$matches[] = '';
			$matches[] = '';
			$matches[] = JText::_('NN_SEARCHBOTS');
		}
		if (empty($groups) || in_array('searchbots', $groups) || in_array('crawlers', $groups)) {
			$matches[] = array(JText::_('JALL'), 'searchbots');
			$matches[] = array('- Alexa', 'ia_archiver-web.archive.org');
			$matches[] = array('- Bing', 'bingbot');
			$matches[] = array('- Google', 'GoogleBot');
			$matches[] = array('- Yahoo', 'Yahoo! Slurp');
		}

		$options = array();
		foreach ($matches as $match) {
			if (!$match) {
				$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true);
			} else if (!is_array($match)) {
				$options[] = JHtml::_('select.option', '-', $match, 'value', 'text', true);
			} else {
				$item_name = str_replace('- ', '&nbsp;&nbsp;', $match['0']);
				$options[] = JHtml::_('select.option', $match['1'], $item_name, 'value', 'text');
			}
		}

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/html.php';
		return nnHTML::selectlist($options, $name, $value, $id, $size, 1, '');
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_Browsers extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'Browsers';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldBrowsers();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes());
	}
}