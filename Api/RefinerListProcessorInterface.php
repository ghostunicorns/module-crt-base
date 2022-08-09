<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Api;

use GhostUnicorns\CrtBase\Exception\CrtException;
use Magento\Framework\Exception\NoSuchEntityException;

interface RefinerListProcessorInterface
{
    /**
     * @param int $activityId
     * @throws CrtException
     * @throws NoSuchEntityException
     */
    public function execute(int $activityId): void;
}
