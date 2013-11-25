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

        $script = array();
        $script[] = 'window.addEvent("domready", function(){';
        $script[] = 'var btnAgregarAdjunto = new Element("button", {';
        $script[] = '   id: "btn-agregar-adjunto",';
        $script[] = '       events: {';
        $script[] = '           click: function() { agregarAdjunto() } ';
        $script[] = '       }';
        $script[] = '}).set("text", "+");';

        $script[] = '$("controles-adjuntos").grab(btnAgregarAdjunto);';

        $script[] = 'var adjuntoCount = 0;';     

        $script[] = 'function agregarAdjunto() {';

        $script[] = '   adjuntoCount++;';
        $script[] = '   var campoAdjunto =  new Element("div", {';
        $script[] = '       id: "adjunto-" + adjuntoCount,';
        $script[] = '       class: "campo-adjunto"';
        $script[] = '   })';
        $script[] = '   var fldArchivo = new Element("input", {';
        $script[] = '       type:"file",';
        $script[] = '       events: {';
        $script[] = '           change: function() { subirArchivo() }';
        $script[] = '       }';    
        $script[] = '   });';

        $script[] = '   var btnEliminarAdjunto = new Element("button", {';
        $script[] = '       id: "btn-eliminar-adjunto",';
        $script[] = '       events: {';
        $script[] = '           click: function() { eliminarAdjunto(this) }';
        $script[] = '       }';
        $script[] = '   }).set({"text": "-", "data-id": campoAdjunto.id})';

        $script[] = '   fldArchivo.inject(campoAdjunto);';
        $script[] = '   btnEliminarAdjunto.inject(campoAdjunto);';

        $script[] = '   $("adjuntos").grab(campoAdjunto);';

        $script[] = '}';

        $script[] = 'function eliminarAdjunto(el) {';
        $script[] = '   $(el.get("data-id")).dispose();';
        $script[] = '}';

        $script[] = 'function subirArchivo() {';
        $script[] = '   console.log("subiendo")';
        $script[] = '}';
        $script[] = '});';

        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        // Salida HTML
        $html = '<div id="controles-adjuntos"></div><div id="adjuntos"></div>';
        
        return $html;
    }

}

?>
