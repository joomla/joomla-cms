<?php
namespace Kunnu\Dropbox\Models;

class MediaInfo extends BaseModel
{

    /**
     * Indicate the photo/video is still under processing
     * and metadata is not available yet.
     *
     * @var bool
     */
    protected $pending = false;

    /**
     * MediaMetadata
     *
     * @var \Kunnu\Dropbox\Models\MediaMetadata
     */
    protected $mediaMetadata;


    /**
     * Create a new MediaInfo instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->pending = $this->getDataProperty('pending');
        $this->setMediaMetadata();
    }

    /**
     * Set Media Metadata
     */
    protected function setMediaMetadata()
    {
        $mediaMetadata = $this->getDataProperty('metadata');
        if (is_array($mediaMetadata)) {
            if ($mediaMetadata['.tag'] === 'photo') {
                //Media is Photo
                $this->mediaMetadata = new PhotoMetadata($mediaMetadata);
            } elseif ($mediaMetadata['.tag'] === 'video') {
                //Media is Video
                $this->mediaMetadata = new VideoMetadata($mediaMetadata);
            } else {
                //Unknown Media (Quite unlikely, though.)
                $this->mediaMetadata = new MediaMetadata($mediaMetadata);
            }
        }
    }

    /**
     * Indicates whether the photo/video is still under
     * processing and is the metadata available yet.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->pending;
    }

    /**
     * The metadata for the photo/video.
     *
     * @return \Kunnu\Dropbox\Models\MediaMetadata
     */
    public function getMediaMetadata()
    {
        return $this->mediaMetadata;
    }
}
