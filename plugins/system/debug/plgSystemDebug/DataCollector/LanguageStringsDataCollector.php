<?php

namespace plgSystemDebug\DataCollector;

use plgSystemDebug\AbstractDataCollector;

class LanguageStringsDataCollector extends AbstractDataCollector
{
    private $name = 'languageStrings';

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
            'untranslated' => [
                'widget' => 'PhpDebugBar.Widgets.KVListWidget',
                'map' => $this->name.'.data',
                'default' => ''
            ],
            'untranslated:badge' => [
                'map' => $this->name.'.count',
                'default' => 'null'
            ]
        ];
    }

    private function getData()
    {
        $stripFirst = $this->params->get('strip-first');
        $stripPref = $this->params->get('strip-prefix');
        $stripSuff = $this->params->get('strip-suffix');

        $orphans = \JFactory::getLanguage()->getOrphans();

        if (!count($orphans))
        {
            return [\JText::_('JNONE')];
        }

        ksort($orphans, SORT_STRING);

        $guesses = [];

        foreach ($orphans as $key => $occurance)
        {
            if (is_array($occurance) && isset($occurance[0]))
            {
                $info = $occurance[0];
                $file = $info['file'] ? $info['file'] : '';

                if (!isset($guesses[$file]))
                {
                    $guesses[$file] = [];
                }

                // Prepare the key.
                if (($pos = strpos($info['string'], '=')) > 0)
                {
                    $parts = explode('=', $info['string']);
                    $key = $parts[0];
                    $guess = $parts[1];
                }
                else
                {
                    $guess = str_replace('_', ' ', $info['string']);

                    if ($stripFirst)
                    {
                        $parts = explode(' ', $guess);

                        if (count($parts) > 1)
                        {
                            array_shift($parts);
                            $guess = implode(' ', $parts);
                        }
                    }

                    $guess = trim($guess);

                    if ($stripPref)
                    {
                        $guess = trim(preg_replace(chr(1) . '^' . $stripPref . chr(1) . 'i', '', $guess));
                    }

                    if ($stripSuff)
                    {
                        $guess = trim(preg_replace(chr(1) . $stripSuff . '$' . chr(1) . 'i', '', $guess));
                    }
                }

                $key = trim(strtoupper($key));
                $key = preg_replace('#\s+#', '_', $key);
                $key = preg_replace('#\W#', '', $key);

                // Prepare the text.
                $guesses[$file][] = $key . '="' . $guess . '"';
            }
        }

        $untranslated = [];
        $count = 1;

        foreach ($guesses as $file => $keys)
        {
            foreach ($keys as $key) {

                $untranslated[$count.' '.$this->stripRoot($file)] = $key;
                $count ++;
            }
        }

        return $untranslated;
    }

    private function getCount()
    {
        return count(\JFactory::getLanguage()->getOrphans());
    }
}
