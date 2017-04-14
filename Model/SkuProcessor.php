<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoEse\InstallationOverrides\Model;


/**
 * Prepare product collection for suggestions functionality.
 */
class SkuProcessor extends \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor
{
    public function getOldSkus()
    {
        if (!$this->oldSkus || $this->newSkus) {
            $this->oldSkus = $this->_getSkus();
        }
        return $this->oldSkus;
    }
}
