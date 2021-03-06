<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Logger\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends AbstractHandler
{
    private const LEVEL = 'level';

    /**
     * @var OutputInterface
     */
    public $consoleOutput;

    /**
     * @param OutputInterface $output
     */
    public function setConsoleOutput(OutputInterface $output)
    {
        $this->consoleOutput = $output;
    }

    /**
     * @param array $record
     */
    public function handle(array $record)
    {
        if (isset($this->consoleOutput)) {
            $message = $this->getFormatter()->format($record);
            if (array_key_exists(self::LEVEL, $record)) {
                switch ($record[self::LEVEL]) {
                    case Logger::NOTICE:
                    case Logger::INFO:
                        $wrapper = 'info';
                        break;
                    case Logger::ALERT:
                    case Logger::WARNING:
                        $wrapper = 'comment';
                        break;
                    case Logger::CRITICAL:
                    case Logger::ERROR:
                        $wrapper = 'error';
                        break;
                    default:
                        break;
                }

                if (isset($wrapper)) {
                    $message = '<' . $wrapper . '>' . $this->getFormatter()->format($record) . '</' . $wrapper . '>';
                }
            }
            $this->consoleOutput->write($message);
        }
    }
}
