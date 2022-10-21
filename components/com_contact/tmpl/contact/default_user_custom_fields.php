<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;

$params             = $this->item->params;

$displayGroups      = $params->get('show_user_custom_fields');
$userFieldGroups    = array();
?>

<?php if (!$displayGroups || !$this->contactUser) : ?>
    <?php return; ?>
<?php endif; ?>

<?php foreach ($this->contactUser->jcfields as $field) : ?>
    <?php if ($field->value && (in_array('-1', $displayGroups) || in_array($field->group_id, $displayGroups))) : ?>
        <?php $userFieldGroups[$field->group_title][] = $field; ?>
    <?php endif; ?>
<?php endforeach; ?>

<?php foreach ($userFieldGroups as $groupTitle => $fields) : ?>
    <?php $id = ApplicationHelper::stringURLSafe($groupTitle); ?>
    <?php echo '<h3>' . ($groupTitle ?: Text::_('COM_CONTACT_USER_FIELDS')) . '</h3>'; ?>

    <div class="com-contact__user-fields contact-profile" id="user-custom-fields-<?php echo $id; ?>">
        <dl class="dl-horizontal">
        <?php foreach ($fields as $field) : ?>
            <?php if (!$field->value) : ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if ($field->params->get('showlabel')) : ?>
                <?php echo '<dt>' . Text::_($field->label) . '</dt>'; ?>
            <?php endif; ?>

            <?php echo '<dd>' . $field->value . '</dd>'; ?>
        <?php endforeach; ?>
        </dl>
    </div>
<?php endforeach; ?>
