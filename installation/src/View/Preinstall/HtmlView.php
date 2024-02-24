<?php

/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\View\Preinstall;

use Joomla\CMS\Installation\View\DefaultView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The HTML Joomla Core Install Preinstall View
 *
 * @since  3.1
 */
class HtmlView extends DefaultView
{
    /**
     * Array of PHP config options.
     *
     * @var    array
     * @since  3.1
     */
    protected $options;

    /**
     * The default model
     *
     * @var    string
     * @since  3.0
     */
    protected $_defaultModel = 'checks';

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
        $this->options = $this->get('PhpOptions');

        parent::display($tpl);
    }
}
