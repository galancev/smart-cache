# smart-cache
Очередной костыль для кеширования.

Usage:

```php
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
```

Ну или вариант для тех, кто не любит чейны:

```php
$cache = SmartCache::factory([
    'group' => 'test',
    'key' => 'supertest1',
    'expire' => 5,
    'scatter' => 0,
])->get();
```

Ну или ещё вариант, кому нравится одним методом:

```php
$cache = SmartCache::init('test', 'supertest1', 5, 0);
```

Ну или ещё один чёткий вариант через замыкание, он вообще современный офигеть какой

```php
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
```
