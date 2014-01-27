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

        $archivo    = $this->element['archivo'];
        $tipo       = $this->element['tipo'];
        $nombre     = $this->element['nombre'];
        $descripcion= $this->element['descripcion'];
        
        // Path subida de archivos
        $path       = $this->element['path'];       

        $style = array();
        $style[] = '#adjuntos {';
        $style[] = '    float: right;';
        $style[] = '    width: 55%;';
        $style[] = '}';
        $style[] = '.error-msg {';
        $style[] = '    background-color: red;';
        $style[] = '    color: yellow;';
        $style[] = '    font-weight: bold;';
        $style[] = '    padding: 3px;';
        $style[] = '    position: relative;';
        $style[] = '}';

        $script = array();
        $script[] = 'window.addEvent("domready", function(){';

        $script[] = 'aId =('.$id.'==0) ? false : true';

        $script[] = 'var btnAgregarAdjunto = new Element("button", {';
        $script[] = '   id: "btn-agregar-adjunto",';
        $script[] = '       events: {';
        $script[] = '           click: function(event) { ';
        $script[] = '               event.preventDefault();';
        $script[] = '               agregarAdjunto()';
        $script[] = '           }';
        $script[] = '       }';
        $script[] = '}).set("text", "+");';

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

        $script[] = '   var progressBar = new Element("div", {';
        $script[] = '       id:"progress-bar-"+adjuntoCount,';
        $script[] = '   })';

        $script[] = '   fieldArchivo.addEvents({';
        $script[] = '           "change": function() {'; 
        $script[] = '               if(aId) {';
        $script[] = '                    subirArchivo();';
        $script[] = '               } else { ';
        $script[] = '                   error = new Element("div",{';
        $script[] = '                       class:"error-msg"';
        $script[] = '                   }).set({"text":"Por favor guarde el Artículo antes de adjuntar archivos"});';
        $script[] = '                   $("controles-adjuntos").grab(error,"before")';
        $script[] = '               }';
        $script[] = '           }';
        $script[] = '   })';    

        $script[] = '   var btnEliminarAdjunto = new Element("button", {';
        $script[] = '       id: "btn-eliminar-adjunto",';
        $script[] = '       events: {';
        $script[] = '           click: function() { eliminarAdjunto(this) }';
        $script[] = '       }';
        $script[] = '   }).set({"text": "-", "data-id": formAdjunto.id})';

        $script[] = '   fieldArchivo.inject(formAdjunto);';
        $script[] = '   progressBar.inject(formAdjunto)';
        $script[] = '   btnEliminarAdjunto.inject(formAdjunto);';

        $script[] = '   $("adjuntos").grab(formAdjunto);';

        $script[] = '   function subirArchivo() {';
        $script[] = '       var upload = new File.Upload({';
        $script[] = '           url: "'.JURI::root().'administrator/index.php?option=com_content&view=article&layout=edit&task=adjuntos.subir",';
        $script[] = '           data: {';
        $script[] = '               "campo":"campo-adjunto-"+adjuntoCount,';
        $script[] = '               "id":'.$id.'},';
        $script[] = '           images: ["campo-adjunto-"+adjuntoCount],';
        $script[] = '           onComplete: function (){ console.log("Request")}';
        $script[] = '       });';
        $script[] = '       upload.send();';
        $script[] = '   }';

        $script[] = '}';

        $script[] = 'function eliminarAdjunto(el) {';
        $script[] = '   $(el.get("data-id")).dispose();';
        $script[] = '}';

        JFactory::getDocument()->addStyleDeclaration(implode("\n", $style));
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

        // Salida HTML
        $html = '<div id="controles-adjuntos"></div><div id="adjuntos"></div>';
        
        return $html;
    }

}

?>
