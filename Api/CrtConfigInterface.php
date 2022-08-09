<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Api;

interface CrtConfigInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return bool
     */
    public function continueInCaseOfErrors(): bool;

    /**
     * @return bool
     */
    public function isLogEnabled(): bool;

    /**
     * @return int
     */
    public function getLogLevel(): int;
}
