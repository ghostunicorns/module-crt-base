<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Api;

use GhostUnicorns\CrtBase\Exception\CrtException;

interface TransferorInterface
{
    /**
     * @param int $activityId
     * @param string $transferorType
     * @throws CrtException
     */
    public function execute(int $activityId, string $transferorType): void;
}
