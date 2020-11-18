## Сервис заказа. Сервис биллинга. Сервис нотификаций.

Event Collaboration стиль взаимодействия с использованием брокера сообщений

### Сценарий

* Пользователь регистрируется в системе. При этом в сервисе биллинга для него создаётся аккаунт.
* Пользователь авторизуется в системе и кладёт 100 р. на свой аккаунт.
* Пользователь создаёт заказ на 80 р. Указанная сумма списывается с аккаунта, а сервис нотификаций отправляет уведомление на email о том, что заказ успешно создан. 
* Пользователь создаёт заказ на 50 р. С аккаунта ничего не списывается, а сервис нотификаций отправляет уведомление на email о том, что при оплате заказа возникла ошибка.

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
