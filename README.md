# Magento 2 Amazon Echo Integration Module

This module is part demonstration of building a Magento 2 module, part an Amazon Echo (Alexa) integration project.
Please see blog posts at http://alankent.me/ for details.

TODO:
* Don't use \Exception everywhere (quick hack to get it going).
* Not necessarily correct way to move order to next state in demo app, but good enough for demo.
* Review whether to use auto-generated factories instead of hand crafted ones (I did this to avoid using object manager in unit test).
* CustomerData in general is not fully implemented yet.
* CustomerData does not persist local attributes into a database table yet, for maintaining customer profile data between requests.
* Customer identity authentication not implemented yet.
* StateManagement class currently does not do any persistence of session or customer data.