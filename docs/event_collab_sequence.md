sequenceDiagram

participant User
participant Gateway
participant Message Broker
participant Order Service
participant Billing Service
participant Notification Service

User->>Gateway: POST /order
activate Gateway
Gateway->>Message Broker: publish
deactivate Gateway
activate Message Broker
Note right of Message Broker: OrderRequestCreated
Message Broker-->>Order Service: consume
activate Order Service
Order Service->>Order Service: create order
Order Service->>Message Broker: publish
deactivate Order Service
Note right of Message Broker: OrderCreated
Message Broker-->>Gateway: consume
activate Gateway
Gateway-->>User: 201 CREATED
deactivate Gateway
Message Broker-->>Billing Service: consume
activate Billing Service
Billing Service->>Billing Service: withdraw money
Billing Service->>Message Broker: publish
deactivate Billing Service
Note right of Message Broker: OrderPaidSuccess
Message Broker-->>Order Service: consume
activate Order Service
Order Service->>Order Service: set order status 'Paid'
deactivate Order Service
Message Broker-->>Notification Service: consume
deactivate Message Broker
activate Notification Service
Notification Service->>Notification Service: send email
deactivate Notification Service
