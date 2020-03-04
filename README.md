## Paypal Integration in Laravel 6

This tutorial will guide you to integrate paypal payment gateway in laravel 6 application. The buyer can easily access paypal payment and can pay by paypal account.Here, we will use paypal SDK of PHP to integrate paypal.

**Step:1**

Install fresh laravel project by following command:

    composer create-project --prefer-dist laravel/laravel paypalIntegration

**Step:2**

Create paypal developer account to generate REST API credentials for the sandbox also create new sandbox test account for merchant and buyer.Generate  REST API credentials for the sandbox and add them into `.env` file as below.

    PAYPAL_CLIENT_ID=CLIENT_ID
    PAYPAL_SECRET=SECRET
    PAYPAL_MODE=sandbox

Install PayPal REST API SDK for PHP by executing the command:

    composer require "paypal/rest-api-sdk-php:*"
    
Next, create `paypal.php` file inside the config directory and add following code:

    return [
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'secret' => env('PAYPAL_SECRET', ''),
        'settings' => array(
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path() . '/logs/paypal.log',
            'log.LogLevel' => 'ERROR'
        ),
    ];
    
 **Step:3**

Create PaypalController by following command:

    php artisan make:controller PayPalController
 
 Next, configure client id and secret key as below also create paypalPayment and getStatus methods.
    
        private $apiContext;
    
        public function __construct()
        {
            # configuration of client_id and secret key
            $paypalConfig = Config::get('paypal');
    
            $this->apiContext = new ApiContext(new OAuthTokenCredential(
                    $paypalConfig['client_id'],
                    $paypalConfig['secret'])
            );
    
            $this->apiContext->setConfig($paypalConfig['settings']);
        }
    

Now, create` view-cart.blade.php` file into resources directory to add paypal payment.

Then, define routes into `routes/web.php` file.

Finally, Test the integration.






# paypalIntegration
