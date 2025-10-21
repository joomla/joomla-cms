<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.note
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Note\Extension;

use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Note Plugin
 *
 * @since  6.0.0
 */
final class Note extends FieldsPlugin implements SubscriberInterface
{
    /**
     * The form event. Load additional parameters when available into the field form.
     * Only when the type of the form is of interest.
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function prepareForm(PrepareFormEvent $event)
    {
        parent::prepareForm($event);

        $form = $event->getForm();
        $data = $event->getData();

        $type = ArrayHelper::getValue((array) $data, 'type');

        if (!$this->getApplication()->isClient('administrator') || $form->getName() !== 'com_fields.field.com_content.article' || $type !== 'note') {
            return;
        }

        $form->removeField('default_value');
        $form->removeField('required');
        $form->removeField('hint', 'params');
        $form->removeField('class', 'params');
        $form->removeField('label_class', 'params');
        $form->removeField('showlabel', 'params');
        $form->removeField('label_render_class', 'params');
        $form->removeField('render_class', 'params');
        $form->removeField('value_render_class', 'params');
        $form->removeField('show_on', 'params');
        $form->removeField('prefix', 'params');
        $form->removeField('suffix', 'params');
        $form->removeField('display_readonly', 'params');

        $xml = $form->getXml();

        foreach ($xml->xpath('//fieldset[@name="smartsearchoptions"]') as $fieldset) {
            $dom = dom_import_simplexml($fieldset);
            $dom->parentNode->removeChild($dom);
        }
    }
}
