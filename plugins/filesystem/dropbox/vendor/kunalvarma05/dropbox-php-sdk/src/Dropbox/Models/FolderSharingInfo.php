<?php
namespace Kunnu\Dropbox\Models;

class FolderSharingInfo extends BaseModel
{

    /**
     * True if the file or folder is inside a read-only shared folder.
     *
     * @var bool
     */
    protected $read_only;

    /**
     * ID of shared folder that holds this folder.
     * Set if the folder is contained by a shared folder.
     *
     * @var string
     */
    protected $parent_shared_folder_id;

    /**
     * If this folder is a shared folder mount point,
     * the ID of the shared folder mounted at this location.
     *
     * @var string
     */
    protected $shared_folder_id;


    /**
     * Create a new Folder Sharing Info instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->read_only = $this->getDataProperty('read_only');
        $this->shared_folder_id = $this->getDataProperty('shared_folder_id');
        $this->parent_shared_folder_id = $this->getDataProperty('parent_shared_folder_id');
    }

    /**
     * True if the folder or folder is inside a read-only shared folder.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->read_only;
    }

    /**
     * ID of shared folder that holds this folder.
     *
     * @return string
     */
    public function getParentSharedFolderId()
    {
        return $this->parent_shared_folder_id;
    }

    /**
     * ID of shared folder.
     *
     * @return string
     */
    public function getSharedFolderId()
    {
        return $this->shared_folder_id;
    }
}
