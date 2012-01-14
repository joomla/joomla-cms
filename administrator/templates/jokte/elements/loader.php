<?php
/**
 * @version     $Id: text.php 15576 2010-03-25 12:43:26Z louis $
 * @package     Joomla.Framework
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Framework
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldLoader extends JFormField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'Loader';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     * @since   1.6
     */
    function getInput(){
        $document = JFactory::getDocument();
        $assets = $document->addScript(JURI::root(1) . '/administrator/templates/minima/js/raphael/raphael.min.js');
        $assets .= $document->addScript(JURI::root(1) . '/administrator/templates/minima/js/libs/minima.colors.js');
        return $assets;
    }
}
