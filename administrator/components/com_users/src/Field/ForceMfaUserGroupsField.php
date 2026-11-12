<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\UsergrouplistField;
use Joomla\CMS\Language\Text;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User group selector for enforcing Multi-factor Authentication.
 *
 * @since  6.2.2
 */
class ForceMfaUserGroupsField extends UsergrouplistField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since 6.2.2
     */
    protected $type = 'ForceMfaUserGroups';

    /**
     * Whether an MFA method is currently available.
     *
     * @var  boolean
     * @since 6.2.2
     */
    private $hasActiveMfaMethods = false;

    /**
     * @inheritDoc
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (!parent::setup($element, $value, $group)) {
            return false;
        }

        $this->hasActiveMfaMethods = !empty(MfaHelper::getMfaMethods());

        if (!$this->hasActiveMfaMethods && empty($this->normalizeGroupIds($value))) {
            $this->disabled = true;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, $group = null, ?Registry $input = null)
    {
        if (!$this->hasActiveMfaMethods) {
            $currentGroups = ComponentHelper::getParams('com_users')->get('forceMFAUserGroups', []);
            $newGroups     = $this->normalizeGroupIds($value);
            $currentGroups = $this->normalizeGroupIds($currentGroups);

            if (!empty(array_diff($newGroups, $currentGroups))) {
                return new \RuntimeException(Text::_('COM_USERS_CONFIG_FORCEMFAUSERGROUPS_NO_METHODS_ERROR'));
            }
        }

        return parent::validate($value, $group, $input);
    }

    /**
     * Normalize user group IDs for safe comparison.
     *
     * @param   mixed  $value  Group IDs
     *
     * @return  string[]
     *
     * @since   6.2.2
     */
    private function normalizeGroupIds($value): array
    {
        if (!\is_array($value)) {
            $value = \is_string($value) && str_contains($value, ',') ? explode(',', $value) : [$value];
        }

        $groupIds = array_map('strval', $value);
        $groupIds = array_filter($groupIds, static fn ($groupId) => $groupId !== '' && $groupId !== '0');
        $groupIds = array_values(array_unique($groupIds));
        sort($groupIds, SORT_STRING);

        return $groupIds;
    }

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        $input = parent::getInput();

        if (!$this->hasActiveMfaMethods) {
            $input = '<div class="alert alert-danger w-100" role="alert">'
                . Text::_('COM_USERS_CONFIG_FORCEMFAUSERGROUPS_NO_METHODS_WARNING')
                . '</div>' . $input;
        }

        return $input;
    }
}
