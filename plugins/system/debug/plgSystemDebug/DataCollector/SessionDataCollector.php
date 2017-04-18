<?php

namespace plgSystemDebug\DataCollector;

use plgSystemDebug\AbstractDataCollector;

class SessionDataCollector  extends AbstractDataCollector
{
    private $name = 'session';

    public function collect()
    {
        $data = [];

        foreach (\JFactory::getApplication()->getSession()->all() as $key => $value) {
            $data[$key] = $this->getDataFormatter()->formatVar($value);
        }

        return ['data' => $data];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWidgets()
    {
        return [
            'session' => [
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => $this->name.'.data',
                'default' => '[]'
            ]
        ];
    }
}
