<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */

class JceViewPopup extends JViewLegacy
{
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();

        $document->addScript(JURI::root(true) . '/components/com_jce/media/js/popup.js');
        $document->addStylesheet(JURI::root(true) . '/components/com_jce/media/css/popup.css');

        // Get variables
        $img    = $app->input->get('img', '', 'STRING');
        $title  = $app->input->getWord('title');
        $mode   = $app->input->getInt('mode', '0');
        $click  = $app->input->getInt('click', '0');
        $print  = $app->input->getInt('print', '0');

        $dim = array('', '');

        if (strpos('://', $img) === false) {
            $path = JPATH_SITE . '/' . trim(str_replace(JURI::root(), '', $img), '/');
            if (is_file($path)) {
                $dim = @getimagesize($path);
            }
        }

        $width  = $app->input->getInt('w', $app->input->getInt('width', ''));
        $height = $app->input->getInt('h', $app->input->getInt('height', ''));

        if (!$width) {
            $width = $dim[0];
        }

        if (!$height) {
            $height = $dim[1];
        }

        // Cleanup img variable
        $img = preg_replace('/[^a-z0-9\.\/_-]/i', '', $img);

        $title = isset($title) ? str_replace('_', ' ', $title) : basename($img);
        // img src must be passed
        if ($img) {
            $features = array(
                'img' => str_replace(JURI::root(), '', $img),
                'title' => $title,
                'alt' => $title,
                'mode' => $mode,
                'click' => $click,
                'print' => $print,
                'width' => $width,
                'height' => $height,
            );

            $document->addScriptDeclaration('(function(){WfWindowPopup.init(' . $width . ', ' . $height . ', ' . $click . ');})();');

            $this->features = $features;
        } else {
            $app->redirect('index.php');
        }

        parent::display($tpl);
    }
}
