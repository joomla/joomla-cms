<?php

/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\View;

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Generic Installation View
 *
 * @since  3.1
 */
class DefaultView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var    Form
     * @since  3.1
     */
    protected $form;

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
        $this->form = $this->get('Form');

        parent::display($tpl);
    }
}
