Замеченные проблемы:

1. Некорректно работают регулярные выражения, у которых в шаблоне используется символ "-",
   например preg_match([\w-]), теперь нужно экранировать - preg_match([\w\-]).
   Поправил такую регулярку в rollun-utils config/env_configurator.php:24
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
7. 