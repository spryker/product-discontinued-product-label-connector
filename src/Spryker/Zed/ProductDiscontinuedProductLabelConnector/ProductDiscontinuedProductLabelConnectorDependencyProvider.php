<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductDiscontinuedProductLabelConnector;

use Orm\Zed\Product\Persistence\SpyProductQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToLocaleFacadeBridge;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductBridge;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductDiscontinuedFacadeBridge;
use Spryker\Zed\ProductDiscontinuedProductLabelConnector\Dependency\Facade\ProductDiscontinuedProductLabelConnectorToProductLabelBridge;

/**
 * @method \Spryker\Zed\ProductDiscontinuedProductLabelConnector\ProductDiscontinuedProductLabelConnectorConfig getConfig()
 */
class ProductDiscontinuedProductLabelConnectorDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_PRODUCT_LABEL = 'FACADE_PRODUCT_LABEL';

    /**
     * @var string
     */
    public const FACADE_PRODUCT_DISCONTINUED = 'FACADE_PRODUCT_DISCONTINUED';

    /**
     * @var string
     */
    public const FACADE_PRODUCT = 'FACADE_PRODUCT';

    /**
     * @var string
     */
    public const FACADE_LOCALE = 'FACADE_LOCALE';

    /**
     * @var string
     */
    public const PROPEL_QUERY_PRODUCT = 'PROPEL_QUERY_PRODUCT';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addProductLabelFacade($container);
        $container = $this->addProductFacade($container);
        $container = $this->addProductDiscontinuedFacade($container);
        $container = $this->addLocaleFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = parent::providePersistenceLayerDependencies($container);
        $container = $this->addProductPropelQuery($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_PRODUCT, $container->factory(function () {
            return SpyProductQuery::create();
        }));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductLabelFacade(Container $container): Container
    {
        $container->set(static::FACADE_PRODUCT_LABEL, function (Container $container) {
            return new ProductDiscontinuedProductLabelConnectorToProductLabelBridge(
                $container->getLocator()->productLabel()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductFacade(Container $container): Container
    {
        $container->set(static::FACADE_PRODUCT, function (Container $container) {
            return new ProductDiscontinuedProductLabelConnectorToProductBridge(
                $container->getLocator()->product()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductDiscontinuedFacade(Container $container): Container
    {
        $container->set(static::FACADE_PRODUCT_DISCONTINUED, function (Container $container) {
            return new ProductDiscontinuedProductLabelConnectorToProductDiscontinuedFacadeBridge(
                $container->getLocator()->productDiscontinued()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addLocaleFacade(Container $container): Container
    {
        $container->set(static::FACADE_LOCALE, function (Container $container) {
            return new ProductDiscontinuedProductLabelConnectorToLocaleFacadeBridge(
                $container->getLocator()->locale()->facade(),
            );
        });

        return $container;
    }
}
