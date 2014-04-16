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
            print_r('Debe haber guardado el artículo para agregar adjuntos');
            return;
        }

        // Obtiene la variable @campo enviada en el request 
        $campo = $jinput->get->get('campo', null, null);

        // Obtiene la variable @exts (extensiones) enviada en el request
        $exts = explode(',', $jinput->get->get('exts', null, null));

        // Obtiene los datos del archivo
        $archivo = $jinput->files->get($campo);

        if(isset($archivo)){

            $mimeArchivo = $archivo['type'];

            // Valida el tipo mime del archivo
            $esValido = self::validarTipoMime($exts, $mimeArchivo);

            // Sanea el nombre de archivo evitando caracteres no deseados
            $nombreArchivo = strtolower(JFile::makeSafe($archivo['name']));

            // Define el origen y destino del archivo
            // TODO: Crear directorio propio para los adjuntos del artículo 
            // y usarlo como path destino.
            $src = $archivo['tmp_name'];
            $dest = JPATH_ROOT.DS.'uploads'.DS.sha1(time()).'-'.$nombreArchivo;

            if(JFile::upload($src, $dest)) {

                $data = self::reformarArchivo($id, $dest);

                print_r(json_encode($data));

                // TODO: Implementa/valida una estructura de datos para los nombres 
                // de los archivos que se guardan en la base de datos
            } else {

                // Muestra el mensaje a partir del código de error generado durante la 
                // subida del archivo
                $err = $archivo['error'];

                $data = array();

                switch ($err) {
                    case 1:
                        $data["Error ".$err] = "El tamaño del archivo excede el máximo permitido por la configuración";
                        break;
                    case 2:
                        $data["Error ".$err] = "El archivo subudo excede el tamaño máximo permitido en el form";
                    case 3:
                        $data["Error ".$err] = "El archivo ha sido parcialmente subido";
                        break;
                    case 4:
                        $data["Error ".$err] = "No se ha subido ningún archivo";
                        break;
                    case 6:
                        $data["Error ".$err] = "Falta el directorio temporal ";
                        break;
                    case 7:
                        $data["Error ".$err] = "Falló al escribir en el disco";
                        break;
                    case 8:
                        $data["Error ".$err] = "La extensión de PHP paró la subida del archivo, no se puede comprobar";
                        break;
                }

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
     * @return  boolean         Habilita o deshabilita la subida del archivo
     */

    function validarTipoMime($exts, $mimeArchivo) {
        $mimes = JMime::set($exts);
    }

    public function borrar() {
        print_r('{"estado": "borrando"}');
    }
}
