<?php

namespace GeoIp2\Record;

/**
 * City-level data associated with an IP address.
 *
 * This record is returned by all the end points except the Country end point.
 *
 * @property int $confidence A value from 0-100 indicating MaxMind's
 * confidence that the city is correct. This attribute is only available
 * from the Insights end point.
 *
 * @property int $geonameId The GeoName ID for the city. This attribute
 * is returned by all end points.
 *
 * @property string $name The name of the city based on the locales list
 * passed to the constructor. This attribute is returned by all end points.
 *
 * @property array $names A array map where the keys are locale codes
 * and the values are names. This attribute is returned by all end points.
 */
class City extends AbstractPlaceRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = array('confidence', 'geonameId', 'names');
}
