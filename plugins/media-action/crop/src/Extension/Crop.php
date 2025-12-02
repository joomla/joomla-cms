<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.crop
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\MediaAction\Crop\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Form\Form;
use Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Crop Action
 *
 * @since  4.0.0
 */
final class Crop extends MediaActionPlugin implements SubscriberInterface
{
    /**
     * The form event. Load additional parameters when available into the field form.
     * Override to dynamically inject aspect ratios from plugin settings.
     *
     * @param   Form       $form  The form
     * @param   \stdClass  $data  The data (required by the event interface, not used in this implementation)
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @note    The $data parameter is required by the Joomla event interface but is not used in this method.
     */
    public function onContentPrepareForm(Form $form, $data): void
    {
        // Check if it is the right form
        if ($form->getName() !== 'com_media.file') {
            return;
        }

        $this->loadCss();
        $this->loadJs();

        // The file with the params for the edit view
        $paramsFile = JPATH_PLUGINS . '/media-action/' . $this->_name . '/form/' . $this->_name . '.xml';

        // When the file exists, load it into the form
        if (file_exists($paramsFile)) {
            $form->loadFile($paramsFile);

            // Get the aspect ratios from plugin parameters
            $aspectRatios = $this->params->get('aspect_ratios');

            // Convert to array if needed (handles stdClass from Registry)
            if (\is_object($aspectRatios)) {
                $aspectRatios = (array) $aspectRatios;
            }

            // If we have custom aspect ratios, modify the form field
            if (!empty($aspectRatios) && \is_array($aspectRatios)) {
                $this->injectAspectRatios($form, $aspectRatios);
            }
        }
    }

    /**
     * Inject custom aspect ratios into the crop form
     *
     * @param   Form   $form          The form object
     * @param   array  $aspectRatios  The aspect ratios from plugin settings
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function injectAspectRatios(Form $form, array $aspectRatios): void
    {
        // Get the aspectRatio field (try without group first, then with 'crop' group)
        $field = $form->getField('aspectRatio');

        if (!$field) {
            $field = $form->getField('aspectRatio', 'crop');
        }

        if (!$field) {
            return;
        }

        // Build new XML for the field with custom options
        $xml = new \SimpleXMLElement('<field/>');
        $xml->addAttribute('name', 'aspectRatio');
        $xml->addAttribute('type', 'groupedlist');
        $xml->addAttribute('label', 'PLG_MEDIA-ACTION_CROP_PARAM_ASPECT');
        $xml->addAttribute('hiddenLabel', 'true');
        $xml->addAttribute('class', 'crop-aspect-ratio-options');
        $xml->addAttribute('default', '10/9');

        // Add default options
        $option = $xml->addChild('option', 'PLG_MEDIA-ACTION_CROP_PARAM_DEFAULT_RATIO');
        $option->addAttribute('class', 'crop-aspect-ratio-option');
        $option->addAttribute('value', '10/9');

        $option = $xml->addChild('option', 'PLG_MEDIA-ACTION_CROP_PARAM_NO_RATIO');
        $option->addAttribute('class', 'crop-aspect-ratio-option');
        $option->addAttribute('value', '');

        // Group ratios by landscape/portrait
        $grouped = [
            ''          => [],
            'landscape' => [],
            'portrait'  => [],
        ];

        foreach ($aspectRatios as $ratio) {
            // Convert individual ratio to array if it's an object
            $ratio = (array) $ratio;

            $label = $ratio['label'] ?? '';
            $value = $ratio['value'] ?? '';
            $group = strtolower(trim($ratio['group'] ?? ''));

            if (empty($label) || $value === '' || $value === null) {
                continue;
            }

            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            $grouped[$group][] = [
                'label' => $label,
                'value' => $value,
                'group' => $group,
            ];
        }

        // Add ungrouped ratios
        foreach ($grouped[''] as $ratio) {
            $option = $xml->addChild('option', htmlspecialchars($ratio['label'], ENT_XML1, 'UTF-8'));
            $option->addAttribute('class', 'crop-aspect-ratio-option');
            $option->addAttribute('value', htmlspecialchars($ratio['value'], ENT_XML1, 'UTF-8'));
        }

        // Add landscape group
        if (!empty($grouped['landscape'])) {
            $group = $xml->addChild('group');
            $group->addAttribute('label', 'PLG_MEDIA-ACTION_CROP_PARAM_LANDSCAPE');

            foreach ($grouped['landscape'] as $ratio) {
                $option = $group->addChild('option', htmlspecialchars($ratio['label'], ENT_XML1, 'UTF-8'));
                $option->addAttribute('class', 'crop-aspect-ratio-option');
                $option->addAttribute('value', htmlspecialchars($ratio['value'], ENT_XML1, 'UTF-8'));
            }
        }

        // Add portrait group
        if (!empty($grouped['portrait'])) {
            $group = $xml->addChild('group');
            $group->addAttribute('label', 'PLG_MEDIA-ACTION_CROP_PARAM_PORTRAIT');

            foreach ($grouped['portrait'] as $ratio) {
                $option = $group->addChild('option', htmlspecialchars($ratio['label'], ENT_XML1, 'UTF-8'));
                $option->addAttribute('class', 'crop-aspect-ratio-option');
                $option->addAttribute('value', htmlspecialchars($ratio['value'], ENT_XML1, 'UTF-8'));
            }
        }

        // Replace the field in the form
        // Try setting in default group first, then 'crop' group
        if (!$form->setField($xml, null, true)) {
            $form->setField($xml, 'crop', true);
        }
    }

    /**
     * Load the javascript files of the plugin.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadJs(): void
    {
        parent::loadJs();

        if (!$this->getApplication() instanceof CMSWebApplicationInterface) {
            return;
        }

        $this->getApplication()->getDocument()->getWebAssetManager()->useScript('cropper-module');
    }

    /**
     * Load the CSS files of the plugin.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadCss(): void
    {
        parent::loadCss();

        if (!$this->getApplication() instanceof CMSWebApplicationInterface) {
            return;
        }

        $this->getApplication()->getDocument()->getWebAssetManager()->useStyle('cropperjs');
    }
}
