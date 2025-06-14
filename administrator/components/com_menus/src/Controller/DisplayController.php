<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base controller class for Menu Manager.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $default_view = 'menus';

    /**
     * Method to display a view.
     *
     * @param   boolean        $cachable   If true, the view output will be cached
     * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types.
     *                         @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  static    This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Verify menu
        $menuType = $this->input->post->getString('menutype', '');

        if ($menuType !== '') {
            $uri = Uri::getInstance();

            if ($uri->getVar('menutype') !== $menuType) {
                $uri->setVar('menutype', $menuType);

                if ($forcedLanguage = $this->input->post->get('forcedLanguage')) {
                    $uri->setVar('forcedLanguage', $forcedLanguage);
                }

                $this->setRedirect(Route::_('index.php' . $uri->toString(['query']), false));

                return parent::display();
            }
        }

        // Check if we have a mod_menu module set to All languages or a mod_menu module for each admin language.
        if ($langMissing = $this->getModel('Menus', 'Administrator')->getMissingModuleLanguages()) {
            $this->app->enqueueMessage(Text::sprintf('JMENU_MULTILANG_WARNING_MISSING_MODULES', implode(', ', $langMissing)), 'warning');
        }

        return parent::display();
    }
}
