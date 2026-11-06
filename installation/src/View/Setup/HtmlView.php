<?php

/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\View\Setup;

use Joomla\CMS\Installation\View\DefaultView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The HTML Joomla Core Install Setup View
 *
 * @since  3.1
 */
class HtmlView extends DefaultView
{
    /**
     * Flag to hide DB section
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    public $hideDbSection = false;

    /**
     * Execute and display a template script.
     *
     * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function display($tpl = null)
    {
        $model  = $this->getModel();
        $envMap = $model->getEnvironmentMap();

        $this->hideDbSection = !empty($envMap['JOOMLA_DB_HOST']);

        parent::display($tpl);
    }
}
