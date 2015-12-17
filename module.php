<?php
namespace Hoteles;

use ZF\Apigility\Provider\ApigilityProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements ApigilityProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
    
    private function getCacheKey($route, $params, $query)
    {
        $cacheKey = str_replace('.','+', $route);
        unset($params['controller']);
        unset($params['action']);
        $cacheKey .= md5(json_encode($params));
        $cacheKey .= md5(json_encode($query));
        return $cacheKey;
    }
    
    public function onBootStrap(MvcEvent $e)
    {
        $routes = array(
            'hoteles.rest.estado',
            'hoteles.rest.hotel',
            'hoteles.rest.vista',
            'hoteles.rest.hotel-rooms',
            'hoteles.rest.hotel-facilities',
            'hoteles.rest.hotel-services',
            'hoteles.rest.hotel-activities',
            'hoteles.rest.hotel-fotos',
        );
        
        $em = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $config = $this->getConfig();
        
        $em->attach(
            MvcEvent::EVENT_ROUTE,
            function ($e) use ($serviceManager, $routes, $config) {
               
                $route = $e->getRouteMatch()->getMatchedRouteName();
                
               
                if (!in_array($route, $routes)) {
                    return;
                }
                $request = $e->getRequest();
                if ($request->getMethod() <> 'GET') {
                    return;
                }
                $params = $e->getRouteMatch()->getParams();
                $query  = $request->getQuery();
                $key = $this->getCacheKey($route, $params, $query);
                $cache = $serviceManager->get('fscache');
                if ($cache->hasItem($key)) {
                    $response = $e->getResponse();
                    $response->setContent($cache->getItem($key));
                    return $response;
                }
            }, -10000
            );
        
        $em->attach(
            MvcEvent::EVENT_RENDER,
            function ($e) use ($serviceManager, $routes) {
                $route = $e->getRouteMatch()->getMatchedRouteName();
                if (!in_array($route, $routes)) {
                    return ;
                }
                $response = $e->getResponse();
                $cache = $serviceManager->get('fscache');
                $request = $e->getRequest();
                if ($request->getMethod() <> 'GET') {
                    return;
                }
                $params = $e->getRouteMatch()->getParams();
                $query  = $request->getQuery();
                $key = $this->getCacheKey($route, $params, $query);
                $cache->setItem($key, $response->getBody());
            },
            -10000);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'ZF\Apigility\Autoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
}
