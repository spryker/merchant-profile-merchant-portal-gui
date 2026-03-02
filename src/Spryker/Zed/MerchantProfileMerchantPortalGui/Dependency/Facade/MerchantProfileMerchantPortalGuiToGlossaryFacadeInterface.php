<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\TranslationTransfer;

interface MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface
{
    public function hasTranslation(string $keyName, ?LocaleTransfer $localeTransfer = null): bool;

    public function getTranslation(string $keyName, LocaleTransfer $localeTransfer): TranslationTransfer;
}
