<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductDiscontinuedProductLabelConnector;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class ProductDiscontinuedProductLabelConnectorConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    protected const PRODUCT_DISCONTINUE_LABEL_NAME = 'Discontinued';

    /**
     * @var string
     */
    protected const PRODUCT_DISCONTINUE_LABEL_FRONT_END_REFERENCE = 'discontinued';

    /**
     * @var int
     */
    protected const PRODUCT_LABEL_DEFAULT_POSITION = 0;

    /**
     * Specification:
     * - Returns product discountinue label name.
     *
     * @api
     *
     * @return string
     */
    public function getProductDiscontinueLabelName(): string
    {
        return static::PRODUCT_DISCONTINUE_LABEL_NAME;
    }

    /**
     * Specification:
     * - Returns frontend reference of product discountinue label.
     *
     * @api
     *
     * @return string
     */
    public function getProductDiscontinueLabelFrontEndReference(): string
    {
        return static::PRODUCT_DISCONTINUE_LABEL_FRONT_END_REFERENCE;
    }

    /**
     * Specification:
     * - Returns default position for product label.
     *
     * @api
     *
     * @return int
     */
    public function getProductLabelDefaultPosition(): int
    {
        return static::PRODUCT_LABEL_DEFAULT_POSITION;
    }
}
