<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.category
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Workflow\Category\Extension;

use Doctrine\Inflector\InflectorFactory;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Event\View\DisplayEvent;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\DatabaseModelInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Workflow\Workflow;
use Joomla\CMS\Workflow\WorkflowPluginTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Workflow Category Transition Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
final class Category extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use WorkflowPluginTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Cache for the "does this workflow set a category" lookup, keyed by workflow ID.
     *
     * @var    boolean[]
     * @since  __DEPLOY_VERSION__
     */
    private $workflowUsesCategoryCache = [];

    /**
     * Cache for the "does any workflow of this context set a category" lookup, keyed by context.
     *
     * @var    boolean[]
     * @since  __DEPLOY_VERSION__
     */
    private $contextUsesCategoryCache = [];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterDisplay'            => 'onAfterDisplay',
            'onContentPrepareForm'      => 'onContentPrepareForm',
            'onWorkflowAfterTransition' => 'onWorkflowAfterTransition',
        ];
    }

    /**
     * The form event.
     *
     * @param   PrepareFormEvent  $event  The event
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $context = $form->getName();

        // Extend the transition form
        if ($context === 'com_workflow.transition') {
            $this->enhanceTransitionForm($form, $data);

            return;
        }

        $this->disableCategoryField($form, $data);
    }

    /**
     * Add different parameter options to the transition view, we need when executing the transition
     *
     * @param   Form       $form The form
     * @param   \stdClass  $data The data
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function enhanceTransitionForm(Form $form, $data): bool
    {
        $workflow = $this->enhanceWorkflowTransitionForm($form, $data);

        if (!$workflow) {
            return true;
        }

        $parts     = explode('.', $workflow->extension);
        $extension = $parts[0];

        $form->setFieldAttribute('category_id', 'extension', $extension, 'options');

        return true;
    }

    /**
     * Disable certain fields in the item form view, when we want to take over this function in the transition
     * Check also for the workflow implementation and if the field exists
     *
     * @param   Form      $form  The form
     * @param   \stdClass  $data  The data
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function disableCategoryField(Form $form, $data): bool
    {
        $context = $form->getName();

        if (!$this->isSupported($context)) {
            return true;
        }

        $parts = explode('.', $context);

        $component = $this->getApplication()->bootComponent($parts[0]);

        $modelName = $component->getModelName($context);

        $table = $component->getMVCFactory()->createModel($modelName, $this->getApplication()->getName(), ['ignore_request' => true])
            ->getTable();

        $fieldname = $table->getColumnAlias('catid');

        $value = $data->$fieldname ?? $form->getValue($fieldname, null, 0);

        if (!$value) {
            return true;
        }

        $keyName = $table->getKeyName();
        $itemId  = (int) ($data->$keyName ?? $form->getValue($keyName, null, 0));

        // A new item has no workflow association yet, so the category stays editable.
        if (!$itemId) {
            return true;
        }

        $association = (new Workflow($context, $this->getApplication(), $this->getDatabase()))->getAssociation($itemId);

        if (empty($association->workflow_id)) {
            return true;
        }

        // Only take over the category when the workflow of this item actually sets one.
        if (!$this->workflowUsesCategory((int) $association->workflow_id)) {
            return true;
        }

        $form->setFieldAttribute($fieldname, 'readonly', 'true');
        $form->setFieldAttribute($fieldname, 'value', $value);

        return true;
    }

    /**
     * Check whether any published transition of the given workflow sets a category.
     *
     * @param   integer  $workflowId  The workflow ID
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function workflowUsesCategory(int $workflowId): bool
    {
        if (isset($this->workflowUsesCategoryCache[$workflowId])) {
            return $this->workflowUsesCategoryCache[$workflowId];
        }

        $db = $this->getDatabase();

        $query = $db->createQuery()
            ->select($db->quoteName('options'))
            ->from($db->quoteName('#__workflow_transitions'))
            ->where(
                [
                    $db->quoteName('workflow_id') . ' = :workflowId',
                    $db->quoteName('published') . ' = 1',
                ]
            )
            ->bind(':workflowId', $workflowId, ParameterType::INTEGER);

        $options = $db->setQuery($query)->loadColumn();

        return $this->workflowUsesCategoryCache[$workflowId] = $this->hasCategoryOption($options);
    }

    /**
     * Check whether any published transition of the given context sets a category.
     *
     * @param   string  $context  The context, e.g. com_content.article
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function contextUsesCategory(string $context): bool
    {
        if (isset($this->contextUsesCategoryCache[$context])) {
            return $this->contextUsesCategoryCache[$context];
        }

        $db = $this->getDatabase();

        $query = $db->createQuery()
            ->select($db->quoteName('t.options'))
            ->from($db->quoteName('#__workflow_transitions', 't'))
            ->innerJoin(
                $db->quoteName('#__workflows', 'w'),
                $db->quoteName('w.id') . ' = ' . $db->quoteName('t.workflow_id')
            )
            ->where(
                [
                    $db->quoteName('t.published') . ' = 1',
                    $db->quoteName('w.extension') . ' = :context',
                ]
            )
            ->bind(':context', $context);

        $options = $db->setQuery($query)->loadColumn();

        return $this->contextUsesCategoryCache[$context] = $this->hasCategoryOption($options);
    }

    /**
     * Check whether one of the given transition option sets holds a usable category.
     *
     * @param   string[]  $transitionOptions  The raw options columns of a set of transitions
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function hasCategoryOption(array $transitionOptions): bool
    {
        foreach ($transitionOptions as $transitionOption) {
            $categoryId = (new Registry($transitionOption))->get('category_id', 0);

            if (is_numeric($categoryId) && (int) $categoryId > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Manipulate the generic list view
     *
     * @param   DisplayEvent  $event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAfterDisplay(DisplayEvent $event)
    {
        if (!$this->getApplication()->isClient('administrator')) {
            return;
        }

        $component = $event->getArgument('extensionName');
        $section   = $event->getArgument('section');

        // We need the single model context for checking for workflow
        $singularsection = InflectorFactory::create()->build()->singularize($section);

        $context = $component . '.' . $singularsection;

        if (!$this->isSupported($context)) {
            return;
        }

        // The list can hold items of several workflows, so only take over batch when at least one of them sets a category.
        if (!$this->contextUsesCategory($context)) {
            return;
        }

        $this->getApplication()->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_workflow_category.disableBatch', 'plg_workflow_category/disable-category-batch.js', [], ['defer' => true], ['core']);
    }

    /**
     * Method to handle the workflow transition event.
     *
     * @param   WorkflowTransitionEvent  $event  The event object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onWorkflowAfterTransition(WorkflowTransitionEvent $event): void
    {
        $context       = $event->getArgument('extension');
        $extensionName = $event->getArgument('extensionName');
        $transition    = $event->getArgument('transition');
        $pks           = $event->getArgument('pks');

        if (!$this->isSupported($context)) {
            return;
        }

        $app = $this->getApplication();

        if (!($transition->options instanceof Registry)) {
            $app->enqueueMessage(Text::_('PLG_WORKFLOW_CATEGORY_INVALID_TRANSITION'), 'error');
            return;
        }

        $categoryId = $transition->options->get('category_id', 0);

        if (!is_numeric($categoryId) || (int) $categoryId <= 0) {
            return;
        }

        if (empty($pks) || !\is_array($pks)) {
            $app->enqueueMessage(Text::_('PLG_WORKFLOW_CATEGORY_NO_PRIMARY_KEY'), 'error');
            return;
        }

        $component = $app->bootComponent($extensionName);
        $modelName = $component->getModelName($context);
        $table     = $component->getMVCFactory()->createModel($modelName, $app->getName(), ['ignore_request' => true])
            ->getTable();

        $errors = 0;

        foreach ($pks as $pk) {
            if (!$this->processItem($table, (int) $pk, (int) $categoryId)) {
                $errors++;
            }
        }

        if ($errors > 0) {
            $app->enqueueMessage(Text::plural('PLG_WORKFLOW_CATEGORY_ERRORS', $errors), 'warning');
        }
    }

    /**
     * Process the item and update its category.
     *
     * @param   TableInterface  $table       The table of the item, already bound to its component/view
     * @param   int             $pk          The primary key
     * @param   int             $categoryId  The category ID
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function processItem(TableInterface $table, int $pk, int $categoryId): bool
    {
        $app = $this->getApplication();

        try {
            $table->reset();

            if (!$table->load($pk)) {
                $app->enqueueMessage(Text::sprintf('PLG_WORKFLOW_CATEGORY_ITEM_NOT_FOUND', $pk), 'warning');
                return false;
            }

            $fieldname = $table->getColumnAlias('catid');

            if ($table->$fieldname == $categoryId) {
                return true;
            }

            $table->$fieldname = $categoryId;

            if (!$table->store()) {
                $app->enqueueMessage(Text::sprintf('PLG_WORKFLOW_CATEGORY_ITEM_UPDATE_FAILED', $pk, $categoryId), 'error');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::sprintf('PLG_WORKFLOW_CATEGORY_ITEM_UPDATE_ERROR', $pk) . ': ' . $e->getMessage(), 'error');
        }

        return false;
    }

    /**
     * Check if the current plugin should execute workflow related activities
     *
     * @param   string  $context
     *
     * @return   boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function isSupported($context): bool
    {
        $parts = explode('.', $context);

        // We need at least the extension + view for loading the table fields
        if (\count($parts) < 2) {
            return false;
        }

        $component = $this->getApplication()->bootComponent($parts[0]);

        if (
            !$component instanceof WorkflowServiceInterface
            || !$component->isWorkflowActive($context)
        ) {
            return false;
        }

        $modelName = $component->getModelName($context);
        $model     = $component->getMVCFactory()->createModel($modelName, $this->getApplication()->getName(), ['ignore_request' => true]);

        if (!$model instanceof DatabaseModelInterface) {
            return false;
        }

        $table = $model->getTable();

        return $table instanceof TableInterface && $table->hasField($table->getColumnAlias('catid'));
    }
}
