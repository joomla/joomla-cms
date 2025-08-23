<?php

/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\View\Remove;

use Joomla\CMS\Installation\Model\ChecksModel;
use Joomla\CMS\Installation\Model\LanguagesModel;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Version;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The HTML Joomla Core Install Remove View
 *
 * @since  3.1
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Is the Joomla Version a development version?
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $development;

    /**
     * List of language choices to install
     *
     * @var    array
     * @since  4.0.0
     */
    protected $items;

    /**
     * Full list of recommended PHP Settings
     *
     * @var    array
     * @since  4.0.0
     */
    protected $phpsettings;

    /**
     * Array of PHP config options
     *
     * @var    array
     * @since  4.0.0
     */
    protected $phpoptions;

    /**
     * Array of PHP config options
     *
     * @var    \stdClass
     * @since  4.0.0
     */
    protected $installed_languages;

    /**
     * Execute and display a template script.
     *
     * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function display($tpl = null)
    {
        $this->development = (new Version())->isInDevelopmentState();

        /** @var LanguagesModel $languagesModel */
        $languagesModel = $this->getModel('Languages');
        $this->items    = $languagesModel->getItems();

        $this->installed_languages                = new \stdClass();
        $this->installed_languages->administrator = $languagesModel->getInstalledlangsAdministrator();
        $this->installed_languages->frontend      = $languagesModel->getInstalledlangsFrontend();

        /** @var ChecksModel $checksModel */
        $checksModel       = $this->getModel('Checks');
        $this->phpoptions  = $checksModel->getPhpOptions();
        $this->phpsettings = $checksModel->getPhpSettings();

        parent::display($tpl);
    }
}
