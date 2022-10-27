<?php

namespace Joomla\CMS\Plugin;

use Joomla\CMS\Factory;

trait LanguageAwareTrait
{
    /**
     * Loads the plugin language file
     *
     * @param   string  $extension  The extension for which a language file should be loaded
     * @param   string  $basePath   The basepath to use
     *
     * @return  boolean  True, if the file has successfully loaded.
     *
     * @since   1.5
     */
    public function loadLanguage($extension = '', $basePath = JPATH_ADMINISTRATOR)
    {
        if (empty($extension)) {
            $extension = 'Plg_' . $this->_type . '_' . $this->_name;
        }

        $extension = strtolower($extension);
        $lang      = $this->getApplication() ? $this->getApplication()->getLanguage() : Factory::getLanguage();

        // If language already loaded, don't load it again.
        if ($lang->getPaths($extension)) {
            return true;
        }

        return $lang->load($extension, $basePath)
            || $lang->load($extension, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name);
    }
}
