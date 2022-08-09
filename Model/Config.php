<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Model;

use GhostUnicorns\CrtBase\Api\CrtConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Monolog\Logger;

class Config implements CrtConfigInterface
{
    /** @var string */
    const crt_IS_ENABLED_CONFIG_PATH = 'crt/general/enabled';

    /** @var string */
    const crt_SEMAPHORE_THRESHOLD_CONFIG_PATH = 'crt/general/semaphore_threshold';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::crt_IS_ENABLED_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getSemaphoreThreshold(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::crt_SEMAPHORE_THRESHOLD_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function continueInCaseOfErrors(): bool
    {
        return true;
    }

    public function isLogEnabled(): bool
    {
        return true;
    }

    public function getLogLevel(): int
    {
        return Logger::DEBUG;
    }
}
