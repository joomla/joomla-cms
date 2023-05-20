<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Exception;
use InvalidArgumentException;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Jed\Component\Jed\Administrator\MediaHandling\ImageSize;
use Jed\Component\Jed\Administrator\Table\ExtensionTable;
use Jed\Component\Jed\Administrator\Traits\ExtensionUtilities;
use Jed\Component\Jed\Site\Helper\JedscoreHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use RuntimeException;
use stdClass;

use function defined;

/**
 * Extension model.
 *
 * @since  4.0.0
 */
class ExtensionModel extends AdminModel
{
    use ExtensionUtilities;

    /**
     * @var    string  Alias to manage history control
     *
     * @since  4.0.0
     */
    public $typeAlias = 'com_jed.extension';

    /**
     * @var    string  The prefix to use with controller messages.
     *
     * @since  4.0.0
     */
    protected $text_prefix = 'COM_JED';

    /**
     * @var    stdClass  Item data
     *
     * @since  4.0.0
     */
    protected mixed $item;

    /**
     * Get the filename of the given extension ID.
     *
     * @param   int  $extensionId  The extension ID to get the filename for
     *
     * @return  stdClass  The extension file information.
     *
     * @since   4.0.0
     */
    public function getFilename(int $extensionId): stdClass
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    [
                        'file',
                        'originalFile',
                    ]
                )
            )
            ->from($db->quoteName('#__jed_extensions_files'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query);

        $fileDetails = $db->loadObject();

        if ($fileDetails === null) {
            $fileDetails       = new stdClass();
            $fileDetails->file = '';
        }

        return $fileDetails;
    }

    /**
     * Method to get the record form.
     *
     * @param   array  $data      An optional array of data for the form to interogate.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A \JForm object on success, false on failure
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function getForm($data = [], $loadData = true, $formname = 'jform_extension'): Form|bool
    {
        // Get the form.
        $form = $this->loadForm('com_jed.extension', 'extension', ['control' => $formname, 'load_data' => $loadData]);


        return $form ?? new Form('com_jed.extension');
    }

    /**
     * Method to get a single record.
     *
     * @param   int  $pk  The id of the primary key.
     *
     * @return  stdClass    Object on success, false on failure.
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function getItem($pk = null): mixed
    {
        return $this->getvariedItem($pk, 0);
    }

    /**
     * Get array of review scores for extension
     *
     * @param   int  $extension_id
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getReviewTypes(int $extension_id): array
    {
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $query  = $db->getQuery(true);
        $query2 = $db->getQuery(true);
        //SELECT supply_options.id AS supply_id, supply_options.title AS supply_type FROM `fqvpf_jed_extension_supply_options` AS `supply_options` WHERE id<3
        //UNION
        //SELECT supply_options.id AS supply_id, supply_options.title AS supply_type FROM
        //fqvpf_jed_extension_varied_data a
        //LEFT JOIN `fqvpf_jed_extension_supply_options` AS `supply_options`
        // ON `supply_options`.id = a.`supply_option_id` WHERE extension_id=80 AND supply_options.id>2;

        $query->select('supply_options.id AS supply_id, supply_options.title AS supply_type')
            ->from($db->quoteName('#__jed_extension_supply_options', 'supply_options'))
            ->where($db->quoteName('id') . ' < 3');
        $query2->select('supply_options.id AS supply_id, supply_options.title AS supply_type')
            //  $query2->select('"3" AS supply_id, "Cloud/Service" AS supply_type')
            ->from($db->quoteName('#__jed_extension_varied_data', 'a'))
            ->join(
                'LEFT',
                $db->quoteName(
                    '#__jed_extension_supply_options',
                    'supply_options'
                ) . ' ON supply_options.id=a.supply_option_id'
            )
            ->where($db->quoteName('extension_id') . ' = ' . $extension_id . ' and supply_options.id>2');


        $db->setQuery($query->union($query2));
        $result = $db->loadObjectList();

        return $result;
    }

    /**
     * Gets array of all reviews for extension
     *
     * @param   int  $extension_id
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getReviews(int $extension_id): array
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true);
        $query->select('a.*,u.name as created_by_name')
            ->from($db->quoteName('#__jed_reviews', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id=a.created_by')
            ->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id) . ' and supply_option_id=1');

        $db->setQuery($query);
        $freeresult = $db->loadAssocList();
        $query      = $db->getQuery(true);
        $query->select('a.*,u.name as created_by_name')
            ->from($db->quoteName('#__jed_reviews', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id=a.created_by')
            ->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id) . ' and supply_option_id=2');

        $db->setQuery($query);
        $paidresult     = $db->loadAssocList();
        $retval['Free'] = $freeresult;
        $retval['Paid'] = $paidresult;

        foreach ($retval['Paid'] as $pr) {
            if (str_contains($pr['body'], '{functionality}')) {
                $pr['body'] = '';
            }
        }
        foreach ($retval['Free'] as $fr) {
            //291505
            if (str_contains($fr['body'], '{functionality}')) {
                $fr['body'] = '';
            }
        }


        return $retval;
    }

    /**
     * Get array of review scores for extension
     *
     * @param   int  $extension_id
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getScores(int $extension_id): array
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__jed_extension_scores'))
            ->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id));

        $db->setQuery($query);
        $result = $db->loadObjectList();
        foreach ($result as $r) {
            if ($r->supply_option_id == 1) {
                $supply = 'Free';
            } else {
                $supply = 'Paid';
            }
            $retval[$supply] = $r;
        }

        return $retval;
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string  $name     The table type to instantiate
     * @param   string  $prefix   A prefix for the table class name. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table    A database object
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function getTable($name = 'Extension', $prefix = 'Administrator', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the varied data form.
     *
     * @param   array  $data      An optional array of data for the form to interogate.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A Form object on success, false on failure
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function getVariedDataForm($data = [], $loadData = true, $formname = 'jform_extensionvarieddata'): Form
    {
        // Get the form.
        $form = $this->loadForm(
            'com_jed.extensionvarieddatum',
            'extensionvarieddatum',
            ['control' => $formname, 'load_data' => $loadData]
        );


        return $form ?? new Form('com_jed.extensionvarieddatum');
    }

    /**
     * Method to get a single record.
     *
     * @param   int|null  $pk                  The id of the primary key.
     * @param   int       $supply_option_type  The type of varied data to look for
     *
     * @return  stdClass    Object on success, false on failure.
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function getvariedItem(int $pk = null, int $supply_option_type = 0)
    {
        if ($item = parent::getItem($pk)) {
            /* Convert cmsobject to stdClass */
            $s = $item->getProperties();

            $this->item = (object)$s;

            if (isset($this->item->includes)) {
                $this->item->includes = array_values(json_decode($this->item->includes));
            }
            if (isset($this->item->joomla_versions)) {
                $this->item->joomla_versions = array_values(json_decode($this->item->joomla_versions));
            }
            if (isset($this->item->created_by)) {
                $this->item->created_by_name = JedHelper::getUserById($this->item->created_by)->name;
            }

            if (isset($this->item->modified_by)) {
                $this->item->modified_by_name = JedHelper::getUserById($this->item->modified_by)->name;
            }

            /* Load Category Hierarchy */
            if (is_null($this->item->primary_category_id)) {
                $this->item->category_hierarchy = "";
            } else {
                $this->item->category_hierarchy = $this->getCategoryHierarchy($this->item->primary_category_id);
            }

            /* Load Varied Data */


            $this->item->varied_data = $this->getVariedData($this->item->id, $supply_option_type);

            foreach ($this->item->varied_data as $v) {
                if ($v->is_default_data === 1) {
                    $this->item->title        = $v->title;
                    $this->item->alias        = $v->alias;
                    $this->item->intro_text   = $v->intro_text;
                    $this->item->support_link = $v->support_link;
                }
            }
            /* Load Scores */
            try {
                $this->item->scores = $this->getScores($this->item->id);
            } catch (Exception $e) {
            }

            $this->item->number_of_reviews = 0;
            $score                         = 0;
            $supplycounter                 = 0;
            $supplytype                    = '';
            foreach ($this->item->scores as $s) {
                $supplycounter = $supplycounter + 1;
                if ($s->supply_option_id == 1) {
                    $supplytype .= 'Free';
                }
                if ($s->supply_option_id == 2) {
                    $comma = '';
                    if ($supplytype <> '') {
                        $comma = ', ';
                    }

                    $supplytype .= $comma . 'Paid';
                }
                $score                         = $score + $s->functionality_score;
                $score                         = $score + $s->ease_of_use_score;
                $score                         = $score + $s->support_score;
                $score                         = $score + $s->value_for_money_score;
                $score                         = $score + $s->documentation_score;
                $this->item->number_of_reviews = $this->item->number_of_reviews + $s->number_of_reviews;
            }
            $this->item->type         = $supplytype;
            $score                    = $score / $supplycounter;
            $this->item->score        = floor($score / 5);
            $this->item->score_string = JedscoreHelper::getStars($this->item->score);
            if ($this->item->number_of_reviews == 0) {
                $this->item->review_string = '';
            } elseif ($this->item->number_of_reviews == 1) {
                $this->item->review_string = '<span>' . $this->item->number_of_reviews . ' review</span>';
            } elseif ($this->item->number_of_reviews > 1) {
                $this->item->review_string = '<span>' . $this->item->number_of_reviews . ' reviews</span>';
            }
            /* Load Reviews */

            $this->item->reviews = $this->getReviews($this->item->id);
            //echo "<pre>";print_r($this->item);echo "</pre>";exit();

            if ($this->item->logo <> "") {
                $this->item->logo = JedHelper::formatImage($this->item->logo, ImageSize::SMALL);
            }


            $this->item->developer_email   = JedHelper::getUserById($this->item->created_by)->email;
            $this->item->developer_company = $this->getDeveloperName($this->item->created_by);


            /*  $db = $this->getDatabase();

            $query = $db->getQuery(true);
            $query->select('supply_options.title AS supply_type, a.*')
                ->from($db->quoteName('#__jed_extension_varied_data', 'a'))
                ->leftJoin(
                    $db->quoteName('#__jed_extension_supply_options', 'supply_options')
                    . ' ON ' . $db->quoteName('supply_options.id') . ' = ' . $db->quoteName('a.supply_option_id')
                )
                ->where($db->quoteName('extension_id') . ' = ' . $db->quote($pk))
            ->where($db->quoteName('supply_option_id') . ' = ' . $db->quote($supply_option_type));

            $db->setQuery($query);
            $result = $db->loadObjectList();

            foreach ($result as $r)
            {

                $supply = $r->supply_type;

                if ($r->logo <> "")
                {
                    ///cache/fab_image/61273fd97f89c_resizeDown1200px525px16.png
                    $r->logo = 'https://extensions.joomla.org/cache/fab_image/' . str_replace('.png', '', $r->logo) . '_resizeDown1200px525px16.png';
                    //echo $item->logo;exit();
                }
                if($r->is_default_data == 1)
                {
                    //echo "<pre>";print_r($r);echo "</pre>";exit();
                    $split_data =  $this->SplitDescription($r->description);
                    if(!is_null($split_data))
                    {
                        $r->intro_text =$split_data['intro'];
                        $r->description = $split_data['body'];
                    }
                }
                $retval[$supply] = $r;
            }
            $item->varied_data = $retval; */

            return $this->item;
        }

        return new stdClass();
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   4.0.0
     * @throws Exception
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jed.edit.extension.data', []);

        if (empty($data)) {
            $data = $this->getItem();


            $this->item = $data;


            // Support for multiple or not foreign key field: uses_updater
            $array = [];

            foreach ((array)$data->uses_updater as $value) {
                if (!is_array($value)) {
                    $array[] = $value;
                }
            }
            if (!empty($array)) {
                $data->uses_updater = $array;
            }

            // Support for multiple or not foreign key field: primary_category_id
            $array = [];

            foreach ((array)$data->primary_category_id as $value) {
                if (!is_array($value)) {
                    $array[] = $value;
                }
            }
            if (!empty($array)) {
                $data->primary_category_id = $array;
            }
        }

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   Table  $table  Table Object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function prepareTable($table)
    {
    }

    /**
     * Remove approved reasons.
     *
     * @param   int  $extensionId  The extension ID to remove the approved reasons for
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function removeApprovedReason(int $extensionId): void
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__jed_extensions_approved_reasons'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query)
            ->execute();
    }

    /**
     * Remove published reasons.
     *
     * @param   int  $extensionId  The extension ID to remove the published reasons for
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function removePublishedReason(int $extensionId): void
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__jed_extensions_published_reasons'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query)
            ->execute();
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  bool  True on success, False on error.
     *
     * @since   4.0.0
     *
     * @throws  Exception
     */
    public function save($data): bool
    {
        unset($data['created_on']);

        if (!$data['id']) {
            $data['created_by'] = Factory::getApplication()->getSession()->get('user')->get('id');
        }

        if (!parent::save($data)) {
            return false;
        }

        $extensionId = $this->getState($this->getName() . '.id');

        /*  if ((int) $data['approve']['approved'] !== 3)
            {
                $this->removeApprovedReason((int) $data['id']);
            }

            if ((int) $data['publish']['published'] === 1)
            {
                $this->removePublishedReason((int) $data['id']);
            }

            $this->storeRelatedCategories($extensionId, $data['related'] ?? []);
            $this->storeVersions($extensionId, $data['phpVersion'] ?? [], 'php');
            $this->storeVersions(
                $extensionId, $data['joomlaVersion'] ?? [], 'joomla'
            );
            $this->storeExtensionTypes($extensionId, $data['extensionTypes'] ?? []);
            $this->storeImages($extensionId, $data['images'] ?? []);
    */

        return true;
    }

    /**
     * Method to save the approved state.
     *
     * @param   array  $data  The form data.
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * @throws  Exception
     */
    public function saveApprove(array $data): void
    {
        if (!$data['id']) {
            throw new InvalidArgumentException(
                Text::_('COM_JED_EXTENSION_ID_MISSING')
            );
        }

        $db          = Factory::getContainer()->get('DatabaseDriver');
        $extensionId = (int)$data['id'];

        /** @var ExtensionTable $table */
        $table = $this->getTable('Extension');

        $table->load($extensionId);

        if (!$table->save($data)) {
            throw new RuntimeException($table->getError());
        }

        $this->removeApprovedReason($extensionId);

        if (empty($data['approvedReason']) || (int)$data['approved'] !== 3) {
            return;
        }

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__jed_extensions_approved_reasons'))
            ->columns(
                $db->quoteName(
                    [
                        'extension_id',
                        'reason',
                    ]
                )
            );

        array_walk(
            $data['approvedReason'],
            static function ($reason) use (&$query, $db, $extensionId) {
                $query->values($extensionId . ',' . $db->quote($reason));
            }
        );

        $db->setQuery($query)
            ->execute();
    }

    /**
     * Method to save the published state.
     *
     * @param   array  $data  The form data.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  Exception
     */
    public function savePublish(array $data): void
    {
        if (!$data['id']) {
            throw new InvalidArgumentException(
                Text::_('COM_JED_EXTENSION_ID_MISSING')
            );
        }

        $db          = Factory::getContainer()->get('DatabaseDriver');
        $extensionId = (int)$data['id'];

        /** @var ExtensionTable $table */
        $table = $this->getTable('Extension');

        $table->load($extensionId);

        if (!$table->save($data)) {
            throw new RuntimeException($table->getError());
        }

        $this->removePublishedReason($extensionId);

        if (empty($data['publishedReason']) || (int)$data['published'] === 1) {
            return;
        }

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__jed_extensions_published_reasons'))
            ->columns(
                $db->quoteName(
                    [
                        'extension_id',
                        'reason',
                    ]
                )
            );

        array_walk(
            $data['publishedReason'],
            static function ($reason) use (&$query, $db, $extensionId) {
                $query->values($extensionId . ',' . $db->quote($reason));
            }
        );

        $db->setQuery($query)
            ->execute();
    }

    /**
     * Store used extension types for an extension.
     *
     * @param   int    $extensionId  The extension ID to save the types for
     * @param   array  $types        The extension types to store
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function storeExtensionTypes(int $extensionId, array $types): void
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__jed_extensions_types'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query)
            ->execute();

        if (empty($types)) {
            return;
        }

        $query->clear()
            ->insert($db->quoteName('#__jed_extensions_types'))
            ->columns(
                $db->quoteName(
                    [
                        'extension_id',
                        'type',
                    ]
                )
            );

        array_walk(
            $types,
            static function ($type) use (&$query, $db, $extensionId) {
                $query->values($extensionId . ',' . $db->quote($type));
            }
        );

        $db->setQuery($query)
            ->execute();
    }

    /**
     * Store the images for an extension.
     *
     * @param   int    $extensionId  The extension ID to save the images for
     * @param   array  $images       The extension types to store
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function storeImages(int $extensionId, array $images): void
    {
        $db = $this->getDatabase();


        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__jed_extensions_images'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query)
            ->execute();

        if (empty($images)) {
            return;
        }

        $query->clear()
            ->insert($db->quoteName('#__jed_extensions_images'))
            ->columns(
                $db->quoteName(
                    [
                        'extension_id',
                        'filename',
                        'order',
                    ]
                )
            );

        array_walk(
            $images,
            static function ($image, $key) use (&$query, $db, $extensionId) {
                $order = (int)str_replace('images', '', $key) + 1;
                $query->values(
                    $extensionId . ',' . $db->quote($image['image']) . ','
                    . $order
                );
            }
        );

        $db->setQuery($query)
            ->execute();
    }

    /**
     * Store an internal note.
     *
     * @param   string  $body         The note content
     * @param   int     $developerId  The developer to store the note for
     * @param   int     $userId       The JED member storing the note
     * @param   int     $extensionId  The extension ID the message is about
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function storeNote(string $body, int $developerId, int $userId, int $extensionId): void
    {
        $developer = User::getInstance($developerId);

        if ($developer->get('id', null) === null) {
            throw new InvalidArgumentException(
                Text::_('COM_JED_DEVELOPER_NOT_FOUND')
            );
        }

        $noteTable = Table::getInstance('Note', 'Table');
        $result    = $noteTable->save(
            [
                'extension_id'    => $extensionId,
                'body'            => $body,
                'developer_id'    => $developer->get('id'),
                'developer_name'  => $developer->get('name'),
                'developer_email' => $developer->get('email'),
                'created'         => (Date::getInstance())->toSql(),
                'created_by'      => $userId,
            ]
        );

        if ($result === false) {
            throw new RuntimeException($noteTable->getError());
        }
    }

    /**
     * Store related categories for an extension.
     *
     * @param   int    $extensionId         The extension ID to save the categories for
     * @param   array  $relatedCategoryIds  The related category IDs to store
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function storeRelatedCategories(
        int $extensionId,
        array $relatedCategoryIds
    ): void {
        $db = $this->getDatabase();


        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__jed_extensions_categories'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query)
            ->execute();

        if (empty($relatedCategoryIds)) {
            return;
        }

        $relatedCategoryIds = array_slice($relatedCategoryIds, 0, 5);

        $query->clear()
            ->insert($db->quoteName('#__jed_extensions_categories'))
            ->columns(
                $db->quoteName(
                    [
                        'extension_id',
                        'category_id',
                    ]
                )
            );

        array_walk(
            $relatedCategoryIds,
            static function ($relatedCategoryId) use (&$query, $extensionId) {
                $query->values($extensionId . ',' . $relatedCategoryId);
            }
        );

        $db->setQuery($query)
            ->execute();
    }

    /**
     * Store supported versions for an extension.
     *
     * @param   int     $extensionId  The extension ID to save the versions for
     * @param   array   $versions     The versions to store
     * @param   string  $type         THe type of versions to store
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function storeVersions(
        int $extensionId,
        array $versions,
        string $type
    ): void {
        $db = $this->getDatabase();


        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__jed_extensions_' . $type . '_versions'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query)
            ->execute();

        if (empty($versions)) {
            return;
        }

        $query->clear()
            ->insert($db->quoteName('#__jed_extensions_' . $type . '_versions'))
            ->columns(
                $db->quoteName(
                    [
                        'extension_id',
                        'version',
                    ]
                )
            );

        array_walk(
            $versions,
            static function ($version) use (&$query, $db, $extensionId) {
                $query->values($extensionId . ',' . $db->quote($version));
            }
        );

        $db->setQuery($query)
            ->execute();
    }
}
