<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

use Joomla\Application\AbstractApplication;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform Base Application Class
 *
 * @property-read  Input  $input  The application input object
 *
 * @since       3.0.0
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Application classes should directly be based on \Joomla\Application\AbstractApplication
 *              don't use this class anymore
 */
abstract class BaseApplication extends AbstractApplication
{
    use EventAware;
    use IdentityAware;

    /**
     * Class constructor.
     *
     * @param   ?Input     $input   An optional argument to provide dependency injection for the application's
     *                              input object.  If the argument is a Input object that object will become
     *                              the application's input object, otherwise a default input object is created.
     * @param   ?Registry  $config  An optional argument to provide dependency injection for the application's
     *                              config object.  If the argument is a Registry object that object will become
     *                              the application's config object, otherwise a default config object is created.
     *
     * @since   3.0.0
     */
    public function __construct(Input $input = null, Registry $config = null)
    {
        $this->input  = $input instanceof Input ? $input : new Input();
        $this->config = $config instanceof Registry ? $config : new Registry();

        $this->initialise();
    }
}
