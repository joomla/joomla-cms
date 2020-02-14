<?php

defined('_JEXEC') or die;

/**
 * Admin helper.
 *
 * @since       3.0
 */
class JceHelperAdmin
{
    /**
     * Configure the Submenu links.
     *
     * @param string $vName The view name
     *
     * @since   3.0
     */
    public static function addSubmenu($vName)
    {
        $uri = (string)JUri::getInstance();
        $return = urlencode(base64_encode($uri));

        $user = JFactory::getUser();

        JHtmlSidebar::addEntry(
            JText::_('WF_CPANEL'),
            'index.php?option=com_jce&view=cpanel',
            $vName == 'cpanel'
        );

        $views = array(
            'config'    => 'WF_CONFIGURATION',
            'profiles'  => 'WF_PROFILES',
            'browser'   => 'WF_CPANEL_BROWSER',
            'mediabox'  => 'WF_MEDIABOX'
        );

        foreach($views as $key => $label) {
            
            if ($key === "mediabox" && !JPluginHelper::isEnabled('system', 'jcemediabox')) {
                continue;
            }
            
            if ($user->authorise('jce.' . $key, 'com_jce')) {
                JHtmlSidebar::addEntry(
                    JText::_($label),
                    'index.php?option=com_jce&view=' . $key,
                    $vName == $key
                );
            }
        }
    }

    public static function getTemplateStylesheets()
    {
        require_once(JPATH_SITE . '/components/com_jce/editor/libraries/classes/editor.php');

        return WFEditor::getTemplateStyleSheets();
    }
}
