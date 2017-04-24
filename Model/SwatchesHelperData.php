<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoEse\InstallationOverrides\Model;

use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class SwatchesHelperData extends \Magento\Swatches\Helper\Data
{


    /**
     * Load Variation Product using fallback
     *
     * @param Product $parentProduct
     * @param array $attributes
     * @return bool|Product
     */
    public function loadVariationByFallback(Product $parentProduct, array $attributes)
    {
        if (! $this->isProductHasSwatch($parentProduct)) {
            return false;
        }

        $productCollection = $this->productCollectionFactory->create();
        //$this->addFilterByParent($productCollection, $parentProduct->getId());
        $this->addFilterByParent($productCollection, $parentProduct->getRowId());

        $configurableAttributes = $this->getAttributesFromConfigurable($parentProduct);
        $allAttributesArray = [];
        foreach ($configurableAttributes as $attribute) {
            $allAttributesArray[$attribute['attribute_code']] = $attribute['default_value'];
        }
        $resultAttributesToFilter = array_merge(
            $attributes,
            array_diff_key($allAttributesArray, $attributes)
        );

        $this->addFilterByAttributes($productCollection, $resultAttributesToFilter);

        $variationProduct = $productCollection->getFirstItem();
        if ($variationProduct && $variationProduct->getId()) {
            return $this->productRepository->getById($variationProduct->getId());
        }

        return false;
    }

    /**
     * @param ProductCollection $productCollection
     * @param integer $parentId
     * @return void
     */
    private function addFilterByParent(ProductCollection $productCollection, $parentId)
    {
        $tableProductRelation = $productCollection->getTable('catalog_product_relation');
        $productCollection
            ->getSelect()
            ->join(
                ['pr' => $tableProductRelation],
                'e.entity_id = pr.child_id'
            )
            ->where('pr.parent_id = ?', $parentId);
    }

    /**
     * @param ProductCollection $productCollection
     * @param array $attributes
     * @return void
     */
    private function addFilterByAttributes(ProductCollection $productCollection, array $attributes)
    {
        foreach ($attributes as $code => $option) {
            $productCollection->addAttributeToFilter($code, ['eq' => $option]);
        }
    }

}
