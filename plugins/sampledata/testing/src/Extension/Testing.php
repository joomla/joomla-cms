<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.testing
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\SampleData\Testing\Extension;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Event\SampleData\GetOverviewEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Categories\Administrator\Model\CategoryModel;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Sampledata - Testing Plugin
 *
 * @since  3.8.0
 */
final class Testing extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     *
     * @since  3.8.0
     */
    protected $autoloadLanguage = true;

    /**
     * Holds the category model
     *
     * @var    CategoryModel
     *
     * @since  3.8.0
     */
    private $categoryModel;

    /**
     * Holds the menuitem model
     *
     * @var    \Joomla\Component\Menus\Administrator\Model\ItemModel
     *
     * @since  3.8.0
     */
    private $menuItemModel;

    protected $menuModuleMapping = [];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since 5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSampledataGetOverview'    => 'onSampledataGetOverview',
            'onAjaxSampledataApplyStep1' => 'onAjaxSampledataApplyStep1',
            'onAjaxSampledataApplyStep2' => 'onAjaxSampledataApplyStep2',
            'onAjaxSampledataApplyStep3' => 'onAjaxSampledataApplyStep3',
            'onAjaxSampledataApplyStep4' => 'onAjaxSampledataApplyStep4',
            'onAjaxSampledataApplyStep5' => 'onAjaxSampledataApplyStep5',
            'onAjaxSampledataApplyStep6' => 'onAjaxSampledataApplyStep6',
            'onAjaxSampledataApplyStep7' => 'onAjaxSampledataApplyStep7',
            'onAjaxSampledataApplyStep8' => 'onAjaxSampledataApplyStep8',
            'onAjaxSampledataApplyStep9' => 'onAjaxSampledataApplyStep9',
        ];
    }

    /**
     * Get an overview of the proposed sampledata.
     *
     * @param   GetOverviewEvent $event  Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onSampledataGetOverview(GetOverviewEvent $event): void
    {
        $data              = new \stdClass();
        $data->name        = $this->_name;
        $data->title       = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_OVERVIEW_TITLE');
        $data->description = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_OVERVIEW_DESC');
        $data->icon        = 'bolt';
        $data->steps       = 9;

        $event->addResult($data);
    }

    /**
     * First step to enter the sampledata. Tags
     *
     * @param   AjaxEvent $event  Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep1(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_tags')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 1, 'com_tags');

            $event->addResult($response);
            return;
        }

        /** @var \Joomla\Component\Tags\Administrator\Model\TagModel $model */
        $model  = $this->getApplication()->bootComponent('com_tags')->getMVCFactory()->createModel('Tag', 'Administrator', ['ignore_request' => true]);
        $access = (int) $this->getApplication()->get('access', 1);
        $user   = $this->getApplication()->getIdentity();
        $tagIds = [];

        // Create first three tags.
        for ($i = 0; $i <= 2; $i++) {
            $title = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_TAG_' . $i . '_TITLE');
            $tag   = [
                'id'              => 0,
                'title'           => $title,
                'alias'           => ApplicationHelper::stringURLSafe($title),
                'parent_id'       => 1,
                'published'       => 1,
                'access'          => $access,
                'created_user_id' => $user->id,
                'language'        => '*',
                'description'     => '',
            ];

            try {
                if (!$model->save($tag)) {
                    $this->getApplication()->getLanguage()->load('com_tags');
                    throw new \Exception($this->getApplication()->getLanguage()->_($model->getError()));
                }
            } catch (\Exception $e) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 1, $e->getMessage());

                $event->addResult($response);
                return;
            }

            $tagIds[] = $model->getState('tag.id');
        }

        // Create fourth tag as child of the third.
        $title = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_TAG_3_TITLE');
        $tag   = [
            'id'              => 0,
            'title'           => $title,
            'alias'           => ApplicationHelper::stringURLSafe($title),
            'parent_id'       => $tagIds[2],
            'published'       => 1,
            'access'          => $access,
            'created_user_id' => $user->id,
            'language'        => '*',
            'description'     => '',
        ];

        try {
            if (!$model->save($tag)) {
                $this->getApplication()->getLanguage()->load('com_tags');
                throw new \Exception($this->getApplication()->getLanguage()->_($model->getError()));
            }
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 1, $e->getMessage());

            $event->addResult($response);
            return;
        }

        $tagIds[] = $model->getState('tag.id');

        // Storing IDs in UserState for later usage.
        $this->getApplication()->setUserState('sampledata.testing.tags', $tagIds);

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP1_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Second step to enter the sampledata. Banners
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep2(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_banners')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 2, 'com_banners');

            $event->addResult($response);
            return;
        }

        $factory = $this->getApplication()->bootComponent('com_banners')->getMVCFactory();

        /** @var \Joomla\Component\Banners\Administrator\Model\ClientModel $clientModel */
        $clientModel = $factory->createModel('Client', 'Administrator', ['ignore_request' => true]);

        /** @var \Joomla\Component\Banners\Administrator\Model\BannerModel $bannerModel */
        $bannerModel = $factory->createModel('Banner', 'Administrator', ['ignore_request' => true]);

        $user = $this->getApplication()->getIdentity();

        // Add categories.
        $categories   = [];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_CATEGORY_0_TITLE'),
            'parent_id' => 1,
        ];

        try {
            $catIds = $this->addCategories($categories, 'com_banners', 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 2, $e->getMessage());

            $event->addResult($response);
            return;
        }

        $this->getApplication()->setUserState('sampledata.testing.banners.catids', $catIds);

        // Add Clients.
        $clients     = [];
        $clients[]   = [
            'name'              => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_CLIENT_1_NAME'),
            'contact'           => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_CLIENT_1_CONTACT'),
            'purchase_type'     => -1,
            'track_clicks'      => -1,
            'track_impressions' => -1,
        ];
        $clients[]   = [
            'name'              => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_CLIENT_2_NAME'),
            'contact'           => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_CLIENT_2_CONTACT'),
            'email'             => 'banner@example.com',
            'purchase_type'     => -1,
            'track_clicks'      => 0,
            'track_impressions' => 0,
        ];
        $clients[]   = [
            'name'              => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_CLIENT_3_NAME'),
            'contact'           => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_CLIENT_3_CONTACT'),
            'purchase_type'     => -1,
            'track_clicks'      => 0,
            'track_impressions' => 0,
        ];
        $clientIds   = [];

        foreach ($clients as $client) {
            // Set values which are always the same.
            $client['id']        = 0;
            $client['email']     = 'banner@example.com';
            $client['state']     = 1;
            $client['metakey']   = '';
            $client['extrainfo'] = '';

            try {
                if (!$clientModel->save($client)) {
                    $this->getApplication()->getLanguage()->load('com_banners');
                    throw new \Exception($this->getApplication()->getLanguage()->_($clientModel->getError()));
                }
            } catch (\Exception $e) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 2, $e->getMessage());

                $event->addResult($response);
                return;
            }

            $clientIds[] = $clientModel->getState('banner.id');
        }

        // Add Banners.
        $banners   = [];
        $banners[] = [
            'cid'         => $clientIds[2],
            'name'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_BANNER_1_NAME'),
            'clickurl'    => 'https://community.joomla.org/the-joomla-shop.html',
            'catid'       => $catIds[0],
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_BANNER_1_DESC'),
            'ordering'    => 1,
            'params'      => '{"imageurl":"images/banners/white.png","width":"","height":"","alt":"Joomla! Books"}',
        ];
        $banners[] = [
            'cid'         => $clientIds[1],
            'name'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_BANNER_2_NAME'),
            'clickurl'    => 'https://shop.joomla.org',
            'catid'       => $catIds[0],
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_BANNER_2_DESC'),
            'ordering'    => 2,
            'params'      => '{"imageurl":"images/banners/white.png","width":"","height":"","alt":"Joomla! Shop"}',
        ];
        $banners[] = [
            'cid'         => $clientIds[0],
            'name'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_BANNER_3_NAME'),
            'clickurl'    => 'https://www.joomla.org/sponsor.html',
            'catid'       => $catIds[0],
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_BANNERS_BANNER_3_DESC'),
            'ordering'    => 3,
            'params'      => '{"imageurl":"images/banners/white.png","width":"","height":"","alt":""}',
        ];

        foreach ($banners as $banner) {
            // Set values which are always the same.
            $banner['id']               = 0;
            $banner['type']             = 0;
            $banner['state']            = 1;
            $banner['alias']            = ApplicationHelper::stringURLSafe($banner['name']);
            $banner['custombannercode'] = '';
            $banner['metakey']          = '';
            $banner['purchase_type']    = -1;
            $banner['created_by']       = $user->id;
            $banner['created_by_alias'] = 'Joomla';
            $banner['language']         = 'en-GB';

            try {
                if (!$bannerModel->save($banner)) {
                    $this->getApplication()->getLanguage()->load('com_banners');
                    throw new \Exception($this->getApplication()->getLanguage()->_($bannerModel->getError()));
                }
            } catch (\Exception $e) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 2, $e->getMessage());

                $event->addResult($response);
                return;
            }
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP2_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Third step to enter the sampledata. Content 1/2
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep3(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_content')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 3, 'com_content');

            $event->addResult($response);
            return;
        }

        // Insert first level of categories.
        $categories   = [];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_TITLE'),
            'parent_id' => 1,
        ];

        try {
            $catIdsLevel1 = $this->addCategories($categories, 'com_content', 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 3, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert second level of categories.
        $categories   = [];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_TITLE'),
            'parent_id' => $catIdsLevel1[0],
        ];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_1_TITLE'),
            'parent_id' => $catIdsLevel1[0],
            'language'  => 'en-GB',
        ];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_2_TITLE'),
            'parent_id' => $catIdsLevel1[0],
        ];

        try {
            $catIdsLevel2 = $this->addCategories($categories, 'com_content', 2);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 3, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert third level of categories.
        $categories   = [];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_DESC'),
            'parent_id'   => $catIdsLevel2[0],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_1_1_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_1_1_DESC'),
            'parent_id'   => $catIdsLevel2[1],
            'params'      => '{"category_layout":"","image":"images/sampledata/parks/banner_cradle.jpg"}',
            'language'    => 'en-GB',
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_1_2_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_1_2_DESC'),
            'parent_id'   => $catIdsLevel2[1],
            'language'    => 'en-GB',
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_2_3_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_2_3_DESC'),
            'parent_id'   => $catIdsLevel2[2],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_2_4_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_2_4_DESC'),
            'parent_id'   => $catIdsLevel2[2],
        ];

        try {
            $catIdsLevel3 = $this->addCategories($categories, 'com_content', 3);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 3, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert fourth level of categories.
        $categories   = [];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_0_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_0_DESC'),
            'parent_id'   => $catIdsLevel3[0],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_DESC'),
            'parent_id'   => $catIdsLevel3[0],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_DESC'),
            'parent_id'   => $catIdsLevel3[0],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_3_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_3_DESC'),
            'parent_id'   => $catIdsLevel3[0],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_4_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_4_DESC'),
            'parent_id'   => $catIdsLevel3[0],
        ];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_1_2_5_TITLE'),
            'parent_id' => $catIdsLevel3[2],
            'language'  => 'en-GB',
        ];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_1_2_6_TITLE'),
            'parent_id' => $catIdsLevel3[2],
            'language'  => 'en-GB',
        ];

        try {
            $catIdsLevel4 = $this->addCategories($categories, 'com_content', 4);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 3, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert fifth level of categories.
        $categories   = [];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_0_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_0_DESC'),
            'parent_id'   => $catIdsLevel4[1],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_1_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_1_DESC'),
            'parent_id'   => $catIdsLevel4[1],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_2_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_2_DESC'),
            'parent_id'   => $catIdsLevel4[1],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_3_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_3_DESC'),
            'parent_id'   => $catIdsLevel4[1],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_4_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_1_4_DESC'),
            'parent_id'   => $catIdsLevel4[1],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_5_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_5_DESC'),
            'parent_id'   => $catIdsLevel4[2],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_6_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_6_DESC'),
            'parent_id'   => $catIdsLevel4[2],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_7_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_CATEGORY_0_0_0_2_7_DESC'),
            'parent_id'   => $catIdsLevel4[2],
        ];

        try {
            $catIdsLevel5 = $this->addCategories($categories, 'com_content', 5);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 3, $e->getMessage());

            $event->addResult($response);
            return;
        }

        $this->getApplication()->setUserState('sampledata.testing.articles.catids1', $catIdsLevel1);
        $this->getApplication()->setUserState('sampledata.testing.articles.catids2', $catIdsLevel2);
        $this->getApplication()->setUserState('sampledata.testing.articles.catids3', $catIdsLevel3);
        $this->getApplication()->setUserState('sampledata.testing.articles.catids4', $catIdsLevel4);
        $this->getApplication()->setUserState('sampledata.testing.articles.catids5', $catIdsLevel5);

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP3_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Fourth step to enter the sampledata. Content 2/2
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function onAjaxSampledataApplyStep4(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_content')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 4, 'com_content');

            $event->addResult($response);
            return;
        }

        ComponentHelper::getParams('com_content')->set('workflow_enabled', 0);

        $catIdsLevel1 = $this->getApplication()->getUserState('sampledata.testing.articles.catids1');
        $catIdsLevel2 = $this->getApplication()->getUserState('sampledata.testing.articles.catids2');
        $catIdsLevel3 = $this->getApplication()->getUserState('sampledata.testing.articles.catids3');
        $catIdsLevel4 = $this->getApplication()->getUserState('sampledata.testing.articles.catids4');
        $catIdsLevel5 = $this->getApplication()->getUserState('sampledata.testing.articles.catids5');
        $tagIds       = $this->getApplication()->getUserState('sampledata.testing.tags', []);

        $articles = [
            // Articles 0 - 9
            [
                'catid'    => $catIdsLevel4[0],
                'ordering' => 7,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 5,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 6,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 7,
            ],
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel2[1],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel5[2],
                'ordering' => 6,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 4,
                'featured' => 1,
            ],
            [
                'catid'    => $catIdsLevel4[0],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel4[0],
                'ordering' => 1,
            ],
            // Articles 10 - 19
            [
                'catid'  => $catIdsLevel4[6],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/landscape/250px_cradle_mountain_seen_from_barn_bluff.jpg',
                    'image_intro_alt'        => 'Cradle Mountain',
                    'image_fulltext'         => 'images/sampledata/parks/landscape/250px_cradle_mountain_seen_from_barn_bluff.jpg',
                    'image_fulltext_alt'     => 'Cradle Mountain',
                    'image_fulltext_caption' => 'Source: https://commons.wikimedia.org/wiki/File:Rainforest,bluemountainsNSW.jpg'
                        . ' Author: Alan J.W.C. License: GNU Free Documentation License v. 1.2 or later',
                ],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel5[2],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel2[2],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 5,
            ],
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 6,
            ],
            [
                'catid'    => $catIdsLevel5[2],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel3[1],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel3[1],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel5[2],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel2[2],
                'ordering' => 1,
            ],
            // Articles 20 - 29
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 8,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 9,
            ],
            [
                'catid'    => $catIdsLevel3[3],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 2,
                'tags'     => array_map('strval', $tagIds),
                'featured' => 1,
            ],
            [
                'catid'  => $catIdsLevel5[4],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/animals/180px_koala_ag1.jpg',
                    'image_intro_alt'        => 'Koala Thumbnail',
                    'image_fulltext'         => 'images/sampledata/parks/animals/800px_koala_ag1.jpg',
                    'image_fulltext_alt'     => 'Koala Climbing Tree',
                    'image_fulltext_caption' => 'Source: https://en.wikipedia.org/wiki/File:Koala-ag1.jpg'
                        . ' Author: Arnaud Gaillard License: Creative Commons Share Alike Attribution Generic 1.0',
                ],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel5[3],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel5[1],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel5[4],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 2,
            ],
            // Articles 30 - 39
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 10,
            ],
            [
                'catid'  => $catIdsLevel5[4],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/animals/200px_phyllopteryx_taeniolatus1.jpg',
                    'image_intro_alt'        => 'Phyllopteryx',
                    'image_fulltext'         => 'images/sampledata/parks/animals/800px_phyllopteryx_taeniolatus1.jpg',
                    'image_fulltext_alt'     => 'Phyllopteryx',
                    'image_fulltext_caption' => 'Source: https://en.wikipedia.org/wiki/File:Phyllopteryx_taeniolatus1.jpg'
                        . ' Author: Richard Ling License: GNU Free Documentation License v 1.2 or later',
                ],
                'ordering' => 3,
            ],
            [
                'catid'  => $catIdsLevel4[6],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/landscape/120px_pinnacles_western_australia.jpg',
                    'image_intro_alt'        => 'Kings Canyon',
                    'image_fulltext'         => 'images/sampledata/parks/landscape/800px_pinnacles_western_australia.jpg',
                    'image_fulltext_alt'     => 'Kings Canyon',
                    'image_fulltext_caption' => 'Source: https://commons.wikimedia.org/wiki/File:Pinnacles_Western_Australia.jpg'
                        . ' Author: Martin Gloss License: GNU Free Documentation license v 1.2 or later.',
                ],
                'ordering' => 4,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 5,
                'featured' => 1,
            ],
            [
                'catid'    => $catIdsLevel5[2],
                'ordering' => 4,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 4,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 11,
            ],
            [
                'catid'    => $catIdsLevel4[0],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel5[3],
                'ordering' => 4,
            ],
            // Articles 40 - 49
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel1[0],
                'ordering' => 1,
            ],
            [
                'catid'  => $catIdsLevel5[4],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/animals/220px_spottedquoll_2005_seanmcclean.jpg',
                    'image_intro_alt'        => 'Spotted Quoll',
                    'image_fulltext'         => 'images/sampledata/parks/animals/789px_spottedquoll_2005_seanmcclean.jpg',
                    'image_fulltext_alt'     => 'Spotted Quoll',
                    'image_fulltext_caption' => 'Source: https://en.wikipedia.org/wiki/File:SpottedQuoll_2005_SeanMcClean.jpg'
                        . ' Author: Sean McClean License: GNU Free Documentation License v 1.2 or later',
                ],
                'ordering' => 4,
            ],
            [
                'catid'    => $catIdsLevel5[3],
                'ordering' => 5,
            ],
            [
                'catid'    => $catIdsLevel5[3],
                'ordering' => 6,
            ],
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel4[2],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 6,
                'featured' => 1,
            ],
            // Articles 50 - 59
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 4,
            ],
            [
                'catid'    => $catIdsLevel4[0],
                'ordering' => 5,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'ordering' => 7,
            ],
            [
                'catid'    => $catIdsLevel5[1],
                'ordering' => 1,
            ],
            [
                'catid'  => $catIdsLevel5[4],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/animals/180px_wobbegong.jpg',
                    'image_intro_alt'        => 'Wobbegon',
                    'image_fulltext'         => 'images/sampledata/parks/animals/800px_wobbegong.jpg',
                    'image_fulltext_alt'     => 'Wobbegon',
                    'image_fulltext_caption' => 'Source: https://en.wikipedia.org/wiki/File:Wobbegong.jpg'
                        . ' Author: Richard Ling License: GNU Free Documentation License v 1.2 or later',
                ],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel3[3],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel5[3],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel4[0],
                'ordering' => 4,
            ],
            [
                'catid'    => $catIdsLevel5[4],
                'ordering' => 2,
            ],
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 7,
            ],
            // Articles 60 - 69
            [
                'catid'  => $catIdsLevel4[6],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/landscape/120px_rainforest_bluemountainsnsw.jpg',
                    'float_intro'            => 'none',
                    'image_intro_alt'        => 'Rain Forest Blue Mountains',
                    'image_fulltext'         => 'images/sampledata/parks/landscape/727px_rainforest_bluemountainsnsw.jpg',
                    'image_fulltext_alt'     => 'Rain Forest Blue Mountains',
                    'image_fulltext_caption' => 'Source: https://commons.wikimedia.org/wiki/File:Rainforest,bluemountainsNSW.jpg'
                        . ' Author: Adam J.W.C. License: GNU Free Public Documentation License',
                ],
                'ordering' => 2,
            ],
            [
                'catid'  => $catIdsLevel4[6],
                'images' => [
                    'image_intro'            => 'images/sampledata/parks/landscape/180px_ormiston_pound.jpg',
                    'float_intro'            => 'none',
                    'image_intro_alt'        => 'Ormiston Pound',
                    'image_fulltext'         => 'images/sampledata/parks/landscape/800px_ormiston_pound.jpg',
                    'image_fulltext_alt'     => 'Ormiston Pound',
                    'image_fulltext_caption' => 'Source: https://commons.wikimedia.org/wiki/File:Ormiston_Pound.JPG'
                        . ' Author: License: GNU Free Public Documentation License',
                ],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel5[1],
                'ordering' => 3,
            ],
            [
                'catid'    => $catIdsLevel2[0],
                'state'    => 2,
                'ordering' => 0,
            ],
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 1,
            ],
            [
                'catid'    => $catIdsLevel4[4],
                'ordering' => 0,
            ],
            [
                'catid'    => $catIdsLevel5[3],
                'ordering' => 0,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'tags'     => array_map('strval', \array_slice($tagIds, 0, 3)),
                'ordering' => 0,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 0,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 0,
            ],
            // Articles 70 -
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 0,
            ],
            [
                'catid'    => $catIdsLevel5[0],
                'ordering' => 0,
            ],
        ];

        try {
            $ids = $this->addArticles($articles);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 4, $e->getMessage());

            $event->addResult($response);
            return;
        }

        $articleNamespace = (array) $this->getApplication()->getUserState('sampledata.testing.articles');
        $this->getApplication()->setUserState('sampledata.testing.articles', array_merge($ids, $articleNamespace));

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP4_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Fifth step to enter the sampledata. Contacts
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep5(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_contact')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 5, 'com_contact');

            $event->addResult($response);
            return;
        }

        $model  = $this->getApplication()->bootComponent('com_contact')->getMVCFactory()->createModel('Contact', 'Administrator', ['ignore_request' => true]);
        $access = (int) $this->getApplication()->get('access', 1);
        $user   = $this->getApplication()->getIdentity();

        // Insert first level of categories.
        $categories   = [];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CATEGORY_0_TITLE'),
            'parent_id' => 1,
        ];

        try {
            $catIdsLevel1 = $this->addCategories($categories, 'com_contact', 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 5, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert second level of categories.
        $categories   = [];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CATEGORY_0_0_TITLE'),
            'parent_id' => $catIdsLevel1[0],
        ];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CATEGORY_0_1_TITLE'),
            'parent_id' => $catIdsLevel1[0],
        ];

        try {
            $catIdsLevel2 = $this->addCategories($categories, 'com_contact', 2);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 5, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert third level of categories.
        $categories   = [];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CATEGORY_0_1_0_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CATEGORY_0_1_0_DESC'),
            'parent_id'   => $catIdsLevel2[1],
        ];
        $categories[] = [
            'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CATEGORY_0_1_1_TITLE'),
            'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CATEGORY_0_1_1_DESC'),
            'parent_id'   => $catIdsLevel2[1],
        ];

        try {
            $catIdsLevel3 = $this->addCategories($categories, 'com_contact', 3);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 5, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert fourth level of categories.
        $categories = [];

        // Categories A-Z.
        for ($i = 65; $i <= 90; $i++) {
            $categories[] = [
                'title'     => \chr($i),
                'parent_id' => $catIdsLevel3[1],
            ];
        }

        try {
            $catIdsLevel4 = $this->addCategories($categories, 'com_contact', 4);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 5, $e->getMessage());

            $event->addResult($response);
            return;
        }

        $contacts   = [
            [
                'name'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_NAME'),
                'con_position' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_POSITION'),
                'address'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_ADDRESS'),
                'suburb'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_SUBURB'),
                'state'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_STATE'),
                'country'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_COUNTRY'),
                'postcode'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_POSTCODE'),
                'telephone'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_TELEPHONE'),
                'fax'          => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_FAX'),
                'misc'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_MISC'),
                'sortname1'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_SORTNAME1'),
                'sortname2'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_SORTNAME2'),
                'sortname3'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_0_SORTNAME3'),
                'image'        => 'images/powered_by.png',
                'email_to'     => 'email@example.com',
                'default_con'  => 1,
                'featured'     => 1,
                'catid'        => $catIdsLevel1[0],
                'params'       => [
                    'show_links' => 1,
                    'linka_name' => 'Twitter',
                    'linka'      => 'https://twitter.com/joomla',
                    'linkb_name' => 'YouTube',
                    'linkb'      => 'https://www.youtube.com/user/joomla',
                    'linkc_name' => 'Facebook',
                    'linkc'      => 'https://www.facebook.com/joomla',
                    'linkd_name' => 'LinkedIn',
                    'linkd'      => 'https://www.linkedin.com/company/joomla',
                    'linke_name' => 'Scribed',
                    'linke'      => 'https://www.scribd.com/people/view/504592-joomla',
                ],
            ],
            [
                'name'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_1_NAME'),
                'email_to' => 'webmaster@example.com',
                'featured' => 1,
                'catid'    => $catIdsLevel2[0],
            ],
            [
                'name'  => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_2_NAME'),
                'misc'  => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_2_MISC'),
                'catid' => $catIdsLevel3[0],
            ],
            [
                'name'  => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_3_NAME'),
                'misc'  => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_3_MISC'),
                'catid' => $catIdsLevel3[0],
            ],
            [
                'name'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_4_NAME'),
                'con_position' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_4_POSITION'),
                'address'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_4_ADDRESS'),
                'state'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_4_STATE'),
                'misc'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_4_MISC'),
                'image'        => 'images/sampledata/fruitshop/bananas_2.jpg',
                'catid'        => $catIdsLevel4[1],
                'params'       => [
                    'show_contact_category' => 'show_with_link',
                    'presentation_style'    => 'plain',
                    'show_position'         => 1,
                    'show_state'            => 1,
                    'show_country'          => 1,
                    'show_links'            => 1,
                    'linka_name'            => 'Wikipedia: Banana English',
                    'linka'                 => 'https://en.wikipedia.org/wiki/Banana',
                    'linkb_name'            => 'Wikipedia: हिन्दी केला',
                    'linkb'                 => 'https://hi.wikipedia.org/wiki/%E0%A4%95%E0%A5%87%E0%A4%B2%E0%A4%BE',
                    'linkc_name'            => 'Wikipedia:Banana Português',
                    'linkc'                 => 'https://pt.wikipedia.org/wiki/Banana',
                    'linkd_name'            => 'Wikipedia: Банан  Русский',
                    'linkd'                 => 'https://ru.wikipedia.org/wiki/%D0%91%D0%B0%D0%BD%D0%B0%D0%BD',
                    'linke_name'            => '',
                    'linke'                 => '',
                    'contact_layout'        => 'beez5:encyclopedia',
                ],
            ],
            [
                'name'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_5_NAME'),
                'con_position' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_5_POSITION'),
                'address'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_5_ADDRESS'),
                'state'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_5_STATE'),
                'misc'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_5_MISC'),
                'image'        => 'images/sampledata/fruitshop/apple.jpg',
                'catid'        => $catIdsLevel4[0],
                'params'       => [
                    'presentation_style' => 'plain',
                    'show_links'         => 1,
                    'linka_name'         => 'Wikipedia: Apples English',
                    'linka'              => 'https://en.wikipedia.org/wiki/Apple',
                    'linkb_name'         => 'Wikipedia: Manzana Español',
                    'linkb'              => 'https://es.wikipedia.org/wiki/Manzana',
                    'linkc_name'         => 'Wikipedia: 苹果 中文',
                    'linkc'              => 'https://zh.wikipedia.org/zh/苹果',
                    'linkd_name'         => 'Wikipedia: Tofaa Kiswahili',
                    'linkd'              => 'https://sw.wikipedia.org/wiki/Tofaa',
                    'linke_name'         => '',
                    'linke'              => '',
                    'contact_layout'     => 'beez5:encyclopedia',
                ],
            ],
            [
                'name'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_6_NAME'),
                'con_position' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_6_POSITION'),
                'address'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_6_ADDRESS'),
                'state'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_6_STATE'),
                'misc'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_6_MISC'),
                'image'        => 'images/sampledata/fruitshop/tamarind.jpg',
                'catid'        => $catIdsLevel4[19],
                'params'       => [
                    'presentation_style' => 'plain',
                    'show_links'         => 1,
                    'linka_name'         => 'Wikipedia: Tamarind English',
                    'linka'              => 'https://en.wikipedia.org/wiki/Tamarind',
                    'linkb_name'         => 'Wikipedia: তেঁতুল  বাংলা',
                    'linkb'              => 'https://bn.wikipedia.org/wiki/তেঁতুল',
                    'linkc_name'         => 'Wikipedia: Tamarinier Français',
                    'linkc'              => 'https://fr.wikipedia.org/wiki/Tamarinier',
                    'linkd_name'         => 'Wikipedia:Tamaline lea faka-Tonga',
                    'linkd'              => 'https://to.wikipedia.org/wiki/Tamaline',
                    'linke_name'         => '',
                    'linke'              => '',
                    'contact_layout'     => 'beez5:encyclopedia',
                ],
            ],
            [
                'name'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_7_NAME'),
                'suburb'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_7_SUBURB'),
                'country'   => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_7_COUNTRY'),
                'address'   => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_7_ADDRESS'),
                'telephone' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_7_TELEPHONE'),
                'misc'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTACT_CONTACT_7_MISC'),
                'catid'     => $catIdsLevel2[1],
            ],
        ];
        $contactIds = [];

        foreach ($contacts as $contact) {
            // Set values which are always the same.
            $contact['id']              = 0;
            $contact['access']          = $access;
            $contact['created_user_id'] = $user->id;
            $contact['alias']           = ApplicationHelper::stringURLSafe($contact['name']);
            $contact['published']       = 1;
            $contact['language']        = '*';
            $contact['associations']    = [];

            // Reset some fields if not specified.
            $fields = ['con_position', 'address', 'suburb', 'state', 'country', 'postcode', 'telephone', 'fax',
                'misc', 'sortname1', 'sortname2', 'sortname3', 'email_to', 'image', ];

            // Temporary, they are waiting for PR #14112
            $fields[]            = 'metakey';
            $fields[]            = 'metadesc';
            $contact['metadata'] = '{}';

            foreach ($fields as $field) {
                if (!isset($contact[$field])) {
                    $contact[$field] = '';
                }
            }

            // Set featured state to published if not set.
            if (!isset($contact['featured'])) {
                $contact['featured'] = 0;
            }

            // Set state to published if not set.
            if (!isset($contact['default_con'])) {
                $contact['default_con'] = 0;
            }

            // Set params to empty if not set.
            if (!isset($contact['params'])) {
                $contact['params'] = [
                    'linka_name' => '',
                    'linka'      => '',
                    'linkb_name' => '',
                    'linkb'      => '',
                    'linkc_name' => '',
                    'linkc'      => '',
                    'linkd_name' => '',
                    'linkd'      => '',
                    'linke_name' => '',
                    'linke'      => '',
                ];
            }

            try {
                if (!$model->save($contact)) {
                    $this->getApplication()->getLanguage()->load('com_contact');
                    throw new \Exception($this->getApplication()->getLanguage()->_($model->getError()));
                }
            } catch (\Exception $e) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 5, $e->getMessage());

                $event->addResult($response);
                return;
            }

            // Get ID from category we just added
            $contactIds[] = $model->getItem()->id;
        }

        // Storing IDs in UserState for later usage.
        $this->getApplication()->setUserState('sampledata.testing.contacts', $contactIds);
        $this->getApplication()->setUserState('sampledata.testing.contacts.catids1', $catIdsLevel1);
        $this->getApplication()->setUserState('sampledata.testing.contacts.catids2', $catIdsLevel2);
        $this->getApplication()->setUserState('sampledata.testing.contacts.catids3', $catIdsLevel3);
        $this->getApplication()->setUserState('sampledata.testing.contacts.catids4', $catIdsLevel4);

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP5_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Sixth step to enter the sampledata. Newsfeed.
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep6(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_newsfeeds')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 6, 'com_newsfeed');

            $event->addResult($response);
            return;
        }

        /** @var \Joomla\Component\Newsfeeds\Administrator\Model\NewsfeedModel $model */
        $model  = $this->getApplication()->bootComponent('com_newsfeeds')->getMVCFactory()->createModel('Newsfeed', 'Administrator', ['ignore_request' => true]);
        $access = (int) $this->getApplication()->get('access', 1);
        $user   = $this->getApplication()->getIdentity();

        // Insert first level of categories.
        $categories   = [];
        $categories[] = [
            'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_NEWSFEEDS_CATEGORY_0_TITLE'),
            'parent_id' => 1,
        ];

        try {
            $catIdsLevel1 = $this->addCategories($categories, 'com_newsfeeds', 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 6, $e->getMessage());

            $event->addResult($response);
            return;
        }

        $newsfeeds    = [
            [
                'name'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_NEWSFEEDS_NEWSFEED_0_NAME'),
                'link'     => 'https://www.joomla.org/announcements.feed?type=rss',
                'ordering' => 1,
            ],
            [
                'name'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_NEWSFEEDS_NEWSFEED_1_NAME'),
                'link'     => 'https://extensions.joomla.org/browse/new?format=feed&type=rss',
                'ordering' => 4,
            ],
            [
                'name'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_NEWSFEEDS_NEWSFEED_2_NAME'),
                'link'     => 'https://developer.joomla.org/security-centre.feed?type=rss',
                'ordering' => 2,
            ],
            [
                'name'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_NEWSFEEDS_NEWSFEED_3_NAME'),
                'link'     => 'https://community.joomla.org/blogs/community.feed',
                'ordering' => 3,
            ],
        ];
        $newsfeedsIds = [];

        foreach ($newsfeeds as $newsfeed) {
            // Set values which are always the same.
            $newsfeed['id']              = 0;
            $newsfeed['access']          = $access;
            $newsfeed['created_user_id'] = $user->id;
            $newsfeed['alias']           = ApplicationHelper::stringURLSafe($newsfeed['name']);
            $newsfeed['published']       = 1;
            $newsfeed['language']        = '*';
            $newsfeed['associations']    = [];
            $newsfeed['numarticles']     = 5;
            $newsfeed['cache_time']      = 3600;
            $newsfeed['rtl']             = 1;
            $newsfeed['description']     = '';
            $newsfeed['images']          = '';
            $newsfeed['catid']           = $catIdsLevel1[0];

            // Temporary, it should be fixed in other place
            $newsfeed['metakey']         = '';
            $newsfeed['metadesc']        = '';
            $newsfeed['xreference']      = '';
            $newsfeed['metadata']        = '{}';
            $newsfeed['params']          = '{}';

            try {
                if (!$model->save($newsfeed)) {
                    $this->getApplication()->getLanguage()->load('com_newsfeeds');
                    throw new \Exception($this->getApplication()->getLanguage()->_($model->getError()));
                }
            } catch (\Exception $e) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 6, $e->getMessage());

                $event->addResult($response);
                return;
            }

            // Get ID from category we just added
            $newsfeedsIds[] = $model->getState('newsfeed.id');
        }

        // Storing IDs in UserState for later usage.
        $this->getApplication()->setUserState('sampledata.testing.newsfeeds', $newsfeedsIds);
        $this->getApplication()->setUserState('sampledata.testing.newsfeeds.catids', $catIdsLevel1);

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP6_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Seventh step to enter the sampledata. Menus.
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep7(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_menus')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 7, 'com_menus');

            $event->addResult($response);
            return;
        }

        /** @var \Joomla\Component\Menus\Administrator\Model\MenuModel $model */
        $factory   = $this->getApplication()->bootComponent('com_menus')->getMVCFactory();
        $model     = $factory->createModel('Menu', 'Administrator', ['ignore_request' => true]);
        $modelItem = $factory->createModel('Item', 'Administrator', ['ignore_request' => true]);
        $menuTypes = [];

        for ($i = 0; $i <= 7; $i++) {
            $menu = [
                'id'          => 0,
                'title'       => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_MENU_' . $i . '_TITLE'),
                'description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_MENU_' . $i . '_DESCRIPTION'),
            ];

            // Calculate menutype.
            $menu['menutype'] = ApplicationHelper::stringURLSafe($menu['title']);

            try {
                $model->save($menu);
            } catch (\Exception $e) {
                $this->getApplication()->getLanguage()->load('com_menus');
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 7, $e->getMessage());

                $event->addResult($response);
                return;
            }

            $menuTypes[] = $menu['menutype'];
        }

        // Storing IDs in UserState for later usage.
        $this->getApplication()->setUserState('sampledata.testing.menutypes', $menuTypes);

        // Get previously entered Data from UserStates
        $contactIds      = $this->getApplication()->getUserState('sampledata.testing.contacts');
        $contactCatids1  = $this->getApplication()->getUserState('sampledata.testing.contacts.catids1');
        $contactCatids3  = $this->getApplication()->getUserState('sampledata.testing.contacts.catids3');
        $articleIds      = $this->getApplication()->getUserState('sampledata.testing.articles');
        $articleCatids1  = $this->getApplication()->getUserState('sampledata.testing.articles.catids1');
        $articleCatids2  = $this->getApplication()->getUserState('sampledata.testing.articles.catids2');
        $articleCatids3  = $this->getApplication()->getUserState('sampledata.testing.articles.catids3');
        $articleCatids4  = $this->getApplication()->getUserState('sampledata.testing.articles.catids4');
        $articleCatids5  = $this->getApplication()->getUserState('sampledata.testing.articles.catids5');
        $tagIds          = $this->getApplication()->getUserState('sampledata.testing.tags');
        $newsfeedsIds    = $this->getApplication()->getUserState('sampledata.testing.newsfeeds');
        $newsfeedsCatids = $this->getApplication()->getUserState('sampledata.testing.newsfeeds.catids');

        // Unset current "Home" menuitem since we set a new one.
        $menuItemTable = $modelItem->getTable();
        $menuItemTable->load(
            [
                'home'     => 1,
                'language' => '*',
            ]
        );
        $menuItemTable->home = 0;
        $menuItemTable->store();

        // Insert User Menu Items
        $userMenuItems = [
            [
                'menutype'     => $menuTypes[0],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_0_TITLE'),
                'link'         => 'index.php?option=com_users&view=profile',
                'component_id' => ComponentHelper::getComponent('com_users')->id,
                'access'       => 2,
                'params'       => [
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
            ],
            [
                'menutype'     => $menuTypes[0],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_66_TITLE'),
                'link'         => 'index.php?option=com_content&view=form&layout=edit',
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'access'       => 3,
                'params'       => [
                    'enable_category'   => 0,
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
            ],
        ];

        try {
            $userMenuIds = $this->addMenuItems($userMenuItems, 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 7, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert Park Menu Items
        $parkMenuItems = [
            [
                'menutype'          => $menuTypes[3],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_4_TITLE'),
                'link'              => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids3[1],
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'template_style_id' => 114,
                'params'            => [
                    'show_description'       => 1,
                    'show_description_image' => 1,
                    'num_leading_articles'   => 1,
                    'num_intro_articles'     => 4,
                    'num_columns'            => 1,
                    'num_links'              => 4,
                    'show_pagination'        => 2,
                    'show_feed_link'         => 1,
                    'show_page_heading'      => 0,
                    'secure'                 => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[3],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_6_TITLE'),
                'link'              => 'index.php?option=com_content&view=form&layout=edit',
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'access'            => 3,
                'template_style_id' => 114,
                'params'            => [
                    'enable_category'   => 0,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[3],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_7_TITLE'),
                'link'              => 'index.php?option=com_content&view=article&id=' . $articleIds[5],
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'template_style_id' => 114,
                'params'            => [
                    'show_title'           => 0,
                    'show_category'        => 0,
                    'link_category'        => 0,
                    'show_author'          => 0,
                    'show_create_date'     => 0,
                    'show_modify_date'     => 0,
                    'show_publish_date'    => 0,
                    'show_item_navigation' => 0,
                    'show_print_icon'      => 0,
                    'show_email_icon'      => 0,
                    'show_hits'            => 0,
                    'show_page_heading'    => 0,
                    'secure'               => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[3],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_8_TITLE'),
                'link'              => 'index.php?option=com_content&view=categories&id=' . $articleCatids3[2],
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'template_style_id' => 114,
                'params'            => [
                    'show_base_description'  => 1,
                    'drill_down_layout'      => 1,
                    'show_description'       => 1,
                    'show_description_image' => 1,
                    'maxLevel'               => -1,
                    'num_leading_articles'   => 1,
                    'num_intro_articles'     => 4,
                    'num_columns'            => 2,
                    'num_links'              => 4,
                    'show_page_heading'      => 0,
                    'secure'                 => 0,
                ],
                'children' => [
                    [
                        'menutype'          => $menuTypes[3],
                        'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_8_1_TITLE'),
                        'link'              => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids4[5],
                        'component_id'      => ComponentHelper::getComponent('com_content')->id,
                        'template_style_id' => 114,
                        'params'            => [
                            'show_description'       => 1,
                            'show_description_image' => 0,
                            'num_leading_articles'   => 0,
                            'num_intro_articles'     => 6,
                            'num_columns'            => 2,
                            'num_links'              => 4,
                            'multi_column_order'     => 1,
                            'show_pagination'        => 2,
                            'show_intro'             => 0,
                            'show_category'          => 1,
                            'link_category'          => 1,
                            'show_author'            => 0,
                            'show_create_date'       => 0,
                            'show_modify_date'       => 0,
                            'show_publish_date'      => 0,
                            'show_item_navigation'   => 1,
                            'show_feed_link'         => 1,
                            'show_page_heading'      => 0,
                            'secure'                 => 0,
                        ],
                    ],
                    [
                        'menutype'          => $menuTypes[3],
                        'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_8_2_TITLE'),
                        'link'              => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids4[5],
                        'component_id'      => ComponentHelper::getComponent('com_content')->id,
                        'template_style_id' => 114,
                        'params'            => [
                            'show_description'       => 0,
                            'show_description_image' => 0,
                            'num_leading_articles'   => 0,
                            'num_intro_articles'     => 4,
                            'num_columns'            => 2,
                            'num_links'              => 4,
                            'multi_column_order'     => 1,
                            'show_pagination'        => 2,
                            'show_intro'             => 0,
                            'show_category'          => 1,
                            'show_parent_category'   => 0,
                            'link_parent_category'   => 0,
                            'show_author'            => 0,
                            'link_author'            => 0,
                            'show_create_date'       => 0,
                            'show_modify_date'       => 0,
                            'show_publish_date'      => 0,
                            'show_item_navigation'   => 1,
                            'show_readmore'          => 1,
                            'show_icons'             => 0,
                            'show_print_icon'        => 0,
                            'show_email_icon'        => 0,
                            'show_hits'              => 0,
                            'show_feed_link'         => 1,
                            'show_page_heading'      => 0,
                            'secure'                 => 0,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $parkMenuIds = $this->addMenuItems($parkMenuItems, 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 7, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert Fruitshop Menu Items
        $fruitshopMenuItems = [
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_56_TITLE'),
                'link'              => 'index.php?option=com_content&view=article&id=' . $articleIds[19],
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'template_style_id' => 7,
                'params'            => [
                    'show_title'           => 0,
                    'link_titles'          => 0,
                    'show_intro'           => 1,
                    'show_category'        => 0,
                    'link_category'        => 0,
                    'show_author'          => 0,
                    'show_create_date'     => 0,
                    'show_modify_date'     => 0,
                    'show_publish_date'    => 0,
                    'show_item_navigation' => 0,
                    'show_icons'           => 0,
                    'show_print_icon'      => 0,
                    'show_email_icon'      => 0,
                    'show_hits'            => 0,
                    'menu_text'            => 1,
                    'show_page_heading'    => 0,
                    'secure'               => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_55_TITLE'),
                'link'              => 'index.php?option=com_contact&view=categories&id=' . $contactCatids3[1],
                'component_id'      => ComponentHelper::getComponent('com_contact')->id,
                'template_style_id' => 7,
                'params'            => [
                    'show_base_description'   => 1,
                    'show_description'        => 1,
                    'show_description_image'  => 1,
                    'maxLevel'                => -1,
                    'show_empty_categories'   => 1,
                    'show_headings'           => 0,
                    'show_email_headings'     => 0,
                    'show_telephone_headings' => 0,
                    'show_mobile_headings'    => 0,
                    'show_fax_headings'       => 0,
                    'show_suburb_headings'    => 0,
                    'show_links'              => 1,
                    'menu_text'               => 1,
                    'show_page_heading'       => 0,
                    'pageclass_sfx'           => ' categories-listalphabet',
                    'secure'                  => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_57_TITLE'),
                'link'              => 'index.php?option=com_contact&view=category&id=' . $contactCatids3[0],
                'component_id'      => ComponentHelper::getComponent('com_contact')->id,
                'template_style_id' => 7,
                'params'            => [
                    'maxLevel'          => -1,
                    'show_headings'     => 0,
                    'show_links'        => 1,
                    'show_feed_link'    => 1,
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_58_TITLE'),
                'link'              => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids3[3],
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'template_style_id' => 7,
                'params'            => [
                    'layout_type'          => 'blog',
                    'show_category_title'  => 1,
                    'show_description'     => 1,
                    'maxLevel'             => 0,
                    'num_leading_articles' => 5,
                    'num_intro_articles'   => 0,
                    'num_columns'          => 1,
                    'num_links'            => 4,
                    'orderby_sec'          => 'alpha',
                    'show_title'           => 1,
                    'link_titles'          => 1,
                    'show_intro'           => 1,
                    'show_category'        => 0,
                    'show_parent_category' => 0,
                    'link_parent_category' => 0,
                    'show_author'          => 0,
                    'show_publish_date'    => 0,
                    'show_hits'            => 0,
                    'menu_text'            => 1,
                    'show_page_heading'    => 0,
                    'secure'               => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_59_TITLE'),
                'link'              => 'index.php?option=com_users&view=login',
                'component_id'      => ComponentHelper::getComponent('com_users')->id,
                'template_style_id' => 7,
                'params'            => [
                    'logindescription_show'  => 1,
                    'logoutdescription_show' => 1,
                    'menu_text'              => 1,
                    'show_page_heading'      => 0,
                    'secure'                 => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_60_TITLE'),
                'link'              => 'index.php?option=com_content&view=article&id=' . $articleIds[12],
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'template_style_id' => 7,
                'params'            => [
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_69_TITLE'),
                'link'              => 'index.php?option=com_content&view=form&layout=edit',
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'access'            => 4,
                'template_style_id' => 7,
                'params'            => [
                    'enable_category'   => 0,
                    'catid'             => 14,
                    'menu_text'         => 1,
                    'show_page_heading' => 1,
                    'secure'            => 0,
                ],
            ],
            [
                'menutype'          => $menuTypes[5],
                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_70_TITLE'),
                'link'              => 'index.php?option=com_content&view=category&id=' . $articleCatids3[4],
                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                'template_style_id' => 7,
                'params'            => [
                    'show_category_title'   => 1,
                    'show_description'      => 1,
                    'maxLevel'              => 0,
                    'show_empty_categories' => 0,
                    'display_num'           => 10,
                    'menu_text'             => 1,
                    'show_page_heading'     => 0,
                    'secure'                => 0,
                ],
            ],
        ];

        try {
            $fruitshopMenuIds = $this->addMenuItems($fruitshopMenuItems, 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 7, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert Frontend Views & Modules Menu Items
        $frontendMenuIds = $this->addFrontendViewsMenu($menuTypes);
        $this->getApplication()->setUserState('sampledata.testing.menu_module_mapping', $this->menuModuleMapping);

        // Insert About Joomla Menu Items
        $aboutMenuItems = [
            [
                'menutype'     => $menuTypes[2],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[52],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_title'           => 1,
                    'link_titles'          => 0,
                    'show_intro'           => 1,
                    'show_category'        => 0,
                    'show_parent_category' => 0,
                    'show_author'          => 0,
                    'show_create_date'     => 0,
                    'show_modify_date'     => 0,
                    'show_publish_date'    => 0,
                    'show_item_navigation' => 0,
                    'show_hits'            => 0,
                    'show_noauth'          => 0,
                    'show_page_heading'    => 0,
                    'secure'               => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[2],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_TITLE'),
                        'link'         => 'index.php?option=com_content&view=categories&id=' . $articleCatids3[0],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_base_description'     => 1,
                            'maxLevelcat'               => 1,
                            'show_empty_categories_cat' => 1,
                            'show_subcat_desc_cat'      => 1,
                            'show_cat_num_articles_cat' => 0,
                            'show_description'          => 1,
                            'show_description_image'    => 1,
                            'maxLevel'                  => 1,
                            'show_empty_categories'     => 1,
                            'num_leading_articles'      => 1,
                            'num_intro_articles'        => 4,
                            'num_columns'               => 2,
                            'num_links'                 => 4,
                            'secure'                    => 0,
                        ],
                        'children' => [
                            [
                                'menutype'     => $menuTypes[2],
                                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_TITLE'),
                                'link'         => 'index.php?option=com_content&view=category&id=' . $articleCatids4[2],
                                'component_id' => ComponentHelper::getComponent('com_content')->id,
                                'params'       => [
                                    'show_description'      => 1,
                                    'maxLevel'              => 2,
                                    'show_empty_categories' => 1,
                                    'show_no_articles'      => '0',
                                    'show_subcat_desc'      => 1,
                                    'show_pagination_limit' => '0',
                                    'filter_field'          => 'hide',
                                    'show_headings'         => '0',
                                    'list_show_date'        => '0',
                                    'list_show_hits'        => '0',
                                    'list_show_author'      => '0',
                                    'show_pagination'       => '0',
                                    'show_title'            => 1,
                                    'link_titles'           => 1,
                                    'menu_text'             => 1,
                                    'page_title'            => 'Templates',
                                    'show_page_heading'     => 0,
                                    'secure'                => 0,
                                ],
                                'children' => [
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_7_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids5[6],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_description'     => 1,
                                            'num_leading_articles' => 1,
                                            'num_intro_articles'   => 4,
                                            'num_columns'          => 2,
                                            'num_links'            => 4,
                                            'show_page_heading'    => 0,
                                            'secure'               => 0,
                                        ],
                                        'children' => [
                                            [
                                                'menutype'          => $menuTypes[2],
                                                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_7_2_TITLE'),
                                                'link'              => 'index.php?option=com_content&view=article&id=' . $articleIds[48],
                                                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                                                'template_style_id' => 4,
                                                'params'            => [
                                                    'show_page_heading' => 0,
                                                    'secure'            => 0,
                                                ],
                                            ],
                                            [
                                                'menutype'          => $menuTypes[2],
                                                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_7_3_TITLE'),
                                                'link'              => 'index.php?option=com_content&view=featured',
                                                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                                                'template_style_id' => 4,
                                                'params'            => [
                                                    'num_leading_articles' => 1,
                                                    'num_intro_articles'   => 3,
                                                    'num_columns'          => 3,
                                                    'num_links'            => 0,
                                                    'multi_column_order'   => 1,
                                                    'orderby_sec'          => 'front',
                                                    'show_pagination'      => 2,
                                                    'show_feed_link'       => 1,
                                                    'show_page_heading'    => 0,
                                                    'secure'               => 0,
                                                ],

                                            ],
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_8_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids5[5],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_description'     => 1,
                                            'num_leading_articles' => 2,
                                            'num_intro_articles'   => 4,
                                            'num_columns'          => 2,
                                            'num_links'            => 4,
                                            'show_page_heading'    => 0,
                                            'secure'               => 0,
                                        ],
                                        'children' => [
                                            [
                                                'menutype'          => $menuTypes[2],
                                                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_8_0_TITLE'),
                                                'link'              => 'index.php?option=com_content&view=article&id=' . $articleIds[48],
                                                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                                                'template_style_id' => 3,
                                                'params'            => [
                                                    'show_page_heading' => 0,
                                                    'secure'            => 0,
                                                ],
                                            ],
                                            [
                                                'menutype'          => $menuTypes[2],
                                                'title'             => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_8_1_TITLE'),
                                                'link'              => 'index.php?option=com_content&view=featured',
                                                'component_id'      => ComponentHelper::getComponent('com_content')->id,
                                                'template_style_id' => 3,
                                                'params'            => [
                                                    'num_leading_articles' => 1,
                                                    'num_intro_articles'   => 3,
                                                    'num_columns'          => 3,
                                                    'num_links'            => 0,
                                                    'multi_column_order'   => 1,
                                                    'orderby_sec'          => 'front',
                                                    'show_pagination'      => 2,
                                                    'show_feed_link'       => 1,
                                                    'show_page_heading'    => 0,
                                                    'secure'               => 0,
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_9_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids5[7],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_description'     => 1,
                                            'num_leading_articles' => 1,
                                            'num_intro_articles'   => 4,
                                            'num_columns'          => 2,
                                            'num_links'            => 4,
                                            'show_page_heading'    => 1,
                                            'secure'               => 0,
                                        ],
                                        'children' => [
                                            [
                                                'menutype'     => $menuTypes[2],
                                                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_9_4_TITLE'),
                                                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[48],
                                                'component_id' => ComponentHelper::getComponent('com_content')->id,
                                                'params'       => [
                                                    'show_page_heading' => 1,
                                                    'secure'            => 0,
                                                ],
                                            ],
                                            [
                                                'menutype'     => $menuTypes[2],
                                                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_0_9_5_TITLE'),
                                                'link'         => 'index.php?option=com_content&view=featured',
                                                'component_id' => ComponentHelper::getComponent('com_content')->id,
                                                'params'       => [
                                                    'num_leading_articles' => 1,
                                                    'num_intro_articles'   => 3,
                                                    'num_columns'          => 3,
                                                    'num_links'            => 0,
                                                    'orderby_sec'          => 'front',
                                                    'show_page_heading'    => 1,
                                                    'secure'               => 0,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'menutype'     => $menuTypes[2],
                                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_1_TITLE'),
                                'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids4[3],
                                'component_id' => ComponentHelper::getComponent('com_content')->id,
                                'params'       => [
                                    'show_description'       => 1,
                                    'show_description_image' => 1,
                                    'show_category_title'    => 1,
                                    'num_leading_articles'   => 1,
                                    'num_intro_articles'     => 4,
                                    'num_columns'            => 2,
                                    'num_links'              => 4,
                                    'show_page_heading'      => 0,
                                    'secure'                 => 0,
                                ],
                            ],
                            [
                                'menutype'     => $menuTypes[2],
                                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_TITLE'),
                                'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids4[4],
                                'component_id' => ComponentHelper::getComponent('com_content')->id,
                                'params'       => [
                                    'show_description'     => 1,
                                    'show_category_title'  => 1,
                                    'num_leading_articles' => 0,
                                    'num_intro_articles'   => 7,
                                    'num_columns'          => 1,
                                    'num_links'            => 0,
                                    'orderby_sec'          => 'order',
                                    'show_category'        => 0,
                                    'link_category'        => 0,
                                    'show_parent_category' => 0,
                                    'link_parent_category' => 0,
                                    'show_author'          => 0,
                                    'show_create_date'     => 0,
                                    'show_modify_date'     => 0,
                                    'show_publish_date'    => 0,
                                    'show_icons'           => 0,
                                    'show_print_icon'      => 0,
                                    'show_email_icon'      => 0,
                                    'show_hits'            => 0,
                                    'show_page_heading'    => 0,
                                    'secure'               => 0,
                                ],
                                'children' => [
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_0_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[45],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_page_heading' => 0,
                                            'secure'            => 0,
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_1_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[4],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_page_heading' => 0,
                                            'secure'            => 0,
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_2_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[59],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_page_heading' => 0,
                                            'secure'            => 0,
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_3_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[13],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_page_heading' => 0,
                                            'secure'            => 0,
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_4_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[14],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_page_heading' => 0,
                                            'secure'            => 0,
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_5_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[40],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_page_heading' => 0,
                                            'secure'            => 0,
                                        ],
                                    ],
                                    [
                                        'menutype'     => $menuTypes[2],
                                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_27_0_2_6_TITLE'),
                                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[50],
                                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                                        'params'       => [
                                            'show_page_heading' => 0,
                                            'secure'            => 0,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'menutype'     => $menuTypes[2],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_62_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[21],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_title'           => 1,
                    'link_titles'          => 0,
                    'show_category'        => 0,
                    'show_parent_category' => 0,
                    'show_author'          => 0,
                    'show_create_date'     => 0,
                    'show_modify_date'     => 0,
                    'show_publish_date'    => 0,
                    'show_item_navigation' => 0,
                    'show_hits'            => 0,
                    'show_page_heading'    => 0,
                    'secure'               => 0,
                ],
            ],
        ];

        try {
            $aboutMenuIds = $this->addMenuItems($aboutMenuItems, 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 7, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert Main Menu Testing Menu Items
        $mainMenuItems = [
            [
                'menutype'     => $menuTypes[4],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_3_TITLE'),
                'link'         => 'index.php?option=com_users&view=login',
                'component_id' => ComponentHelper::getComponent('com_users')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
            ],
            [
                'menutype'     => $menuTypes[4],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_5_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[37],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_category'        => 0,
                    'show_parent_category' => 0,
                    'show_author'          => 0,
                    'show_create_date'     => 0,
                    'show_modify_date'     => 0,
                    'show_publish_date'    => 0,
                    'show_item_navigation' => 0,
                    'show_hits'            => 0,
                    'show_page_heading'    => 0,
                    'secure'               => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[4],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_102_TITLE'),
                        'link'         => 'index.php?Itemid=',
                        'type'         => 'alias',
                        'component_id' => 0,
                        'params'       => [
                            'aliasoptions' => $parkMenuIds[0],
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[4],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_104_TITLE'),
                        'link'         => 'index.php?Itemid=',
                        'type'         => 'alias',
                        'component_id' => 0,
                        'params'       => [
                            'aliasoptions' => $fruitshopMenuIds[0],
                        ],
                    ],
                ],
            ],
            [
                'menutype'     => $menuTypes[4],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_61_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[23],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_title'           => 1,
                    'show_category'        => 0,
                    'link_category'        => 0,
                    'show_parent_category' => 0,
                    'link_parent_category' => 0,
                    'show_author'          => 0,
                    'link_author'          => 0,
                    'show_create_date'     => 0,
                    'show_modify_date'     => 0,
                    'show_publish_date'    => 0,
                    'show_item_navigation' => 0,
                    'show_icons'           => 0,
                    'show_print_icon'      => 0,
                    'show_email_icon'      => 0,
                    'show_hits'            => 0,
                    'menu_text'            => 1,
                    'show_page_heading'    => 0,
                    'secure'               => 0,
                ],
                'home' => 1,
            ],
            [
                'menutype'     => $menuTypes[4],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_65_TITLE'),
                'link'         => 'administrator',
                'type'         => 'url',
                'component_id' => 0,
                'params'       => [],
            ],
        ];

        try {
            $mainMenuIds = $this->addMenuItems($mainMenuItems, 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 7, $e->getMessage());

            $event->addResult($response);
            return;
        }

        // Insert Top Menu Items
        $topMenuItems = [
            [
                'menutype'     => $menuTypes[1],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_1_TITLE'),
                'link'         => 'https://joomla.org',
                'type'         => 'url',
                'component_id' => 0,
            ],
            [
                'menutype'     => $menuTypes[1],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_100_TITLE'),
                'link'         => 'index.php?Itemid=',
                'type'         => 'alias',
                'component_id' => 0,
                'params'       => [
                    'aliasoptions' => $mainMenuIds[1],
                ],
            ],
            [
                'menutype'     => $menuTypes[1],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_101_TITLE'),
                'link'         => 'index.php?Itemid=',
                'type'         => 'alias',
                'component_id' => 0,
                'params'       => [
                    'aliasoptions' => end($aboutMenuIds),
                ],
            ],
            [
                'menutype'     => $menuTypes[1],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_102_TITLE'),
                'link'         => 'index.php?Itemid=',
                'type'         => 'alias',
                'component_id' => 0,
                'params'       => [
                    'aliasoptions' => $parkMenuIds[0],
                    'menu_text'    => 1,
                ],
            ],
            [
                'menutype'     => $menuTypes[1],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_103_TITLE'),
                'link'         => 'index.php?Itemid=',
                'type'         => 'alias',
                'component_id' => 0,
                'params'       => [
                    'aliasoptions' => $fruitshopMenuIds[0],
                    'menu_text'    => 1,
                ],
            ],
        ];

        try {
            $topMenuIds = $this->addMenuItems($topMenuItems, 1);
        } catch (\Exception $e) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 7, $e->getMessage());

            $event->addResult($response);
            return;
        }

        $this->getApplication()->setUserState('sampledata.testing.menus.user', $userMenuIds);
        $this->getApplication()->setUserState('sampledata.testing.menus.park', $parkMenuIds);
        $this->getApplication()->setUserState('sampledata.testing.menus.fruitshop', $fruitshopMenuIds);
        $this->getApplication()->setUserState('sampledata.testing.menus.frontend', $frontendMenuIds);
        $this->getApplication()->setUserState('sampledata.testing.menus.about', $aboutMenuIds);
        $this->getApplication()->setUserState('sampledata.testing.menus.main', $mainMenuIds);
        $this->getApplication()->setUserState('sampledata.testing.menus.top', $topMenuIds);

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP7_SUCCESS');

        $event->addResult($response);
    }

    protected function addFrontendViewsMenu($menuTypes)
    {
        // Get previously entered Data from UserStates
        $contactIds      = $this->getApplication()->getUserState('sampledata.testing.contacts');
        $contactCatids1  = $this->getApplication()->getUserState('sampledata.testing.contacts.catids1');
        $contactCatids3  = $this->getApplication()->getUserState('sampledata.testing.contacts.catids3');
        $articleIds      = $this->getApplication()->getUserState('sampledata.testing.articles');
        $articleCatids1  = $this->getApplication()->getUserState('sampledata.testing.articles.catids1');
        $articleCatids2  = $this->getApplication()->getUserState('sampledata.testing.articles.catids2');
        $articleCatids3  = $this->getApplication()->getUserState('sampledata.testing.articles.catids3');
        $articleCatids4  = $this->getApplication()->getUserState('sampledata.testing.articles.catids4');
        $articleCatids5  = $this->getApplication()->getUserState('sampledata.testing.articles.catids5');
        $tagIds          = $this->getApplication()->getUserState('sampledata.testing.tags');
        $newsfeedsIds    = $this->getApplication()->getUserState('sampledata.testing.newsfeeds');
        $newsfeedsCatids = $this->getApplication()->getUserState('sampledata.testing.newsfeeds.catids');

        $menuItems = [
            // com_config
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_105_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[69],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_78_TITLE'),
                        'link'         => 'index.php?option=com_config&view=config',
                        'component_id' => ComponentHelper::getComponent('com_config')->id,
                        'access'       => 6,
                        'params'       => [
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_79_TITLE'),
                        'link'         => 'index.php?option=com_config&view=templates',
                        'component_id' => ComponentHelper::getComponent('com_config')->id,
                        'access'       => 6,
                        'params'       => [
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                ],
            ],

            // com_contact
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_22_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[8],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_2_TITLE'),
                        'link'         => 'index.php?option=com_contact&view=contact&id=' . $contactIds[0],
                        'component_id' => ComponentHelper::getComponent('com_contact')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_9_TITLE'),
                        'link'         => 'index.php?option=com_contact&view=categories&id=' . $contactCatids1[0],
                        'component_id' => ComponentHelper::getComponent('com_contact')->id,
                        'params'       => [
                            'maxLevel'           => -1,
                            'presentation_style' => 'sliders',
                            'show_links'         => 1,
                            'show_page_heading'  => 0,
                            'secure'             => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_25_TITLE'),
                        'link'         => 'index.php?option=com_contact&view=category&id=' . $contactCatids3[0],
                        'component_id' => ComponentHelper::getComponent('com_contact')->id,
                        'params'       => [
                            'maxLevel'           => -1,
                            'display_num'        => 20,
                            'presentation_style' => 'sliders',
                            'show_links'         => 1,
                            'show_feed_link'     => 1,
                            'show_page_heading'  => 0,
                            'secure'             => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_67_TITLE'),
                        'link'         => 'index.php?option=com_contact&view=featured',
                        'component_id' => ComponentHelper::getComponent('com_contact')->id,
                        'params'       => [
                            'maxLevel'           => -1,
                            'presentation_style' => 'sliders',
                            'show_links'         => 1,
                            'show_page_heading'  => 1,
                            'secure'             => 0,
                        ],
                    ],
                ],
            ],

            // com_content
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_20_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[9],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_14_TITLE'),
                        'link'         => 'index.php?option=com_content&view=archive',
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_category'     => 1,
                            'link_category'     => 1,
                            'show_title'        => 1,
                            'link_titles'       => 1,
                            'show_intro'        => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_15_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[5],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_16_TITLE'),
                        'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $articleCatids3[1],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_description'       => 0,
                            'show_description_image' => 0,
                            'num_leading_articles'   => 1,
                            'num_intro_articles'     => 4,
                            'num_columns'            => 2,
                            'num_links'              => 4,
                            'show_pagination'        => 2,
                            'show_feed_link'         => 1,
                            'show_page_heading'      => 0,
                            'secure'                 => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_17_TITLE'),
                        'link'         => 'index.php?option=com_content&view=category&id=' . $articleCatids2[0],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'orderby_sec'       => 'alpha',
                            'display_num'       => 10,
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_18_TITLE'),
                        'link'         => 'index.php?option=com_content&view=featured',
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'num_leading_articles' => 1,
                            'num_intro_articles'   => 4,
                            'num_columns'          => 2,
                            'num_links'            => 4,
                            'multi_column_order'   => 1,
                            'orderby_sec'          => 'front',
                            'show_pagination'      => 2,
                            'show_feed_link'       => 1,
                            'show_page_heading'    => 0,
                            'secure'               => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_19_TITLE'),
                        'link'         => 'index.php?option=com_content&view=form&layout=edit',
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'access'       => 3,
                        'params'       => [
                            'enable_category'   => 0,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_24_TITLE'),
                        'link'         => 'index.php?option=com_content&view=categories&id=' . $articleCatids1[0],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'maxLevel'             => -1,
                            'num_leading_articles' => 1,
                            'num_intro_articles'   => 4,
                            'num_columns'          => 2,
                            'num_links'            => 4,
                            'show_page_heading'    => 0,
                            'secure'               => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_30_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[29],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_articles_popular',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_36_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[30],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_articles_news',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_37_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[26],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_articles_latest',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_42_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[1],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_articles_archive',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_43_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[36],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_related_items',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_63_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[2],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_articles_categories',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_68_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[3],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 1,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_articles_category',
                    ],
                ],
            ],

            // com_finder
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_26_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[38],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_71_TITLE'),
                        'link'         => 'index.php?option=com_finder&view=search&q=&f=',
                        'component_id' => ComponentHelper::getComponent('com_finder')->id,
                        'params'       => [
                            'description_length' => 255,
                            'allow_empty_query'  => 0,
                            'show_feed'          => 0,
                            'show_feed_text'     => 0,
                            'menu_text'          => 1,
                            'show_page_heading'  => 0,
                            'secure'             => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_72_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[66],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_finder',
                    ],
                ],
            ],

            // com_newsfeeds
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_21_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[57],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 1,
                    'page_title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_21_PARAM_PAGE_TITLE'),
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_10_TITLE'),
                        'link'         => 'index.php?option=com_newsfeeds&view=categories&id=0',
                        'component_id' => ComponentHelper::getComponent('com_newsfeeds')->id,
                        'params'       => [
                            'show_base_description'  => 1,
                            'categories_description' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_10_PARAM_CATEGORIES_DESCRIPTION'),
                            'maxLevel'               => -1,
                            'show_empty_categories'  => 1,
                            'show_description'       => 1,
                            'show_description_image' => 1,
                            'show_cat_num_articles'  => 1,
                            'feed_character_count'   => 0,
                            'show_page_heading'      => 0,
                            'secure'                 => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_11_TITLE'),
                        'link'         => 'index.php?option=com_newsfeeds&view=category&id=' . $newsfeedsCatids[0],
                        'component_id' => ComponentHelper::getComponent('com_newsfeeds')->id,
                        'params'       => [
                            'maxLevel'             => -1,
                            'feed_character_count' => 0,
                            'show_page_heading'    => 0,
                            'secure'               => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_12_TITLE'),
                        'link'         => 'index.php?option=com_newsfeeds&view=newsfeed&id=' . $newsfeedsIds[0],
                        'component_id' => ComponentHelper::getComponent('com_newsfeeds')->id,
                        'params'       => [
                            'feed_character_count' => 0,
                            'show_page_heading'    => 0,
                            'secure'               => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_38_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[44],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_syndicate',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_50_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[15],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_feed',
                    ],
                ],
            ],
            // com_privacy
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_109_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[71],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 1,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_110_TITLE'),
                        'link'         => 'index.php?option=com_privacy&view=confirm',
                        'component_id' => ComponentHelper::getComponent('com_privacy')->id,
                        'params'       => [
                            'tag_list_item_maximum_characters' => 0,
                            'maximum'                          => 200,
                            'menu_text'                        => 1,
                            'show_page_heading'                => 0,
                            'secure'                           => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_111_TITLE'),
                        'link'         => 'index.php?option=com_privacy&view=remind',
                        'component_id' => ComponentHelper::getComponent('com_privacy')->id,
                        'params'       => [
                            'tag_list_item_maximum_characters' => 0,
                            'maximum'                          => 200,
                            'menu_text'                        => 1,
                            'show_page_heading'                => 0,
                            'secure'                           => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_112_TITLE'),
                        'link'         => 'index.php?option=com_privacy&view=request',
                        'component_id' => ComponentHelper::getComponent('com_privacy')->id,
                        'params'       => [
                            'tag_columns'                     => 4,
                            'all_tags_tag_maximum_characters' => 0,
                            'maximum'                         => 200,
                            'menu_text'                       => 1,
                            'show_page_heading'               => 0,
                            'secure'                          => 0,
                        ],
                    ],
                ],
            ],

            //com_tags
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_106_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[70],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 1,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_75_TITLE'),
                        'link'         => 'index.php?option=com_tags&view=tag&layout=list&id[0]=' . $tagIds[2],
                        'component_id' => ComponentHelper::getComponent('com_tags')->id,
                        'params'       => [
                            'tag_list_item_maximum_characters' => 0,
                            'maximum'                          => 200,
                            'menu_text'                        => 1,
                            'show_page_heading'                => 0,
                            'secure'                           => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_76_TITLE'),
                        'link'         => 'index.php?option=com_tags&view=tag&id[0]=' . $tagIds[1],
                        'component_id' => ComponentHelper::getComponent('com_tags')->id,
                        'params'       => [
                            'tag_list_item_maximum_characters' => 0,
                            'maximum'                          => 200,
                            'menu_text'                        => 1,
                            'show_page_heading'                => 0,
                            'secure'                           => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_77_TITLE'),
                        'link'         => 'index.php?option=com_tags&view=tags',
                        'component_id' => ComponentHelper::getComponent('com_tags')->id,
                        'params'       => [
                            'tag_columns'                     => 4,
                            'all_tags_tag_maximum_characters' => 0,
                            'maximum'                         => 200,
                            'menu_text'                       => 1,
                            'show_page_heading'               => 0,
                            'secure'                          => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_73_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[67],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_tags_similar',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_74_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[68],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_tags_popular',
                    ],
                ],
            ],

            // com_users
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_23_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[51],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_44_TITLE'),
                        'link'         => 'index.php?option=com_users&view=login',
                        'component_id' => ComponentHelper::getComponent('com_users')->id,
                        'params'       => [
                            'logindescription_show'  => 1,
                            'logoutdescription_show' => 1,
                            'show_page_heading'      => 0,
                            'secure'                 => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_45_TITLE'),
                        'link'         => 'index.php?option=com_users&view=profile',
                        'component_id' => ComponentHelper::getComponent('com_users')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_46_TITLE'),
                        'link'         => 'index.php?option=com_users&view=profile&layout=edit',
                        'component_id' => ComponentHelper::getComponent('com_users')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_47_TITLE'),
                        'link'         => 'index.php?option=com_users&view=registration',
                        'component_id' => ComponentHelper::getComponent('com_users')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_48_TITLE'),
                        'link'         => 'index.php?option=com_users&view=remind',
                        'component_id' => ComponentHelper::getComponent('com_users')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_49_TITLE'),
                        'link'         => 'index.php?option=com_users&view=reset',
                        'component_id' => ComponentHelper::getComponent('com_users')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_28_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[62],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_users_latest',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_29_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[55],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_whosonline',
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_39_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[27],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_login',
                    ],
                ],
            ],

            // com_wrapper
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_107_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[71],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'children' => [
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_108_TITLE'),
                        'link'         => 'index.php?option=com_wrapper&view=wrapper',
                        'component_id' => ComponentHelper::getComponent('com_wrapper')->id,
                        'params'       => [
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                    ],
                    [
                        'menutype'     => $menuTypes[6],
                        'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_40_TITLE'),
                        'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[56],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                        'params'       => [
                            'menu_text'         => 1,
                            'show_page_heading' => 0,
                            'secure'            => 0,
                        ],
                        'sampledata_module' => 'mod_wrapper',
                    ],
                ],
            ],

            // Misc
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_31_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[28],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_menu',
            ],
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_32_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[43],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_stats',
            ],
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_33_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[6],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_banners',
            ],
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_35_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[35],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_random_image',
            ],
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_41_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[18],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_footer',
            ],
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_53_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[58],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'menu_text'         => 1,
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_breadcrumbs',
            ],
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_54_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[11],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_custom',
            ],
            [
                'menutype'     => $menuTypes[6],
                'title'        => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MENUS_ITEM_64_TITLE'),
                'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[25],
                'component_id' => ComponentHelper::getComponent('com_content')->id,
                'params'       => [
                    'show_page_heading' => 0,
                    'secure'            => 0,
                ],
                'sampledata_module' => 'mod_languages',
            ],
        ];

        return $this->addMenuItems($menuItems, 1);
    }

    /**
     * Eighth step to enter the sampledata. Modules.
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  3.8.0
     */
    public function onAjaxSampledataApplyStep8(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_modules')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_SKIPPED', 8, 'com_modules');

            $event->addResult($response);
            return;
        }

        $model  = $this->getApplication()->bootComponent('com_modules')->getMVCFactory()->createModel('Module', 'Administrator', ['ignore_request' => true]);
        $access = (int) $this->getApplication()->get('access', 1);

        // Get previously entered Data from UserStates
        $menuTypes        = $this->getApplication()->getUserState('sampledata.testing.menutypes');
        $articleCatids1   = $this->getApplication()->getUserState('sampledata.testing.articles.catids1');
        $articleCatids2   = $this->getApplication()->getUserState('sampledata.testing.articles.catids2');
        $articleCatids3   = $this->getApplication()->getUserState('sampledata.testing.articles.catids3');
        $articleCatids4   = $this->getApplication()->getUserState('sampledata.testing.articles.catids4');
        $articleCatids5   = $this->getApplication()->getUserState('sampledata.testing.articles.catids5');
        $bannerCatids     = $this->getApplication()->getUserState('sampledata.testing.banners.catids');
        $menuMapping      = $this->getApplication()->getUserState('sampledata.testing.menu_module_mapping');
        $userMenuIds      = $this->getApplication()->getUserState('sampledata.testing.menus.user');
        $parkMenuIds      = $this->getApplication()->getUserState('sampledata.testing.menus.park');
        $fruitshopMenuIds = $this->getApplication()->getUserState('sampledata.testing.menus.fruitshop');
        $frontendMenuIds  = $this->getApplication()->getUserState('sampledata.testing.menus.frontend');
        $aboutMenuIds     = $this->getApplication()->getUserState('sampledata.testing.menus.about');
        $mainMenuIds      = $this->getApplication()->getUserState('sampledata.testing.menus.main');
        $topMenuIds       = $this->getApplication()->getUserState('sampledata.testing.menus.top');

        $modules = [
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_0_TITLE'),
                'ordering' => 1,
                'position' => 'sidebar-right',
                'module'   => 'mod_menu',
                'access'   => $access,
                'params'   => [
                    'menutype'        => $menuTypes[4],
                    'startLevel'      => 0,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'moduleclass_sfx' => '_menu',
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_1_TITLE'),
                'ordering'  => 1,
                'position'  => 'bottom-a',
                'module'    => 'mod_banners',
                'access'    => $access,
                'showtitle' => 0,
                'params'    => [
                    'target'      => 1,
                    'count'       => 1,
                    'cid'         => 3,
                    'catid'       => [],
                    'tag_search'  => 0,
                    'ordering'    => 0,
                    'footer_text' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_1_FOOTEER_TEXT'),
                    'cache'       => 1,
                    'cache_time'  => 900,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_2_TITLE'),
                'ordering'   => 3,
                'position'   => 'sidebar-right',
                'module'     => 'mod_menu',
                'access'     => 2,
                'assignment' => -1,
                'assigned'   => array_merge($parkMenuIds, $fruitshopMenuIds),
                'params'     => [
                    'menutype'        => $menuTypes[0],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'moduleclass_sfx' => '_menu',
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_3_TITLE'),
                'ordering' => 1,
                'position' => 'menu',
                'module'   => 'mod_menu',
                'access'   => $access,
                'params'   => [
                    'menutype'        => $menuTypes[1],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'class_sfx'       => ' nav-pills',
                    'cache'           => 0,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_4_TITLE'),
                'ordering'   => 2,
                'position'   => 'sidebar-left',
                'module'     => 'mod_menu',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => array_merge($parkMenuIds, [$mainMenuIds[1]]),
                'params'     => [
                    'menutype'        => $menuTypes[3],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'cache'           => 0,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_5_TITLE'),
                'ordering'   => 4,
                'position'   => 'sidebar-right',
                'module'     => 'mod_menu',
                'access'     => $access,
                'assignment' => -1,
                'assigned'   => array_merge($parkMenuIds, $fruitshopMenuIds, [$mainMenuIds[4]]),
                'params'     => [
                    'menutype'        => $menuTypes[2],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'moduleclass_sfx' => '_menu',
                    'cache'           => 0,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_6_TITLE'),
                'ordering'   => 1,
                'position'   => 'sitemapload',
                'module'     => 'mod_menu',
                'access'     => $access,
                'showtitle'  => 0,
                'assignment' => '-',
                'params'     => [
                    'menutype'        => $menuTypes[4],
                    'startLevel'      => 2,
                    'endLevel'        => 3,
                    'showAllChildren' => 1,
                    'class_sfx'       => 'sitemap',
                    'cache'           => 0,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_7_TITLE'),
                'ordering'   => 5,
                'position'   => 'sidebar-right',
                'module'     => 'mod_menu',
                'access'     => $access,
                'assignment' => -1,
                'assigned'   => array_merge($parkMenuIds, $fruitshopMenuIds, [$mainMenuIds[1]]),
                'params'     => [
                    'menutype'        => $menuTypes[4],
                    'startLevel'      => 1,
                    'endLevel'        => 1,
                    'showAllChildren' => 0,
                    'moduleclass_sfx' => '_menu',
                    'cache'           => 0,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_8_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_articles_archive',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_articles_archive'],
                ],
                'params' => [
                    'count'      => '10',
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_9_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_articles_latest',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_articles_latest'],
                ],
                'params' => [
                    'catid'      => [$articleCatids2[0]],
                    'count'      => 5,
                    'ordering'   => 'c_dsc',
                    'user_id'    => 0,
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_10_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_articles_popular',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_articles_popular'],
                ],
                'params' => [
                    'catid'      => [$articleCatids2[1], $articleCatids2[2]],
                    'count'      => 5,
                    'show_front' => 1,
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_11_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_feed',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_feed'],
                ],
                'params' => [
                    'rssurl'      => 'https://community.joomla.org/blogs/community.feed?type=rss',
                    'rssrtl'      => 0,
                    'rsstitle'    => 1,
                    'rssdate'     => 0,
                    'rssdesc'     => 1,
                    'rssimage'    => 1,
                    'rssitems'    => 3,
                    'rssitemdesc' => 1,
                    'rssitemdate' => 0,
                    'word_count'  => 0,
                    'cache'       => 1,
                    'cache_time'  => 900,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_12_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_articles_news',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_articles_news'],
                ],
                'params' => [
                    'catid'             => [$articleCatids2[0]],
                    'image'             => 0,
                    'item_title'        => 0,
                    'item_heading'      => 'h4',
                    'showLastSeparator' => 1,
                    'readmore'          => 1,
                    'count'             => 1,
                    'ordering'          => 'a.publish_up',
                    'cache'             => 1,
                    'cache_time'        => 900,
                    'cachemode'         => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_13_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_random_image',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_random_image'],
                ],
                'params' => [
                    'type'   => 'jpg',
                    'folder' => 'images/sampledata/parks/animals',
                    'width'  => 180,
                    'cache'  => 0,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_14_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_related_items',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_related_items'],
                ],
                'params' => [
                    'showDate' => 0,
                    'owncache' => 1,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_16_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_stats',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_stats'],
                ],
                'params' => [
                    'serverinfo' => 1,
                    'siteinfo'   => 1,
                    'counter'    => 1,
                    'increase'   => 0,
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_17_TITLE'),
                'ordering'   => 1,
                'position'   => 'syndicateload',
                'module'     => 'mod_syndicate',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_syndicate'],
                ],
                'params' => [
                    'text'   => 'Feed Entries',
                    'format' => 'rss',
                    'cache'  => 0,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_18_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_users_latest',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_users_latest'],
                ],
                'params' => [
                    'shownumber' => 5,
                    'linknames'  => 0,
                    'cache'      => 0,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_19_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_whosonline',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_whosonline'],
                ],
                'params' => [
                    'showmode'  => 2,
                    'linknames' => 0,
                    'cache'     => 0,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_20_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_wrapper',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_wrapper'],
                ],
                'params' => [
                    'url'         => 'https://www.youtube.com/embed/vb2eObvmvdI',
                    'add'         => 1,
                    'scrolling'   => 'auto',
                    'width'       => '100%',
                    'height'      => 390,
                    'height_auto' => 1,
                    'cache'       => 1,
                    'cache_time'  => 900,
                    'cachemode'   => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_21_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_footer',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_footer'],
                ],
                'params' => [
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_22_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_login',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_login'],
                ],
                'params' => [
                    'login'     => 280,
                    'logout'    => 280,
                    'greeting'  => 1,
                    'name'      => 0,
                    'usesecure' => 0,
                    'cache'     => 0,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_23_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_menu',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_menu'],
                ],
                'params' => [
                    'menutype'        => $menuTypes[4],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'cache'           => 0,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_24_TITLE'),
                'ordering'   => 6,
                'position'   => 'sidebar-right',
                'module'     => 'mod_articles_latest',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => $parkMenuIds,
                'params'     => [
                    'catid'      => [$articleCatids3[1]],
                    'count'      => 5,
                    'ordering'   => 'c_dsc',
                    'user_id'    => 0,
                    'show_front' => 1,
                    'cache'      => 1,
                    'cache_time' => 900,
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_25_TITLE'),
                'content'  => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_25_CONTENT'),
                'ordering' => 1,
                'module'   => 'mod_custom',
                'access'   => $access,
                'assigned' => [
                    $menuMapping['mod_custom'],
                ],
                'params' => [
                    'prepare_content' => 1,
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'static',
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_26_TITLE'),
                'ordering' => 1,
                'module'   => 'mod_breadcrumbs',
                'access'   => $access,
                'assigned' => [
                    $menuMapping['mod_breadcrumbs'],
                ],
                'params' => [
                    'showHere'   => 1,
                    'showHome'   => 1,
                    'homeText'   => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_26_HOMETEXT'),
                    'showLast'   => 1,
                    'cache'      => 0,
                    'cache_time' => 900,
                    'cachemode'  => 'itemid',
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_27_TITLE'),
                'ordering' => 1,
                'module'   => 'mod_banners',
                'access'   => $access,
                'assigned' => [
                    $menuMapping['mod_banners'],
                ],
                'params' => [
                    'target'     => 1,
                    'count'      => 1,
                    'cid'        => 1,
                    'catid'      => [$bannerCatids[0]],
                    'tag_search' => 0,
                    'ordering'   => 'random',
                    'cache'      => 1,
                    'cache_time' => 900,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_28_TITLE'),
                'ordering'   => 3,
                'position'   => 'sidebar-left',
                'module'     => 'mod_menu',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => array_merge($fruitshopMenuIds, [$mainMenuIds[1]]),
                'params'     => [
                    'menutype'        => $menuTypes[5],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'cache'           => 0,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_29_TITLE'),
                'content'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_29_CONTENT'),
                'ordering'   => 1,
                'position'   => 'main-top',
                'module'     => 'mod_custom',
                'access'     => 4,
                'assignment' => 1,
                'assigned'   => $fruitshopMenuIds,
                'params'     => [
                    'prepare_content' => 1,
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_30_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_articles_categories',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_articles_categories'],
                ],
                'params' => [
                    'parent'           => 19,
                    'show_description' => 0,
                    'show_children'    => 0,
                    'count'            => 0,
                    'maxlevel'         => 0,
                    'item_heading'     => 4,
                    'owncache'         => 1,
                    'cache_time'       => 900,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_31_TITLE'),
                'ordering'   => 3,
                'position'   => 'sidebar-left',
                'published'  => 0,
                'module'     => 'mod_languages',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => $parkMenuIds,
                'params'     => [
                    'image'      => 1,
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_33_TITLE'),
                'ordering'   => 1,
                'position'   => 'languageswitcherload',
                'published'  => 0,
                'module'     => 'mod_languages',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_languages'],
                ],
                'params' => [
                    'image'      => 1,
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_34_TITLE'),
                'content'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_34_CONTENT'),
                'ordering'   => 1,
                'position'   => 'sidebar-left',
                'module'     => 'mod_custom',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => $fruitshopMenuIds,
                'params'     => [
                    'prepare_content' => 1,
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_36_TITLE'),
                'content'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_36_CONTENT'),
                'ordering'   => 2,
                'position'   => 'sidebar-left',
                'module'     => 'mod_custom',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $parkMenuIds[0],
                ],
                'params' => [
                    'prepare_content' => 1,
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'static',
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_37_TITLE'),
                'ordering'   => 1,
                'module'     => 'mod_articles_category',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_articles_category'],
                ],
                'params' => [
                    'mode'                         => 'normal',
                    'show_on_article_page'         => 1,
                    'show_front'                   => 'show',
                    'count'                        => 0,
                    'category_filtering_type'      => 1,
                    'catid'                        => [$articleCatids4[5]],
                    'show_child_category_articles' => 0,
                    'levels'                       => 1,
                    'author_filtering_type'        => 1,
                    'created_by'                   => [],
                    'author_alias_filtering_type'  => 1,
                    'created_by_alias'             => [],
                    'date_filtering'               => 'off',
                    'date_field'                   => 'a.created',
                    'relative_date'                => 30,
                    'article_ordering'             => 'a.title',
                    'article_ordering_direction'   => 'ASC',
                    'article_grouping'             => 'none',
                    'article_grouping_direction'   => 'ksort',
                    'month_year_format'            => 'F Y',
                    'item_heading'                 => 4,
                    'link_titles'                  => 1,
                    'show_date'                    => 0,
                    'show_date_field'              => 'created',
                    'show_date_format'             => 'Y-m-d H:i:s',
                    'show_category'                => 0,
                    'show_hits'                    => 0,
                    'show_author'                  => 0,
                    'show_introtext'               => 0,
                    'introtext_limit'              => 100,
                    'show_readmore'                => 0,
                    'show_readmore_title'          => 1,
                    'readmore_limit'               => 15,
                    'owncache'                     => 1,
                    'cache_time'                   => 900,
                ],
            ],
            [
                'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_45_TITLE'),
                'ordering'  => 1,
                'position'  => 'bottom-a',
                'module'    => 'mod_banners',
                'access'    => $access,
                'showtitle' => 0,
                'params'    => [
                    'target'      => 1,
                    'count'       => 1,
                    'cid'         => 2,
                    'catid'       => [$bannerCatids[0]],
                    'tag_search'  => 0,
                    'ordering'    => 0,
                    'footer_text' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_45_FOOTER_TEXT'),
                    'cache'       => 1,
                    'cache_time'  => 900,
                ],
            ],
            [
                'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_46_TITLE'),
                'ordering'  => 1,
                'position'  => 'bottom-a',
                'module'    => 'mod_banners',
                'access'    => $access,
                'showtitle' => 0,
                'params'    => [
                    'target'      => 1,
                    'count'       => 1,
                    'cid'         => 1,
                    'catid'       => [$bannerCatids[0]],
                    'tag_search'  => 0,
                    'ordering'    => 0,
                    'footer_text' => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_46_FOOTER_TEXT'),
                    'cache'       => 1,
                    'cache_time'  => 900,
                ],
            ],
            [
                'title'      => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_47_TITLE'),
                'ordering'   => 2,
                'module'     => 'mod_finder',
                'access'     => $access,
                'assignment' => 1,
                'assigned'   => [
                    $menuMapping['mod_finder'],
                ],
                'params' => [
                    'show_autosuggest' => 1,
                    'show_advanced'    => 0,
                    'field_size'       => [20],
                    'show_label'       => 0,
                    'label_pos'        => 'top',
                    'show_button'      => 0,
                    'button_pos'       => 'right',
                    'opensearch'       => 1,
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_48_TITLE'),
                'ordering' => '2',
                'position' => 'sidebar-left',
                'module'   => 'mod_menu',
                'access'   => $access,
                'params'   => [
                    'menutype'        => $menuTypes[6],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_49_TITLE'),
                'ordering' => 1,
                'position' => 'sidebar-left',
                'module'   => 'mod_menu',
                'access'   => $access,
                'params'   => [
                    'menutype'        => $menuTypes[7],
                    'startLevel'      => 1,
                    'endLevel'        => 0,
                    'showAllChildren' => 0,
                    'cache'           => 1,
                    'cache_time'      => 900,
                    'cachemode'       => 'itemid',
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_51_TITLE'),
                'ordering' => 1,
                'module'   => 'mod_tags_popular',
                'access'   => $access,
                'assigned' => [
                    $menuMapping['mod_tags_popular'],
                ],
                'params' => [
                    'maximum'         => 5,
                    'timeframe'       => 'alltime',
                    'order_value'     => 'count',
                    'order_direction' => 1,
                    'display_count'   => 0,
                    'no_results_text' => 0,
                    'minsize'         => 1,
                    'maxsize'         => 2,
                    'owncache'        => 1,
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_52_TITLE'),
                'ordering' => 1,
                'module'   => 'mod_tags_similar',
                'access'   => $access,
                'assigned' => [
                    $menuMapping['mod_tags_similar'],
                ],
                'params' => [
                    'maximum'   => 5,
                    'matchtype' => 'any',
                    'owncache'  => 1,
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_53_TITLE'),
                'ordering' => 1,
                'position' => 'sidebar-left',
                'module'   => 'mod_syndicate',
                'access'   => $access,
                'params'   => [
                    'display_text' => 1,
                    'text'         => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_53_TEXT'),
                    'format'       => 'rss',
                    'cache'        => 0,
                ],
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_54_TITLE'),
                'ordering' => 1,
                'position' => 'sidebar-left',
                'module'   => 'mod_tags_similar',
                'access'   => $access,
                'params'   => [
                    'maximum'   => 5,
                    'matchtype' => 'any',
                    'owncache'  => 1,
                ],
            ],
            // Admin modules
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_55_TITLE'),
                'content'  => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_55_CONTENT'),
                'ordering' => 5,
                'position' => 'cpanel',
                'module'   => 'mod_custom',
                'access'   => $access,
                'params'   => [
                    'prepare_content' => 1,
                    'cache'           => 1,
                    'cache_time'      => 900,
                ],
                'client_id' => 1,
            ],
            [
                'title'     => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_56_TITLE'),
                'ordering'  => 6,
                'position'  => 'cpanel',
                'published' => 0,
                'module'    => 'mod_feed',
                'access'    => $access,
                'params'    => [
                    'rssurl'      => 'http://feeds.joomla.org/JoomlaAnnouncements',
                    'rssrtl'      => 0,
                    'rsstitle'    => 1,
                    'rssdesc'     => 1,
                    'rssimage'    => 1,
                    'rssitems'    => 3,
                    'rssitemdesc' => 1,
                    'word_count'  => 0,
                    'cache'       => 1,
                    'cache_time'  => 900,
                ],
                'client_id' => 1,
            ],
            [
                'title'    => $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_MODULES_MODULE_57_TITLE'),
                'ordering' => 3,
                'position' => 'cpanel',
                'module'   => 'mod_stats_admin',
                'access'   => $access,
                'params'   => [
                    'serverinfo' => 1,
                    'siteinfo'   => 1,
                    'counter'    => 1,
                    'increase'   => 0,
                    'cache'      => 1,
                    'cache_time' => 900,
                    'cachemode'  => 'static',
                ],
                'client_id' => 1,
            ],
        ];

        foreach ($modules as $module) {
            // Set values which are always the same.
            $module['id']              = 0;
            $module['asset_id']        = 0;
            $module['language']        = '*';
            $module['description']     = '';

            if (!isset($module['published'])) {
                $module['published'] = 1;
            }

            if (!isset($module['note'])) {
                $module['note'] = '';
            }

            if (!isset($module['content'])) {
                $module['content'] = '';
            }

            if (!isset($module['showtitle'])) {
                $module['showtitle'] = 1;
            }

            if (!isset($module['position'])) {
                $module['position'] = '';
            }

            if (!isset($module['params'])) {
                $module['params'] = [];
            }

            if (!isset($module['client_id'])) {
                $module['client_id'] = 0;
            }

            if (!isset($module['assignment'])) {
                $module['assignment'] = 0;
            }

            if (!$model->save($module)) {
                $this->getApplication()->getLanguage()->load('com_modules');
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_TESTING_STEP_FAILED', 8, $this->getApplication()->getLanguage()->_($model->getError()));

                $event->addResult($response);
                return;
            }
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP8_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Final step to show completion of sampledata.
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function onAjaxSampledataApplyStep9(AjaxEvent $event): void
    {
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_STEP9_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Adds categories.
     *
     * @param   array    $categories  Array holding the category arrays.
     * @param   string   $extension   Name of the extension.
     * @param   integer  $level       Level in the category tree.
     *
     * @return  array  IDs of the inserted categories.
     *
     * @throws  \Exception
     *
     * @since  3.8.0
     */
    private function addCategories(array $categories, $extension, $level)
    {
        if (!$this->categoryModel) {
            $this->categoryModel = $this->getApplication()->bootComponent('com_categories')
                ->getMVCFactory()
                ->createModel('Category', 'Administrator', ['ignore_request' => true]);
        }

        $catIds = [];
        $access = (int) $this->getApplication()->get('access', 1);
        $user   = $this->getApplication()->getIdentity();

        foreach ($categories as $category) {
            // Set values which are always the same.
            $category['id']              = 0;
            $category['published']       = 1;
            $category['access']          = $access;
            $category['created_user_id'] = $user->id;
            $category['extension']       = $extension;
            $category['level']           = $level;
            $category['alias']           = ApplicationHelper::stringURLSafe($category['title']);
            $category['associations']    = [];
            $category['params']          = [];

            // Set description to empty if not set
            if (!isset($category['description'])) {
                $category['description'] = '';
            }

            // Language defaults to "All" (*) when not set
            if (!isset($category['language'])) {
                $category['language'] = '*';
            }

            if (!$this->categoryModel->save($category)) {
                throw new \Exception($this->categoryModel->getError());
            }

            // Get ID from category we just added
            $catIds[] = $this->categoryModel->getState($this->categoryModel->getName() . '.id');
        }

        return $catIds;
    }

    /**
     * Adds articles.
     *
     * @param   array  $articles  Array holding the category arrays.
     *
     * @return  array  IDs of the inserted categories.
     *
     * @throws  \Exception
     *
     * @since  3.8.0
     */
    private function addArticles(array $articles)
    {
        $ids = [];

        $access     = (int) $this->getApplication()->get('access', 1);
        $user       = $this->getApplication()->getIdentity();
        $mvcFactory = $this->getApplication()->bootComponent('com_content')->getMVCFactory();

        foreach ($articles as $i => $article) {
            /** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $model */
            $model = $mvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);

            // Set values from language strings.
            $article['title']     = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_ARTICLE_' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_TITLE');
            $article['introtext'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_ARTICLE_' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_INTROTEXT');
            $article['fulltext']  = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_TESTING_SAMPLEDATA_CONTENT_ARTICLE_' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_FULLTEXT');

            // Set values which are always the same.
            $article['id']              = 0;
            $article['access']          = $access;
            $article['created_user_id'] = $user->id;
            $article['alias']           = ApplicationHelper::stringURLSafe($article['title']);
            $article['language']        = '*';
            $article['associations']    = [];
            $article['metakey']         = '';
            $article['metadesc']        = '';
            $article['xreference']      = '';

            // Set article to published if not set.
            if (!isset($article['state'])) {
                $article['state'] = 1;
            }

            // Set article to not featured if not set.
            if (!isset($article['featured'])) {
                $article['featured'] = 0;
            }

            // Set images to empty if not set.
            if (!isset($article['images'])) {
                $article['images'] = '';
            } else {
                // JSON Encode it when set.
                $article['images'] = json_encode($article['images']);
            }

            if (!$model->save($article)) {
                $this->getApplication()->getLanguage()->load('com_content');
                throw new \Exception($this->getApplication()->getLanguage()->_($model->getError()));
            }

            // Get ID from category we just added
            $ids[] = $model->getState($model->getName() . '.id');
        }

        return $ids;
    }

    /**
     * Adds menuitems.
     *
     * @param   array    $menuItems  Array holding the menuitems arrays.
     * @param   integer  $level      Level in the category tree.
     *
     * @return  array  IDs of the inserted menuitems.
     *
     * @throws  \Exception
     *
     * @since  3.8.0
     */
    private function addMenuItems(array $menuItems, $level)
    {
        if (!$this->menuItemModel) {
            $this->menuItemModel = $this->getApplication()->bootComponent('com_menus')
                ->getMVCFactory()
                ->createModel('Item', 'Administrator', ['ignore_request' => true]);
        }

        $itemIds = [];
        $access  = (int) $this->getApplication()->get('access', 1);
        $user    = $this->getApplication()->getIdentity();

        foreach ($menuItems as $menuItem) {
            // Reset item.id in model state.
            $this->menuItemModel->setState('item.id', 0);

            // Set values which are always the same.
            $menuItem['id']              = 0;
            $menuItem['created_user_id'] = $user->id;
            $menuItem['alias']           = ApplicationHelper::stringURLSafe($menuItem['title']);
            $menuItem['published']       = 1;
            $menuItem['language']        = '*';
            $menuItem['note']            = '';
            $menuItem['img']             = '';
            $menuItem['browserNav']      = 0;
            $menuItem['associations']    = [];
            $menuItem['client_id']       = 0;
            $menuItem['level']           = $level;

            // Set access to default if not set
            if (!isset($menuItem['access'])) {
                $menuItem['access'] = $access;
            }

            // Set type to 'component' if not set
            if (!isset($menuItem['type'])) {
                $menuItem['type'] = 'component';
            }

            // Set template_style_id to global if not set
            if (!isset($menuItem['template_style_id'])) {
                $menuItem['template_style_id'] = 0;
            }

            // Set home if not set
            if (!isset($menuItem['home'])) {
                $menuItem['home'] = 0;
            }

            // Set parent_id to root (1) if not set
            if (!isset($menuItem['parent_id'])) {
                $menuItem['parent_id'] = 1;
            }

            if (!$this->menuItemModel->save($menuItem)) {
                // Try two times with another alias (-1 and -2).
                $menuItem['alias'] .= '-1';

                if (!$this->menuItemModel->save($menuItem)) {
                    $menuItem['alias'] = substr_replace($menuItem['alias'], '2', -1);

                    if (!$this->menuItemModel->save($menuItem)) {
                        throw new \Exception($menuItem['title'] . ' => ' . $menuItem['alias'] . ' : ' . $this->menuItemModel->getError());
                    }
                }
            }

            // Get ID from menuitem we just added
            $id        = $this->menuItemModel->getstate('item.id');
            $itemIds[] = $id;

            if (isset($menuItem['sampledata_module'])) {
                $this->menuModuleMapping[$menuItem['sampledata_module']] = $id;
            }

            if (isset($menuItem['children'])) {
                foreach ($menuItem['children'] as &$item) {
                    $item['parent_id'] = $id;
                }

                $itemIds = array_merge($itemIds, $this->addMenuItems($menuItem['children'], $level + 1));
            }
        }

        return $itemIds;
    }
}
