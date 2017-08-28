<?php
namespace Kunnu\Dropbox\Models;

class FolderMetadata extends BaseModel
{

    /**
     * A unique identifier of the folder
     *
     * @var string
     */
    protected $id;

    /**
     * The last component of the path (including extension).
     * This never contains a slash.
     *
     * @var string
     */
    protected $name;

    /**
     * The lowercased full path in the user's Dropbox.
     * This always starts with a slash.
     *
     * @var string
     */
    protected $path_lower;

    /**
     * Set if this folder is contained in a shared folder.
     *
     * @var \Kunnu\Dropbox\Models\FolderSharingInfo
     */
    protected $sharing_info;

    /**
     * The cased path to be used for display purposes only.
     *
     * @var string
     */
    protected $path_display;


    /**
     * Create a new FolderMetadata instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->id = $this->getDataProperty('id');
        $this->name = $this->getDataProperty('name');
        $this->path_lower = $this->getDataProperty('path_lower');
        $this->path_display = $this->getDataProperty('path_display');

        //Make SharingInfo
        $sharingInfo = $this->getDataProperty('sharing_info');
        if (is_array($sharingInfo)) {
            $this->sharing_info = new FolderSharingInfo($sharingInfo);
        }
    }

    /**
     * Get the 'id' property of the folder model.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the 'name' property of the folder model.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the 'path_lower' property of the folder model.
     *
     * @return string
     */
    public function getPathLower()
    {
        return $this->path_lower;
    }

    /**
     * Get the 'sharing_info' property of the folder model.
     *
     * @return \Kunnu\Dropbox\Models\FolderSharingInfo
     */
    public function getSharingInfo()
    {
        return $this->sharing_info;
    }

    /**
     * Get the 'path_display' property of the folder model.
     *
     * @return string
     */
    public function getPathDisplay()
    {
        return $this->path_display;
    }
}
