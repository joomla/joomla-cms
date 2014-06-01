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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.mime');

class ContentControllerAdjuntos extends JControllerForm
{
    public function subir() {

        $jinput = JFactory::getApplication()->input;

        /**
         * Esta tarea debe accionarse sólamente cuándo el artículo ha sido previamente guardado,
         * con el fin de evitar subir archivos huerfanos
         */

        $id = $jinput->get('id', null, null);
        if($id == 0) {
            $data = array();
            $data['msg'] = JText::_("COM_CONTENT_ADJUNTOS_MSG_GUARDAR_ARTICULO_PRIMERO");
            $data['tipo'] = "warn";
            print_r(json_encode($data));
            return;
        }

        // Obtiene la variable @campo enviada en el request 
        $campo = $jinput->get->get('campo', null, null);

        // Obtiene la variable @exts (extensiones) enviada en el request
        $exts = explode(',', $jinput->get->get('exts', null, null));

        // Obtiene datos del servidor
        $maxFileSize = ini_get("upload_max_filesize");
        $serverContent = $_SERVER['CONTENT_LENGTH'];

        // Obtiene los datos del archivo
        $archivo = $jinput->files->get($campo);


        // Verifica que el tamaño del request no supere el definido en la configuración PHP
        if ($maxFileSize > $serverContent && is_null($archivo['size'])) {
            $data = array();
            $data['msg'] = JText::_("COM_CONTENT_ADJUNTOS_MSG_ARCHIVO_SUPERA_TAMANO_PERMITIDO_SERVIDOR");
            $data['tipo'] = "error";
            print_r(json_encode($data));
            return;
        }

        if(isset($archivo)){

            $mimeArchivo = $archivo['type'];

            // Valida el tip)o mime del archivo, si no es valido retorna mensaje de error
            // y detiene la ejecución
            if(!is_null($mimeArchivo)) {
                $esValido = self::validarTipoMime($exts, $mimeArchivo);

                if($esValido['estado'] === false) {
                    $data = array(
                        'msg' => $esValido['msg'],
                        'tipo' => $esValido['tipo']
                    );

                    print_r(json_encode($data));

                    return;
                }
            }

            // Sanea el nombre de archivo evitando caracteres no deseados
            $nombreArchivo = strtolower(JFile::makeSafe($archivo['name']));

            // Define el origen y destino del archivo
            // TODO: Crear directorio propio para los adjuntos del artículo 
            // y usarlo como path destino.
            $src = $archivo['tmp_name'];
            $dest = JPATH_ROOT.DS.'uploads'.DS.sha1(time()).'-'.$nombreArchivo;

            if(JFile::upload($src, $dest)) {

                $archivo = self::reformarArchivo($id, $dest);
                $data = array_merge($archivo, self::guardar($archivo));

                print_r(json_encode($data));

            } else {
                // Muestra el mensaje a partir del código de error generado durante la 
                // subida del archivo
                $err = $archivo['error'];

                $data = array();

                switch ($err) {
                    case 1:
                        $data["msg"] = "#".$err.": ".JText::_('COM_CONTENT_ADJUNTOS_MSG_ARCHIVO_SUPERA_TAMANO_PERMITIDO_CONFIG');
                        break;
                    case 2:
                        $data["msg"] = "#".$err.": ".JTex::_('COM_CONTENT_ADJUNTOS_MSG_ARCHIVO_SUPERA_TAMANO_PERMITIDO_FORM');
                    case 3:
                        $data["msg"] = "#".$err.": ".JText::_("COM_CONTENT_ADJUNTOS_MSG_ARCHIVO_PARCIALMENTE_SUBIDO");
                        break;
                    case 4:
                        $data["msg"] = "#".$err.": ".JText::_("COM_CONTENT_ADJUNTOS_MSG_ARCHIVO_NO_SUBIDO");
                        break;
                    case 6:
                        $data["msg"] = "#".$err.": ".JText::_("COM_CONTENT_ADJUNTOS_MSG_NO_TEMPORAL_DIR");
                        break;
                    case 7:
                        $data["msg"] = "#".$err.": ".JText::_('COM_CONTENT_ADJUNTOS_MSG_FALLO_ESCRIBIR_EN_DISCO');
                        break;
                    case 8:
                        $data["msg"] = "#".$err.": ".JText::('COM_CONTENT_ADJUNTOS_MSG_NO_EXTENSION_PHP');
                        break;
                }

                $data["tipo"] = "error";

                // Retorna un objeto JSON que puede ser utilizado en el cliente
                print_r(json_encode($data));
            }
        }
    }

    /**
     * Asigna un formato estandard al nombre de archivo
     *
     * @param   $id     String entregado por el Id del artículo     
     * @param   $dest   String ruta de destino del archivo
     * @return  $arr    Array con los datos relacionados con el archivo subido
     */

    function reformarArchivo($id, $dest) {

        // Encontrar una mejor forma de descomponer @dest :E
        $ruta = implode('/', explode('/', $dest, '-1'));
        $archivo = end(explode('/',$dest));
        $hash = array_shift(explode('-', $archivo));
        $nombreArchivo = substr($archivo, (strpos($archivo, '-')+1));

        $arr = array("id" => $id, "nombreArchivo" => $nombreArchivo, "ruta" => $ruta, "hash" => $hash);

        $dest = null;

        return $arr;
    }

    /**
     * Valida que el tipo mime del archivo corresponda al los tipos mime definidos en
     * la configuración del elemento Adjuntos
     *
     * @param   $exts           Array con las extensiones de archivo permitidas, 
     *                          definidas en la configuración xml
     * @param   $mimeArchivo    String contiene el tipo mime del archivo
     * @return  $arr            Array con el estado de la validación, mensaje resultante 
     *                          y tipo de mensaje
     */

    function validarTipoMime($exts, $mimeArchivo) {
        $mimes = JMime::set($exts);
        $estado = array_search($mimeArchivo, $mimes, true);

        $arr = array();

        if ($estado === false) {
            $arr['msg'] = JText::_('COM_CONTENT_ADJUNTOS_MSG_MIMETYPE_NO_PERMITIDO', $mimeArchivo);
            $arr['tipo'] = "warn"; 
            $arr['estado'] = false;
        } else {
            $arr['msg'] = JText::_('COM_CONTENT_ADJUNTOS_MSG_MIMETYPE_PERMITIDO', $mimeArchivo);
            $arr['tipo'] = "success"; 
            $arr['estado'] = true;
        }

        return $arr;
    }

    /*
     * Elimina los datos del archivo adjunto indicado
     * 
     * $return  $arr    Array con el estado de la eliminación y mensaje resultante
     */

    public function borrar() {

        // Recibe los datos necesarios para eliminar el archivo adjunto indicado
        $jinput = JFactory::getApplication()->input;

        $id = $jinput->get('id', null, null);
        $hash = $jinput->get('hash', null, null);

        print_r('{"hash": "'.$hash.'"}');
    }

    /*
     * Guarda los datos del archivo adjunto en la Base de Datos
     *
     * @param   $data   Array con los datos del archivo adjuntado
     * @return  $arr    Array con el estado de la subida y mensaje resultante        
     *
     */

    public function guardar($data) {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $columnas = array('propietario_id', 'nombre_archivo', 'ruta', 'hash');
        $valores = array(
            $data['id'], 
            $db->quote($data['nombreArchivo']), 
            $db->quote($data['ruta']), 
            $db->quote($data['hash']));
        
        $query
            ->insert($db->quoteName('#__adjuntos'))
            ->columns($db->quoteName($columnas))
            ->values(implode(',', $valores));

        $arr = array();

        // Maneja posibles errores arrojados por la Base de Datos
        try {
            $db->setQuery($query);
            $db->query();

            $arr['msg'] = JText::_('COM_CONTENT_ADJUNTOS_MSG_ARCHIVO_GUARDADO');
            $arr['tipo'] = "success";
        }
        catch (RuntimeException $e) {
            $arr['msg'] = JText::_('COM_CONTENT_ADJUNTOS_MSG_ERROR_AL_GUARDAR',$e->getMessage());
            $arr['tipo'] = "error";
        }
        
        return $arr;
    }
}
