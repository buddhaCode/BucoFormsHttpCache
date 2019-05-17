<?php

namespace BucoFormsHttpCache\Subscriber;

use BucoFormsHttpCache\BucoFormsHttpCache;
use BucoFormsHttpCache\Services\CacheIdCollectorDecorator;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Form\Field;
use Shopware\Models\Form\Form;

class HttpCache implements SubscriberInterface
{
    /**
     * @var \Enlight_Event_EventManager
     */
    private $events;

    public function __construct(\Enlight_Event_EventManager $events)
    {
        $this->events = $events;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            'Shopware\Models\Form\Form::postPersist' => 'onFormsChange',
            'Shopware\Models\Form\Form::postUpdate' => 'onFormsChange',
            'Shopware\Models\Form\Form::postRemove' => 'onFormsChange',
            'Shopware\Models\Form\Field::postUpdate' => 'onFormsChange',
            'Shopware\Models\Form\Field::postPersist' => 'onFormsChange',
            'Shopware\Models\Form\Field::postRemove' => 'onFormsChange',

            'Enlight_Bootstrap_AfterInitResource_http_cache.cache_id_collector' => 'decorateCacheIdCollector',
        ];
    }

    public function decorateCacheIdCollector(\Enlight_Event_EventArgs $args)
    {
        /** @var Container $dic */
        $dic = $args->getSubject();
        $serviceName = 'http_cache.cache_id_collector';

        $coreService = $dic->get($serviceName);
        $dic->set($serviceName, new CacheIdCollectorDecorator($coreService));
    }

    public function onFormsChange(\Enlight_Event_EventArgs $args)
    {
        /** @var Form|Field $entity */
        $entity = $args->get('entity');

        if($entity instanceof Field) {
            $id = $entity->getFormId();
        }
        elseif ($entity instanceof Form) {
            $id = $entity->getId();
        }
        else {
            return;
        }

        $this->events->notify(
            'Shopware_Plugins_HttpCache_InvalidateCacheId',
            ['cacheId' => BucoFormsHttpCache::CACHE_KEY . $id]
        );
    }
}