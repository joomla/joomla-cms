<?php
/**
 * @package		Jokte.Site
 * @subpackage	com_ayuda
 * @copyright	Copyleft 2012 - 2014 Comunidad Juuntos.
 * @license		GNU General Public License version 3
 */

// Acceso directo a este archivo prohibido.
defined('_JEXEC') or die;

/**
 * Ayuda controller
 *
 * @static
 * @package		Jokte.Site
 * @subpackage	com_ayuda
 * @since 1.2.2
 */
class AyudaController extends JControllerLegacy
{
	/**
	 * Metodo para mostrar una salida raw
	 * @since	1.2.2
	 */
	 
	public function ayuda() 
	{
		JRequest::setVar('view', 'Ayuda');
		parent::display();
	}
}
