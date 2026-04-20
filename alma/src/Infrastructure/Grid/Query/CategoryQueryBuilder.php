<?php

namespace PrestaShop\Module\Alma\Infrastructure\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class CategoryQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var int
     */
    private int $contextLangId;

    /**
     * @var int
     */
    private int $contextShopId;
    private array $excludedCategoryIds;

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     * @param int $contextLangId
     * @param int $contextShopId
     * @param array $excludedCategoryIds
     */
    public function __construct(
        Connection $connection,
        $dbPrefix,
        int $contextLangId,
        int $contextShopId,
        array $excludedCategoryIds = []
    ) {
        parent::__construct($connection, $dbPrefix);

        $this->contextLangId = $contextLangId;
        $this->contextShopId = $contextShopId;
        $this->excludedCategoryIds = $excludedCategoryIds;
    }

    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery();

        $qb->select('c.id_category, cl.name, cl.description, c.active')
            ->orderBy(
                $searchCriteria->getOrderBy() ?: 'c.id_category',
                $searchCriteria->getOrderWay() ?: 'ASC'
            )
            ->setFirstResult($searchCriteria->getOffset())
            ->setMaxResults($searchCriteria->getLimit());

        if (!empty($this->excludedCategoryIds)) {
            $qb->addSelect(
                'CASE WHEN c.id_category IN (:excluded_category_ids) THEN 1 ELSE 0 END as is_excluded'
            );
            $qb->setParameter('excluded_category_ids', $this->excludedCategoryIds, Connection::PARAM_INT_ARRAY);
        } else {
            $qb->addSelect('0 as is_excluded');
        }

        foreach ($searchCriteria->getFilters() ?? [] as $filterName => $filterValue) {
            if ('id_category' === $filterName) {
                $qb->andWhere("c.id_category = :$filterName");
                $qb->setParameter($filterName, $filterValue);

                continue;
            }

            $qb->andWhere("$filterName LIKE :$filterName");
            $qb->setParameter($filterName, '%' . $filterValue . '%');
        }

        return $qb;
    }

    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery();
        $qb->select('COUNT(c.id_category)');

        return $qb;
    }

    private function getBaseQuery(): QueryBuilder
    {
        return $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'category', 'c')
            ->leftJoin(
                'c',
                $this->dbPrefix . 'category_lang',
                'cl',
                'c.id_category = cl.id_category AND cl.id_lang = :context_lang_id AND cl.id_shop = :context_shop_id'
            )
            ->innerJoin(
                'c',
                $this->dbPrefix . 'category_shop',
                'cs',
                'c.id_category = cs.id_category AND cs.id_shop = :context_shop_id'
            )
            ->setParameter('context_lang_id', $this->contextLangId)
            ->setParameter('context_shop_id', $this->contextShopId);
    }
}
