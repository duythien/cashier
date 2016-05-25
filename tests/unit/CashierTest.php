<?php

use Carbon\Carbon;
use App\Models\Users;
use Phalcon\Cashier\Subscription;

require_once 'UnitTestCase.php';




class CashierTest extends \UnitTestCase
{

    /**
     * Tests.
     */
    public function testSubscriptionsCanBeCreated()
    {


        $user = Users::findFirst();

        //Create Subscription
        $user->newSubscription('main', 'monthly-10-1')->create($this->getTestToken());

        $this->assertEquals(1, count($user->subscriptions));
        $this->assertNotNull($user->subscription('main')->stripe_id);

        $this->assertTrue($user->subscribed('main'));
        $this->assertTrue($user->subscribedToPlan('monthly-10-1', 'main'));
        $this->assertFalse($user->subscribedToPlan('monthly-10-1', 'something'));
        $this->assertFalse($user->subscribedToPlan('monthly-10-2', 'main'));
        $this->assertTrue($user->subscribed('main', 'monthly-10-1'));
        $this->assertFalse($user->subscribed('main', 'monthly-10-2'));
        $this->assertTrue($user->subscription('main')->active());
        $this->assertFalse($user->subscription('main')->cancelled());
        $this->assertFalse($user->subscription('main')->onGracePeriod());

         //Cancel Subscription
        $subscription = $user->subscription('main');
        $subscription->cancel();

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue($subscription->onGracePeriod());

        // Modify Ends Date To Past
        $oldGracePeriod = $subscription->ends_at;
        $subscription->ends_at = Carbon::now()->subDays(5)->toDateTimeString();
        $subscription->save();


        $this->assertFalse($subscription->active());
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());

        $subscription->ends_at = $oldGracePeriod;
        $subscription->save();

        // Resume Subscription
        $subscription->resume();

        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());

        // Increment & Decrement
        $subscription->incrementQuantity();

        $this->assertEquals(2, $subscription->quantity);

        $subscription->decrementQuantity();

        $this->assertEquals(1, $subscription->quantity);

        // Swap Plan
        $subscription->swap('monthly-10-2');

        $this->assertEquals('monthly-10-2', $subscription->stripe_plan);

        // Invoice Tests
        $invoice = $user->invoices()[1];
        $this->assertEquals('$10.00', $invoice->total());
        $this->assertFalse($invoice->hasDiscount());
        $this->assertFalse($invoice->hasStartingBalance());
        $this->assertNull($invoice->coupon());
        $this->assertInstanceOf(Carbon::class, $invoice->date());

    }

    public function test_creating_subscription_with_coupons()
    {
       $user = Users::findFirst();

        // Create Subscription
        $user->newSubscription('main', 'monthly-10-1')
                ->withCoupon('coupon-1')->create($this->getTestToken());

        $subscription = $user->subscription('main');

        $this->assertTrue($user->subscribed('main'));
        $this->assertTrue($user->subscribed('main', 'monthly-10-1'));
        $this->assertFalse($user->subscribed('main', 'monthly-10-2'));
        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());

        // Invoice Tests
        $invoice = $user->invoices()[0];

        $this->assertTrue($invoice->hasDiscount());
        $this->assertEquals('$5.00', $invoice->total());
        $this->assertEquals('$5.00', $invoice->amountOff());
        $this->assertFalse($invoice->discountIsPercentage());
    }

    public function test_generic_trials()
    {
//        $user = new Users;
//        $this->assertFalse($user->onGenericTrial());
//        $user->trial_ends_at = Carbon::tomorrow();
//        $this->assertTrue($user->onGenericTrial());
//        $user->trial_ends_at = Carbon::today()->subDays(5);
//        $this->assertFalse($user->onGenericTrial());
    }

    public function test_creating_subscription_with_trial()
    {
        $user = Users::findFirst();

        // Create Subscription
        $user->newSubscription('main', 'monthly-10-1')
            ->trialDays(7)->create($this->getTestToken());

        $subscription = $user->subscription('main');

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->onTrial());
        $dt = Carbon::parse($subscription->trial_ends_at);
        $this->assertEquals(Carbon::today()->addDays(7)->day, $dt->day);

        // Cancel Subscription
        $subscription->cancel();

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->onGracePeriod());

        // Resume Subscription
        $subscription->resume();

        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertTrue($subscription->onTrial());
        $dt = Carbon::parse($subscription->trial_ends_at);
        $this->assertEquals(Carbon::today()->addDays(7)->day, $dt->day);
    }

    public function test_applying_coupons_to_existing_customers()
    {
        $user = Users::findFirst();

        // Create Subscription
        $user->newSubscription('main', 'monthly-10-1')
                ->create($this->getTestToken());

        $user->applyCoupon('coupon-1');

        $customer = $user->asStripeCustomer();

        $this->assertEquals('coupon-1', $customer->discount->coupon->id);
    }



    public function testCreatingOneOffInvoices()
    {
        $user = Users::findFirst();

        // Create Invoice
        $user->createAsStripeCustomer($this->getTestToken());
        $user->invoiceFor('Phalcon PHP Cashier', 1000);

        // Invoice Tests
        $invoice = $user->invoices()[0];
        $this->assertEquals('$10.00', $invoice->total());
        $this->assertEquals('Phalcon PHP Cashier', $invoice->invoiceItems()[0]->asStripeInvoiceItem()->description);
    }

    public function testRefunds()
    {
        $user = Users::findFirst();

        // Create Invoice
        $user->createAsStripeCustomer($this->getTestToken());
        $invoice = $user->invoiceFor('Phalcon PHP Cashier', 1000);

        // Create the refund
        $refund = $user->refund($invoice->charge);

        // Refund Tests
        $this->assertEquals(1000, $refund->amount);
    }

    protected function getTestToken()
    {
        return Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 5,
                'exp_year' => 2020,
                'cvc' => '123',
            ],
        ], ['api_key' => getenv('STRIPE_SECRET')])->id;
    }


    protected function connection()
    {
        return ;
    }
}


