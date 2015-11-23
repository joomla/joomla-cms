<?php

namespace GeoIp2\Record;

/**
 * Contains data for the location record associated with an IP address
 *
 * This record is returned by all the end points except the Country end point.
 *
 * @property int $averageIncome The average income in US dollars associated with
 * the requested IP address. This attribute is only available from the Insights
 * end point.
 *
 * @property int $accuracyRadius The radius in kilometers around the
 * specified location where the IP address is likely to be. This attribute
 * is only available from the Insights end point.
 *
 * @property float $latitude The latitude of the location as a floating
 * point number. This attribute is returned by all end points except the
 * Country end point.
 *
 * @property float $longitude The longitude of the location as a
 * floating point number. This attribute is returned by all end points
 * except the Country end point.
 *
 * @property int $populationDensity The estimated population per square
 * kilometer associated with the IP address. This attribute is only available
 * from the Insights end point.
 *
 * @property int $metroCode The metro code of the location if the location
 * is in the US. MaxMind returns the same metro codes as the
 * {@link
 * https://developers.google.com/adwords/api/docs/appendix/cities-DMAregions
 * Google AdWords API}. This attribute is returned by all end points except
 * the Country end point.
 *
 * @property string $timeZone The time zone associated with location, as
 * specified by the {@link http://www.iana.org/time-zones IANA Time Zone
 * Database}, e.g., "America/New_York". This attribute is returned by all
 * end points except the Country end point.
 */
class Location extends AbstractRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = array(
        'averageIncome',
        'accuracyRadius',
        'latitude',
        'longitude',
        'metroCode',
        'populationDensity',
        'postalCode',
        'postalConfidence',
        'timeZone'
    );
}
