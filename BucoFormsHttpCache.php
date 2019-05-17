<?php

namespace BucoFormsHttpCache;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class BucoFormsHttpCache extends Plugin
{
    const CONTROLLER_NAME = 'frontend/forms';
    const CACHE_KEY = 'bucoF';

    public function install(InstallContext $context)
    {
        $messages = [];
        $messages[] = "{$this->fixLinksToFormsInternalUrl()} links to Shopware's internal form URL has been updated.";

        $context->scheduleMessage(implode('<br>', $messages));
        $context->scheduleClearCache([InstallContext::CACHE_TAG_PROXY]);
    }

    public function activate(ActivateContext $context)
    {
        $this->container->get('shopware.http_cache.route_installer')->addHttpCacheRoute(
            self::CONTROLLER_NAME,
            60*60*4
        );

        $context->scheduleClearCache([InstallContext::CACHE_TAG_CONFIG]);
    }

    public function deactivate(DeactivateContext $context)
    {
        $this->container->get('shopware.http_cache.route_installer')->removeHttpCacheRoute(self::CONTROLLER_NAME);
        $context->scheduleClearCache([InstallContext::CACHE_TAG_CONFIG, InstallContext::CACHE_TAG_HTTP]);
    }

    public function uninstall(UninstallContext $context)
    {
        $context->scheduleClearCache([]);
    }

    /**
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fixLinksToFormsInternalUrl() : int
    {
        $dbal = $this->container->get('dbal_connection');
        $count = 0;

        $count += $dbal->executeUpdate(<<<'SQL'
            UPDATE s_core_rewrite_urls
            SET org_path = REPLACE(org_path, 'iewport=ticket&', 'iewport=forms&')
            WHERE org_path LIKE LOWER('sViewport=ticket&%id=%'); -- catch case insensitive viewport and params id and sFid
SQL
        );

        $count += $dbal->executeUpdate(<<<'SQL'
            UPDATE s_categories
            SET external = REPLACE(external, 'iewport=ticket&', 'iewport=forms&')
            WHERE external LIKE LOWER('sViewport=ticket&%id=%') OR external LIKE LOWER('shopware.php?sViewport=ticket&%id=%');
SQL
        );

        $count += $dbal->executeUpdate(<<<'SQL'
            UPDATE s_cms_static
            SET link = REPLACE(link, 'iewport=ticket&', 'iewport=forms&')
            WHERE link LIKE LOWER('sViewport=ticket&%id=%') OR link LIKE LOWER('shopware.php?sViewport=ticket&%id=%');
SQL
        );

        return $count;
    }
}