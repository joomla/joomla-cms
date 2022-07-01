<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Extension;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper as LibraryContentHelper;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Tag\TagServiceInterface;
use Joomla\CMS\Tag\TagServiceTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\CMS\Workflow\WorkflowServiceTrait;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Joomla\Component\Content\Administrator\Service\HTML\AdministratorService;
use Joomla\Component\Content\Administrator\Service\HTML\Icon;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_content
 *
 * @since  4.0.0
 */
class ContentComponent extends MVCComponent implements
    BootableExtensionInterface,
    CategoryServiceInterface,
    FieldsServiceInterface,
    AssociationServiceInterface,
    WorkflowServiceInterface,
    RouterServiceInterface,
    TagServiceInterface
{
    use AssociationServiceTrait;
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;
    use WorkflowServiceTrait;
    use CategoryServiceTrait, TagServiceTrait {
        CategoryServiceTrait::getTableNameForSection insteadof TagServiceTrait;
        CategoryServiceTrait::getStateColumnForSection insteadof TagServiceTrait;
    }

    /** @var array Supported functionality */
    protected $supportedFunctionality = [
        'core.featured' => true,
        'core.state' => true,
    ];

    /**
     * The trashed condition
     *
     * @since   4.0.0
     */
    public const CONDITION_NAMES = [
        self::CONDITION_PUBLISHED   => 'JPUBLISHED',
        self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
        self::CONDITION_ARCHIVED    => 'JARCHIVED',
        self::CONDITION_TRASHED     => 'JTRASHED',
    ];

    /**
     * The archived condition
     *
     * @since   4.0.0
     */
    public const CONDITION_ARCHIVED = 2;

    /**
     * The published condition
     *
     * @since   4.0.0
     */
    public const CONDITION_PUBLISHED = 1;

    /**
     * The unpublished condition
     *
     * @since   4.0.0
     */
    public const CONDITION_UNPUBLISHED = 0;

    /**
     * The trashed condition
     *
     * @since   4.0.0
     */
    public const CONDITION_TRASHED = -2;

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
        $this->getRegistry()->register('contentadministrator', new AdministratorService());
        $this->getRegistry()->register('contenticon', new Icon());

        // The layout joomla.content.icons does need a general icon service
        $this->getRegistry()->register('icon', $this->getRegistry()->getService('contenticon'));
    }

    /**
     * Returns a valid section for the given section. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     * @param   object  $item     The item
     *
     * @return  string|null  The new section
     *
     * @since   4.0.0
     */
    public function validateSection($section, $item = null)
    {
        if (Factory::getApplication()->isClient('site')) {
            // On the front end we need to map some sections
            switch ($section) {
                // Editing an article
                case 'form':
                    // Category list view
                case 'featured':
                case 'category':
                    $section = 'article';
            }
        }

        if ($section != 'article') {
            // We don't know other sections
            return null;
        }

        return $section;
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getContexts(): array
    {
        Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_content.article'    => Text::_('COM_CONTENT'),
            'com_content.categories' => Text::_('JCATEGORY')
        );

        return $contexts;
    }

    /**
     * Returns valid contexts
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getWorkflowContexts(): array
    {
        Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_content.article'    => Text::_('COM_CONTENT')
        );

        return $contexts;
    }

    /**
     * Returns the workflow context based on the given category section
     *
     * @param   string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    public function getCategoryWorkflowContext(?string $section = null): string
    {
        $context = $this->getWorkflowContexts();

        return array_key_first($context);
    }

    /**
     * Returns the table for the count items functions for the given section.
     *
     * @param   string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getTableNameForSection(string $section = null)
    {
        return '#__content';
    }

    /**
     * Returns a table name for the state association
     *
     * @param   string  $section  An optional section to separate different areas in the component
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getWorkflowTableBySection(?string $section = null): string
    {
        return '#__content';
    }

    /**
     * Returns the model name, based on the context
     *
     * @param   string  $context  The context of the workflow
     *
     * @return string
     */
    public function getModelName($context): string
    {
        $parts = explode('.', $context);

        if (count($parts) < 2) {
            return '';
        }

        array_shift($parts);

        $modelname = array_shift($parts);

        if ($modelname === 'article' && Factory::getApplication()->isClient('site')) {
            return 'Form';
        } elseif ($modelname === 'featured' && Factory::getApplication()->isClient('administrator')) {
            return 'Article';
        }

        return ucfirst($modelname);
    }

    /**
     * Method to filter transitions by given id of state.
     *
     * @param   array  $transitions  The Transitions to filter
     * @param   int    $pk           Id of the state
     *
     * @return  array
     *
     * @since  4.0.0
     */
    public function filterTransitions(array $transitions, int $pk): array
    {
        return ContentHelper::filterTransitions($transitions, $pk);
    }

    /**
     * Adds Count Items for Category Manager.
     *
     * @param   \stdClass[]  $items    The category objects
     * @param   string       $section  The section
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function countItems(array $items, string $section)
    {
        $config = (object) array(
            'related_tbl'    => 'content',
            'state_col'      => 'state',
            'group_col'      => 'catid',
            'relation_type'  => 'category_or_group',
            'uses_workflows' => true,
            'workflows_component' => 'com_content'
        );

        LibraryContentHelper::countRelations($items, $config);
    }

    /**
     * Adds Count Items for Tag Manager.
     *
     * @param   \stdClass[]  $items      The content objects
     * @param   string       $extension  The name of the active view.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function countTagItems(array $items, string $extension)
    {
        $parts   = explode('.', $extension);
        $section = count($parts) > 1 ? $parts[1] : null;

        $config = (object) array(
            'related_tbl'   => ($section === 'category' ? 'categories' : 'content'),
            'state_col'     => ($section === 'category' ? 'published' : 'state'),
            'group_col'     => 'tag_id',
            'extension'     => $extension,
            'relation_type' => 'tag_assigments',
        );

        LibraryContentHelper::countRelations($items, $config);
    }

    /**
     * Prepares the category form
     *
     * @param   Form          $form  The form to prepare
     * @param   array|object  $data  The form data
     *
     * @return void
     */
    public function prepareForm(Form $form, $data)
    {
        ContentHelper::onPrepareForm($form, $data);
    }
}
