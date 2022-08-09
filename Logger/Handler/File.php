<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Logger\Handler;

use Exception;
use GhostUnicorns\CrtBase\Api\CrtConfigInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;

class File extends Base
{
    /**
     * @var CrtConfigInterface
     */
    private $config;

    /**
     * @param CrtConfigInterface $config
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     * @throws Exception
     */
    public function __construct(
        CrtConfigInterface $config,
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        parent::__construct($filesystem, $filePath, $fileName);
        $this->config = $config;
    }

    /**
     * @inerhitDoc
     */
    public function handle(array $record): bool
    {
        if ($this->config->isLogEnabled() && ($record['level'] >= $this->config->getLogLevel())) {
            return parent::handle($record);
        }
        return false;
    }
}
