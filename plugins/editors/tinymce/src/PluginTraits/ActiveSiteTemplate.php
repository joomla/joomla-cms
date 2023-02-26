<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\PluginTraits;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Gets the active Site template style.
 *
 * @since  4.1.0
 */
trait ActiveSiteTemplate
{
    /**
     * Helper function to get the active Site template style.
     *
     * @return  object
     *
     * @since   4.1.0
     */
    protected function getActiveSiteTemplate()
    {
        $db    = Factory::getContainer()->get('db');
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__template_styles'))
            ->where(
                [
                    $db->quoteName('client_id') . ' = 0',
                    $db->quoteName('home') . ' = ' . $db->quote('1'),
                ]
            );

        $db->setQuery($query);

        try {
            return $db->loadObject();
        } catch (RuntimeException $e) {
            $this->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return new \stdClass();
        }
    }
}
