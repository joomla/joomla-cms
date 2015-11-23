<?php

namespace GeoIp2\Record;

/**
 * Contains data for the continent record associated with an IP address
 *
 * This record is returned by all the end points.
 *
 * @property string $code A two character continent code like "NA" (North
 * America) or "OC" (Oceania). This attribute is returned by all end points.
 *
 * @property int $geonameId The GeoName ID for the continent. This attribute
 * is returned by all end points.
 *
 * @property string $name Returns the name of the continent based on the
 * locales list passed to the constructor. This attribute is returned by
 * all end points.
 *
 * @property array $names An array map where the keys are locale codes
 * and the values are names. This attribute is returned by all end points.
 */
class Continent extends AbstractPlaceRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = array(
        'code',
        'geonameId',
        'names'
    );
}
