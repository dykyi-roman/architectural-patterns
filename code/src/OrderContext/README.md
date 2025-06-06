# Order Context (OrderContext)

## Ubiquitous Language

This document describes terms and concepts used in the order context. Ubiquitous language helps developers and domain experts speak the same language and avoid misunderstandings.

### Core Concepts and Entities

#### Order
The central entity in our domain. An order is created by a customer and contains a set of items. The order has a unique identifier, status, and total cost. Order is an aggregate (root) that encapsulates all logic related to its lifecycle.

#### OrderItem
A component of an order representing a specific product in a certain quantity and at a specific price. An order item always belongs to a specific order and cannot exist separately.

#### OrderId
A unique order identifier represented as a UUID. Used for unambiguous identification of an order in the system.

#### CustomerId
A unique customer identifier to whom the order belongs. Represented as a UUID.

#### ProductId
A unique product identifier included in the order. Represented as a UUID.

#### Money
A monetary amount consisting of a numerical value (amount) and currency. Used to represent product prices and the total cost of an order.

#### OrderStatus
The state of an order in its lifecycle. An order can be in the following states:
- **Created**: initial state after order creation
- **Processing**: the order is accepted and being processed
- **Completed**: order processing is completed, ready for shipping
- **Shipped**: the order has been shipped to the customer
- **Delivered**: the order has been successfully delivered to the customer
- **Cancelled**: the order was cancelled

### Domain Events

#### OrderCreatedEvent
An event generated when a new order is created. Contains information about the created order, including its identifier, customer identifier, order items, and total cost.

#### OrderStatusChangedEvent
An event generated when the status of an order is changed. Contains information about the order identifier, previous status, and new status.

### Repositories

#### OrderRepository
Responsible for saving and retrieving orders from persistent storage. Used for write operations in the CQRS model.

#### OrderReadModelRepository
Responsible for retrieving order data from a read-optimized storage. Used for read operations in the CQRS model.

### Architectural Components

#### UseCases
Central components of the application architecture, implementing specific business scenarios. Each UseCase represents a separate module containing a command/request and its corresponding handler. Main use cases:

- **CreateOrder**: Creating a new order
- **ChangeOrderStatus**: Changing the status of an existing order
- **GetOrder**: Retrieving detailed information about an order
- **GetOrdersList**: Retrieving a list of orders with filtering and pagination

#### OrderApplicationService
Coordinates the execution of operations on orders, providing a single entry point for use cases. Delegates command and query execution to corresponding handlers through a message bus or directly.

#### API Action Controllers
Entry points for REST API, accepting HTTP requests, converting them into corresponding commands or queries, and passing them to OrderApplicationService for execution:

- **CreateOrderAction**: Creating an order through API
- **ChangeOrderStatusAction**: Changing the status of an order through API
- **GetOrderAction**: Retrieving information about an order through API
- **GetOrdersListAction**: Retrieving a list of orders through API

### Commands and Queries (CQRS)

#### CreateOrderCommand
A command for creating a new order. Contains information necessary for creating an order: customer identifier and list of products. Handled by CreateOrderCommandHandler.

#### ChangeOrderStatusCommand
A command for changing the status of an existing order. Contains the order identifier and new status. Handled by ChangeOrderStatusCommandHandler.

#### GetOrderQuery
A query for retrieving information about a specific order by its identifier. Handled by GetOrderQueryHandler.

#### GetOrdersListQuery
A query for retrieving a list of orders with filtering by customer, status, and other parameters. Supports pagination and sorting. Handled by GetOrdersListQueryHandler.

### Contract Interfaces

#### CommandInterface
A base interface for all commands in the system. Marks an object as a command that performs a write operation.

#### CommandHandlerInterface
An interface for command handlers. Defines the __invoke method, accepting a command and executing the corresponding business logic.

#### QueryInterface
A base interface for all queries in the system. Marks an object as a query that performs a read operation.

#### QueryHandlerInterface
An interface for query handlers. Defines the __invoke method, accepting a query and returning the result.

### Infrastructure Concepts

#### EventStore
A component responsible for storing all domain events related to orders.

#### Outbox Pattern
A mechanism ensuring reliable publication of domain events to external systems after a successful transaction.

### Transactional Outbox Pattern

#### Description and Principles
Transactional Outbox Pattern is an architectural pattern that solves the problem of atomicity between data saving operations and event publishing in distributed systems. It ensures reliable message delivery between services in asynchronous communication.

**Main Components of Implementation:**
- `OutboxEvent` — an entity representing a record in the outbox_events table
- `OutboxPublisherInterface` — an interface for publishing events through Outbox
- `OutboxPublisher` — an implementation of the interface, saving events to the outbox_events table
- `OutboxEventRepository` — a repository for working with the outbox_events table
- `OutboxEventProcessor` — a service processing and publishing events from the outbox_events table

#### Reasons for Use
1. **Ensuring Atomicity** — saving data (e.g., an order) and recording an event occur in a single transaction, ensuring consistency.
2. **Reliable Event Delivery** — even if the message broker (RabbitMQ) is temporarily unavailable, events are not lost but saved in the database.
3. **Separation of Main Business Logic from Messaging Infrastructure** — creating an order does not depend on the availability of the message broker.
4. **Preserving the Order of Events** — events are published in the same order they were created.
5. **Improving System Fault Tolerance** — the system continues to function even during temporary communication failures between services.

#### Workflow
1. A command (e.g., `CreateOrderCommand`) is handled by the corresponding handler.
2. The handler performs the business operation (creating an order) and saves the data to the repository.
3. A domain event (e.g., `OrderCreatedEvent`) is created.
4. `OutboxPublisher` saves the event to the outbox_events table in the same transaction as the main data.
5. A separate process (`OutboxEventProcessor`) periodically checks the outbox_events table for unprocessed events.
6. Unprocessed events are published to the message broker (RabbitMQ).
7. After successful publication, events are marked as processed.
8. In case of publication errors, events remain in the table and will be reprocessed later, with an increased retry counter.

## Interactions and Processes

### Creating an Order
1. The client sends a request to create an order through the API
2. CreateOrderAction converts the request into a CreateOrderCommand
3. OrderApplicationService passes the command to CreateOrderCommandHandler
4. The command handler creates a new order and generates an OrderCreatedEvent
5. The order is saved to the order repository
6. The event is saved to the event store and published through Outbox
7. The event handler updates the read model

### Changing the Status of an Order
1. A request to change the status is received through the API
2. ChangeOrderStatusAction converts the request into a ChangeOrderStatusCommand
3. OrderApplicationService passes the command to ChangeOrderStatusCommandHandler
4. The command handler changes the order status and generates an OrderStatusChangedEvent
5. The updated order is saved to the repository
6. The event is saved to the event store and published through Outbox
7. The event handler updates the read model

### Retrieving Information about an Order
1. The client sends a request to retrieve an order through the API
2. GetOrderAction converts the request into a GetOrderQuery
3. OrderApplicationService passes the query to GetOrderQueryHandler
4. The query handler retrieves data from the read model repository
5. The data is converted to a DTO and returned to the client

### Retrieving a List of Orders
1. The client sends a request to retrieve a list of orders with filters through the API
2. GetOrdersListAction converts the request into a GetOrdersListQuery
3. OrderApplicationService passes the query to GetOrdersListQueryHandler
4. The query handler retrieves data from the read model repository with filters applied
5. The data is converted to a DTO with pagination and returned to the client

## Technology Stack

- **Programming Language**: PHP 8.3 with declare(strict_types=1)
- **Framework**: Symfony 7.2
- **Architectural Patterns**: DDD, CQRS, Clean Architecture
- **Design Patterns**: Repository, Factory, Value Object, Command/Query, Handler
- **Code Organization**: UseCases by business functions

## Notes

EventStore and Outbox Pattern: difference and purpose
You raised a very important question. EventStore and Outbox are two different patterns, often used together, but with different goals:

EventStore: storing event history
EventStore serves to store the entire history of all domain events in the system. This is an implementation of the Event Sourcing pattern, where:

Main goal: storing the entire history of changes to an aggregate (order)
Orientation: internal system need for storing history, auditing, and possible state reconstruction
Responsibility: storing all events in chronological order
Typical EventStore usage scenarios:
Reconstructing the state of an aggregate from events
Auditing (who and when changed the order status)
Temporal queries (what the order looked like at a specific point in time)
Analytics and reporting
Outbox: guaranteed event delivery
Outbox serves to ensure the reliable delivery of messages to external subscribers:

Main goal: ensuring the reliable publication of events to other services
Orientation: integration between services and systems
Responsibility: atomic transaction of saving state and publishing events
Difference in application
In our code, EventStore is not used directly in command handlers because:

We do not implement full Event Sourcing, but use a traditional state-saving model
Our focus is on reliable event publication through Outbox, not on building aggregates from events
Perhaps EventStore was planned for future extensions or specific use cases
Possible EventStore usage scenarios in OrderContext
EventStore could be used in the following cases:

Order history: storing all events to provide the client with a complete chronology of changes
Auditing: storing information about who and when changed the order status
Debugging: analyzing the sequence of events to identify issues
State reconstruction: restoring the state of an order at any point in time