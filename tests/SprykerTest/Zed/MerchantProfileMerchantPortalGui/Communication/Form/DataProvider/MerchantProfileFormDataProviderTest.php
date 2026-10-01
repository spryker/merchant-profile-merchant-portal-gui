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
use Generated\Shared\Transfer\GlossaryKeyTransfer;
use Generated\Shared\Transfer\LocaleCriteriaTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\MerchantProfileTransfer;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\TranslationTransfer;
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

    protected const int ID_LOCALE_DE = 46;

    protected const int ID_LOCALE_EN = 66;

    protected const string DESCRIPTION_GLOSSARY_KEY = 'merchant.description_glossary_key.6';

    protected const string IMPRINT_GLOSSARY_KEY = 'merchant.imprint_glossary_key.6';

    protected const string DESCRIPTION_DE = 'Beschreibung';

    protected const string DESCRIPTION_EN = 'Description';

    protected const string IMPRINT_EN = 'Imprint';

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

    public function testFindMerchantByIdLoadsAllGlossaryTranslationsWithOneCallAndSkipsInactiveOnes(): void
    {
        // Arrange
        $localeTransferDe = (new LocaleTransfer())->setIdLocale(static::ID_LOCALE_DE)->setLocaleName('de_DE');
        $localeTransferEn = (new LocaleTransfer())->setIdLocale(static::ID_LOCALE_EN)->setLocaleName('en_US');
        $merchantTransfer = (new MerchantTransfer())
            ->setIdMerchant(6)
            ->setUrlCollection(new ArrayObject())
            ->setMerchantProfile(
                (new MerchantProfileTransfer())
                    ->setDescriptionGlossaryKey(static::DESCRIPTION_GLOSSARY_KEY)
                    ->setImprintGlossaryKey(static::IMPRINT_GLOSSARY_KEY),
            );

        $merchantFacadeMock = Stub::makeEmpty(MerchantProfileMerchantPortalGuiToMerchantFacadeInterface::class, [
            'findOne' => $merchantTransfer,
        ]);
        $localeFacadeMock = Stub::makeEmpty(MerchantProfileMerchantPortalGuiToLocaleFacadeInterface::class, [
            'getLocaleCollection' => [$localeTransferDe, $localeTransferEn],
        ]);
        $glossaryFacadeMock = $this->createMock(MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface::class);

        // Expect
        $glossaryFacadeMock->expects($this->never())->method('hasTranslation');
        $glossaryFacadeMock->expects($this->never())->method('getTranslation');
        $glossaryFacadeMock->expects($this->once())
            ->method('getTranslationsByGlossaryKeysAndLocaleTransfers')
            ->with(
                $this->equalTo([static::DESCRIPTION_GLOSSARY_KEY, static::IMPRINT_GLOSSARY_KEY]),
                $this->equalTo([$localeTransferDe, $localeTransferEn]),
            )
            ->willReturn([
                $this->createTranslationTransfer(static::DESCRIPTION_GLOSSARY_KEY, static::ID_LOCALE_DE, static::DESCRIPTION_DE, true),
                $this->createTranslationTransfer(static::DESCRIPTION_GLOSSARY_KEY, static::ID_LOCALE_EN, static::DESCRIPTION_EN, true),
                $this->createTranslationTransfer(static::IMPRINT_GLOSSARY_KEY, static::ID_LOCALE_EN, static::IMPRINT_EN, false),
            ]);

        $merchantProfileFormDataProvider = new MerchantProfileFormDataProvider(
            new MerchantProfileMerchantPortalGuiConfig(),
            $merchantFacadeMock,
            $glossaryFacadeMock,
            $localeFacadeMock,
        );

        // Act
        $resultMerchantTransfer = $merchantProfileFormDataProvider->findMerchantById(6);

        // Assert
        $localizedGlossaryAttributesTransfers = $resultMerchantTransfer->getMerchantProfileOrFail()->getMerchantProfileLocalizedGlossaryAttributes();
        $this->assertCount(2, $localizedGlossaryAttributesTransfers);

        $glossaryAttributeValuesTransferDe = $localizedGlossaryAttributesTransfers->offsetGet(0)->getMerchantProfileGlossaryAttributeValuesOrFail();
        $this->assertSame(static::DESCRIPTION_DE, $glossaryAttributeValuesTransferDe->getDescriptionGlossaryKey());
        $this->assertNull($glossaryAttributeValuesTransferDe->getImprintGlossaryKey());

        $glossaryAttributeValuesTransferEn = $localizedGlossaryAttributesTransfers->offsetGet(1)->getMerchantProfileGlossaryAttributeValuesOrFail();
        $this->assertSame(static::DESCRIPTION_EN, $glossaryAttributeValuesTransferEn->getDescriptionGlossaryKey());
        $this->assertNull($glossaryAttributeValuesTransferEn->getImprintGlossaryKey());
    }

    protected function createTranslationTransfer(string $glossaryKey, int $idLocale, string $value, bool $isActive): TranslationTransfer
    {
        return (new TranslationTransfer())
            ->setGlossaryKey((new GlossaryKeyTransfer())->setKey($glossaryKey))
            ->setFkLocale($idLocale)
            ->setValue($value)
            ->setIsActive($isActive);
    }
}
