## Build Status and Join chats us:

[![Build Status](https://travis-ci.org/duythien/cashier.svg?branch=master)](https://travis-ci.org/duythien/cashier) [![Slack](https://img.shields.io/badge/slack-join%20chat%20%E2%86%92-brightgreen.svg?style=flat-square)](http://chat.phalcontip.com)

## Introduction

Phalcon Cashier provides an expressive, fluent interface to [Stripe's](https://stripe.com) subscription billing services. It handles almost all of the boilerplate subscription billing code you are dreading writing. In addition to basic subscription management, Cashier can handle coupons, swapping subscription, subscription "quantities", cancellation grace periods, and even generate invoice PDFs.

##Installing
Create the composer.json file as follows:

```
{
    "require": {
        "duythien/cashier": "*"
    }
}
## Test Setup
You will need to set the following details locally and on your Stripe account in order to test:

### Local
#### Add some parameter in config.php such as like below

```
    'stripe' => [
        'model' => 'App\Models\Users',
        'secretKey' => null,
        'publishKey' => null
     ]
```
    

### Stripe
#### Plans
    * monthly-10-1 ($10)
    * monthly-10-2 ($10)
#### Coupons
    * coupon-1 ($5)

## Official Documentation

Not yet, but it is inspiring by Laravel so you can take look on the [Laravel website](http://laravel.com/docs/billing).

## Contributing

Thank you for considering contributing to the Cashier. You can read the contribution guide lines [here](contributing.md).

## License

Phalcon Cashier is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
