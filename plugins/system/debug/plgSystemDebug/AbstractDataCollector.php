<?php

namespace plgSystemDebug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

abstract class AbstractDataCollector extends DataCollector implements Renderable
{
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    protected function stripRoot($path)
    {
        return str_replace(JPATH_ROOT, 'JROOT', $path);
    }
}
