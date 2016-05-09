Alexa Module Design
===================

# Goal

The design of this module is intended to also demonstrate several common
sceanarios in Magento.

# Alexa Abstraction API

The purpose of the module is to provide common framework code for
developing web services to support Alexa applications.
The module performs the following:

- Decodes JSON Alexa requests and encodes the JSON responses.
- Implements Alexa session management.
- (Future) Identifies customer and loads locally stored customer profile (which may include access to the customer's shopping cart or wishlist).
- Stores session data in persistant storage (JSON blog, data interfaces, or similar) - for example, can be used to remember current state within a conversation.
- Automatically reclaims expired sessions (deletes sessions storage of expired sessions).
- Typical flow of a requst is to create new session (or load state if existing sessions), process the intent (with "slots"), return a result, with modified state saved to storage for access upon the next request.

Possible extensions include

- Also accepts IFTTT triggers, adjusting customer state appropriately.

# Demonstrates

This module demonstrates the following:

- How to handle raw JSON requests not appropriate for processing through sercie contracts.
- How to save and load data structures in a database table (simple persistence of customer and session data).
- How a di.xml file is used to wire things together.

# Example Usage

An example of an application that could be built upon this module is a
pick, pack, and ship module. This would be a separate module - not in the
Alexa module. The Alexa module is generic infrastructure for other modules
to build upon.

For example, consider the following conversation (U = user, A = Alexa):

- U: Alexa, pick and pack the next order.
- A: The next order is order 23. You will need a size 23 box. 
- U: First item.
- A: 2 black felt tip pens, SKU 34223.
- U: Next item.
- A: 1 double space lined pad, SKU 4112.
- U: Next item.
- A: There are no more items. Shall I print the shipping label?
- U: Yes.

In this case "customer data" is not used, only session data. The session
data would remember the current order being processed, the current item
within the order, and whether the label had been printed yet or not.

Another application may be allowing an end user to add items to a cart
before purpchase. In this case it is desirable to identify the customer
to avoid them having to supply shipping address details, payment details,
etc per order.
