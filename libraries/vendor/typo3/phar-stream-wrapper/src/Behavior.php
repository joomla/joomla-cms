<?php
declare(strict_types=1);
namespace TYPO3\PharStreamWrapper;

/*
 * This file is part of the TYPO3 project.
 *
 * It is free software; you can redistribute it and/or modify it under the terms
 * of the MIT License (MIT). For the full copyright and license information,
 * please read the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

class Behavior implements Assertable
{
    const COMMAND_DIR_OPENDIR = 'dir_opendir';
    const COMMAND_MKDIR = 'mkdir';
    const COMMAND_RENAME = 'rename';
    const COMMAND_RMDIR = 'rmdir';
    const COMMAND_STEAM_METADATA = 'stream_metadata';
    const COMMAND_STREAM_OPEN = 'stream_open';
    const COMMAND_UNLINK = 'unlink';
    const COMMAND_URL_STAT = 'url_stat';

    /**
     * @var string[]
     */
    private $availableCommands = [
        self::COMMAND_DIR_OPENDIR,
        self::COMMAND_MKDIR,
        self::COMMAND_RENAME,
        self::COMMAND_RMDIR,
        self::COMMAND_STEAM_METADATA,
        self::COMMAND_STREAM_OPEN,
        self::COMMAND_UNLINK,
        self::COMMAND_URL_STAT,
    ];

    /**
     * @var Assertable[]
     */
    private $assertions;

    /**
     * @param Assertable $assertable
     * @param string ...$commands
     * @return static
     */
    public function withAssertion(Assertable $assertable, string ...$commands): self
    {
        $this->assertCommands($commands);
        $commands = $commands ?: $this->availableCommands;

        $target = clone $this;
        foreach ($commands as $command) {
            $target->assertions[$command] = $assertable;
        }
        return $target;
    }

    /**
     * @param string $path
     * @param string $command
     * @return bool
     */
    public function assert(string $path, string $command): bool
    {
        $this->assertCommand($command);
        $this->assertAssertionCompleteness();

        return $this->assertions[$command]->assert($path, $command);
    }

    /**
     * @param array $commands
     */
    private function assertCommands(array $commands)
    {
        $unknownCommands = array_diff($commands, $this->availableCommands);
        if (empty($unknownCommands)) {
            return;
        }
        throw new \LogicException(
            sprintf(
                'Unknown commands: %s',
                implode(', ', $unknownCommands)
            ),
            1535189881
        );
    }

    private function assertCommand(string $command)
    {
        if (in_array($command, $this->availableCommands, true)) {
            return;
        }
        throw new \LogicException(
            sprintf(
                'Unknown command "%s"',
                $command
            ),
            1535189882
        );
    }

    private function assertAssertionCompleteness()
    {
        $undefinedAssertions = array_diff(
            $this->availableCommands,
            array_keys($this->assertions)
        );
        if (empty($undefinedAssertions)) {
            return;
        }
        throw new \LogicException(
            sprintf(
                'Missing assertions for commands: %s',
                implode(', ', $undefinedAssertions)
            ),
            1535189883
        );
    }
}
