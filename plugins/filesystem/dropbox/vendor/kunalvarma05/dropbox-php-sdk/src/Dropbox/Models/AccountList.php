<?php
namespace Kunnu\Dropbox\Models;

class AccountList extends ModelCollection
{
    /**
     * Create a new Metadata Collection
     *
     * @param array $data Collection Data
     */
    public function __construct(array $data)
    {
        $processedItems = $this->processItems($data);
        parent::__construct($processedItems);
    }

    /**
     * Process items and cast them
     * to Account Model
     *
     * @param array $items Unprocessed Items
     *
     * @return array Array of Account models
     */
    protected function processItems(array $items)
    {
        $processedItems = [];

        foreach ($items as $entry) {
            $processedItems[] = new Account($entry);
        }

        return $processedItems;
    }
}
