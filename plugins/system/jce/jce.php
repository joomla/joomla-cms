<?php

/**
 * @copyright   Copyright (C) 2015 Ryan Demmer. All rights reserved
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later
 */
defined('JPATH_BASE') or die;

/**
 * JCE.
 *
 * @since       2.5.5
 */
class PlgSystemJce extends JPlugin
{
    public function onPlgSystemJceContentPrepareForm($form, $data)
    {
        return $this->onContentPrepareForm($form, $data);
    }

    /**
     * adds additional fields to the user editing form.
     *
     * @param JForm $form The form to be altered
     * @param mixed $data The associated data for the form
     *
     * @return bool
     *
     * @since   2.5.20
     */
    public function onContentPrepareForm($form, $data)
    {
        $app = JFactory::getApplication();

        $version = new JVersion();

        if (!$version->isCompatible('3.4')) {
            return true;
        }

        if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');

            return false;
        }

        $params = JComponentHelper::getParams('com_jce');

        if ((bool) $params->get('replace_media_manager', 1) === false) {
            return;
        }

        // get form name.
        $name = $form->getName();

        if (!$version->isCompatible('3.6')) {
            $valid = array(
                'com_content.article',
                'com_categories.categorycom_content',
                'com_templates.style',
                'com_tags.tag',
                'com_banners.banner',
                'com_contact.contact',
                'com_newsfeeds.newsfeed',
            );

            // only allow some forms, see - https://github.com/joomla/joomla-cms/pull/8657
            if (!in_array($name, $valid)) {
                return true;
            }
        }

        $config = JFactory::getConfig();
        $user = JFactory::getUser();

        if ($user->getParam('editor', $config->get('editor')) !== 'jce') {
            return true;
        }

        if (!JPluginHelper::getPlugin('editors', 'jce')) {
            return true;
        }

        $hasMedia = false;
        $fields = $form->getFieldset();

        foreach ($fields as $field) {
            if (method_exists($field, 'getAttribute') === false) {
                continue;
            }

            $name = $field->getAttribute('name');

            // avoid processing twice
            if (strpos($form->getFieldAttribute($name, 'class'), 'wf-media-input') !== false) {
                continue;
            }

            $type = $field->getAttribute('type');

            if (strtolower($type) === 'media') {
                $group = (string) $field->group;
                $form->setFieldAttribute($name, 'type', 'mediajce', $group);
                $hasMedia = true;
            }
        }
        
        // form has a converted media field
        if ($hasMedia) {
            $form->addFieldPath(JPATH_PLUGINS . '/system/jce/fields');
        }

        return true;
    }
}
