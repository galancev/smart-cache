<?php
/*

Usage:

use Components\Cache\SmartCache;

$cache = (new SmartCache())
    ->setGroup('test')
    ->setKey('supertest1')
    ->setExpire(5)
    ->get();

if ($cache->hasResult()) {
    $need = $cache->getResult();
} else {
    $need = rand(0, 666);

    $cache->set($need);
}

Dev::pre($need);

Ну или вариант для тех, кто не любит чейны:

$cache = SmartCache::factory([
    'group' => 'test',
    'key' => 'supertest1',
    'expire' => 5,
    'scatter' => 0,
])->get();

Ну или ещё вариант, кому нравится одним методом:

$cache = SmartCache::init('test', 'supertest1', 5, 0);

Ну или ещё один чёткий вариант через замыкание, он вообще современный офигеть какой

$data = (new SmartCache())
    ->setGroup('test')
    ->setKey('test')
    ->setExpire(5)
    ->remember(function () {
        $res = App::$DB->Query('SELECT * FROM b_user LIMIT 1');

        if(!$res)
            return false;

        return $res->FetchAll();
    });

*/

namespace Components\Cache;

use Func\Instance;

/**
 * Новая кешилка через инстансы
 * Можно вкладывать друг в друга сколько угодно раз :)
 * Class SmartCache
 * @package Components\Cache
 */
class SmartCache
{
    use Instance;

    /**
     * Глобальная группа кэша для невариативных ключей.
     */
    const GLOBAL_CACHE_GROUP = "global";

    /**
     * Время жизни 1 секунда
     */
    const TTL_1S = 1;

    /**
     * Время жизни 1 минута
     */
    const TTL_1M = 60;

    /**
     * Время жизни 1 час
     */
    const TTL_1H = 3600;

    /**
     * Время жизни 3 часа
     */
    const TTL_3H = 10800;

    /**
     * Время жизни 2 часа
     */
    const TTL_2H = 7200;

    /**
     * Время жизни сутки
     */
    const TTL_24H = 86400;

    /**
     * Время жизни - месяц
     */
    const TTL_1MONTH = 2592000;

    /**
     * @var \CPHPCache
     */
    private $cache;

    /**
     * Настройки кеша
     * @var SmartOptions
     */
    protected $options;

    /**
     * Есть ли результат выполнения кеша
     * @var bool
     */
    protected $hasResult = false;

    /**
     * Проинициализирован ли кеш
     * @var bool
     */
    protected $isInited = false;

    /**
     * SmartCache constructor.
     * @param array $opt Параметры кеша
     */
    public function __construct($opt = [])
    {
        $this->cache = new \CPHPCache();
        $this->options = new SmartOptions();

        if (!empty($opt))
            $this->setOptions($opt);
    }

    /**
     * Устанавливает настройки кеша через массив
     * @param array $opt Параметры кеша
     * @return $this
     */
    public function setOptions($opt = [])
    {
        if (isset($opt['group']))
            $this->setGroup($opt['group']);

        if (isset($opt['key']))
            $this->setKey($opt['key']);

        if (isset($opt['expire']))
            $this->setExpire($opt['expire']);

        if (isset($opt['scatter']))
            $this->setScatter($opt['scatter']);

        return $this;
    }

    /**
     * Устанавливает группу кеша
     * @param string $group Группа кеша
     * @return $this
     */
    public function setGroup($group)
    {
        if ($this->options->group !== $group)
            $this->isInited = false;

        $this->options->group = $group;

        return $this;
    }

    /**
     * Устанавливает ключ кеша
     * @param string $key Ключ кеша
     * @return $this
     */
    public function setKey($key)
    {
        if ($this->options->key !== $key)
            $this->isInited = false;

        $this->options->key = $key;

        return $this;
    }

    /**
     * Устанавливает время жизни кеша
     * @param int $expire Время жизни кеша в секундах
     * @return $this
     */
    public function setExpire($expire)
    {
        $this->options->expire = $expire;

        return $this;
    }

    /**
     * Устанавливает разброс времени кеширования : предположим генерим страничку и тогда все блоки одновременно протухают
     * если юзать этот параметр, то время жизни кэша  = время жизни кэша +- время разброса Тем самым закешированные компоненты на странице протухнут не сразу
     * что снизит единомоментную нагрузку на сайт
     * @param int $scatter Разброс кеша
     * @return $this
     */
    public function setScatter($scatter)
    {
        $this->options->scatter = $scatter;

        return $this;
    }

    /**
     * Получает данные из кеша
     * @return $this
     */
    public function get()
    {
        $this->hasResult = $this->cache->InitCache(
            $this->options->getExpire(),
            $this->options->getKey(),
            $this->options->getPath()
        );

        if ($_GET['clear_cache'] == 'y' || $_GET['CLEAR_CACHE'] == 'Y') {
            $this->hasResult = false;
            $this->clear();
        }

        $this->isInited = true;

        return $this;
    }

    /**
     * Сохраняет данные в кеш
     * @param mixed $data Любые данные для сохранения
     * @return $this
     */
    public function set($data)
    {
        if (!$this->isInited)
            $this->get();

        $this->cache->StartDataCache();
        $this->cache->EndDataCache([
            'Result' => $data
        ]);

        return $this;
    }

    /**
     * Возвращает результат из кеша
     * @return mixed
     */
    public function getResult()
    {
        if (!$this->isInited)
            $this->get();

        return $this->cache->GetVars()['Result'];
    }

    /**
     * Возвращает, получен ли результат из кеша
     * @return bool
     */
    public function hasResult()
    {
        return $this->hasResult;
    }

    /**
     * Возвращает надо ли начитывать данные в кеша
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->hasResult();
    }

    /**
     * Очистка кеша по ключу
     * @return $this
     */
    public function clear()
    {
        $this->cache->Clean($this->options->getKey(), $this->options->getPath());

        return $this;
    }

    /**
     * Полная очистка кеша по группе
     * @return $this
     */
    public function clearAll()
    {
        $this->cache->CleanDir($this->options->getPath());

        return $this;
    }

    /**
     * Инициализирует кеш и возвращает объект себя
     * @param string $group Группа кеша
     * @param string $key Ключ кеша
     * @param int $expire Время жизни кеша
     * @param int $scatter Разброс кеша
     * @return $this
     */
    public static function init($group, $key, $expire, $scatter = 0)
    {
        return (new self())
            ->setGroup($group)
            ->setKey($key)
            ->setExpire($expire)
            ->setScatter($scatter)
            ->get();
    }

    /**
     * Возвращает либо данные из кеша, либо выполняет содержимое замыкания, сохраняет в кеш результат выполнения и возвращает эти данные
     * @param \Closure $callback
     * @return mixed
     */
    public function remember($callback)
    {
        if (!$this->isInited)
            $this->get();

        if ($this->hasResult())
            return $this->getResult();

        $data = $callback->__invoke();

        $this->set($data);

        return $data;
    }

    /**
     * Устанавливает ключ из массива переменных
     * @param array $vars
     * @return $this
     */
    public function setKeyByArray($vars)
    {
        $this->setKey(join('_', $vars));

        return $this;
    }
}
