<?php

namespace PrestaShop\Module\Alma\Infrastructure\Grid\Data;

use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class CategoryGridDataFactory implements GridDataFactoryInterface
{
    private GridDataFactoryInterface $decorated;

    public function __construct(
        GridDataFactoryInterface $decorated
    ) {
        $this->decorated = $decorated;
    }

    public function getData(SearchCriteriaInterface $searchCriteria): GridData
    {
        $gridData = $this->decorated->getData($searchCriteria);
        $records = $gridData->getRecords()->all();

        foreach ($records as &$record) {
            if (isset($record['description'])) {
                $record['description'] = strip_tags($record['description']);
            }
        }

        return new GridData(
            new RecordCollection($records),
            $gridData->getRecordsTotal(),
            $gridData->getQuery()
        );
    }
}
