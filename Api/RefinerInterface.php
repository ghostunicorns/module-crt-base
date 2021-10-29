<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Api;

use GhostUnicorns\CrtBase\Exception\CrtException;
use GhostUnicorns\CrtBase\Exception\CrtImportantException;
use GhostUnicorns\CrtEntity\Api\Data\EntityInterface;

interface RefinerInterface
{
    /**
     * @param int $activityId
     * @param string $refinerType
     * @param string $entityIdentifier
     * @param EntityInterface[] $entities
     * @throws CrtException
     * @throws CrtImportantException
     */
    public function execute(
        int $activityId,
        string $refinerType,
        string $entityIdentifier,
        array $entities
    ): void;
}
