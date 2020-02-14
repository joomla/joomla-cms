<?php

abstract class CssMin {
    public static function minify($text)
    {
        $compressor = new tubalmartin\CssMin\Minifier();

        return $compressor->run($text);
    }
}