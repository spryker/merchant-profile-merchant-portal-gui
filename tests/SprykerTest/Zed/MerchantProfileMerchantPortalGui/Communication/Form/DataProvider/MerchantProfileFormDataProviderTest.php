<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider;

use ArrayObject;
use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\StoreRelationBuilder;
use Generated\Shared\Transfer\LocaleCriteriaTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider\MerchantProfileFormDataProvider;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToLocaleFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToMerchantFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\MerchantProfileMerchantPortalGuiConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group MerchantProfileMerchantPortalGui
 * @group Communication
 * @group Form
 * @group DataProvider
 * @group MerchantProfileFormDataProviderTest
 * Add your own group annotations below this line
 */
class MerchantProfileFormDataProviderTest extends Unit
{
    /**
     * @var string
     */
    protected const MERCHANT_STORE_NAME = 'DE';

    /**
     * @var string
     */
    protected const OTHER_STORE_NAME = 'TR';

    public function testFindMerchantByIdShouldScopeUrlCollectionLocalesToMerchantAssignedStores(): void
    {
        // Arrange
        $merchantStoreTransfer = (new StoreTransfer())->setIdStore(1)->setName(static::MERCHANT_STORE_NAME);
        $storeRelationTransfer = (new StoreRelationBuilder())
            ->withStores($merchantStoreTransfer->toArray())
            ->build();

        $merchantTransfer = (new MerchantTransfer())
            ->setIdMerchant(6)
            ->setStoreRelation($storeRelationTransfer)
            ->setUrlCollection(new ArrayObject());

        $merchantStoreLocaleTransfer = (new LocaleTransfer())->setIdLocale(46)->setLocaleName('de_DE');
        $otherStoreLocaleTransfer = (new LocaleTransfer())->setIdLocale(203)->setLocaleName('tr_TR');

        $merchantFacadeMock = Stub::makeEmpty(
            MerchantProfileMerchantPortalGuiToMerchantFacadeInterface::class,
            [
                'findOne' => function () use ($merchantTransfer) {
                    return $merchantTransfer;
                },
            ],
        );

        $localeFacadeMock = Stub::makeEmpty(
            MerchantProfileMerchantPortalGuiToLocaleFacadeInterface::class,
            [
                'getLocaleCollection' => function (?LocaleCriteriaTransfer $localeCriteriaTransfer = null) use ($merchantStoreLocaleTransfer, $otherStoreLocaleTransfer) {
                    $storeNames = $localeCriteriaTransfer?->getLocaleConditions()?->getStoreNames() ?? [];

                    if ($storeNames === [static::MERCHANT_STORE_NAME]) {
                        return [$merchantStoreLocaleTransfer];
                    }

                    return [$merchantStoreLocaleTransfer, $otherStoreLocaleTransfer];
                },
            ],
        );

        $glossaryFacadeMock = Stub::makeEmpty(MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface::class);

        $merchantProfileFormDataProvider = new MerchantProfileFormDataProvider(
            new MerchantProfileMerchantPortalGuiConfig(),
            $merchantFacadeMock,
            $glossaryFacadeMock,
            $localeFacadeMock,
        );

        // Act
        $resultMerchantTransfer = $merchantProfileFormDataProvider->findMerchantById(6);

        // Assert
        $this->assertCount(1, $resultMerchantTransfer->getUrlCollection());
    }
}
