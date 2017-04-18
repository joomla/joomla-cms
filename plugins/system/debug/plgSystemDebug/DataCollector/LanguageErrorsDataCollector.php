<?php

namespace plgSystemDebug\DataCollector;

use plgSystemDebug\AbstractDataCollector;

class LanguageErrorsDataCollector extends AbstractDataCollector
{
    private $name = 'languageErrors';

    public function collect()
    {
        return [
            'data' => $this->getData(),
            'count' => $this->getCount(),
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWidgets()
    {
        return [
            'errors' => [
                'widget' => 'PhpDebugBar.Widgets.KVListWidget',
                'map' => $this->name.'.data',
                'default' => ''
            ],
            'errors:badge' => [
                'map' => $this->name.'.count',
                'default' => 'null'
            ]
        ];
    }

    private function getData()
    {
        $errorFiles = \JFactory::getLanguage()->getErrorFiles();
        $errors = [];

        if (count($errorFiles))
        {
            $count = 1;
            foreach ($errorFiles as $error)
            {
                $errors[$count] = $this->stripRoot($error);
                $count ++;
            }
        }
        else
        {
            $errors[] = \JText::_('JNONE');
        }

        return $errors;
    }

    private function getCount()
    {
        return count(\JFactory::getLanguage()->getErrorFiles());
    }
}
