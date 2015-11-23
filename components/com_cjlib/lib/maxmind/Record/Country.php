<?php

namespace GeoIp2\Record;

/**
 * Contains data for the country record associated with an IP address
 *
 * This record is returned by all the end points.
 *
 * @property int $confidence A value from 0-100 indicating MaxMind's
 * confidence that the country is correct. This attribute is only available
 * from the Omni end point.
 *
 * @property int $geonameId The GeoName ID for the country. This attribute is
 * returned by all end points.
 *
 * @property string $isoCode The {@link http://en.wikipedia.org/wiki/ISO_3166-1
 * two-character ISO 3166-1 alpha code} for the country. This attribute is
 * returned by all end points.
 *
 * @property string $name The name of the country based on the locales list
 * passed to the constructor. This attribute is returned by all end points.
 *
 * @property array $names An array map where the keys are locale codes and
 * the values are names. This attribute is returned by all end points.
 */
class Country extends AbstractPlaceRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = array(
        'confidence',
        'geonameId',
        'isoCode',
        'names'
    );
}
