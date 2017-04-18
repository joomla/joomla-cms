<?php

namespace plgSystemDebug\DataCollector;

use plgSystemDebug\AbstractDataCollector;

class LanguageFilesDataCollector extends AbstractDataCollector
{
    private $name = 'languageFiles';

    public function collect()
    {
        $loaded = [];
        $statuses = [
            \JText::_('PLG_DEBUG_LANG_NOT_LOADED'),
            \JText::_('PLG_DEBUG_LANG_LOADED'),
        ];

        foreach (\JFactory::getLanguage()->getPaths() as $extension => $files)
        {
            $count = 1;
            foreach ($files as $file => $status)
            {
                $loaded[$count . ' ' . $extension] = $this->stripRoot($file) . ' - ' . $statuses[(int)$status];
                $count ++;
            }
        }

        return ['loaded' => $loaded];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWidgets()
    {
        return [
            'loaded' => [
                'widget' => 'PhpDebugBar.Widgets.KVListWidget',
                'map' => $this->name.'.loaded',
                'default' => '[]'
            ]
        ];
    }
}
