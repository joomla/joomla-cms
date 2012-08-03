<?php
/**
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/

// Acceso directo prohibido
defined('_JEXEC') or die;

// Cargar aplicación
$app = JFactory::getApplication();

// Cargar parámetros de la plantilla
$tpl_params = $app->getTemplate(true)->params;

// Cargar skins
$skin = $tpl_params->get('skincss');

// Armo rutas
$baseurlskin =  $this->baseurl.'/templates/'.$this->template.'/css/skins/'.$skin;

?>
