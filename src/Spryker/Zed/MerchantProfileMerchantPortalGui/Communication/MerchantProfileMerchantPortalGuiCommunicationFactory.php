<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfileMerchantPortalGui\Communication;

use Generated\Shared\Transfer\MerchantTransfer;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\Constraint\UniqueUrl;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider\MerchantProfileAddressFormDataProvider;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider\MerchantProfileAddressFormDataProviderInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider\MerchantProfileFormDataProvider;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\DataProvider\MerchantProfileFormDataProviderInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Communication\Form\MerchantProfileForm;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToCountryFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToLocaleFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToMerchantFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToMerchantUserFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\Dependency\Facade\MerchantProfileMerchantPortalGuiToUrlFacadeInterface;
use Spryker\Zed\MerchantProfileMerchantPortalGui\MerchantProfileMerchantPortalGuiDependencyProvider;
use Symfony\Component\Form\FormInterface;

/**
 * @method \Spryker\Zed\MerchantProfileMerchantPortalGui\MerchantProfileMerchantPortalGuiConfig getConfig()
 */
class MerchantProfileMerchantPortalGuiCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer|null $data
     * @param array<string, mixed> $options
     *
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    public function createMerchantProfileForm(?MerchantTransfer $data = null, array $options = []): FormInterface
    {
        return $this->getFormFactory()->create(MerchantProfileForm::class, $data, $options);
    }

    public function createMerchantProfileFormDataProvider(): MerchantProfileFormDataProviderInterface
    {
        return new MerchantProfileFormDataProvider(
            $this->getConfig(),
            $this->getMerchantFacade(),
            $this->getGlossaryFacade(),
            $this->getLocaleFacade(),
        );
    }

    public function createMerchantProfileAddressFormDataProvider(): MerchantProfileAddressFormDataProviderInterface
    {
        return new MerchantProfileAddressFormDataProvider($this->getCountryFacade());
    }

    public function createUniqueUrlConstraint(): UniqueUrl
    {
        return new UniqueUrl();
    }

    public function getMerchantFacade(): MerchantProfileMerchantPortalGuiToMerchantFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileMerchantPortalGuiDependencyProvider::FACADE_MERCHANT);
    }

    public function getMerchantUserFacade(): MerchantProfileMerchantPortalGuiToMerchantUserFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileMerchantPortalGuiDependencyProvider::FACADE_MERCHANT_USER);
    }

    public function getGlossaryFacade(): MerchantProfileMerchantPortalGuiToGlossaryFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileMerchantPortalGuiDependencyProvider::FACADE_GLOSSARY);
    }

    public function getLocaleFacade(): MerchantProfileMerchantPortalGuiToLocaleFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileMerchantPortalGuiDependencyProvider::FACADE_LOCALE);
    }

    public function getUrlFacade(): MerchantProfileMerchantPortalGuiToUrlFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileMerchantPortalGuiDependencyProvider::FACADE_URL);
    }

    public function getCountryFacade(): MerchantProfileMerchantPortalGuiToCountryFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileMerchantPortalGuiDependencyProvider::FACADE_COUNTRY);
    }

    /**
     * @return array<\Spryker\Zed\MerchantProfileMerchantPortalGuiExtension\Dependency\Plugin\OnlineProfileMerchantProfileFormExpanderPluginInterface>
     */
    public function getOnlineProfileMerchantProfileFormExpanderPlugins(): array
    {
        return $this->getProvidedDependency(MerchantProfileMerchantPortalGuiDependencyProvider::PLUGINS_ONLINE_PROFILE_MERCHANT_PROFILE_FORM_EXPANDER);
    }
}
