<?php
/**
 * @package		Jokte.Site
 * @subpackage	com_ayuda
 * @copyright	Copyleft 2012 - 2014 Comunidad Juuntos.
 * @license		GNU General Public License version 3
 */

// Acceso directo a este archivo prohibido.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Ayuda view
 *
 * @static
 * @package		Jokte.Site
 * @subpackage	com_ayuda
 * @since 1.2.2
 */
class AyudaViewAyuda extends JView
{
    function display($tpl = null)
    {
		$app	 = JFactory::getApplication();
		$urlkey  = JRequest::getVar('key');
		$urlang  = JRequest::getVar('lang');
		$url ="http://ayuda.jokte.org/".$urlang.'/'.$urlkey.'.html'; 
		$doc = new DOMDocument();
		$doc->loadHTMLFile($url);
		echo $doc->saveHTML();
    }
}