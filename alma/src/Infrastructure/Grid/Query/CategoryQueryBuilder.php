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

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     * @param int $contextLangId
     * @param int $contextShopId
     */
    public function __construct(Connection $connection, $dbPrefix, int $contextLangId, int $contextShopId)
    {
        parent::__construct($connection, $dbPrefix);

        $this->contextLangId = $contextLangId;
        $this->contextShopId = $contextShopId;
    }

    // Get Search query builder returns a QueryBuilder that is used to fetch filtered, sorted and paginated data from the database.
    // This query builder is also used to get the SQL query that was executed.
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery();
        $qb->select('c.id_category, cl.name, cl.description, c.active')
            ->orderBy(
                $searchCriteria->getOrderBy(),
                $searchCriteria->getOrderWay()
            )
            ->setFirstResult($searchCriteria->getOffset())
            ->setMaxResults($searchCriteria->getLimit());

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
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

    // Get Count query builder that is used to get the total count of all records (products)
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery();
        $qb->select('COUNT(c.id_category)');

        return $qb;
    }

    // Base query can be used for both Search and Count query builders
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
            ->setParameter('context_lang_id', $this->contextLangId)
            ->setParameter('context_shop_id', $this->contextShopId);
    }
}
