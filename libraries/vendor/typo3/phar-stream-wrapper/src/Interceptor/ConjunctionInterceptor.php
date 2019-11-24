<?php
namespace TYPO3\PharStreamWrapper\Interceptor;

/*
 * This file is part of the TYPO3 project.
 *
 * It is free software; you can redistribute it and/or modify it under the terms
 * of the MIT License (MIT). For the full copyright and license information,
 * please read the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\PharStreamWrapper\Assertable;
use TYPO3\PharStreamWrapper\Exception;

class ConjunctionInterceptor implements Assertable
{
    /**
     * @var Assertable[]
     */
    private $assertions;

    public function __construct(array $assertions)
    {
        $this->assertAssertions($assertions);
        $this->assertions = $assertions;
    }

    /**
     * Executes assertions based on all contained assertions.
     *
     * @param string $path
     * @param string $command
     * @return bool
     * @throws Exception
     */
    public function assert($path, $command)
    {
        if ($this->invokeAssertions($path, $command)) {
            return true;
        }
        throw new Exception(
            sprintf(
                'Assertion failed in "%s"',
                $path
            ),
            1539625084
        );
    }

    /**
     * @param Assertable[] $assertions
     */
    private function assertAssertions(array $assertions)
    {
        foreach ($assertions as $assertion) {
            if (!$assertion instanceof Assertable) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Instance %s must implement Assertable',
                        get_class($assertion)
                    ),
                    1539624719
                );
            }
        }
    }

    /**
     * @param string $path
     * @param string $command
     * @return bool
     */
    private function invokeAssertions($path, $command)
    {
        try {
            foreach ($this->assertions as $assertion) {
                if (!$assertion->assert($path, $command)) {
                    return false;
                }
            }
        } catch (Exception $exception) {
            return false;
        }
        return true;
    }
}
