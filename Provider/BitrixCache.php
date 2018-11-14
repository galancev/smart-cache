<?php

namespace Galantcev\Components\Cache\Provider;

/**
 * Class BitrixCache
 * @package Galantcev\Components\Cache\Provider
 */
class BitrixCache implements ICache
{
    /**
     * Объект кеша битрикса
     * @var \CPHPCache
     */
    protected $cache;

    /**
     * BitrixCache constructor.
     */
    public function __construct()
    {
        $this->cache = new \CPHPCache();
    }

    /**
     * Инициализация кеша
     * @param int $expire
     * @param string $key
     * @param string $path
     * @return bool
     */
    public function init($expire, $key, $path)
    {
        return $this->cache->InitCache($expire, $key, $path);
    }

    /**
     * Сохраняет данные в кеш
     * @param mixed $data
     * @return mixed|void
     */
    public function set($data)
    {
        $this->cache->StartDataCache();
        $this->cache->EndDataCache([
            'Result' => $data
        ]);
    }

    /**
     * Возвращает данные из кеша
     * @return mixed|void
     */
    public function get()
    {
        $this->cache->GetVars()['Result'];
    }

    /**
     * Чистит данные в кеше по ключу и разделу
     * @param string $key
     * @param string $path
     * @return mixed|void
     */
    public function clear($key, $path)
    {
        $this->cache->Clean($key, $path);
    }

    /**
     * Чистит данные по разделу
     * @param $path
     * @return mixed|void
     */
    public function clearAll($path)
    {
        $this->cache->CleanDir($path);
    }

    /**
     * Возвращает, надо ли использовать кеш
     * @return bool
     */
    public function isEnabled()
    {
        return !(strtolower($_GET['clear_cache']) === 'y');
    }
}
