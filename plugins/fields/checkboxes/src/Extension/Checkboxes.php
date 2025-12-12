<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.checkboxes
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Checkboxes\Extension;

use Joomla\CMS\Event\CustomFields\BeforePrepareFieldEvent;
use Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Checkboxes Plugin
 *
 * @since  3.7.0
 */
final class Checkboxes extends FieldsListPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onCustomFieldsBeforePrepareField' => 'beforePrepareField',
        ]);
    }

    /**
     * Before prepares the field value.
     *
     * @param   BeforePrepareFieldEvent $event    The event instance.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function beforePrepareField(BeforePrepareFieldEvent $event): void
    {
        if (!$this->getApplication()->isClient('api')) {
            return;
        }

        $field = $event->getField();

        if (!$this->isTypeSupported($field->type)) {
            return;
        }

        $field->apivalue = [];

        $options = $this->getOptionsFromField($field);

        if (empty($field->value)) {
            return;
        }

        if (\is_array($field->value)) {
            foreach ($field->value as $key => $value) {
                $field->apivalue[$value] = $options[$value];
            }
        } else {
            $field->apivalue[$field->value] = $options[$field->value];
        }
    }
}
