<?php
/**
 * @version     1.0.0
 * @package     Jokte.element
 * @copyright   CopyLeft 2012 - 2013 Comunidad Juuntos, Proyecto Jokte!
 * @License     GNU/GPL v3.0
 */

defined ('_JEXEC') or die('Acceso directo a este archivo restringido');

jimport('joomla.form.formfield');

/*
 * Clase de campo de formulario para la plataforma Joomla
 * Provee el control para agregar adjuntos usando el mecanismo subida de archivos 
 *
 */

class JFormFieldAdjuntos extends JFormField
{
    /**
     * Element name     Adjuntos
     * @access          protected
     * @var             string
     */

    protected $type = 'Adjuntos';

    function getInput() 
    {
        $archivo    = $this->element['archivo'];
        $tipo       = $this->element['tipo'];
        $nombre     = $this->element['nombre'];
        $descripcion= $this->element['descripcion'];
        
        // Path subida de archivos
        $path       = $this->element['path'];       

        // Salida HTML
        $html = '<input type="file" name="'.$this->name.'" accept="video/*" />';
        
        return $html;
    }

}

?>
