<?php

namespace EG\Components\Cache;

/**
 * Настройки SmartCache
 * Class SmartOptions
 * @package Components\Cache
 */
class SmartOptions
{
    /**
     * Группа кеша
     * @var string
     */
    public $group = SmartCache::GLOBAL_CACHE_GROUP;

    /**
     * Ключ кеша
     * @var
     */
    public $key;

    /**
     * Время жизни кеша
     * @var int
     */
    public $expire = SmartCache::TTL_1H;

    /**
     * Разброс кеша
     * @var int
     */
    public $scatter = 0;

    /**
     * Сохранённое время кеша, чтобы не генерить постоянно
     * @var int
     */
    protected $saveExpire;

    /**
     * Возвращает время жизни кеша с учётом разброса
     * @return int
     */
    public function getExpire()
    {
        if (isset($this->saveExpire))
            return $this->saveExpire;

        $scatter = rand(-$this->scatter, $this->scatter);
        $this->saveExpire = $this->expire + $scatter;

        if ($this->saveExpire < 0)
            $this->saveExpire = 0;

        return $this->saveExpire;
    }

    /**
     * Возвращает финальный ключ кеша
     * @return string
     */
    public function getKey()
    {
        return md5($this->group . '_' . $this->key);
    }

    /**
     * Возвращает путь к файлам кеша
     * @return string
     */
    public function getPath()
    {
        return '/' . $this->group . '/';
    }
}
