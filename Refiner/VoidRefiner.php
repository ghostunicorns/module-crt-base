<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Refiner;

use GhostUnicorns\CrtBase\Api\RefinerInterface;

class VoidRefiner implements RefinerInterface
{
    public function execute(int $activityId, string $refinerType, string $entityIdentifier, array $entities): void
    {
        return;
    }
}