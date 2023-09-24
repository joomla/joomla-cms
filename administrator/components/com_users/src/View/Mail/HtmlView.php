<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Mail;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Users mail view.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        // Redirect to admin index if mass mailer disabled in conf
        if (Factory::getApplication()->get('massmailoff', 0) == 1) {
            Factory::getApplication()->redirect(Route::_('index.php', false));
        }

        // Get data from the model
        $this->form = $this->get('Form');

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        ToolbarHelper::title(Text::_('COM_USERS_MASS_MAIL'), 'users massmail');
        $toolbar = Toolbar::getInstance();
        $toolbar->standardButton('COM_USERS_TOOLBAR_MAIL_SEND_MAIL', 'COM_USERS_TOOLBAR_MAIL_SEND_MAIL', 'mail.send')
            ->icon('icon-envelope')
            ->formValidation(true);

        $toolbar->cancel('mail.cancel', 'JTOOLBAR_CANCEL');
        $toolbar->divider();
        $toolbar->preferences('com_users');
        $toolbar->divider();
        $toolbar->help('Mass_Mail_Users');
    }
}
