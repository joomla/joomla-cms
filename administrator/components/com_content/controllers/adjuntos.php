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

        $archivo = $jinput->files->get($campo);

        if(isset($archivo)){

            // Sanea el nombre de archivo evitando caracteres no deseados
            $nombreArchivo = strtolower(JFile::makeSafe($archivo['name']));

            // Define el origen y destino del archivo
            // TODO: Crear directorio propio para los adjuntos del artículo 
            // y usarlo como path destino.
            $src = $archivo['tmp_name'];
            $dest = JPATH_ROOT.DS.'uploads'.DS.sha1(time()).'-'.$nombreArchivo;

            if(JFile::upload($src, $dest)) {
                // TODO: Implementa/valida una estructura de datos para los nombres 
                // de los archivos que se guardan en la base de datos
               print_r("Archivo Subido"); 
            } else {
                print_r("Ha ocurrido un error");
                print_r($archivo['error']);
            }
        }
    }
}
