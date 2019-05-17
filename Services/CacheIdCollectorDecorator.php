<?php

namespace BucoFormsHttpCache\Services;

use BucoFormsHttpCache\BucoFormsHttpCache;
use Enlight_Controller_Action as Controller;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use ShopwarePlugins\HttpCache\CacheIdCollector;

class CacheIdCollectorDecorator
{
    /** @var CacheIdCollector */
    private $coreService;

    public function __construct($coreService)
    {
        $this->coreService = $coreService;
    }

    /**
     * Returns an array of affected cache ids for this $controller
     *
     * @param Controller $controller
     * @param ShopContextInterface $context
     *
     * @return string[]
     */
    public function getCacheIdsFromController(Controller $controller, ShopContextInterface $context) : array
    {
        $request = $controller->Request();
        $controllerName = $this->getControllerRoute($request);

        switch ($controllerName) {
            case BucoFormsHttpCache::CONTROLLER_NAME:
                return $this->getFormsCacheIds($request);

            default:
                return $this->coreService->getCacheIdsFromController($controller, $context);
        }
    }

    /**
     * @param Request $request
     *
     * @return string[]
     */
    private function getFormsCacheIds(Request $request) : array
    {
        $formsId = $request->getParam('sFid');
        $formsId = $formsId ?: $request->getParam('id');

        return [BucoFormsHttpCache::CACHE_KEY . (int) $formsId];
    }

    private function getControllerRoute(Request $request) : string
    {
        return implode('/', [
            strtolower($request->getModuleName()),
            strtolower($request->getControllerName()),
        ]);
    }
}