<?php
namespace Kunnu\Dropbox\Models;

class VideoMetadata extends MediaMetadata
{

    /**
     * The duration of the video in milliseconds
     *
     * @var int
     */
    protected $duration;

    /**
     * Create a new VideoMetadata instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->duration = $this->getDataProperty('duration');
    }

    /**
     * Get the duration of the video
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }
}
