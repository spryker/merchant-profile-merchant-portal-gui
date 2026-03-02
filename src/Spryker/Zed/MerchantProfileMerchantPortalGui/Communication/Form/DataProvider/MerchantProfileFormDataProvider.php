<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider;

use ArrayObject;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\MerchantCriteriaTransfer;
use Generated\Shared\Transfer\MerchantProfileGlossaryAttributeValuesTransfer;
use Generated\Shared\Transfer\MerchantProfileLocalizedGlossaryAttributesTransfer;
use Generated\Shared\Transfer\MerchantProfileTransfer;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToLocaleFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToMerchantFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\MerchantProfileMerchantPortalGuiConfig;

class MerchantProfileFormDataProvider implements MerchantProfileFormDataProviderInterface
{
    /**
     * @var \Spryker\Zed\MerchantProfileMerchantPortalGui\MerchantProfileMerchantPortalGuiConfig
     */
    protected MerchantProfileMerchantPortalGuiConfig $merchantProfileMerchantPortalGuiConfig;

    /**
     * @var \Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToMerchantFacadeInterface
     */
    protected MerchantProfileMerchantPortalGuiToMerchantFacadeInterface $merchantFacade;

    /**
     * @var \Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface
     */
    protected MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface $glossaryFacade;

    /**
     * @var \Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToLocaleFacadeInterface
     */
    protected MerchantProfileMerchantPortalGuiToLocaleFacadeInterface $localeFacade;

    public function __construct(
        MerchantProfileMerchantPortalGuiConfig $merchantProfileMerchantPortalGuiConfig,
        MerchantProfileMerchantPortalGuiToMerchantFacadeInterface $merchantFacade,
        MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface $glossaryFacade,
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
        $merchantProfileTransfer = $this->addLocalizedGlossaryAttributes($merchantProfileTransfer);

        $merchantTransfer->setMerchantProfile($merchantProfileTransfer);

        return $merchantTransfer;
    }

    protected function addInitialUrlCollection(MerchantTransfer $merchantTransfer): MerchantTransfer
    {
        $merchantProfileUrlCollection = $merchantTransfer->getUrlCollection();
        $urlCollection = new ArrayObject();
        $availableLocaleTransfers = $this->localeFacade->getLocaleCollection();

        foreach ($availableLocaleTransfers as $localeTransfer) {
            $urlCollection->append(
                $this->addUrlPrefixToUrlTransfer($merchantProfileUrlCollection, $localeTransfer),
            );
        }
        $merchantTransfer->setUrlCollection($urlCollection);

        return $merchantTransfer;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\UrlTransfer> $merchantProfileUrlCollection
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
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

    protected function addLocalizedGlossaryAttributes(MerchantProfileTransfer $merchantProfileTransfer): MerchantProfileTransfer
    {
        $merchantProfileGlossaryAttributeValues = new ArrayObject();
        $localeTransfers = $this->localeFacade->getLocaleCollection();
        foreach ($localeTransfers as $localeTransfer) {
            $merchantProfileGlossaryAttributeValues->append(
                $this->addGlossaryAttributesByLocale($merchantProfileTransfer, $localeTransfer),
            );
        }

        $merchantProfileTransfer->setMerchantProfileLocalizedGlossaryAttributes($merchantProfileGlossaryAttributeValues);

        return $merchantProfileTransfer;
    }

    protected function addGlossaryAttributesByLocale(
        MerchantProfileTransfer $merchantProfileTransfer,
        LocaleTransfer $localeTransfer
    ): MerchantProfileLocalizedGlossaryAttributesTransfer {
        $merchantProfileLocalizedGlossaryAttributesTransfer = new MerchantProfileLocalizedGlossaryAttributesTransfer();
        $merchantProfileLocalizedGlossaryAttributesTransfer->setLocale($localeTransfer);
        $merchantProfileLocalizedGlossaryAttributesTransfer->setMerchantProfileGlossaryAttributeValues(
            $this->addGlossaryAttributeTranslations($merchantProfileTransfer, $localeTransfer),
        );

        return $merchantProfileLocalizedGlossaryAttributesTransfer;
    }

    protected function addGlossaryAttributeTranslations(
        MerchantProfileTransfer $merchantProfileTransfer,
        LocaleTransfer $localeTransfer
    ): MerchantProfileGlossaryAttributeValuesTransfer {
        $merchantProfileGlossaryAttributeValuesTransfer = new MerchantProfileGlossaryAttributeValuesTransfer();

        $merchantProfileGlossaryAttributeValuesData = $merchantProfileGlossaryAttributeValuesTransfer->toArray(true, true);
        $merchantProfileData = $merchantProfileTransfer->toArray(true, true);
        foreach ($merchantProfileGlossaryAttributeValuesData as $merchantProfileGlossaryAttributeFieldName => $glossaryAttributeValue) {
            $merchantProfileGlossaryKey = $merchantProfileData[$merchantProfileGlossaryAttributeFieldName];
            if (!$merchantProfileGlossaryKey) {
                continue;
            }

            $merchantProfileGlossaryAttributeValuesData[$merchantProfileGlossaryAttributeFieldName] = $this->getLocalizedTranslationValue($merchantProfileGlossaryKey, $localeTransfer);
        }

        $merchantProfileGlossaryAttributeValuesTransfer->fromArray($merchantProfileGlossaryAttributeValuesData);

        return $merchantProfileGlossaryAttributeValuesTransfer;
    }

    protected function getLocalizedTranslationValue(string $key, LocaleTransfer $localeTransfer): ?string
    {
        if ($this->glossaryFacade->hasTranslation($key, $localeTransfer) === false) {
            return null;
        }

        $translationTransfer = $this->glossaryFacade->getTranslation($key, $localeTransfer);

        return $translationTransfer->getIsActive() ? $translationTransfer->getValue() : null;
    }
}
