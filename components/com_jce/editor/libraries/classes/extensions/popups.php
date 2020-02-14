<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class WFPopupsExtension extends WFExtension
{
    protected static $instance;

    private $_popups = array();
    private $_templates = array();

    /**
     * Constructor activating the default information of the class.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->setProperties($config);
    }

    /**
     * Returns a reference to a plugin object.
     *
     * This method must be invoked as:
     *    <pre>  $advlink =AdvLink::getInstance();</pre>
     *
     * @return JCE The editor object
     *
     * @since 1.5
     */
    public static function getInstance($config = array())
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public function display()
    {
        parent::display();

        $document = WFDocument::getInstance();

        // get all popups extensions
        $popups = parent::loadExtensions('popups');

        $config = $this->getProperties();

        if ($config) {
            // Create global config
            $document->addScriptDeclaration('WFExtensions.Popups.setConfig('.json_encode($config).');');
        }

        // Create an instance of each popup and check if enabled
        foreach ($popups as $item) {
            $popup = $this->getPopupExtension($item->name);

            if ($popup->isEnabled()) {
                $this->addPopup($item);

                $params = $popup->getParams();

                if (!empty($params)) {
                    $document->addScriptDeclaration('WFExtensions.Popups.setParams("'.$item->name.'",'.json_encode($params).');');
                }
            }
        }

        $tabs = WFTabs::getInstance();

        // Add popup tab and assign popups reference to document
        if (count($this->getPopups())) {
            $tabs->addTab('popups');
            $tabs->getPanel('popups')->assign('popups', $this);
        }
    }

    private function getPopups()
    {
        return $this->_popups;
    }

    public function addPopup($popup)
    {
        $this->_popups[] = $popup;
    }

    private function getTemplates()
    {
        return $this->_templates;
    }

    public function addTemplate($template)
    {
        $this->_templates[] = $template;
    }

    private function getPopupExtension($name)
    {
        static $popups = array();

        if (!isset($popups[$name])) {
            $classname = 'WFPopupsExtension_'.ucfirst($name);
            $popups[$name] = new $classname();
        }

        return $popups[$name];
    }

    public function getPopupList()
    {
        $options = array();

        $options[] = JHTML::_('select.option', '', '-- '.JText::_('WF_POPUP_TYPE_SELECT').' --');

        foreach ($this->getPopups() as $popup) {
            $options[] = JHTML::_('select.option', $popup->name, JText::_('WF_POPUPS_'.strtoupper($popup->name).'_TITLE'));
        }

        return JHTML::_('select.genericlist', $options, 'popup_list', '', 'value', 'text', $this->get('default'));
    }

    public function getPopupTemplates()
    {
        $output = '';

        foreach ($this->getTemplates() as $template) {
            $wf = WFEditorPlugin::getInstance();
            $view = $wf->getView();

            $output .= $view->loadTemplate($template);
        }

        foreach ($this->getPopups() as $popup) {
            $view = new WFView(array(
                'name' => $popup->name,
                'base_path' => $popup->path,
                'template_path' => $popup->path.'/tmpl',
            ));

            $instance = $this->getPopupExtension($popup->name);
            $view->assign('popup', $instance);

            if (file_exists($popup->path.'/tmpl/default.php')) {
                ob_start();

                $output .= '<div id="popup_extension_'.$popup->name.'" style="display:none;">';

                $view->display();

                $output .= ob_get_contents();
                $output .= '</div>';
                ob_end_clean();
            }
        }

        return $output;
    }
}
