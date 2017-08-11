<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoEse\InstallationOverrides\Model;

class CategoryProcessorInit extends \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
{

    public function runInit(){
        //$this->initCategories();
        $collection = $this->categoryColFactory->create();
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        foreach ($collection as $category) {
            $structure = explode(self::DELIMITER_CATEGORY, $category->getPath());
            $pathSize = count($structure);

            $this->categoriesCache[$category->getId()] = $category;
            if ($pathSize > 1) {
                $path = [];
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $collection->getItemById((int)$structure[$i])->getName();
                }
                /** @var string $index */
                $index = $this->standardizeString(
                    implode(self::DELIMITER_CATEGORY, $path)
                );
                $this->categories[$index] = $category->getId();
            }
        }
   }
    private function standardizeString($string)
    {
        return mb_strtolower($string);
    }

}
