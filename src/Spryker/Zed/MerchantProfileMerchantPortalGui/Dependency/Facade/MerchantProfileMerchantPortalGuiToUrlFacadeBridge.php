<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade;

use Generated\Shared\Transfer\UrlTransfer;

class MerchantProfileMerchantPortalGuiToUrlFacadeBridge implements MerchantProfileMerchantPortalGuiToUrlFacadeInterface
{
    /**
     * @var \Spryker\Zed\Url\Business\UrlFacadeInterface
     */
    protected $urlFacade;

    /**
     * @param \Spryker\Zed\Url\Business\UrlFacadeInterface $urlFacade
     */
    public function __construct($urlFacade)
    {
        $this->urlFacade = $urlFacade;
    }

    public function hasUrlCaseInsensitive(UrlTransfer $urlTransfer): bool
    {
        return $this->urlFacade->hasUrlCaseInsensitive($urlTransfer);
    }

    public function findUrlCaseInsensitive(UrlTransfer $urlTransfer): ?UrlTransfer
    {
        return $this->urlFacade->findUrlCaseInsensitive($urlTransfer);
    }
}
