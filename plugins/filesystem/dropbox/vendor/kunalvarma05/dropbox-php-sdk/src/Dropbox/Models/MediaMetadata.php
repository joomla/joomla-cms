<?php
namespace Kunnu\Dropbox\Models;

use DateTime;

class MediaMetadata extends BaseModel
{

    /**
     * The GPS coordinate of the photo/video.
     *
     * @var array
     */
    protected $location = array();

    /**
     * Dimension of the photo/video.
     *
     * @var array
     */
    protected $dimensions = array();

    /**
     * The timestamp when the photo/video is taken.
     *
     * @var DateTime
     */
    protected $time_taken;


    /**
     * Create a new MediaMetadata instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->location = (array) $this->getDataProperty('location');
        $this->dimensions = (array) $this->getDataProperty('dimensions');

        $time_taken = $this->getDataProperty('time_taken');
        if ($time_taken) {
            $this->time_taken = new DateTime($time_taken);
        }
    }

    /**
     * Get the location of the Media
     *
     * @return array
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get the dimensions of the Media
     *
     * @return array
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Get the Time the Media was taken on
     *
     * @return DateTime
     */
    public function getTimeTaken()
    {
        return $this->time_taken;
    }
}
