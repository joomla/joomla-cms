<?php

namespace GeoIp2\Record;

/**
 *
 * Contains data for the subdivisions associated with an IP address
 *
 * This record is returned by all the end points except the Country end point.
 *
 * @property int $confidence This is a value from 0-100 indicating MaxMind's
 * confidence that the subdivision is correct. This attribute is only
 * available from the Insights end point.
 *
 * @property int $geonameId This is a GeoName ID for the subdivision. This
 * attribute is returned by all end points except Country.
 *
 * @property string $isoCode This is a string up to three characters long
 * contain the subdivision portion of the {@link
 * http://en.wikipedia.org/wiki/ISO_3166-2 ISO 3166-2 code}. This attribute
 * is returned by all end points except Country.
 *
 * @property string $name The name of the subdivision based on the locales
 * list passed to the constructor. This attribute is returned by all end
 * points except Country.
 *
 * @property array $names An array map where the keys are locale codes and
 * the values are names. This attribute is returned by all end points except
 * Country.
 */
class Subdivision extends AbstractPlaceRecord
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
