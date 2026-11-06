<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Extension;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Helper\ContentHelper as LibraryContentHelper;
use Joomla\CMS\Helper\SecondaryCategoriesHelper;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Tag\TagServiceInterface;
use Joomla\CMS\Tag\TagServiceTrait;
use Joomla\Component\Banners\Administrator\Service\Html\Banner;
use Joomla\Database\DatabaseInterface;
use Psr\Container\ContainerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_banners
 *
 * @since  4.0.0
 */
class BannersComponent extends MVCComponent implements
    BootableExtensionInterface,
    CategoryServiceInterface,
    RouterServiceInterface,
    TagServiceInterface
{
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait;
    use CategoryServiceTrait, TagServiceTrait {
        CategoryServiceTrait::getTableNameForSection insteadof TagServiceTrait;
        CategoryServiceTrait::getStateColumnForSection insteadof TagServiceTrait;
    }

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
        $banner = new Banner();
        $banner->setDatabase($container->get(DatabaseInterface::class));

        $this->getRegistry()->register('banner', $banner);
    }

    /**
     * Returns the table for the count items functions for the given section.
     *
     * @param   ?string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getTableNameForSection(?string $section = null)
    {
        return 'banners';
    }

    /**
     * Adds Count Items for Category Manager.
     *
     * @param   \stdClass[]  $items    The category objects.
     * @param   string       $section  The section.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function countItems(array $items, string $section)
    {
        $config = (object) [
            'related_tbl'   => $this->getTableNameForSection($section),
            'state_col'     => $this->getStateColumnForSection($section),
            'group_col'     => 'catid',
            'relation_type' => 'category_or_group',
        ];

        LibraryContentHelper::countRelations($items, $config);

        $this->countSecondaryCategoryItems($items);
    }

    /**
     * Populate secondary category item counters.
     *
     * @param   array  $items  The category items.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function countSecondaryCategoryItems(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $helper = new SecondaryCategoriesHelper('com_banners.banner');
        $counts = $helper->getCategoryItemCounts(array_column($items, 'id'), '#__banners');

        foreach ($items as $item) {
            $itemCounts = $counts[$item->id] ?? [];

            $item->count_secondary_published   = $itemCounts['count_secondary_published'] ?? 0;
            $item->count_secondary_unpublished = $itemCounts['count_secondary_unpublished'] ?? 0;
            $item->count_secondary_archived    = $itemCounts['count_secondary_archived'] ?? 0;
            $item->count_secondary_trashed     = $itemCounts['count_secondary_trashed'] ?? 0;
        }
    }
}
