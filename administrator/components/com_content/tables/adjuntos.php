<?php
/**
 * @version     1.3
 * @package     Jokte.Administrator
 * @subpackage  com_content
 *
 * @copyright   CopyLeft 2012 - 2014 Comunidad Juuntos, Proyecto Jokte!
 * @License     GNU/GPL v3.0
 */

// Restringir el acceso directo a este archivo
defined('_JEXEC') or die();

class TableAdjuntos extends JTable
{
    var $id = null;
    var $propietario_id = null;
    var $nombre_archivo = null;
    var $ruta = null;
    var $hash = null;

    function __construct(&$db)
    {
        parent::__construct('#__adjuntos', 'id', $db);
    }
}
