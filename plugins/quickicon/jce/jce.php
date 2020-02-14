<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_JEXEC') or die;

/**
 * JCE File Browser Quick Icon plugin.
 *
 * @since		2.1
 */
class plgQuickiconJce extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        $app = JFactory::getApplication();

        // only in Admin and only if the component is enabled
        if ($app->getClientId() !== 1 || JComponentHelper::getComponent('com_jce', true)->enabled === false) {
            return;
        }

        $this->loadLanguage();
    }

    public function onGetIcons($context)
    {
        if ($context != $this->params->get('context', 'mod_quickicon')) {
            return;
        }

        $user = JFactory::getUser();

        if (!$user->authorise('jce.browser', 'com_jce')) {
            return;
        }

        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR);

        $filter = $this->params->get('filter', '');

        return array(array(
            'link'      => 'index.php?option=com_jce&view=browser&filter=' . $filter,
            'image'     => 'picture fa-file-image-o',
            'access'    => array('jce.browser', 'com_jce'),
            'text'      => JText::_('PLG_QUICKICON_JCE_TITLE'),
            'id'        => 'plg_quickicon_jce',
        ));
    }
}
