<?php
namespace Kunnu\Dropbox\Models;

class MetadataCollection extends BaseModel
{

    /**
     * Collection Items Key
     *
     * @const string
     */
    const COLLECTION_ITEMS_KEY = 'entries';

    /**
     * Collection Cursor Key
     *
     * @const string
     */
    const COLLECTION_CURSOR_KEY = 'cursor';

    /**
     * Collection has-more-items Key
     *
     * @const string
     */
    const COLLECTION_HAS_MORE_ITEMS_KEY = 'has_more';

    /**
     * Collection Data
     *
     * @var array
     */
    protected $data;

    /**
     * List of Files/Folder Metadata
     *
     * @var \Kunnu\Dropbox\Models\ModelCollection
     */
    protected $items = null;

    /**
     * Cursor for pagination and updates
     *
     * @var string
     */
    protected $cursor;

    /**
     * If more items are available
     *
     * @var boolean
     */
    protected $hasMoreItems;

    /**
     * Create a new Metadata Collection
     *
     * @param array $data Collection Data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->cursor = isset($data[$this->getCollectionCursorKey()]) ? $data[$this->getCollectionCursorKey()] : '';
        $this->hasMoreItems = isset($data[$this->getCollectionHasMoreItemsKey()]) && $data[$this->getCollectionHasMoreItemsKey()] ? true : false;

        $items = isset($data[$this->getCollectionItemsKey()]) ? $data[$this->getCollectionItemsKey()] : [];
        $this->processItems($items);
    }

    /**
     * Get the Collection Items Key
     *
     * @return string
     */
    public function getCollectionItemsKey()
    {
        return static::COLLECTION_ITEMS_KEY;
    }

    /**
     * Get the Collection has-more-items Key
     *
     * @return string
     */
    public function getCollectionHasMoreItemsKey()
    {
        return static::COLLECTION_HAS_MORE_ITEMS_KEY;
    }

    /**
     * Get the Collection Cursor Key
     *
     * @return string
     */
    public function getCollectionCursorKey()
    {
        return static::COLLECTION_CURSOR_KEY;
    }

    /**
     * Get the Items
     *
     * @return \Kunnu\Dropbox\Models\ModelCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get the cursor
     *
     * @return string
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * More items are available
     *
     * @return boolean
     */
    public function hasMoreItems()
    {
        return $this->hasMoreItems;
    }

    /**
     * Process items and cast them
     * to their respective Models
     *
     * @param array $items Unprocessed Items
     *
     * @return void
     */
    protected function processItems(array $items)
    {
        $processedItems = [];

        foreach ($items as $entry) {
            $processedItems[] = ModelFactory::make($entry);
        }

        $this->items = new ModelCollection($processedItems);
    }
}
