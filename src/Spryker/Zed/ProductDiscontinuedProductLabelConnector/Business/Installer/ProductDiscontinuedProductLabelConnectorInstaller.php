<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductDiscontinuedProductLabelConnector\Business\Installer;

use Generated\Shared\Transfer\ProductLabelLocalizedAttributesTransfer;
use Generated\Shared\Transfer\ProductLabelTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToLocaleFacadeInterface;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductLabelFacadeInterface;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToStoreFacadeInterface;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\ProductDiscontinuedProductLabelConnectorConfig;

class ProductDiscontinuedProductLabelConnectorInstaller implements ProductDiscontinuedProductLabelConnectorInstallerInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\ProductDiscontinuedProductLabelConnectorConfig
     */
    protected $config;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductLabelFacadeInterface
     */
    protected $productLabelFacade;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToLocaleFacadeInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToStoreFacadeInterface
     */
    protected ProductDiscontinuedProductLabelConnectorToStoreFacadeInterface $storeFacade;

    /**
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\ProductDiscontinuedProductLabelConnectorConfig $config
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductLabelFacadeInterface $productLabelFacade
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToLocaleFacadeInterface $localeFacade
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        ProductDiscontinuedProductLabelConnectorConfig $config,
        ProductDiscontinuedProductLabelConnectorToProductLabelFacadeInterface $productLabelFacade,
        ProductDiscontinuedProductLabelConnectorToLocaleFacadeInterface $localeFacade,
        ProductDiscontinuedProductLabelConnectorToStoreFacadeInterface $storeFacade
    ) {
        $this->config = $config;
        $this->productLabelFacade = $productLabelFacade;
        $this->localeFacade = $localeFacade;
        $this->storeFacade = $storeFacade;
    }

    /**
     * @return void
     */
    public function install(): void
    {
        $this->getTransactionHandler()->handleTransaction(function () {
            $this->executeInstallTransaction();
        });
    }

    /**
     * @return void
     */
    protected function executeInstallTransaction(): void
    {
        if (!$this->productLabelFacade->findLabelByLabelName($this->config->getProductDiscontinueLabelName())) {
            $productLabelTransfer = new ProductLabelTransfer();
            $productLabelTransfer->setName($this->config->getProductDiscontinueLabelName());
            $productLabelTransfer->setFrontEndReference($this->config->getProductDiscontinueLabelFrontEndReference());
            $productLabelTransfer->setPosition($this->config->getProductLabelDefaultPosition());

            $this->addDataToProductLabelTransfer($productLabelTransfer);
            $this->productLabelFacade->createLabel(
                $productLabelTransfer,
            );
        }

        $productLabelTransfer = $this->productLabelFacade->findLabelByLabelName($this->config->getProductDiscontinueLabelName());

        if ($productLabelTransfer) {
            $productLabelTransfer->setFrontEndReference($this->config->getProductDiscontinueLabelFrontEndReference());
            $this->addDataToProductLabelTransfer($productLabelTransfer);
            $this->productLabelFacade->updateLabel($productLabelTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ProductLabelTransfer $productLabelTransfer
     *
     * @return \Generated\Shared\Transfer\ProductLabelTransfer
     */
    protected function addDataToProductLabelTransfer(ProductLabelTransfer $productLabelTransfer): ProductLabelTransfer
    {
        $productLabelTransfer
            ->setIsActive(true)
            ->setIsExclusive(false)
            ->setIsDynamic(true)
            ->setIsPublished(true);

        if ($this->storeFacade->isDynamicStoreEnabled() === true) {
            return $productLabelTransfer;
        }

        foreach ($this->localeFacade->getLocaleCollection() as $localeTransfer) {
            $localizedAttributesTransfer = new ProductLabelLocalizedAttributesTransfer();
            $localizedAttributesTransfer->setFkLocale($localeTransfer->getIdLocale());
            $localizedAttributesTransfer->setFkProductLabel($productLabelTransfer->getIdProductLabel());
            $localizedAttributesTransfer->setLocale($localeTransfer);
            $localizedAttributesTransfer->setName($this->config->getProductDiscontinueLabelName());

            $productLabelTransfer->addLocalizedAttributes($localizedAttributesTransfer);
        }

        return $productLabelTransfer;
    }
}
