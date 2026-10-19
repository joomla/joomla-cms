<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Workflow;

use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Collects the checks that automation filters and conditions may be built on.
 *
 * Plugins call addField() once per check they provide. Item checks, such as a tag, are offered in
 * a rule's filter, and moment checks, such as the day of the week, in its fire condition.
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowConditionFieldsEvent extends Event
{
    /**
     * A check that describes the item, offered in a rule's filter.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const SCOPE_ITEM = 'item';

    /**
     * A check that describes the current moment, offered in a rule's condition.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const SCOPE_MOMENT = 'moment';

    /**
     * The comparisons a check may declare support for. The values are stored inside saved
     * rules, so they must never change.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    public const OPERATOR_IS = 'is';

    public const OPERATOR_IS_NOT = 'is not';

    public const OPERATOR_IN = 'in';

    public const OPERATOR_NOT_IN = 'not in';

    public const OPERATOR_HAS_ANY = 'has any';

    public const OPERATOR_HAS_ALL = 'has all';

    public const OPERATOR_HAS_NONE = 'has none';

    public const OPERATOR_BEFORE = 'before';

    public const OPERATOR_AFTER = 'after';

    public const OPERATOR_ON = 'on';

    public const OPERATOR_NOT_ON = 'not on';

    public const OPERATOR_GREATER_THAN = 'greater than';

    public const OPERATOR_LESS_THAN = 'less than';

    /**
     * How a check's value is entered. Select and multiselect pick from the options
     * a check supplies.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */

    public const VALUE_SELECT = 'select';

    public const VALUE_MULTISELECT = 'multiselect';

    public const VALUE_DATE = 'date';

    public const VALUE_TEXT = 'text';

    public const VALUE_NUMBER = 'number';

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  Must contain 'extension', e.g. com_content.article.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        $arguments['fields'] = [];

        parent::__construct($name, $arguments);
    }

    /**
     * The workflow extension the builder is being drawn for, e.g. com_content.article.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getExtension(): string
    {
        return (string) ($this->arguments['extension'] ?? '');
    }

    /**
     * Offers one check to the condition builder.
     *
     * @param   string    $name       Unique key for the check, stored in the saved rule. Prefix
     *                                it with your extension to avoid clashing, e.g. "fields.priority".
     * @param   string    $label      Translated label shown in the field dropdown.
     * @param   string    $scope      self::SCOPE_ITEM or self::SCOPE_MOMENT.
     * @param   string[]  $operators  Operator keys this check supports, e.g. ['is', 'is not'].
     *                                Only keys com_workflow knows are offered, so a check written
     *                                against a newer Joomla loses that operator rather than
     *                                breaking the builder.
     * @param   string    $valueType  How the value is entered: select or multiselect to pick from
     *                                $options, or date, text or number to type a value freely.
     * @param   array     $options    Selectable values as ['value' => ..., 'label' => ...] pairs.
     *                                Ignored by the typed value types.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addField(
        string $name,
        string $label,
        string $scope,
        array $operators,
        string $valueType = 'select',
        array $options = []
    ): void {
        $fields = $this->arguments['fields'];

        $fields[$name] = [
            'name'      => $name,
            'label'     => $label,
            'scope'     => $scope === self::SCOPE_MOMENT ? self::SCOPE_MOMENT : self::SCOPE_ITEM,
            'operators' => $operators,
            'valueType' => $valueType,
            'options'   => $options,
        ];

        $this->arguments['fields'] = $fields;
    }

    /**
     * Every check offered, keyed by name.
     *
     * @return  array<string, array>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFields(): array
    {
        return $this->arguments['fields'];
    }
}
