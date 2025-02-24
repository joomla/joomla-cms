<?php

/**
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Behaviour\Compat\HTMLHelper;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class Bootstrap
{
    public function __construct()
    {
        if (!HTMLHelper::isRegistered('bootstrap.framework')) {
            HTMLHelper::register('bootstrap.framework', 'Joomla\Plugin\Behaviour\Compat\HTMLHelper\Bootstrap::framework');
        }
    }

    /**
     * Method to load the ALL the Bootstrap Components
     *
     * If debugging mode is on an uncompressed version of Bootstrap is included for easier debugging.
     *
     * @param   mixed  $debug  Is debugging mode on? [optional]
     *
     * @return  void
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Will be removed without replacement
     *              Load the different scripts with their individual method calls
     */
    public static function framework($debug = null): void
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        array_map(
            function ($script) use ($wa) {
                $wa->useScript('bootstrap.' . $script);
            },
            ['alert', 'button', 'carousel', 'collapse', 'dropdown', 'modal', 'offcanvas', 'popover', 'scrollspy', 'tab', 'toast']
        );
    }
}
