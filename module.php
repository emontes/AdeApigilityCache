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
        
        foreach ($params as $key=>$value) {
            $strKey = $key . '-' . str_replace('.', 'p', $value);
            $cacheKey .= '+' .$strKey;
        }
        
        foreach ($query as $key=>$value) {
            $strKey = $key . '-' . str_replace('.', 'p', $value);
            $cacheKey .= '+' .$strKey;
        }
        return $cacheKey;
    }
    
    public function onBootStrap(MvcEvent $e)
    {
        $routes = array(
            'hoteles.rest.hotels-nearby'
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
                $cache->setItem($key, $response->getContent());
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
