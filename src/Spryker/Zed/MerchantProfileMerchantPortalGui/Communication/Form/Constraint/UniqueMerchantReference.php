<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class UniqueMerchantReference extends Constraint
{
    /**
     * @var string
     */
    public const OPTION_CURRENT_MERCHANT_ID = 'currentMerchantId';

    /**
     * @var string
     */
    protected const VALIDATION_MESSAGE = 'Merchant reference is already used.';

    /**
     * @var int|null
     */
    protected ?int $currentMerchantId;

    public function getTargets(): string
    {
        return static::CLASS_CONSTRAINT;
    }

    public function getMessage(): string
    {
        return static::VALIDATION_MESSAGE;
    }

    public function getCurrentMerchantId(): ?int
    {
        return $this->currentMerchantId;
    }
}
