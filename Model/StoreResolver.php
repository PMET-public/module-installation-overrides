<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoEse\InstallationOverrides\Model;


/**
 * Prepare product collection for suggestions functionality.
 */
class StoreResolver extends \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
{
    public function getStoreCodeToId($code = null)
    {
        if (empty($this->storeCodeToId)) {
            $this->_initStores();
        }
        if ($code) {
            if(!isset($this->storeCodeToId[$code])){
                $this->_initStores();
            }
            return $this->storeCodeToId[$code] ??  null;
        }
        return $this->storeCodeToId;
    }

}
