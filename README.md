## Идемпотентность и коммутативность API в HTTP и очередях

Для обеспечения идемпотентности метода создания заказа использовались паттерны **Idempotency Key** и **Optimistic locking**. 

Для создания заказа в методе POST /order необходимо передать ключ идемпотентности в заголовке X-Version.

Значение ключа - версия коллекции заказов клиента, которая возвращается в результате метода GET /order.

Версия коллекции считается как хэш от количества заказов клиента и максимального идентификатора заказа клиента.

Такой подход "заставляет" клиента получить список заказов перед добавлением нового заказа.

Если при создании заказа клиент отвалился по таймауту, а заказ не создался, то повторный запрос выполнится без ошибок.
Если при создании заказа клиент отвалился по таймауту, а заказ создался, то версия коллекции изменится и повторный запрос выполнится с ошибкой.

Для надёжной отправки команды NotifyAboutOrderCreated из сервиса заказа в сервис нотификаций использовался паттерн **Transactional Outbox**.
Сервис заказа отправляет сообщение через очередь в postgresql в транзакции.
Отдельный деплоймент воркера сервиса заказа читает эту очередь и отправляет сообщение в rabbitmq.


Установка приложения и запуск тестов описаны [тут](description.md).
