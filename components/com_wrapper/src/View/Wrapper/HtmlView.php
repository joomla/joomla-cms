<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Wrapper\Site\View\Wrapper;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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

        if ($params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($params->get('menu-meta_description'));
        }

        if ($params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $params->get('robots'));
        }

        $wrapper = new \stdClass();

        // Auto height control
        if ($params->def('height_auto')) {
            $wrapper->load = 'onload="iFrameHeight(this)"';
        } else {
            $wrapper->load = '';
        }

        $url = $params->def('url', '');

        if ($params->def('add_scheme', 1)) {
            // Adds 'http://' or 'https://' if none is set
            if (str_starts_with($url, '//')) {
                // URL without scheme in component. Prepend current scheme.
                $wrapper->url = Uri::getInstance()->toString(['scheme']) . substr($url, 2);
            } elseif (str_starts_with($url, '/')) {
                // Relative URL in component. Use scheme + host + port.
                $wrapper->url = Uri::getInstance()->toString(['scheme', 'host', 'port']) . $url;
            } elseif (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                // URL doesn't start with either 'http://' or 'https://'. Add current scheme.
                $wrapper->url = Uri::getInstance()->toString(['scheme']) . $url;
            } else {
                // URL starts with either 'http://' or 'https://'. Do not change it.
                $wrapper->url = $url;
            }
        } else {
            $wrapper->url = $url;
        }

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));
        $this->params        = &$params;
        $this->wrapper       = &$wrapper;

        parent::display($tpl);
    }
}
