<?php

namespace EG\Components\Cache\Provider;

/**
 * Interface ICache
 * @package EG\Components\Cache\Provider
 */
interface ICache
{
    /**
     * Инициализация кеша
     * @param int $expire
     * @param string $key
     * @param string $path
     * @return bool
     */
    public function init($expire, $key, $path);

    /**
     * Сохраняет данные в кеш
     * @param mixed $data
     * @return mixed
     */
    public function set($data);

    /**
     * Получает данные из кеша
     * @return mixed
     */
    public function get();

    /**
     * Чистит данные в кеше
     * @param string $key
     * @param string $path
     * @return mixed
     */
    public function clear($key, $path);

    /**
     * Чистит данные по группе
     * @param $path
     * @return mixed
     */
    public function clearAll($path);

    /**
     * Возвращает, надо ли использовать данные из кешаы
     * @return mixed
     */
    public function isEnabled();
}
