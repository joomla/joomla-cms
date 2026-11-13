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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\UsergrouplistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User group selector for enforcing Multi-factor Authentication.
 *
 * @since  __DEPLOY_VERSION__
 */
class ForceMfaUserGroupsField extends UsergrouplistField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since __DEPLOY_VERSION__
     */
    protected $type = 'ForceMfaUserGroups';

    /**
     * Whether an MFA method is currently available.
     *
     * @var  boolean
     * @since __DEPLOY_VERSION__
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
                return new \RuntimeException(
                    Text::sprintf('COM_USERS_CONFIG_FORCEMFAUSERGROUPS_NO_METHODS_ERROR', $this->getMfaPluginLinks())
                );
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
     * @since   __DEPLOY_VERSION__
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
                . Text::sprintf('COM_USERS_CONFIG_FORCEMFAUSERGROUPS_NO_METHODS_WARNING', $this->getMfaPluginLinks())
                . '</div>' . $input;
        }

        return $input;
    }

    /**
     * Create modal links to the installed MFA plugin settings.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getMfaPluginLinks(): string
    {
        $app = Factory::getApplication();

        if (!$app->getIdentity()->authorise('core.edit', 'com_plugins')) {
            return '';
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['extension_id', 'name', 'element']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('multifactorauth'))
            ->order($db->quoteName('name'));

        $plugins  = $db->setQuery($query)->loadObjectList();
        $language = $app->getLanguage();
        $links    = [];

        foreach ($plugins as $plugin) {
            $language->load('plg_multifactorauth_' . $plugin->element . '.sys', JPATH_ADMINISTRATOR)
                || $language->load('plg_multifactorauth_' . $plugin->element . '.sys', JPATH_PLUGINS . '/multifactorauth/' . $plugin->element);

            $pluginName = Text::_($plugin->name);
            $options    = [
                'popupType'  => 'iframe',
                'textHeader' => $pluginName,
                'src'        => Route::_(
                    'index.php?option=com_plugins&task=plugin.edit&extension_id=' . (int) $plugin->extension_id . '&tmpl=component&layout=modal',
                    false
                ),
            ];

            $links[] = HTMLHelper::_(
                'link',
                '#',
                $pluginName,
                [
                    'class'                 => 'alert-link',
                    'data-joomla-dialog'    => json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'data-close-on-message' => '',
                    'data-mfa-plugin-link'  => '',
                ]
            );
        }

        if ($links) {
            $app->getDocument()->getWebAssetManager()->useScript('joomla.dialog-autocreate');
        }

        return implode(', ', $links);
    }
}
