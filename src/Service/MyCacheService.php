<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyCacheService extends AbstractController
{
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getCacheData(string $key)
    {
        $data = $this->getDataFromCache($key);
    
        if ($data === null) {
            // Retourne null si aucune donnée n'est trouvée.
            return null;
        }
    
        return $data;
    }    

    private function getDataFromCache(string $key)
    {
        $cacheItem = $this->cache->getItem($key);

        if (!$cacheItem->isHit()) {
            return null; // Aucune donnée trouvée dans le cache
        }

        return $cacheItem->get();
    }

    private function setDataInCache(string $key, $data)
    {
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($data);
        $cacheItem->expiresAfter(3600); // Définit le temps de vie du cache (en secondes)
        $this->cache->save($cacheItem);
    }

    public function setCacheData(string $key, $data): Response
    {
        if (empty($key) || empty($data)) {
            return new Response('Key and data parameters are required', Response::HTTP_BAD_REQUEST);
        }

        $this->setDataInCache($key, $data);

        return new Response('Data has been cached successfully');
    }

    public function deleteCacheData(string $key): Response
    {
        if (empty($key)) {
            return new Response('Key parameter is required', Response::HTTP_BAD_REQUEST);
        }

        $this->deleteDataFromCache($key);

        return new Response('Data has been deleted from cache successfully');
    }

    private function deleteDataFromCache(string $key)
    {
        $this->cache->deleteItem($key);
    }

    public function getInitials($firstname, $lastname) {
        return strtoupper($firstname[0] . $lastname[0]);
    }
}
