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

        $id = $jinput->get->get('id', null, null);
        if($id == 0) {
            $data = array();
            $data['msg'] = "Por favor guarde el artículo antes de aduntar archivos";
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
            $data['msg'] = "El tamaño del archivo supera el máximo permitido por el request al servidor";
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
                        $data["msg"] = "#".$err.": El tamaño del archivo excede el máximo permitido por la configuración";
                        break;
                    case 2:
                        $data["msg"] = "#".$err.": El archivo subudo excede el tamaño máximo permitido en el form";
                    case 3:
                        $data["msg"] = "#".$err.": El archivo ha sido parcialmente subido";
                        break;
                    case 4:
                        $data["msg"] = "#".$err.": No se ha subido ningún archivo";
                        break;
                    case 6:
                        $data["msg"] = "#".$err.": Falta el directorio temporal ";
                        break;
                    case 7:
                        $data["msg"] = "#".$err.": Falló al escribir en el disco";
                        break;
                    case 8:
                        $data["msg"] = "#".$err.": La extensión de PHP paró la subida del archivo, no se puede comprobar";
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
            $arr['msg'] = "El tipo ".$mimeArchivo." no es permitido para la subida de archivos, contacte al administrador";
            $arr['tipo'] = "warn"; 
            $arr['estado'] = false;
        } else {
            $arr['msg'] = "El tipo ".$mimeArchivo." es permitido para la subida de archivos";
            $arr['tipo'] = "success"; 
            $arr['estado'] = true;
        }

        return $arr;
    }

    public function borrar() {
        print_r('{"estado": "borrando"}');
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

            $arr['msg'] = "Archivo guardado satisfactoriamente!";
            $arr['tipo'] = "success";
        }
        catch (RuntimeException $e) {
            $arr['msg'] = "Se ha presentado el error: " . $e->getMessage();
            $arr['tipo'] = "error";
        }
        
        return $arr;
    }
}
