sequenceDiagram

participant User
participant Order Service
participant Billing Service
participant Message Broker
participant Notification Service
participant User Service

User->>Order Service: POST /order
activate Order Service
Order Service->>Billing Service: POST /account/{user_id}/withdraw
activate Billing Service
Billing Service-->>Order Service: 200 OK
deactivate Billing Service
Order Service->>Message Broker: publish
activate Message Broker
Note right of Message Broker: NotifyAboutOrderCreated
Order Service-->>User: 200 OK {order_id}
deactivate Order Service
Message Broker-->>Notification Service: consume
deactivate Message Broker
activate Notification Service
Notification Service->>Order Service:GET /order/{order_id}
activate Order Service
Order Service-->>Notification Service: 200 OK
deactivate Order Service
Notification Service->>User Service:GET /user/{user_id}
activate User Service
User Service-->>Notification Service:200 OK
deactivate User Service
Notification Service->>Notification Service: send email
deactivate Notification Service