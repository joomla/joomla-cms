<?php
/**
 * @version 0.1.0
 * @package Jokte.Plugin
 * @copyright CopyLeft Comparte Igual. Comunidad Juuntos Latinoamérica.
 * @license GNU/GPL v3.0
 */

defined('_JEXEC') or die('Acceso directo a este archivo restringido');

jimport('joomla.plugin.plugin');
jimport('joomla.form.form');
jimport('joomla.form.helper');
jimport('joomla.utilities.simplexml');
jimport('joomla.filesystem.mime');

class plgSystemJokte_Adjuntos extends JPlugin {

    private static $app;
    private static $user;
    private static $reqParams;

    function __construct(&$subject, $config) {

        // Define las variables que serán reutilizadas en el scope del plugin
        self::$app = JFactory::getApplication();
        $jinput = self::$app->input;

        // obtiene los parámetros del request para identificar el contexto un 
        // artículo
        self::$reqParams = $jinput->getArray(array('id' => 'int', 'view' => '','layout' => ''));

        self::$user = JFactory::getUser();

        parent::__construct($subject, $config);

    }

    function onBeforeRender () {

        $app = self::$app;
        $reqParams = self::$reqParams;
        $id = $reqParams['id'];

        if(!$app->isAdmin()) return;

        $user = self::$user;
        if(!$user->authorise('core.edit', 'com_content')) return;

        // termina la ejecución del plugin si los parametros recibidos no cumplen
        // con las condiciones preestablecidas para modificar el contexto de edición de
        // artículos
        if ($reqParams["view"] != "article" || $reqParams["layout"] != "edit") return;

        $doc = JFactory::getDocument();

        $doc->addStyleSheet(JURI::root().'plugins/system/jokte_adjuntos/assets/css/estilos.css');

        // obtiene el buffer del documento que será renderizado
        $buffer = mb_convert_encoding($doc->getBuffer('component'),'html-entities','utf-8');

        // inicializa la manipulación del DOM para el buffer del documento
        $dom = new DomDocument;
        $dom->validateOnParse = true;
        $dom->loadHTML($buffer);

        // obtiene los datos de los adjuntos
        $data = self::getAttachmentsData($id);

        // selecciona elemento del DOM  que contendrá los registros de los archivos adjuntos
        $contenedor = $dom->getElementById("adjuntos");

        // realiza la construcción de la tabla con el listado de adjuntos
        $wrap = $dom->createElement("div");
        $wrap->setAttribute("class", "wrap");
        $tabla = $dom->createElement("table");
        $tbody = $dom->createElement("tbody");

        $c = 0;
        foreach($data as $item){

            $itemId = 'item-'.$c;

            $iconSrc = JMime::checkIcon($item->mime_type);

            $row = $dom->createElement("tr");
            $row->setAttribute('id', $itemId);
            $mime = $dom->createElement("td");
            $file = $dom->createElement("td");
            $info = $dom->createElement("td");
            $info->setAttribute('class', 'center');
            $trash = $dom->createElement("td");
            $trash->setAttribute('class', 'center');

            $mimeImg = $dom->createElement("img");
            $mimeImg->setAttribute('src', $iconSrc);
            $mime->appendChild($mimeImg);

            $name = $dom->createElement("span");
            $nameAnchor = $dom->createElement("a");
            $nameAnchor->setAttribute('href', '/uploads/'.$item->hash.'-'.$item->nombre_archivo);
            $nameAnchor->setAttribute('target','_blank');
            $nameAnchor->nodeValue = $item->nombre_archivo;
            $name->appendChild($nameAnchor);
            $file->appendChild($name);

            $infoBtn = $dom->createElement("button");
            $infoBtn->setAttribute('onclick', 'showInfo()');
            $infoBtn->setAttribute('title', 'Información');
            $infoImg = $dom->createElement("img");
            $infoImg->setAttribute('src', JURI::root() . '/media/adjuntos/file-info-icon.png');
            $infoBtn->appendChild($infoImg);
            $info->appendChild($infoBtn);

            $trashBtn = $dom->createElement("button");
            $trashBtn->setAttribute('onclick', 'eliminarAdjunto(this, event)');
            $trashBtn->setAttribute('title', 'Eliminar archivo');
            $trashBtn->setAttribute('data-hash', $item->hash);
            $trashBtn->setAttribute('data-id', $itemId);
            $trashImg = $dom->createElement("img");
            $trashImg->setAttribute('src', JURI::root() . '/media/adjuntos/caneca.png');
            $trashBtn->appendChild($trashImg);
            $trash->appendChild($trashBtn);

            $row->appendChild($mime);
            $row->appendChild($file);
            $row->appendChild($info);
            $row->appendChild($trash);

            $tbody->appendChild($row);

            $c++;
        }

        $wrap->appendChild($tabla);
        $tabla->appendChild($tbody);
        $contenedor->appendChild($wrap);

        // aplica los cambios realizados al DOM en un nuevo buffer para actualizar la presentación
        // del la vista del componente en el contexto indicado
        $newBuffer = $dom->saveHTML();
        $doc->setBuffer($newBuffer,'component');
    }

    function onAfterRender () {

        $app = self::$app;
        $reqParams = self::$reqParams;
        $id = $reqParams['id'];

        if ($app->isAdmin()) return;

        JPluginHelper::importPlugin('content', 'jokteadjuntos');

        $dispatcher = JDispatcher::getInstance();

    }

    private function getAttachmentsData ($id) {

        // carga el subcontrolador para ejecutar la tarea mostrar
        JLoader::register('ContentControllerAdjuntos', JPATH_ADMINISTRATOR . '/components/com_content/controllers/adjuntos.json.php');

        $data = new ContentControllerAdjuntos;
        $adjuntos = $data->mostrar($id);

        return $adjuntos;
    }

}
