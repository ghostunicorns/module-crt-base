<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Plugin\Refiner;

use GhostUnicorns\CrtBase\Api\RefinerInterface;
use GhostUnicorns\CrtEntity\Api\Data\EntityInterface;

class RemoveSkippedEntities
{
    /**
     * @param RefinerInterface $subject
     * @param callable $proceed
     * @param int $activityId
     * @param string $refinerType
     * @param string $entityIdentifier
     * @param EntityInterface[] $entities
     * @return void
     */
    public function aroundExecute(
        RefinerInterface $subject,
        callable $proceed,
        int $activityId,
        string $refinerType,
        string $entityIdentifier,
        array $entities
    ) {
        /** @var EntityInterface $entity */
        $entities = array_filter($entities, function ($entity) {
            return false === $entity->isSkip();
        });

        if (count($entities) > 0) {
            $proceed($activityId, $refinerType, $entityIdentifier, $entities);
        }
    }
}
