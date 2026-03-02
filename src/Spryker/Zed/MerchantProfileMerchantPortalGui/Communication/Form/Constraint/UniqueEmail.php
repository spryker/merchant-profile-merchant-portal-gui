<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\Constraint;

use Symfony\Component\Validator\Constraint as SymfonyConstraint;

class UniqueEmail extends SymfonyConstraint
{
    /**
     * @var string
     */
    public const OPTION_CURRENT_ID_MERCHANT = 'currentIdMerchant';

    /**
     * @var int|null
     */
    protected ?int $currentIdMerchant;

    public function getCurrentIdMerchant(): ?int
    {
        return $this->currentIdMerchant;
    }

    public function getTargets(): string
    {
        return static::CLASS_CONSTRAINT;
    }
}
