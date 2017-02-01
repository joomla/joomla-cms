<?php


class TestAllowedAttributes implements \enshrined\svgSanitize\data\AttributeInterface
{

    /**
     * Returns an array of attributes
     *
     * @return array
     */
    public static function getAttributes()
    {
        return array(
            'testAttribute',
        );
    }
}