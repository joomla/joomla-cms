<?php

/**
 * @package     Joomla\CMS
 * @subpackage  Helper
 *
 * @copyright   (C) 2026 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Factory;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait to handle secondary categories core logic within Administrator Models.
 *
 * @since   __DEPLOY_VERSION__
 */
trait SecondaryCategoriesSaveTrait
{
    /**
     * Get the secondary category ids the current user is allowed to manage.
     *
     * @param   int  $currentCategoryId  The current primary category id.
     *
     * @return  array An array of category ids available to the current user.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getManageableSecondaryCategoryIds(int $currentCategoryId): array
    {
        $db        = $this->getDatabase();
        $user      = $this->getCurrentUser();
        $extension = explode('.', $this->typeAlias)[0];

        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('extension') . ' = :extension')
            ->whereIn($db->quoteName('published'), [0, 1, 2])
            ->bind(':extension', $extension, ParameterType::STRING);

        if (!$user->authorise('core.admin')) {
            $query->whereIn(
                $db->quoteName('access'),
                $user->getAuthorisedViewLevels()
            );
        }

        $categories = array_map('intval', $db->setQuery($query)->loadColumn());
        $manageable = [];

        if ($currentCategoryId === 0) {
            foreach ($categories as $categoryId) {
                if ($user->authorise('core.create', $extension . '.category.' . $categoryId)) {
                    $manageable[] = $categoryId;
                }
            }

            return $manageable;
        }

        $currentAsset = $extension . '.category.' . $currentCategoryId;

        foreach ($categories as $categoryId) {
            if ($categoryId === $currentCategoryId) {
                continue;
            }

            if (!$user->authorise('core.edit.state', $currentAsset)) {
                continue;
            }

            if (!$user->authorise('core.create', $extension . '.category.' . $categoryId)) {
                continue;
            }

            $manageable[] = $categoryId;
        }

        return $manageable;
    }

    /**
     * Get the currently assigned secondary categories for the item.
     *
     * @param   int  $itemId  The item id.
     *
     * @return  array An array of category ids.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getCurrentSecondaryCategories(int $itemId): array
    {
        $helper = new SecondaryCategoriesHelper($this->typeAlias);

        return $helper->getCurrentSecondaryCategoriesByItem($itemId);
    }

    /**
     * Normalize submitted secondary categories before saving.
     *
     * @param   array  $data  The form data.
     *
     * @return  array An array of valid secondary category ids to save.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function normalizeSecondaryCategories(array $data): array
    {
        $itemId            = (int) ($data['id'] ?? 0);
        $primaryCategoryId = (int) ($data['catid'] ?? 0);
        $currentIds        = $itemId > 0 ? $this->getCurrentSecondaryCategories($itemId) : [];
        $manageableIds     = $this->getManageableSecondaryCategoryIds($primaryCategoryId);

        $submitted = array_filter(array_map('intval', (array) ($data['secondary_categories'] ?? [])));
        $submitted = array_intersect($submitted, $manageableIds);
        $hiddenIds = array_diff($currentIds, $manageableIds);

        return array_values(array_unique(array_diff(array_merge($submitted, $hiddenIds), [$primaryCategoryId])));
    }

    /**
     * Create new secondary categories submitted from the fancy select field.
     *
     * @param   array  $data  The form data.
     *
     * @return  array  Category ids and existing values.
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    private function createSecondaryCategories(array $data): array
    {
        $categories = (array) ($data['secondary_categories'] ?? []);
        $extension  = explode('.', $this->typeAlias)[0];

        foreach ($categories as $key => $categoryId) {
            if (is_numeric($categoryId) && CategoriesHelper::validateCategoryId($categoryId, $extension)) {
                continue;
            }

            if (!\is_string($categoryId) || !str_starts_with($categoryId, '#new#') || !$this->canCreateCategory()) {
                unset($categories[$key]);
                continue;
            }

            $title = trim(substr($categoryId, 5));

            if ($title === '') {
                unset($categories[$key]);
                continue;
            }

            $category = [
                'title'     => $title,
                'parent_id' => 1,
                'extension' => $extension,
                'language'  => $data['language'] ?? '*',
                'published' => 1,
            ];

            /** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $categoryModel */
            $categoryModel = Factory::getApplication()->bootComponent('com_categories')
                ->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);

            try {
                $categoryModel->save($category);
            } catch (\Throwable $e) {
                throw new \RuntimeException('Failed to create secondary category "' . $category['title'] . '"');
            }

            $categories[$key] = $categoryModel->getState('category.id');
        }

        return $categories;
    }

    /**
     * Save secondary category mappings for an item.
     *
     * @param   array  $data  The form data.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function saveSecondaryCategories(array $data): void
    {
        $itemId = (int) ($data['id'] ?? 0);

        if (!$itemId) {
            $itemId = (int) $this->getState($this->getName() . '.id');
        }

        $submitted = $data['secondary_categories'] ?? [];

        if (empty($submitted)) {
            $submitted = [];
        }

        $helper = new SecondaryCategoriesHelper($this->typeAlias);
        $helper->replaceMappings($itemId, (array) $submitted);
    }
}
