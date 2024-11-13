<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.sql
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\SQL\Extension;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Event\Model\BeforeSaveEvent;
use Joomla\CMS\Form\Form;
use Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields SQL Plugin
 *
 * @since  3.7.0
 */
final class SQL extends FieldsListPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onContentBeforeSave' => 'contentBeforeSave',
        ]);
    }

    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   \stdClass    $field   The field.
     * @param   \DOMElement  $parent  The field node parent.
     * @param   Form         $form    The form.
     *
     * @return  ?\DOMElement
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom($field, \DOMElement $parent, Form $form)
    {
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

        if (!$fieldNode) {
            return $fieldNode;
        }

        $fieldNode->setAttribute('value_field', 'text');
        $fieldNode->setAttribute('key_field', 'value');

        return $fieldNode;
    }

    /**
     * The save event.
     *
     * @param   BeforeSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function contentBeforeSave(BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();

        // Only work on new SQL fields
        if ($context != 'com_fields.field' || !isset($item->type) || $item->type != 'sql') {
            return;
        }

        // If we are not a super admin, don't let the user create or update a SQL field
        if (!Access::getAssetRules(1)->allow('core.admin', $this->getApplication()->getIdentity()->getAuthorisedGroups())) {
            $item->setError($this->getApplication()->getLanguage()->_('PLG_FIELDS_SQL_CREATE_NOT_POSSIBLE'));

            $event->addResult(false);
        }
    }
}
