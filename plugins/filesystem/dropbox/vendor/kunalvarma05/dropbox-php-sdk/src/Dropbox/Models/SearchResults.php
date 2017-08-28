<?php
namespace Kunnu\Dropbox\Models;

class SearchResults extends MetadataCollection
{
    /**
     * Collection Items Key
     *
     * @const string
     */
    const COLLECTION_ITEMS_KEY = 'matches';

    /**
     * Collection Cursor Key
     *
     * @const string
     */
    const COLLECTION_CURSOR_KEY = 'start';

    /**
     * Collection has-more-items Key
     *
     * @const string
     */
    const COLLECTION_HAS_MORE_ITEMS_KEY = 'more';

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
            if (isset($entry['metadata']) && is_array($entry['metadata'])) {
                $processedItems[] = new SearchResult($entry);
            }
        }

        $this->items = new ModelCollection($processedItems);
    }
}
