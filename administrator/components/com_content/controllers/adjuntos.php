<?php
/**
 * @version     1.3
 * @package     Jokte.Administrator
 * @subpackage  com_content
 *
 * @copyright   CopyLeft 2012 - 2014 Comunidad Juuntos, Proyecto Jokte!
 * @License     GNU/GPL v3.0
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class ContentControllerAdjuntos extends JControllerForm
{
    public function subir() {
        $archivos = $_FILES;
        print_r($archivos);
    }
}
