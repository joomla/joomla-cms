<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsFormServiceInterface;
use Joomla\CMS\Fields\FieldsServiceTrait;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Component\Users\Administrator\Service\HTML\Users;
use Psr\Container\ContainerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_users
 *
 * @since  4.0.0
 */
class UsersComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface, FieldsFormServiceInterface
{
    use FieldsServiceTrait;
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     *
     * @param   ContainerInterface  $container  The container
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function boot(ContainerInterface $container)
    {
        $this->getRegistry()->register('users', new Users());
    }

    /**
     * Returns a valid section for the given section. If it is not valid then null is returned.
     *
     * @param   string       $section  The section to get the mapping for
     * @param   object|null  $item     The content item or null
     *
     * @return  string|null  The new section or null
     *
     * @since   4.0.0
     */
    public function validateSection($section, $item = null)
    {
        if (Factory::getApplication()->isClient('site')) {
            switch ($section) {
                case 'registration':
                case 'profile':
                    return 'user';
            }
        }

        if ($section === 'user') {
            return $section;
        }

        // We don't know other sections.
        return null;
    }

    /**
     * Returns valid contexts.
     *
     * @return  array  Associative array with contexts as keys and translated strings as values
     *
     * @since   4.0.0
     */
    public function getContexts(): array
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_users', JPATH_ADMINISTRATOR);

        return [
            'com_users.user' => $language->_('COM_USERS'),
        ];
    }
}
