<?php
/**
 * @version     1.3
 * @package     Jokte.Element
 * @copyright   CopyLeft 2012 - 2014 Comunidad Juuntos, Proyecto Jokte!
 * @License     GNU/GPL v3.0
 */

defined ('_JEXEC') or die('Acceso directo a este archivo restringido');


jimport('joomla.form.formfield');

/*
 * Clase de campo de formulario para la plataforma Joomla
 * Provee el control para agregar adjuntos usando el mecanismo para subida de archivos de mootools  
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
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get->get('id', '0', null);

        JHtml::_('script', 'system/progressbar-uncompressed.js', false, true);
        JHtml::_('script', 'system/mootools-file-upload.js', false, true);

        $archivo        = $this->element['archivo'];
        $tipo           = $this->element['tipo'];
        $nombre         = $this->element['nombre'];
        $descripcion    = $this->element['descripcion'];
        $extensiones    = $this->element->getAttribute('extensiones');
        
        // Path subida de archivos
        $path       = $this->element['path'];       

        // Icono eliminar adjunto
        $caneca     = JHtml::_('image', 'media/adjuntos/caneca.png', 'Eliminar adjunto', 'class="img-eliminar"', false);

        $style = array();
        $style[] = '#controles-adjuntos {';
        $style[] = '    width: 180px;';
        $style[] = '    position:relative;';
        $style[] = '}';
        $style[] = '#adjuntos {';
        $style[] = '    float: left;';
        $style[] = '    position:relative;';
        $style[] = '    width: 100%;';
        $style[] = '}';
        $style[] = '.form-adjunto {';
        $style[] = '    margin-bottom: 3px;';
        $style[] = '    position:relative;';
        $style[] = '}';
        $style[] = '.error-msg {';
        $style[] = '    background-color: #f2dede;';
        $style[] = '    border-bottom: 1px solid #ebccd1;';
        $style[] = '    color: #a94442;';
        $style[] = '    font-weight: bold;';
        $style[] = '    padding: 3px 10px;';
        $style[] = '}';
        $style[] = '.warn-msg {';
        $style[] = '    background-color: #fcf8e3;';
        $style[] = '    border-bottom: 1px solid #faebcc;';
        $style[] = '    color: #8a6d3b;';
        $style[] = '    font-weight: bold;';
        $style[] = '    padding: 3px 10px;';
        $style[] = '}';
        $style[] = '.progress {';
        $style[] = '    background-color: #111111;';
        $style[] = '    height: 3px;';
        $style[] = '}';
        $style[] = '.uploaded {';
        $style[] = '    border: 1px solid #cccccc;';
        $style[] = '    position: relative;';
        $style[] = '    height: 38px;';
        $style[] = '}';
        $style[] = '.nombre-archivo {';
        $style[] = '    padding: 0 40px;';
        $style[] = '}';
        $style[] = '.uploaded .btn-adjunto {';
        $style[] = '    position: absolute;';
        $style[] = '    right: 0;';
        $style[] = '    top: 0;';
        $style[] = '}';
        $style[] = '.img-eliminar {';
        $style[] = '    margin: 4px 0;';
        $style[] = '}';

        $script = array();
        $script[] = 'window.addEvent("domready", function(){';

        $script[] = 'aId =('.$id.'==0) ? false : true';

        $script[] = 'var btnAgregarAdjunto = new Element("button", {';
        $script[] = '   id: "btn-agregar-adjunto",';
        $script[] = '   class:"btn-adjunto",';
        $script[] = '       events: {';
        $script[] = '           click: function(event) { ';
        $script[] = '               event.preventDefault();';
        $script[] = '               agregarAdjunto()';
        $script[] = '           }';
        $script[] = '       }';
        $script[] = '}).set("text", "+");';

        $script[] = 'contenedor = $("controles-adjuntos").getParent("fieldset")';

        $script[] = '$("controles-adjuntos").grab(btnAgregarAdjunto);';

        $script[] = '});';

        $script[] = 'var adjuntoCount = 0;';     

        $script[] = 'function agregarAdjunto() {';

        $script[] = '   adjuntoCount++;';

        $script[] = '   var formAdjunto =  new Element("form", {';
        $script[] = '       id: "form-adjunto-" + adjuntoCount,';
        $script[] = '       class: "form-adjunto",';
        $script[] = '       action: "",';
        $script[] = '       name: "form-adjunto-" + adjuntoCount,';
        $script[] = '       enctype: "multipart/form-data"';
        $script[] = '   })';

        $script[] = '   var fieldArchivo = new Element("input", {';
        $script[] = '       id:"campo-adjunto-"+adjuntoCount,';
        $script[] = '       name:"campo-adjunto-"+adjuntoCount,';
        $script[] = '       type:"file"';
        $script[] = '   });';

        $script[] = '   fieldArchivo.addEvents({';
        $script[] = '           "change": function() {'; 
        $script[] = '               if(aId) {';
        $script[] = '                    subirArchivo();';
        $script[] = '               } else { ';
        $script[] = '                   msg = "'.JText::_("COM_CONTENT_ADJUNTOS_MSG_GUARDAR_ARTICULO_PRIMERO").'"';
        $script[] = '                   mostrarMensaje(msg, "warn")';
        $script[] = '               }';
        $script[] = '           }';
        $script[] = '   })';    

        $script[] = '   var btnEliminarAdjunto = new Element("button", {';
        $script[] = '       id: "btn-eliminar-adjunto-"+adjuntoCount,';
        $script[] = '       class:"btn-adjunto",';
        $script[] = '       events: {';
        $script[] = '           click: function(event) {'; 
        $script[] = '               event.preventDefault();';
        $script[] = '               eliminarAdjunto(this)';
        $script[] = '           }';
        $script[] = '       }';
        $script[] = '   }).set({"html": "'.addslashes($caneca).'", "data-id": formAdjunto.id})';

        $script[] = '   fieldArchivo.inject(formAdjunto);';
        $script[] = '   btnEliminarAdjunto.inject(formAdjunto);';
        $script[] = '   $("adjuntos").grab(formAdjunto);';

        $script[] = '   function subirArchivo() {';
        $script[] = '       var upload = new File.Upload({';
        $script[] = '           url: "'.JURI::root().'administrator/index.php?option=com_content&task=adjuntos.subir&format=json",';
        $script[] = '           data: {';
        $script[] = '               "campo":"campo-adjunto-"+adjuntoCount,';
        $script[] = '               "exts":"'.$extensiones.'",';
        $script[] = '               "id":'.$id.'},';
        $script[] = '           images: ["campo-adjunto-"+adjuntoCount],';
        $script[] = '           adjuntoId: adjuntoCount,'; 
        $script[] = '           onComplete: function(res) {';
        $script[] = '               var data = JSON.decode(res);';
        $script[] = '               if (data.tipo == "error" || data.tipo == "warn") {';
        $script[] = '                   mostrarMensaje(data.msg, data.tipo);';
        $script[] = '                   return;';
        $script[] = '               }';
        $script[] = '               var nombreArchivo = new Element("div",{';
        $script[] = '                   class:"nombre-archivo"';
        $script[] = '               }).set("text", data.nombreArchivo);';
        $script[] = '               btnEliminarAdjunto.set("data-hash", data.hash)';
        $script[] = '               uploaded = formAdjunto.getElements(".uploaded")';
        $script[] = '               uploaded.grab(nombreArchivo,"top");';
        $script[] = '               console.log(data);';
        $script[] = '           }';
        $script[] = '       });';
        $script[] = '       upload.send();';
        $script[] = '   }';

        $script[] = '}';

        $script[] = 'function eliminarAdjunto(el) {';
        $script[] = '   var hash = el.get("data-hash");';
        $script[] = '   var request = new Request.JSON({';
        $script[] = '       url: "'.JURI::root().'administrator/index.php?option=com_content&task=adjuntos.borrar&format=json",';
        $script[] = '       data: {';
        $script[] = '           "id":'.$id.',';
        $script[] = '           "hash": hash';
        $script[] = '       },';
        $script[] = '       onComplete: function(res) {';
        $script[] = '           console.log(res)';
        $script[] = '       }';
        $script[] = '   }).send();';
        $script[] = '   $(el.get("data-id")).dispose();';
        $script[] = '}';

        $script[] = 'function mostrarMensaje(msg, type) {';
        $script[] = '   el = new Element("div", {';
        $script[] = '       class: type+"-msg"';
        $script[] = '       }).set({"text": msg});';
        $script[] = '   contenedor.grab(el, "before");';
        $script[] = '   var slide = new Fx.Slide(el).hide().slideIn();';
        $script[] = '';
        $script[] = '';
        $script[] = '}';

        JFactory::getDocument()->addStyleDeclaration(implode("\n", $style));
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        // Salida HTML
        $html = '<div id="controles-adjuntos"></div><div id="adjuntos"></div>';
        
        return $html;
    }

}

?>
