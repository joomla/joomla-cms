<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Schemaorg.book
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Book\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Schemaorg Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
final class Book extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * The name of the schema form
     *
     * @var   string
     * @since __DEPLOY_VERSION__
     */
    protected $pluginName = 'Book';

    /**
     *  To add plugin specific functions
     *
     *  @param   array $schema Schema form
     *
     *  @return  array Updated schema form
     */
    public function customCleanup(array $schema)
    {
        return $this->cleanupDate($schema, ['datePublished']);
    }
}
