<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider;

use ArrayObject;
use Generated\Shared\Transfer\LocaleConditionsTransfer;
use Generated\Shared\Transfer\LocaleCriteriaTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\MerchantCriteriaTransfer;
use Generated\Shared\Transfer\MerchantProfileGlossaryAttributeValuesTransfer;
use Generated\Shared\Transfer\MerchantProfileLocalizedGlossaryAttributesTransfer;
use Generated\Shared\Transfer\MerchantProfileTransfer;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Spryker\Zed\Glossary\Business\GlossaryFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToLocaleFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToMerchantFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\MerchantProfileMerchantPortalGuiConfig;

class MerchantProfileFormDataProvider implements MerchantProfileFormDataProviderInterface
{
    protected MerchantProfileMerchantPortalGuiConfig $merchantProfileMerchantPortalGuiConfig;

    protected MerchantProfileMerchantPortalGuiToMerchantFacadeInterface $merchantFacade;

    protected GlossaryFacadeInterface $glossaryFacade;

    protected MerchantProfileMerchantPortalGuiToLocaleFacadeInterface $localeFacade;

    public function __construct(
        MerchantProfileMerchantPortalGuiConfig $merchantProfileMerchantPortalGuiConfig,
        MerchantProfileMerchantPortalGuiToMerchantFacadeInterface $merchantFacade,
        GlossaryFacadeInterface $glossaryFacade,
        MerchantProfileMerchantPortalGuiToLocaleFacadeInterface $localeFacade
    ) {
        $this->merchantFacade = $merchantFacade;
        $this->merchantProfileMerchantPortalGuiConfig = $merchantProfileMerchantPortalGuiConfig;
        $this->glossaryFacade = $glossaryFacade;
        $this->localeFacade = $localeFacade;
    }

    public function findMerchantById(int $idMerchant): ?MerchantTransfer
    {
        $merchantCriteriaTransfer = new MerchantCriteriaTransfer();
        $merchantCriteriaTransfer->setIdMerchant($idMerchant);

        $merchantTransfer = $this->merchantFacade->findOne($merchantCriteriaTransfer);

        if (!$merchantTransfer) {
            return null;
        }

        $merchantTransfer = $this->addMerchantProfileData($merchantTransfer);
        $merchantTransfer = $this->addInitialUrlCollection($merchantTransfer);

        return $merchantTransfer;
    }

    protected function addMerchantProfileData(MerchantTransfer $merchantTransfer): MerchantTransfer
    {
        $merchantProfileTransfer = $merchantTransfer->getMerchantProfile() ?? new MerchantProfileTransfer();
        $merchantProfileTransfer = $this->addLocalizedGlossaryAttributes(
            $merchantProfileTransfer,
            $this->getMerchantStoreLocales($merchantTransfer),
        );

        $merchantTransfer->setMerchantProfile($merchantProfileTransfer);

        return $merchantTransfer;
    }

    protected function addInitialUrlCollection(MerchantTransfer $merchantTransfer): MerchantTransfer
    {
        $merchantProfileUrlCollection = $merchantTransfer->getUrlCollection();
        $urlCollection = new ArrayObject();
        $availableLocaleTransfers = $this->getMerchantStoreLocales($merchantTransfer);

        foreach ($availableLocaleTransfers as $localeTransfer) {
            $urlCollection->append(
                $this->addUrlPrefixToUrlTransfer($merchantProfileUrlCollection, $localeTransfer),
            );
        }
        $merchantTransfer->setUrlCollection($urlCollection);

        return $merchantTransfer;
    }

    /**
     * Limited to locales of the stores the merchant is assigned to, not all locales in the system.
     *
     * @return array<\Generated\Shared\Transfer\LocaleTransfer>
     */
    protected function getMerchantStoreLocales(MerchantTransfer $merchantTransfer): array
    {
        if (!$merchantTransfer->getStoreRelation()) {
            return $this->localeFacade->getLocaleCollection();
        }

        $storeNames = [];
        foreach ($merchantTransfer->getStoreRelationOrFail()->getStores() as $storeTransfer) {
            $storeNames[] = $storeTransfer->getNameOrFail();
        }

        if (!$storeNames) {
            return [];
        }

        $localeCriteriaTransfer = (new LocaleCriteriaTransfer())
            ->setLocaleConditions(
                (new LocaleConditionsTransfer())->setStoreNames($storeNames),
            );

        return $this->localeFacade->getLocaleCollection($localeCriteriaTransfer);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\UrlTransfer> $merchantProfileUrlCollection
     */
    protected function addUrlPrefixToUrlTransfer(
        ArrayObject $merchantProfileUrlCollection,
        LocaleTransfer $localeTransfer
    ): UrlTransfer {
        $urlTransfer = new UrlTransfer();
        foreach ($merchantProfileUrlCollection as $merchantProfileUrlTransfer) {
            if ($merchantProfileUrlTransfer->getFkLocale() === $localeTransfer->getIdLocale()) {
                $urlTransfer->fromArray($merchantProfileUrlTransfer->toArray(), true);

                break;
            }
        }
        $urlTransfer->setFkLocale($localeTransfer->getIdLocale());
        $urlTransfer->setUrlPrefix(
            $this->getLocalizedUrlPrefix($localeTransfer),
        );

        return $urlTransfer;
    }

    protected function getLocalizedUrlPrefix(LocaleTransfer $localeTransfer): string
    {
        $localeName = $localeTransfer->getLocaleNameOrFail();
        $localeNameParts = explode('_', $localeName);
        $languageCode = $localeNameParts[0];

        return '/' . $languageCode . '/' . $this->merchantProfileMerchantPortalGuiConfig->getMerchantUrlPrefix() . '/';
    }

    /**
     * @param array<\Generated\Shared\Transfer\LocaleTransfer> $localeTransfers
     */
    protected function addLocalizedGlossaryAttributes(
        MerchantProfileTransfer $merchantProfileTransfer,
        array $localeTransfers
    ): MerchantProfileTransfer {
        $merchantProfileGlossaryAttributeValues = new ArrayObject();
        $glossaryKeysIndexedByFieldName = $this->extractGlossaryKeysIndexedByFieldName($merchantProfileTransfer);
        $activeTranslationValues = $this->getActiveTranslationValuesIndexedByGlossaryKeyAndIdLocale(
            array_values(array_unique($glossaryKeysIndexedByFieldName)),
            array_values($localeTransfers),
        );

        foreach ($localeTransfers as $localeTransfer) {
            $merchantProfileGlossaryAttributeValues->append(
                $this->addGlossaryAttributesByLocale($glossaryKeysIndexedByFieldName, $localeTransfer, $activeTranslationValues),
            );
        }

        $merchantProfileTransfer->setMerchantProfileLocalizedGlossaryAttributes($merchantProfileGlossaryAttributeValues);

        return $merchantProfileTransfer;
    }

    /**
     * @param array<string, string> $glossaryKeysIndexedByFieldName
     * @param array<string, array<int, string|null>> $activeTranslationValues
     */
    protected function addGlossaryAttributesByLocale(
        array $glossaryKeysIndexedByFieldName,
        LocaleTransfer $localeTransfer,
        array $activeTranslationValues
    ): MerchantProfileLocalizedGlossaryAttributesTransfer {
        $merchantProfileLocalizedGlossaryAttributesTransfer = new MerchantProfileLocalizedGlossaryAttributesTransfer();
        $merchantProfileLocalizedGlossaryAttributesTransfer->setLocale($localeTransfer);
        $merchantProfileLocalizedGlossaryAttributesTransfer->setMerchantProfileGlossaryAttributeValues(
            $this->addGlossaryAttributeTranslations($glossaryKeysIndexedByFieldName, $localeTransfer, $activeTranslationValues),
        );

        return $merchantProfileLocalizedGlossaryAttributesTransfer;
    }

    /**
     * @param array<string, string> $glossaryKeysIndexedByFieldName
     * @param array<string, array<int, string|null>> $activeTranslationValues
     */
    protected function addGlossaryAttributeTranslations(
        array $glossaryKeysIndexedByFieldName,
        LocaleTransfer $localeTransfer,
        array $activeTranslationValues
    ): MerchantProfileGlossaryAttributeValuesTransfer {
        $merchantProfileGlossaryAttributeValuesData = [];
        foreach ($glossaryKeysIndexedByFieldName as $fieldName => $glossaryKey) {
            $merchantProfileGlossaryAttributeValuesData[$fieldName] = $activeTranslationValues[$glossaryKey][$localeTransfer->getIdLocaleOrFail()] ?? null;
        }

        return (new MerchantProfileGlossaryAttributeValuesTransfer())->fromArray($merchantProfileGlossaryAttributeValuesData);
    }

    /**
     * @return array<string, string>
     */
    protected function extractGlossaryKeysIndexedByFieldName(MerchantProfileTransfer $merchantProfileTransfer): array
    {
        $merchantProfileData = $merchantProfileTransfer->toArray(true, true);
        $glossaryKeysIndexedByFieldName = [];
        foreach (array_keys((new MerchantProfileGlossaryAttributeValuesTransfer())->toArray(true, true)) as $fieldName) {
            $glossaryKey = $merchantProfileData[$fieldName] ?? null;
            if (!$glossaryKey) {
                continue;
            }

            $glossaryKeysIndexedByFieldName[$fieldName] = $glossaryKey;
        }

        return $glossaryKeysIndexedByFieldName;
    }

    /**
     * @param array<string> $glossaryKeys
     * @param array<\Generated\Shared\Transfer\LocaleTransfer> $localeTransfers
     *
     * @return array<string, array<int, string|null>>
     */
    protected function getActiveTranslationValuesIndexedByGlossaryKeyAndIdLocale(array $glossaryKeys, array $localeTransfers): array
    {
        if ($glossaryKeys === [] || $localeTransfers === []) {
            return [];
        }

        $activeTranslationValues = [];
        foreach ($this->glossaryFacade->getTranslationsByGlossaryKeysAndLocaleTransfers($glossaryKeys, $localeTransfers) as $translationTransfer) {
            if (!$translationTransfer->getIsActive()) {
                continue;
            }

            $activeTranslationValues[$translationTransfer->getGlossaryKeyOrFail()->getKeyOrFail()][$translationTransfer->getFkLocaleOrFail()] = $translationTransfer->getValue();
        }

        return $activeTranslationValues;
    }
}
