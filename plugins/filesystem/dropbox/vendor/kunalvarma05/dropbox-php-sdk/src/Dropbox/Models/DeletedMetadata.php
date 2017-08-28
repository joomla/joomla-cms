<?php
namespace Kunnu\Dropbox\Models;

class DeletedMetadata extends BaseModel
{

    /**
     * The last component of the path (including extension)
     *
     * @var string
     */
    protected $name;

    /**
     * The lowercased full path in the user's Dropbox
     *
     * @var string
     */
    protected $path_lower;

    /**
     * Set if this file is contained in a shared folder
     *
     * @var \Kunnu\Dropbox\Models\FileSharingInfo
     */
    protected $sharing_info;

    /**
     * The cased path to be used for display purposes only.
     *
     *  @var string
     */
    protected $path_display;

    /**
     * Create a new DeletedtMetadata instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->name = $this->getDataProperty('name');
        $this->path_lower = $this->getDataProperty('path_lower');
        $this->sharing_info = $this->getDataProperty('sharing_info');
        $this->path_display = $this->getDataProperty('path_display');
    }

    /**
     * Get the 'name' property of the metadata.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the 'path_lower' property of the metadata.
     *
     * @return string
     */
    public function getPathLower()
    {
        return $this->path_lower;
    }

    /**
     * Get the 'path_display' property of the metadata.
     *
     * @return string
     */
    public function getPathDisplay()
    {
        return $this->path_display;
    }

    /**
     * Get the 'sharing_info' property of the file model.
     *
     * @return \Kunnu\Dropbox\Models\FileSharingInfo
     */
    public function getSharingInfo()
    {
        return $this->sharing_info;
    }
}
