<?php

namespace BucoFormsHttpCache\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\HttpCache\UrlProvider\UrlProviderInterface;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;

class HttpCacheFormsUrlProvider implements UrlProviderInterface
{
    const NAME = 'bucoForms';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(Connection $connection, RouterInterface $router)
    {
        $this->connection = $connection;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $context, $limit = null, $offset = null)
    {
        $qb = $this->getBaseQuery()
            ->addSelect(['id'])
            ->setParameter(':shop', $context->getShopId())
            ->orderBy('id');

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $result = $qb->execute()->fetchAll();

        if (!count($result)) {
            return [];
        }

        return $this->router->generateList(
            array_filter(array_map(
                function ($custom) {
                    return ['sViewport' => 'forms', 'sFid' => $custom['id']];
                },
                $result
            )),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context)
    {
        return (int) $this->getBaseQuery()
            ->addSelect(['COUNT(id)'])
            ->setParameter(':shop', $context->getShopId())
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        return $this->connection->createQueryBuilder()
            ->from('s_cms_support')
            ->where('active = 1')
            ->andWhere('shop_ids IS NULL OR shop_ids LIKE CONCAT("%|",:shop,"|%")');
    }
}
