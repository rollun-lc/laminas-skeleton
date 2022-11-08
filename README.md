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