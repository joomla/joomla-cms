<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_contact
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Load the language
     *
     * @since   __DEPLOY_VERSION__
     *
     * @return  void
     */
    protected function loadLanguage()
    {
        if ($this->input->get('view') === 'contacts' && $this->input->get('layout') === 'modal') {
            $this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR);
        }

        parent::loadLanguage();
    }

    /**
     * Dispatch a controller task. Redirecting the user if appropriate.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function dispatch()
    {
        if ($this->input->get('view') === 'contacts' && $this->input->get('layout') === 'modal') {
            if (!$this->app->getIdentity()->authorise('core.create', 'com_contact')) {
                $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');

                return;
            }
        }

        parent::dispatch();
    }
}
