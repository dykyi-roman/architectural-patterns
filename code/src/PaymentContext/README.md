# Ubiquitous Language — PaymentContext

- Payment: transaction associated with the order.
- PaymentMethod: payment method.
- TransactionId: external transaction identifier.
- PaymentStatus: PENDING, SUCCESS, FAILED.

## Cross-Context Communication: OrderContext Events

The PaymentContext implements a Domain Event Handler pattern to process events from the OrderContext. This design follows the principles of loose coupling and high cohesion between bounded contexts.

### Event Handling Mechanism

When an `OrderStatusChangedEvent` is published in the OrderContext, the PaymentContext captures this event through the Symfony Messenger component. Our event handler processes these events and executes the appropriate business logic based on the order status change:

1. **Order Placed**: Initializes the payment process, creating payment records and potentially reserving funds.
2. **Order Paid**: Updates payment records, confirming transactions and sending receipts.
3. **Order Cancelled**: Processes payment cancellation, including refunds or voiding authorizations.

This event-driven architecture allows the PaymentContext to react to changes in the OrderContext without creating tight dependencies, maintaining the autonomy of each bounded context while ensuring consistent business processes.