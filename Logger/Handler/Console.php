<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Logger\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
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

    public $formatter;

    /**
     * @param OutputInterface $output
     */
    public function setConsoleOutput(OutputInterface $output)
    {
        $this->consoleOutput = $output;
    }

    /**
     * @inerhitDoc
     */
    public function handle(array $record): bool
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

            return $this->bubble;
        }
        return false;
    }

    /**
     * @return FormatterInterface|LineFormatter
     */
    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }


    /**
     * @return FormatterInterface
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter();
    }
}
