sequenceDiagram

participant User
participant Order Service
participant Billing Service
participant Notification Service

User->>Order Service: POST /order
activate Order Service
Order Service->>Billing Service: POST /account/{user_id}/withdraw
activate Billing Service
Billing Service-->>Order Service: 200 OK
deactivate Billing Service
Order Service->>Notification Service: POST /notification/email
activate Notification Service
Notification Service-->>Order Service: 202 ACCEPTED
Order Service-->User: 200 OK {order_id}
deactivate Order Service
Notification Service->>Notification Service: send email
deactivate Notification Service