### Проблемы при переходе на php v8 и Laminas:

1. Некорректно работают регулярные выражения, у которых в шаблоне используется символ "-",
   например preg_match([\w-]), теперь нужно экранировать - preg_match([\w\-]).
   Поправил такую регулярку в rollun-utils config/env_configurator.php:24.
   Также в пакете rollun-openapi src/OpenAPI/Server/Attribute/Transfer.php:78
2. Не удалось запустить сессии. Не разобрался. Пока временно убрал.
3. Dotenv теперь не добавляет переменные окружения, если у него не указан параметр usePutenv.
   Нужно вызвать метод usePutenv(true). У нас это в config.php
4. В rollun-dic в src/Dic/src/InsideConstruct.php:150 был такой код
   ```php
    $functionName = "is_$type";
   ```
   В php 8 это не работает. Пришлось добавить проверку типа переменной $type
   ```php
   if ($type instanceof \ReflectionNamedType) {
       $type = $type->getName();
   }
   $functionName = "is_$type";
   ```
5. Пакет xiag/rql-parser больше не поддерживается, он передан другой организации и сейчас называется graviton/rql-parser.
   С ним возникли проблемы - классах нод появился абстрактный метод, который у нас не реализован - toRql().
6. В пакете datastore в тестах используются утверждения assertAttributeEquals, которые были удалены из фреймворка phpunit
7. В пакете openapi в файле config.php указаны конфиги для аутентификации и сессий, временно удалил.
8. В пакете openapi изменилось название поля в consumer, раньше было mediaType, теперь mediaRange.
   В producer осталось mediaType. Изменил в шаблонах api.mustache
9. В пакете mindplay/jsonfreeze, который используется в rollun-utils в сериализациях (\rollun\utils\Json\Serializer и \rollun\utils\Php\Serializer)
   версии больше 0.3.3 не сериализуют массивы, в которых есть строчные и числовые ключи. Пример такой:
   {
      "param": "string key",
      "0": "numeric key"
   }
   Текущая стабильная версия данного пакета 1.3.0. Вопрос в том, обновлять ли, игнорируя описанную выше проблему или оставить старую версию

# Сумісність з rollun бібліотеками

Бібліотеки сумісні починаючи з наступних версій:

```json
{
   "rollun-com/rollun-callback": "^7.0.0",
   "rollun-com/rollun-datastore": "^8.0.0",
   "rollun-com/rollun-logger": "^7.0.0",
   "rollun-com/rollun-utils": "^7.0.0",
   "rollun-com/rollun-openapi": "^10.0.0"
}
```

# Перехід на laminas з zend
1. Замінити усі zend бібліотеки на їх laminas аналоги. Аналог знайти достатньо просто: треба знайти zend бібліотеку на 
github і там в README пишеться куди переїхала ця бібліотека.
2. Оновити версії rollun бібліотек, актуальні версії можна знайти вище
3. За потреби оновити усі інші стороні бібліотеки
4. В composer.json, після розділу scripts дописати 
```
"repositories": [
  {
    "type": "github",
    "url": "git@github.com:orlyk-rollun/guzzle3.git"
  }
],
"minimum-stability": "dev"
```
5. Додайте в require секцію composer.json рядок `"guzzle/guzzle": "dev-php-8.0 as v3.9.0"` 
6. Для того щоб запрацювали сесії додайте пакет `mezzio/mezzio-session-ext`
7. Конфігурація whoops в `development.local.php.dist`:
```php
<?php
/**
 * Development-only configuration.
 *
 * Put settings you want enabled when under development mode in this file, and
 * check it into your repository.
 *
 * Developers on your team will then automatically enable them by calling on
 * `composer development-enable`.
 */

declare(strict_types=1);

use Mezzio\Container;
use Mezzio\Middleware\ErrorResponseGenerator;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

return [
    'dependencies' => [
        'invokables' => [
        ],
        'factories'  => [
            ErrorResponseGenerator::class       => Container\WhoopsErrorResponseGeneratorFactory::class,
            'Mezzio\Whoops'            => Container\WhoopsFactory::class,
            'Mezzio\WhoopsPageHandler' => Container\WhoopsPageHandlerFactory::class,
        ],
    ],

    'whoops' => [
        'json_exceptions' => [
            'display'    => true,
            'show_trace' => true,
            'ajax_only'  => true,
        ],
    ],
];
```