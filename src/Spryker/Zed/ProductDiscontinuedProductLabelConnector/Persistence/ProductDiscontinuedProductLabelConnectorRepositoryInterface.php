<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductDiscontinuedProductLabelConnector\Persistence;

interface ProductDiscontinuedProductLabelConnectorRepositoryInterface
{
    /**
     * @return array<int>
     */
    public function getProductAbstractIdsToBeLabeled(): array;
}
