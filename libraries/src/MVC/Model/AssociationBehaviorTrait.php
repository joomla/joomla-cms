<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\TableInterface;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait which supports multilingual associations for an administrator model.
 *
 * @since  __DEPLOY_VERSION__
 */
trait AssociationBehaviorTrait
{
    /**
     * Adds the association fields to the form.
     *
     * @param   Form    $form             The form to change.
     * @param   mixed   $data             The data of the item being edited.
     * @param   array   $fieldAttributes  Attributes for the per language fields, at least a "type".
     * @param   string  $fieldPrefix      Namespace to resolve the field type in, when it is not a core field.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function associationPreprocessForm(Form $form, $data, array $fieldAttributes, string $fieldPrefix = '')
    {
        $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

        if (\count($languages) < 2) {
            return;
        }

        $item = \is_object($data) ? $data : (object) (array) $data;
        $id   = (int) ($item->id ?? 0);

        if (!$id) {
            $id = (int) $this->getState($this->getName() . '.id');
        }

        if (!$id) {
            $id = (int) Factory::getApplication()->getInput()->getInt($this->getTable()->getKeyName());
        }

        $addform = new \SimpleXMLElement('<form />');

        $outdated = $this->getAssociationOutdated($id);

        if ($outdated !== null) {
            $field = $addform->addChild('field');
            $field->addAttribute('name', 'translation_relevance');
            $field->addAttribute('type', 'list');
            $field->addAttribute('fieldset', 'item_associations');
            $field->addAttribute('label', 'JGLOBAL_ASSOCIATIONS_RELEVANCE_LABEL');
            $field->addAttribute('description', 'JGLOBAL_ASSOCIATIONS_RELEVANCE_DESC');
            $field->addAttribute('default', $outdated ? 'sync' : 'outdate');
            $field->addAttribute('validate', 'options');

            $option = $field->addChild('option', 'JGLOBAL_ASSOCIATIONS_RELEVANCE_OUTDATE');
            $option->addAttribute('value', 'outdate');

            $option = $field->addChild(
                'option',
                $outdated ? 'JGLOBAL_ASSOCIATIONS_RELEVANCE_NONE_OUTDATED' : 'JGLOBAL_ASSOCIATIONS_RELEVANCE_NONE'
            );
            $option->addAttribute('value', 'none');

            if ($outdated) {
                $option = $field->addChild('option', 'JGLOBAL_ASSOCIATIONS_RELEVANCE_SYNC');
                $option->addAttribute('value', 'sync');
            }
        }

        $fields = $addform->addChild('fields');
        $fields->addAttribute('name', 'associations');
        $fieldset = $fields->addChild('fieldset');
        $fieldset->addAttribute('name', 'item_associations');

        if ($fieldPrefix !== '') {
            $fieldset->addAttribute('addfieldprefix', $fieldPrefix);
        }

        foreach ($languages as $language) {
            $field = $fieldset->addChild('field');
            $field->addAttribute('name', $language->lang_code);
            $field->addAttribute('language', $language->lang_code);
            $field->addAttribute('label', $language->title);
            $field->addAttribute('translate_label', 'false');
            $field->addAttribute('select', 'true');
            $field->addAttribute('new', 'true');
            $field->addAttribute('edit', 'true');
            $field->addAttribute('clear', 'true');
            $field->addAttribute('propagate', 'true');

            foreach ($fieldAttributes as $name => $value) {
                $field->addAttribute($name, $value);
            }
        }

        $form->load($addform, false);
    }

    /**
     * Clears the outdated flag of the given items.
     *
     * @param   array  $pks  The ids of the items to mark as up to date.
     *
     * @return  boolean  True on success.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function markAssociationsUpToDate($pks)
    {
        if (!$this->associationsContext || !Associations::isEnabled()) {
            return true;
        }

        $pks   = array_filter(ArrayHelper::toInteger((array) $pks));
        $table = $this->getTable();

        foreach ($pks as $i => $pk) {
            $table->reset();

            if (!$table->load($pk) || $this->canEditState($table)) {
                continue;
            }

            unset($pks[$i]);
            Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
        }

        if (!$pks) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->update($db->quoteName('#__associations'))
            ->set($db->quoteName('outdated') . ' = 0')
            ->where($db->quoteName('context') . ' = :context')
            ->whereIn($db->quoteName('id'), $pks)
            ->bind(':context', $this->associationsContext);

        $db->setQuery($query);
        $db->execute();

        $this->cleanCache();

        return true;
    }

    /**
     * Writes the association group of an item and applies the translation relevance of this save.
     *
     * @param   TableInterface  $table  The table of the item which has been stored.
     * @param   array           $data   The validated form data.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    protected function storeAssociations(TableInterface $table, array $data)
    {
        if (!$this->associationsContext || !Associations::isEnabled()) {
            return;
        }

        $db  = $this->getDatabase();
        $key = $table->getKeyName();
        $id  = (int) $table->$key;

        if (!empty($data['associations'])) {
            $associations = ArrayHelper::toInteger($data['associations']);

            foreach ($associations as $tag => $assocId) {
                if (!$assocId) {
                    unset($associations[$tag]);
                }
            }

            // Show a warning if the item isn't assigned to a language but we have associations.
            if ($associations && $table->language === '*') {
                Factory::getApplication()->enqueueMessage(
                    Text::_(strtoupper($this->option) . '_ERROR_ALL_LANGUAGE_ASSOCIATED'),
                    'warning'
                );
            }

            $query = $db->createQuery()
                ->select($db->quoteName('key'))
                ->from($db->quoteName('#__associations'))
                ->where(
                    [
                        $db->quoteName('context') . ' = :context',
                        $db->quoteName('id') . ' = :id',
                    ]
                )
                ->bind(':context', $this->associationsContext)
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($query);
            $oldKey = $db->loadResult();

            // Keep the outdated flags of the current group
            $outdated = [];

            if ($oldKey !== null) {
                $query = $db->createQuery()
                    ->select(
                        [
                            $db->quoteName('id'),
                            $db->quoteName('outdated'),
                        ]
                    )
                    ->from($db->quoteName('#__associations'))
                    ->where($db->quoteName('context') . ' = :context')
                    ->where($db->quoteName('key') . ' = :oldKey')
                    ->bind(':context', $this->associationsContext)
                    ->bind(':oldKey', $oldKey);
                $db->setQuery($query);
                $outdated = $db->loadAssocList('id', 'outdated');
            }

            if ($associations || $oldKey !== null) {
                // Deleting old associations for the associated items
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__associations'))
                    ->where($db->quoteName('context') . ' = :context')
                    ->bind(':context', $this->associationsContext);

                $where = [];

                if ($associations) {
                    $where[] = $db->quoteName('id') . ' IN (' . implode(',', $query->bindArray(array_values($associations))) . ')';
                }

                if ($oldKey !== null) {
                    $where[] = $db->quoteName('key') . ' = :oldKey';
                    $query->bind(':oldKey', $oldKey);
                }

                $query->extendWhere('AND', $where, 'OR');
                $db->setQuery($query);
                $db->execute();
            }

            // Adding self to the association
            if ($table->language !== '*') {
                $associations[$table->language] = $id;
            }

            if (\count($associations) > 1) {
                $groupKey = md5(json_encode($associations));
                $query    = $db->createQuery()
                    ->insert($db->quoteName('#__associations'))
                    ->columns(
                        [
                            $db->quoteName('id'),
                            $db->quoteName('context'),
                            $db->quoteName('key'),
                            $db->quoteName('outdated'),
                        ]
                    );

                foreach ($associations as $assocId) {
                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$assocId, $this->associationsContext, $groupKey, (int) ($outdated[$assocId] ?? 0)],
                                [ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING, ParameterType::INTEGER]
                            )
                        )
                    );
                }

                $db->setQuery($query);
                $db->execute();
            }
        }

        $this->applyTranslationRelevance($table, $data, $id);
    }

    /**
     * Applies the translation relevance chosen on save to the association group of an item.
     *
     * @param   TableInterface  $table  The table of the item which has been stored.
     * @param   array           $data   The validated form data.
     * @param   integer         $id     The id of the item which has been stored.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    private function applyTranslationRelevance(TableInterface $table, array $data, int $id)
    {
        $relevance = $data['translation_relevance'] ?? '';

        if (!\in_array($relevance, ['outdate', 'sync'], true) || $table->language === '*') {
            return;
        }

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('key'))
            ->from($db->quoteName('#__associations'))
            ->where(
                [
                    $db->quoteName('context') . ' = :context',
                    $db->quoteName('id') . ' = :id',
                ]
            )
            ->bind(':context', $this->associationsContext)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $groupKey = $db->loadResult();

        if ($groupKey === null) {
            return;
        }

        $query = $db->createQuery()
            ->update($db->quoteName('#__associations'))
            ->set($db->quoteName('outdated') . ' = 0')
            ->where(
                [
                    $db->quoteName('context') . ' = :context',
                    $db->quoteName('key') . ' = :groupKey',
                    $db->quoteName('id') . ' = :id',
                ]
            )
            ->bind(':context', $this->associationsContext)
            ->bind(':groupKey', $groupKey)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        if ($relevance !== 'outdate') {
            return;
        }

        $query = $db->createQuery()
            ->update($db->quoteName('#__associations'))
            ->set($db->quoteName('outdated') . ' = 1')
            ->where(
                [
                    $db->quoteName('context') . ' = :context',
                    $db->quoteName('key') . ' = :groupKey',
                    $db->quoteName('id') . ' != :id',
                ]
            )
            ->bind(':context', $this->associationsContext)
            ->bind(':groupKey', $groupKey)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Returns the outdated state of an item, or null when the item has no associations.
     *
     * @param   integer  $id  The id of the item.
     *
     * @return  integer|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getAssociationOutdated(int $id): ?int
    {
        if (!$id || !$this->associationsContext) {
            return null;
        }

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('outdated'))
            ->from($db->quoteName('#__associations'))
            ->where(
                [
                    $db->quoteName('context') . ' = :context',
                    $db->quoteName('id') . ' = :id',
                ]
            )
            ->bind(':context', $this->associationsContext)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);

        $outdated = $db->loadResult();

        return $outdated === null ? null : (int) $outdated;
    }
}
