<?php
/**
 * @package        Alter Sentry
 * @copyright      Copyright (C) 2025-2025 AlterBrains.com. All rights reserved.
 * @license        https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL
 */

namespace AlterBrains\Plugin\Behaviour\Altersentry\Sentry;

use Joomla\Database\QueryMonitorInterface;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;

class SentryDebugMonitor implements QueryMonitorInterface
{
    /**
     * @var ?QueryMonitorInterface
     * @since 1.0
     */
    public $oldMonitor;

    /**
     * @var string
     * @since 1.0
     */
    protected $sql;

    /**
     * @var float
     * @since 1.0
     */
    protected $startTime;

    /**
     * @var ?array
     * @since 1.0
     */
    protected $boundParams;

    /**
     * @var bool
     * @since 1.0
     */
    protected $breadcrumbsSql;

    /**
     * @var bool
     * @since 1.0
     */
    protected $breadcrumbsSqlBindings;

    /**
     * @var bool
     * @since 1.0
     */
    protected $tracingSql;

    /**
     * @var bool
     * @since 1.0
     */
    protected $tracingSqlBindings;

    /**
     * @since 1.0
     */
    public function __construct(array $config, ?QueryMonitorInterface $oldMonitor = null)
    {
        $this->oldMonitor = $oldMonitor;

        $this->breadcrumbsSql = $config['breadcrumbs_sql'];
        $this->breadcrumbsSqlBindings = $config['breadcrumbs_sql_bindings'];

        $this->tracingSql = $config['breadcrumbs_sql'];
        $this->tracingSqlBindings = $config['breadcrumbs_sql_bindings'];
    }

    /**
     * @inheritDoc
     * @since 1.0
     */
    public function startQuery(string $sql, ?array $boundParams = null): void
    {
        $this->sql = $sql;
        $this->boundParams = $boundParams;
        $this->startTime = \microtime(true);

        $this->oldMonitor?->startQuery($sql, $boundParams);
    }

    /**
     * @inheritDoc
     * @since 1.0
     */
    public function stopQuery(): void
    {
        $executionTimeMs = \round((\microtime(true) - $this->startTime) * 1000, 2);
        $bindings = [];

        if (($this->breadcrumbsSqlBindings || $this->tracingSqlBindings) && $this->boundParams) {
            foreach ($this->boundParams as $key => $binding) {
                /** @noinspection OnlyWritesOnParameterInspection
                 * @noinspection RedundantSuppression
                 */
                $bindings[$key] = $binding instanceof \stdClass ? $binding->value : $binding;
            }
        }

        // Breadcrumbs
        if ($this->breadcrumbsSql) {
            Integration::addBreadcrumb(
                new Breadcrumb(
                    Breadcrumb::LEVEL_INFO,
                    Breadcrumb::TYPE_DEFAULT,
                    'db.sql.query',
                    $this->sql,
                    [
                        'executionTimeMs' => $executionTimeMs,
                    ] + ($bindings ? ['bindings' => $bindings] : []),
                )
            );
        }

        // Tracing
        if ($this->tracingSql) {
            $parentSpan = SentrySdk::getCurrentHub()->getSpan();
            if ($parentSpan === null || !$parentSpan->getSampled()) {
                return;
            }

            $context = SpanContext::make()
                ->setOp('db.sql.query')
                // useless
                /*->setData([
                    'db.name' => $connection->getDatabaseName(),
                    'db.system' => $connection->getDriverName(),
                    'server.address' => $connection->getConfig('host'),
                    'server.port' => $connection->getConfig('port'),
                ])*/
                ->setOrigin('auto.db')
                ->setDescription($this->sql)
                ->setStartTimestamp($this->startTime)
                ->setEndTimestamp($executionTimeMs);

            if ($this->tracingSqlBindings && $bindings) {
                $context->setData(/*$context->getData() + */ [
                    'db.sql.bindings' => $bindings,
                ]);
            }

            $parentSpan->startChild($context);
        }

        $this->oldMonitor?->stopQuery();
    }

    /**
     * @since 1.0
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->oldMonitor->$name(...$arguments);
    }
}
