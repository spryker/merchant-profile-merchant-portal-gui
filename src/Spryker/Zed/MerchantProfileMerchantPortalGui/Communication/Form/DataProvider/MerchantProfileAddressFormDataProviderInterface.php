<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider;

interface MerchantProfileAddressFormDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getCountryChoices(): array;
}
