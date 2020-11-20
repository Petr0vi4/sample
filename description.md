### Сценарий

* Пользователь регистрируется в системе. При этом в сервисе биллинга для него создаётся аккаунт.
* Пользователь авторизуется в системе и кладёт 100 р. на свой аккаунт.
* Пользователь создаёт заказ без заголовка X-Version и получает ошибку 400.
* Пользователь создаёт заказ со случайным значением заголовка X-Version и получает ошибку 409.
* Пользователь получает список своих заказов и берёт оттуда значение X-Version.
* Пользователь создаёт заказ с полученным значением X-Version. Указанная сумма списывается с аккаунта, а сервис нотификаций отправляет уведомление на email о том, что заказ успешно создан. 
* Пользователь получает список своих заказов, в котором появляется только что созданный заказ и новое значение X-Version.

### Запуск приложения
```
kubectl create namespace sample
helm install nginx stable/nginx-ingress -f nginx-ingress.yaml -n sample
helm install rabbitmq ./rabbitmq-chart -n sample
helm install postgresql ./postgresql-chart -n sample
helm install auth ./auth/.helm -n sample
helm install billing ./billing/.helm -n sample
helm install notification ./notification/.helm -n sample
helm install order ./order/.helm -n sample
helm install user ./user/.helm -n sample
```

### Запуск тестов
```
newman run tests.postman_collection.json
```
