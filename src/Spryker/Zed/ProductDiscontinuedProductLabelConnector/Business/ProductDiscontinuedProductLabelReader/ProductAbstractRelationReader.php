<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductDiscontinuedProductLabelConnector\Business\ProductDiscontinuedProductLabelReader;

use Generated\Shared\Transfer\ProductLabelProductAbstractRelationsTransfer;
use Orm\Zed\ProductLabel\Persistence\SpyProductLabel;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductDiscontinuedFacadeInterface;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductInterface;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductLabelInterface;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Persistence\ProductDiscontinuedProductLabelConnectorRepositoryInterface;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\ProductDiscontinuedProductLabelConnectorConfig;

class ProductAbstractRelationReader implements ProductAbstractRelationReaderInterface
{
    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductInterface $productFacade
     */
    protected $productFacade;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductLabelInterface $productLabelFacade
     */
    protected $productLabelFacade;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductDiscontinuedFacadeInterface $productDiscontinuedFacade
     */
    protected $productDiscontinuedFacade;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Persistence\ProductDiscontinuedProductLabelConnectorRepositoryInterface $productDiscontinuedProductLabelConnectorRepository
     */
    protected $productDiscontinuedProductLabelConnectorRepository;

    /**
     * @var \Spryker\Zed\ProductDiscontinuedProductLabelConnector\ProductDiscontinuedProductLabelConnectorConfig $config
     */
    protected $config;

    /**
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductInterface $productFacade
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductLabelInterface $productLabelFacade
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductDiscontinuedFacadeInterface $productDiscontinuedFacade
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\Persistence\ProductDiscontinuedProductLabelConnectorRepositoryInterface $productDiscontinuedProductLabelConnectorRepository
     * @param \Spryker\Zed\ProductDiscontinuedProductLabelConnector\ProductDiscontinuedProductLabelConnectorConfig $config
     */
    public function __construct(
        ProductDiscontinuedProductLabelConnectorToProductInterface $productFacade,
        ProductDiscontinuedProductLabelConnectorToProductLabelInterface $productLabelFacade,
        ProductDiscontinuedProductLabelConnectorToProductDiscontinuedFacadeInterface $productDiscontinuedFacade,
        ProductDiscontinuedProductLabelConnectorRepositoryInterface $productDiscontinuedProductLabelConnectorRepository,
        ProductDiscontinuedProductLabelConnectorConfig $config
    )
    {
        $this->productFacade = $productFacade;
        $this->productLabelFacade = $productLabelFacade;
        $this->productDiscontinuedFacade = $productDiscontinuedFacade;
        $this->productDiscontinuedProductLabelConnectorRepository = $productDiscontinuedProductLabelConnectorRepository;
        $this->config = $config;
    }

    /**
     * @return \Generated\Shared\Transfer\ProductLabelProductAbstractRelationsTransfer[]
     */
    public function findProductLabelProductAbstractRelationChanges(): array
    {
        $productLabelDiscontinuedEntity = $this->getProductLabelDiscontinuedEntity();

        if (!$productLabelDiscontinuedEntity->getIsActive()) {
            return [];
        }

        $productIds = $this->productDiscontinuedProductLabelConnectorRepository->getProductConcreteIds();

        $idsToAssign = [];
        $idsToDeAssign = [];

        $idProductLabel = $this->productLabelFacade->findLabelByLabelName(
            $this->config->getProductDiscontinueLabelName()
        )->getIdProductLabel();

        foreach ($productIds as $idProduct) {
            $idProductAbstract = $this->productFacade->getProductAbstractIdByConcreteId($idProduct);
            $concreteIds = [];

            foreach ($this->productFacade->getConcreteProductsByAbstractProductId($idProductAbstract) as $productConcreteTransfer) {
                $concreteIds[] = $productConcreteTransfer->getIdProductConcrete();
            }

            if ($this->productDiscontinuedFacade->areAllConcreteProductsDiscontinued($concreteIds)) {
                if (!in_array($idProductLabel, $this->productLabelFacade->findActiveLabelIdsByIdProductAbstract($idProductAbstract))
                    && !in_array($idProductAbstract, $idsToAssign)
                ) {
                    $idsToAssign[] = $idProductAbstract;
                }
            } else {
                $idsToDeAssign[] = $idProductAbstract;
            }
        }

        $result[] = $this->mapRelationTransfer($idProductLabel, $idsToAssign, $idsToDeAssign);

        return $result;
    }

    /**
     * @return null|\Orm\Zed\ProductLabel\Persistence\SpyProductLabel
     */
    protected function getProductLabelDiscontinuedEntity(): ?SpyProductLabel
    {
        $labelDiscontinuedName = $this->config->getProductDiscontinueLabelName();
        $productLabelDiscontinuedEntity = $this->productDiscontinuedProductLabelConnectorRepository
            ->findProductLabelByName($labelDiscontinuedName);

        if (!$productLabelDiscontinuedEntity) {
            return null;
        }

        return $productLabelDiscontinuedEntity;
    }

    /**
     * @param int $idProductLabel
     * @param array $idToAssign
     * @param array $idsToDeAssign
     *
     * @return \Generated\Shared\Transfer\ProductLabelProductAbstractRelationsTransfer
     */
    protected function mapRelationTransfer(
        int $idProductLabel,
        array $idToAssign,
        array $idsToDeAssign
    ): ProductLabelProductAbstractRelationsTransfer
    {
        $productLabelProductAbstractRelationsTransfer = new ProductLabelProductAbstractRelationsTransfer();
        $productLabelProductAbstractRelationsTransfer->setIdProductLabel($idProductLabel);

        if (!empty($idToAssign)) {
            $productLabelProductAbstractRelationsTransfer->setIdsProductAbstractToAssign($idToAssign);
        }

        if (!empty($idsToDeAssign)) {
            $productLabelProductAbstractRelationsTransfer->setIdsProductAbstractToDeAssign($idsToDeAssign);
        }

        return $productLabelProductAbstractRelationsTransfer;
    }
}
