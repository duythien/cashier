## Build Status and Join chats us:

[![Build Status](https://travis-ci.org/duythien/cashier.svg?branch=master)](https://travis-ci.org/duythien/cashier) [![Slack](https://img.shields.io/badge/slack-join%20chat%20%E2%86%92-brightgreen.svg?style=flat-square)](http://chat.phalcontip.com)

## Introduction

Phalcon Cashier provides an expressive, fluent interface to [Stripe's](https://stripe.com) subscription billing services. It handles almost all of the boilerplate subscription billing code you are dreading writing. In addition to basic subscription management, Cashier can handle coupons, swapping subscription, subscription "quantities", cancellation grace periods, and even generate invoice PDFs.

##Installing
Create the composer.json file as follows:

```
{
    "require": {
        "duythien/cashier": "^v1"
    }
}
```

Before using Cashier, we'll also need to prepare the database. We need to add several columns to your users table and create a new subscriptions table to hold all of our customer's subscriptions. To do that just typing into SQL console

```
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `stripe_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `stripe_plan` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `trial_ends_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ends_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;


ALTER TABLE `users` ADD `stripe_id` VARCHAR(200) NULL;
ALTER TABLE `users` ADD `card_brand` VARCHAR(200) NULL;
ALTER TABLE `users` ADD `card_last_four` VARCHAR(200) NULL;
ALTER TABLE `users` ADD `trial_ends_at` timestamp NULL DEFAULT NULL;
```

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
