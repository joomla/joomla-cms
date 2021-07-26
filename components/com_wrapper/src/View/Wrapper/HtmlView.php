<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Wrapper\Site\View\Wrapper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

/**
 * Wrapper view class.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $pageclass_sfx = '';

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

	/**
	 * The page parameters
	 *
	 * @var    \stdClass
	 * @since  4.0.0
	 */
	protected $wrapper = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		$params = Factory::getApplication()->getParams();

		// Because the application sets a default page title, we need to get it
		// right from the menu item itself

		$this->setDocumentTitle($params->get('page_title', ''));

		if ($params->get('menu-meta_description'))
		{
			$this->document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('robots'))
		{
			$this->document->setMetaData('robots', $params->get('robots'));
		}

		$wrapper = new \stdClass;

		// Auto height control
		if ($params->def('height_auto'))
		{
			$wrapper->load = 'onload="iFrameHeight(this)"';
		}
		else
		{
			$wrapper->load = '';
		}

		$url = $params->def('url', '');

		if ($params->def('add_scheme', 1))
		{
			// Adds 'http://' or 'https://' if none is set
			if (strpos($url, '//') === 0)
			{
				// URL without scheme in component. Prepend current scheme.
				$wrapper->url = Uri::getInstance()->toString(array('scheme')) . substr($url, 2);
			}
			elseif (strpos($url, '/') === 0)
			{
				// Relative URL in component. Use scheme + host + port.
				$wrapper->url = Uri::getInstance()->toString(array('scheme', 'host', 'port')) . $url;
			}
			elseif (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
			{
				// URL doesn't start with either 'http://' or 'https://'. Add current scheme.
				$wrapper->url = Uri::getInstance()->toString(array('scheme')) . $url;
			}
			else
			{
				// URL starts with either 'http://' or 'https://'. Do not change it.
				$wrapper->url = $url;
			}
		}
		else
		{
			$wrapper->url = $url;
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$this->params        = &$params;
		$this->wrapper       = &$wrapper;

		parent::display($tpl);
	}
}
