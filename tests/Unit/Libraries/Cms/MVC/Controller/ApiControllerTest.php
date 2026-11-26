<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Controller;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\MVC\Controller\ApiController
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The ApiController')]
class ApiControllerTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('releases the check-out when an edit is refused')]
    public function testRefusedEditChecksTheRecordBackIn()
    {
        $model      = $this->modelDouble();
        $controller = $this->controller($model, new \RuntimeException('Save failed'));

        try {
            $controller->edit();
            $this->fail('The refusal must still reach the client');
        } catch (\RuntimeException $e) {
            $this->assertSame('Save failed', $e->getMessage());
        }

        $this->assertSame([42], $model->checkedOut);
        $this->assertSame([42], $model->checkedIn);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('leaves a successful edit to save()\'s own check-in')]
    public function testSuccessfulEditIsUnchanged()
    {
        $model      = $this->modelDouble();
        $controller = $this->controller($model, null);

        $controller->edit();

        $this->assertSame([42], $model->checkedOut);
        $this->assertSame([], $model->checkedIn, 'edit() adds no check-in of its own on success');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps a check-out the user already held when an edit is refused')]
    public function testRefusedEditKeepsAnExistingCheckOut()
    {
        $model      = $this->modelDouble(7);
        $controller = $this->controller($model, new \RuntimeException('Save failed'));

        try {
            $controller->edit();
            $this->fail('The refusal must still reach the client');
        } catch (\RuntimeException $e) {
            $this->assertSame('Save failed', $e->getMessage());
        }

        $this->assertSame([42], $model->checkedOut);
        $this->assertSame([], $model->checkedIn, 'The check-out was not taken by this request');
    }

    /**
     * A model double that records check-out and check-in calls.
     *
     * @param   integer  $checkedOutBy  The user already holding the check-out (0 for none)
     *
     * @return  object
     *
     * @since   __DEPLOY_VERSION__
     */
    private function modelDouble(int $checkedOutBy = 0): object
    {
        return new class ($checkedOutBy) {
            public array $checkedOut = [];
            public array $checkedIn  = [];

            public function __construct(private int $checkedOutBy)
            {
            }

            public function getTable()
            {
                return new class ($this->checkedOutBy) {
                    public function __construct(public int $checked_out)
                    {
                    }

                    public function load($pk)
                    {
                        return true;
                    }

                    public function getColumnAlias($name)
                    {
                        return $name;
                    }

                    public function hasField($name)
                    {
                        return $name === 'checked_out';
                    }

                    public function getKeyName()
                    {
                        return 'id';
                    }
                };
            }

            public function checkout($pk)
            {
                $this->checkedOut[] = (int) $pk;

                return true;
            }

            public function checkin($pk)
            {
                $this->checkedIn[] = (int) $pk;

                return true;
            }
        };
    }

    /**
     * An ApiController editing record 42, whose save() throws $refusal (or succeeds when null).
     *
     * @param   object           $model    The model double
     * @param   \Throwable|null  $refusal  What save() throws
     *
     * @return  ApiController
     *
     * @since   __DEPLOY_VERSION__
     */
    private function controller(object $model, ?\Throwable $refusal): ApiController
    {
        $config = ['base_path' => __DIR__, 'option' => 'com_content'];
        $input  = new Input(['id' => 42]);

        return new class ($config, $this->createStub(MVCFactoryInterface::class), $this->createStub(CMSWebApplicationInterface::class), $input, $model, $refusal) extends ApiController {
            protected $contentType = 'articles';

            public function __construct($config, $factory, $app, $input, private object $modelDouble, private ?\Throwable $refusal)
            {
                parent::__construct($config, $factory, $app, $input);
            }

            public function getModel($name = '', $prefix = '', $config = [])
            {
                return $this->modelDouble;
            }

            protected function allowEdit($data = [], $key = 'id')
            {
                return true;
            }

            protected function save($recordKey = null)
            {
                if ($this->refusal) {
                    throw $this->refusal;
                }

                return $recordKey;
            }

            public function displayItem($id = null)
            {
                return $this;
            }
        };
    }
}
