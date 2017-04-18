<?php

namespace plgSystemDebug\DataCollector;

use plgSystemDebug\AbstractDataCollector;

class QueryDataCollector  extends AbstractDataCollector
{
    private $name = 'queries';

    public function collect()
    {
        return [
            'data' => $this->getData(),
            'count' => $this->getCount()
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWidgets()
    {
        return [
            'queries' => [
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => $this->name.'.data',
                'default' => '[]'
            ],
            'queries:badge' => [
                'map' => $this->name.'.count',
                'default' => 'null'
            ]
        ];
    }

    private function getData()
    {
        return \JFactory::getDbo()->getLog();
    }

    private function getCount()
    {
        return count(\JFactory::getDbo()->getLog());
    }
}
