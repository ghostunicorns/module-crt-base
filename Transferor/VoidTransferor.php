<?php
/*
  * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtBase\Transferor;

use GhostUnicorns\CrtBase\Api\TransferorInterface;

class VoidTransferor implements TransferorInterface
{
    public function execute(int $activityId, string $transferorType): void
    {
        return;
    }
}
