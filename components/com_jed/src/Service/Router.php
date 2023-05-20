<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Service;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

use function defined;

/**
 * JED Router.
 *
 * @package   JED
 * @since     4.0.0
 */
class Router extends RouterView
{
    use MVCFactoryAwareTrait;
    use DatabaseAwareTrait;

    /**
     * The category cache
     *
     * @var   array
     * @since 4.0.0
     */
    private array $categoryCache = [];

    /**
     * The category factory
     *
     * @var   CategoryFactoryInterface
     * @since 4.0.0
     */
    private CategoryFactoryInterface $categoryFactory;

    /**
     * Class constructor.
     *
     * @param   CMSApplication  $app   Application-object that the router should use
     * @param   AbstractMenu    $menu  Menu-object that the router should use
     *
     * @since   3.4
     */
    public function __construct(SiteApplication $app = null, AbstractMenu $menu, DatabaseInterface $db, MVCFactory $factory, CategoryFactoryInterface $categoryFactory)
    {
        parent::__construct($app, $menu);

        $this->categoryFactory = $categoryFactory;
        $this->setDatabase($db);
        $this->setMVCFactory($factory);

        $homepage = new RouterViewConfiguration('homepage');
        $this->registerView($homepage);

        // Extensions
        $categories = new RouterViewConfiguration('categories');
        $categories->setKey('id');
        $this->registerView($categories);

        $extensions = new RouterViewConfiguration('extensions');
        $extensions
            ->setKey('id')
            ->setParent($categories, 'catid')
            ->setNestable();
        $this->registerView($extensions);

        $extension = new RouterViewConfiguration('extension');
        $extension
            ->setKey('id')
            ->setParent($categories, 'catid')
            ->addLayout('edit');
        $this->registerView($extension);

        $extensionform = new RouterViewConfiguration('extensionform');
        $this->registerView($extensionform);
        $extensionvarieddatum = new RouterViewConfiguration('extensionvarieddatum');
        $this->registerView($extensionvarieddatum);

        // JED Tickets
        $jedtickets = new RouterViewConfiguration('jedtickets');
        $this->registerView($jedtickets);
        $jedticket = new RouterViewConfiguration('jedticket');
        $jedticket->setKey('id')->setParent($jedtickets);
        $this->registerView($jedticket);
        $jedticketform = new RouterViewConfiguration('jedticketform');
        $this->registerView($jedticketform);

        // Reviews
        $reviews = new RouterViewConfiguration('reviews');
        $this->registerView($reviews);
        $review = new RouterViewConfiguration('review');
        $review->setKey('id')->setParent($reviews);
        $this->registerView($review);
        $reviewform = new RouterViewConfiguration('reviewform');
        $this->registerView($reviewform);
        $reviewscomments = new RouterViewConfiguration('reviewscomments');
        $this->registerView($reviewscomments);
        $reviewcomment = new RouterViewConfiguration('reviewcomment');
        $reviewcomment->setKey('id')->setParent($reviewscomments);
        $this->registerView($reviewcomment);
        $reviewcommentform = new RouterViewConfiguration('reviewcommentform');
        $this->registerView($reviewcommentform);

        // Tickets
        $ticketmessages = new RouterViewConfiguration('ticketmessages');
        $this->registerView($ticketmessages);
        $ticketmessage = new RouterViewConfiguration('ticketmessage');
        $ticketmessage->setKey('id')->setParent($ticketmessages);
        $this->registerView($ticketmessage);
        $ticketmessageform = new RouterViewConfiguration('ticketmessageform');
        $this->registerView($ticketmessageform);

        // VEL list
        $velabandonedreports = new RouterViewConfiguration('velabandonedreports');
        $this->registerView($velabandonedreports);
        $velabandonedreport = new RouterViewConfiguration('velabandonedreport');
        $velabandonedreport->setKey('id')->setParent($velabandonedreports);
        $this->registerView($velabandonedreport);
        $velabandonedreportform = new RouterViewConfiguration('velabandonedreportform');
        $this->registerView($velabandonedreportform);

        $veldeveloperupdates = new RouterViewConfiguration('veldeveloperupdates');
        $this->registerView($veldeveloperupdates);
        $veldeveloperupdate = new RouterViewConfiguration('veldeveloperupdate');
        $veldeveloperupdate->setKey('id')->setParent($veldeveloperupdates);
        $this->registerView($veldeveloperupdate);
        $veldeveloperupdateform = new RouterViewConfiguration('veldeveloperupdateform');
        $this->registerView($veldeveloperupdateform);

        $velabandoneditems = new RouterViewConfiguration('velabandoneditems');
        $this->registerView($velabandoneditems);
        $velitem = new RouterViewConfiguration('velitem');
        $this->registerView($velitem);
        $velliveitems = new RouterViewConfiguration('velliveitems');
        $this->registerView($velliveitems);
        $velpatcheditems = new RouterViewConfiguration('velpatcheditems');
        $this->registerView($velpatcheditems);

        // VEL reports
        $velreports = new RouterViewConfiguration('velreports');
        $this->registerView($velreports);

        $velreport = new RouterViewConfiguration('velreport');
        $velreport->setKey('id')->setParent($velreports);
        $this->registerView($velreport);

        $velreportform = new RouterViewConfiguration('velreportform');
        $this->registerView($velreportform);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   4.0.0
     */
    public function getCategoriesId(string $segment, array $query): int|bool|null
    {
        return $this->getCategoryId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     * @since   4.0.0
     */
    public function getCategoriesSegment(string $id, array $query): array|string
    {
        return $this->getCategorySegment($id, $query);
    }

    /**
     * Method to get the id for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   4.0.0
     */
    public function getCategoryId(string $segment, array $query): int|bool|null
    {
        $id = $query['id'] ?? 'root';

        $category = $this->getCategories(['access' => false])->get($id);

        if (!$category) {
            return false;
        }

        foreach ($category->getChildren() as $child) {
            if ($child->alias == $segment) {
                return $child->id;
            }
        }

        return false;
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     * @since   4.0.0
     */
    public function getCategorySegment(string $id, array $query): array|string
    {
        $category = $this->getCategories(['access' => true])->get($id);

        if ($category) {
            $path    = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            foreach ($path as &$segment) {
                [$id, $segment] = explode(':', $segment, 2);
            }

            return $path;
        }

        return [];
    }

    /**
     * @param $segment
     * @param $query
     *
     * @return int
     *
     * @since 4.0.0
     */
    public function getExtensionId($segment, $query): int
    {
        return (int) $segment;
    }

    /**
     * @param $id
     * @param $query
     *
     * @return array|string[]
     *
     * @since 4.0.0
     */
    public function getExtensionSegment($id, $query): array
    {
        if (strpos($id, ':')) {
            return [(int) $id => $id];
        }

        $id        = (int) $id;
        $numericId = $id;
        $db        = $this->getDatabase();
        $query     = $db->getQuery(true);
        $query->select($db->quoteName('alias'))
              ->from($db->quoteName('#__jed_extension_varied_data'))
              ->where($db->quoteName('extension_id') . ' = :id')
              ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);

        $id .= ':' . $db->loadResult();

        return [$numericId => $id];
    }

    /**
     * @param $segment
     * @param $query
     *
     * @return bool|int|null
     *
     * @since 4.0.0
     */
    public function getExtensionsId($segment, $query): bool|int|null
    {
        return $this->getCategoryId($segment, $query);
    }

    /**
     * @param $id
     * @param $query
     *
     * @return array|string
     *
     * @since 4.0.0
     */
    public function getExtensionsSegment($id, $query): array|string
    {
        return $this->getCategorySegment($id, $query);
    }

    /**
     * Method to get categories from cache
     *
     * @param   array  $options  The options for retrieving categories
     *
     * @return  CategoryInterface  The object containing categories
     *
     * @since   4.0.0
     */
    private function getCategories(array $options = []): CategoryInterface
    {
        $key = serialize($options);

        if (!isset($this->categoryCache[$key])) {
            $this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
        }

        return $this->categoryCache[$key];
    }

    public function getJedticketId($segment, $query)
    {
        return $segment;
    }

    public function getJedticketSegment($id, $query)
    {
        return [$id];
    }

    public function getReviewId($segment, $query)
    {
        return $segment;
    }

    public function getReviewSegment($id, $query)
    {
        return [$id];
    }

    public function getReviewcommentId($segment, $query)
    {
        return $segment;
    }

    public function getReviewcommentSegment($id, $query)
    {
        return [$id];
    }

    public function getTicketmessageId($segment, $query)
    {
        return $segment;
    }

    public function getTicketmessageSegment($id, $query)
    {
        return [$id];
    }

    public function getVelabandonedreportId($segment, $query)
    {
        return $segment;
    }

    public function getVelabandonedreportSegment($id, $query)
    {
        return [$id];
    }

    public function getVeldeveloperupdateId($segment, $query)
    {
        return $segment;
    }

    public function getVeldeveloperupdateSegment($id, $query)
    {
        return [$id];
    }

    public function getVelreportId($segment, $query)
    {
        return $segment;
    }

    public function getVelreportSegment($id, $query)
    {
        return [$id];
    }
}
