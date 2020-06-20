<?php
namespace enshrined\svgSanitize\Tests\Fixtures;

use enshrined\svgSanitize\data\TagInterface;

class TestAllowedTags implements TagInterface
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
