<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2026 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Categories\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Multiple category selection field.
 *
 * @since __DEPLOY_VERSION__
 */
class CategoryMultipleField extends CategoryeditField
{
    /**
     * Field type.
     *
     * @var    string
     *
     * @since  __DEPLOY_VERSION__
     */
    public $type = 'CategoryMultiple';

    /**
     * Layout to use for the field.
     *
     * @var string
     *
     * @since __DEPLOY_VERSION__
     */
    protected $layout = 'joomla.form.field.categoryedit';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getOptions()
    {
        $options   = [];
        $published = $this->element['published'] ? explode(',', (string) $this->element['published']) : [0, 1, 2];

        $extension = $this->element['extension'] ? (string) $this->element['extension'] : 'com_content';

        // Always use the primary category as ACL reference.
        $primaryCategoryField = !empty($this->element['primarycategoryfield']) ? (string) $this->element['primarycategoryfield'] : 'catid';

        $primaryCatId = (int) $this->form->getValue($primaryCategoryField, 0);

        $user       = $this->getCurrentUser();
        $viewLevels = $user->getAuthorisedViewLevels();
        $state      = ArrayHelper::toInteger($published);

        $root = Factory::getApplication()->bootComponent($extension)->getCategory([ 'published' => [0, 1, 2]])->get('root');
        foreach ($root->getChildren(true) as $category) {
            if (!\in_array((int) $category->published, $state, true)) {
                continue;
            }

            if (!$user->authorise('core.admin') && !\in_array((int) $category->access, $viewLevels, true)) {
                continue;
            }

            $option            = new \stdClass();
            $option->value     = $category->id;
            $option->text      = $category->title;
            $option->level     = $category->level;
            $option->published = $category->published;
            $option->language  = $category->language;

            $options[] = $option;
        }

        foreach ($options as $option) {
            if ($option->published == 1) {
                $option->text = str_repeat('- ', max(0, $option->level - 1)) . $option->text;
            } else {
                $option->text = str_repeat('- ', max(0, $option->level - 1)) . '[' . $option->text . ']';
            }

            if ($option->published === 0) {
                $option->text .= ' (' . Text::_('JUNPUBLISHED') . ')';
            } elseif ($option->published === 2) {
                $option->text .= ' (' . Text::_('JARCHIVED') . ')';
            }

            if ($option->language !== '*') {
                $option->text .= ' (' . $option->language . ')';
            }
        }

        if ($primaryCatId === 0) {
            foreach ($options as $i => $option) {
                if (!$user->authorise('core.create', $extension . '.category.' . $option->value)) {
                    unset($options[$i]);
                }
            }
        } else {
            $currentAsset = $extension . '.category.' . $primaryCatId;

            foreach ($options as $i => $option) {
                if ((int) $option->value != $primaryCatId && !$user->authorise('core.edit.state', $currentAsset)) {
                    unset($options[$i]);
                    continue;
                }

                $targetAsset = $extension . '.category.' . $option->value;

                if ((int) $option->value != $primaryCatId && !$user->authorise('core.create', $targetAsset)) {
                    unset($options[$i]);
                }
            }
        }

        return $options;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        // Pass the exact variables the categoryedit layout expects
        $data['options']        = $this->getOptions();
        $data['allowCustom']    = $this->allowAdd;
        $data['customPrefix']   = $this->customPrefix;
        $data['refreshPage']    = (bool) ($this->element['refresh-enabled'] ?? false);
        $data['refreshCatId']   = (string) ($this->element['refresh-cat-id'] ?? '');
        $data['refreshSection'] = (string) ($this->element['refresh-section'] ?? '');

        $renderer = $this->getRenderer($this->layout);
        $renderer->setComponent('com_categories');
        $renderer->setClient(1);

        $html = $renderer->render($data);

        // Load external JS and pass the field id
        if ($data['refreshPage']) {
            $document = Factory::getApplication()->getDocument();
            $wa       = $document->getWebAssetManager();

            // Register and attach the external JS file
            $wa->registerAndUseScript('field.category-multiple-change', 'layouts/joomla/form/field/category-multiple-change.min.js', [], ['defer' => true], ['core']);

            // Pass the specific field ID to the external JS file.
            $document->addScriptOptions('category-multiple-change', $this->id);
        }

        return $html;
    }
}
