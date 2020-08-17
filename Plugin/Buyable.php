<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 *
 * This overrides the plugin vendor/magento/module-catalog-inventory-data-exporter/Model/Plugin/Buyable.php
 * to only execute the query to determine if a product is buyable IF there is a Production API Key defined
 *
 */
declare(strict_types=1);

namespace MagentoEse\InstallationOverrides\Plugin;

use Magento\CatalogDataExporter\Model\Provider\Product\Buyable as ProductBuyable;
use Magento\CatalogInventoryDataExporter\Model\Query\CatalogInventoryQuery;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Buyable
 *
 */
class Buyable
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CatalogInventoryQuery
     */
    private $catalogInventoryQuery;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * Buyable constructor.
     * @param ResourceConnection $resourceConnection
     * @param CatalogInventoryQuery $catalogInventoryQuery
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CatalogInventoryQuery $catalogInventoryQuery,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->catalogInventoryQuery = $catalogInventoryQuery;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $row
     * @return array
     */
    private function format(array $row): array
    {
        $output = [
            'productId' => $row['product_id'],
            'storeViewCode' => $row['storeViewCode'],
            'inStock' => $row['is_in_stock'],
        ];
        return $output;
    }

    public function afterGet(ProductBuyable $subject, array $result)
    {
        if($this->scopeConfig->getValue('services_connector/services_connector_integration/production_api_key','default')) {
            $connection = $this->resourceConnection->getConnection();
            $queryArguments = [];
            try {
                $output = [];
                foreach ($result as $value) {
                    $queryArguments['productId'][$value['productId']] = $value['productId'];
                    $queryArguments['storeViewCode'][$value['storeViewCode']] = $value['storeViewCode'];
                }
                $select = $this->catalogInventoryQuery->getInStock($queryArguments);
                $cursor = $connection->query($select);
                $outOfStock = [];
                while ($row = $cursor->fetch()) {
                    if ($row['is_in_stock'] == 0) {
                        $outOfStock[$row['product_id']] = false;
                    }
                }
                foreach ($result as &$item) {
                    if (isset($outOfStock[$item['productId']])) {
                        $item['buyable'] = false;
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                throw new UnableRetrieveData(__('Unable to retrieve stock data'));
            }
        }
        return $result;
    }
}
