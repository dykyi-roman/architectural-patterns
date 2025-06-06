# Enterprise Application Architecture Patterns

A comprehensive implementation of modern enterprise application architecture patterns following Domain-Driven Design (DDD) principles. 
This project demonstrates advanced architectural concepts with practical examples using PHP and Symfony.

## Navigation

Use the links below to explore different sections of the documentation:

| Section                                                   | Description                                                                                                                                                                                  |
|-----------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [Context Map & Architecture Patterns](code/src/README.md) | Overview of context boundaries, their relationships, and detailed explanation of implemented architectural patterns (CQRS, Hexagonal Architecture, Clean Architecture, Layered Architecture) |
| [Order Context](code/src/OrderContext/README.md)          | Documentation of the Order bounded context, including domain concepts, events, repositories, use cases, and workflows                                                                        |
| [Payment Context](code/src/PaymentContext/README.md)      | Documentation of the Payment bounded context and its cross-context event handling with the Order context                                                                                     |
| [Shared Components](code/src/Shared/README.md)            | Common components used across all contexts including outbox pattern implementation, rate limiting, and other shared infrastructure                                                           |
| [Infrastructure](infrastructure/README.md)                | Development environment setup, available services, and tooling information                                                                                                                   |

## Project Overview

This project implements a multi-context enterprise application following Domain-Driven Design principles. The system is divided into separate bounded contexts (Order and Payment) that communicate through asynchronous events via message queues.

### Key Features

- **Domain-Driven Design (DDD)**: Strategic and tactical patterns for complex domain modeling
- **Bounded Contexts**: Independently functioning domains with clear boundaries
- **Event-Driven Communication**: Contexts interact through domain events
- **CQRS Pattern**: Separation of read and write operations
- **Hexagonal Architecture**: Clear separation of domain from infrastructure
- **Clean Architecture**: Dependency rule enforcement and layer separation
- **Transactional Outbox**: Reliable event publishing between contexts
- **Domain Events**: Rich event modeling for business processes
- **REST API**: Well-structured API endpoints for external integration

## Getting Started

The project is built on top of a comprehensive infrastructure stack that provides all necessary development services. To get started:

1. Explore the [Infrastructure documentation](infrastructure/README.md) for environment setup
2. Review the [Context Map](code/src/README.md) to understand the system architecture
3. Dive into specific bounded contexts to understand their domain models and behavior

## Architecture Decision Records

All significant architectural decisions are documented in Architecture Decision Records (ADRs) located in the `docs/adr` directory. These records provide context and rationale for the key design choices made during development.