<?php
namespace Kunnu\Dropbox\Models;

class FileSharingInfo extends BaseModel
{

    /**
     * True if the file or folder is inside a read-only shared folder.
     *
     * @var bool
     */
    protected $read_only;

    /**
     * ID of shared folder that holds this file.
     *
     * @var string
     */
    protected $parent_shared_folder_id;

    /**
     * The last user who modified the file.
     * This field will be null if the user's account has been deleted.
     *
     * @var string
     */
    protected $modified_by;

    /**
     * Create a new File Sharing Info instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->read_only = $this->getDataProperty('read_only');
        $this->modified_by = $this->getDataProperty('modified_by');
        $this->parent_shared_folder_id = $this->getDataProperty('parent_shared_folder_id');
    }

    /**
     * True if the file or folder is inside a read-only shared folder.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->read_only;
    }

    /**
     * ID of shared folder that holds this file.
     *
     * @return string
     */
    public function getParentSharedFolderId()
    {
        return $this->parent_shared_folder_id;
    }

    /**
     * Get the last user who modified the file.
     *
     * @return string
     */
    public function getModifiedBy()
    {
        return $this->modified_by;
    }
}
