<?php


class TestAllowedTags implements enshrined\svgSanitize\data\TagInterface
{

    /**
     * Returns an array of tags
     *
     * @return array
     */
    public static function getTags()
    {
        return array(
            'testTag',
        );
    }
}