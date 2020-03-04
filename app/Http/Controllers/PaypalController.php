<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\WebProfile;
use PayPal\Api\ItemList;
use PayPal\Api\InputFields;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Details;
use Redirect;
use Session;
use URL;
use App\Invoice;
use App\Order;
use App\Suborder;
use Str;
Use Request;
use Auth;
use Config;

class PaypalController extends Controller
{

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

    public function paypalPayment(Request $request){

        // initialize the payer object and set the payment method to PayPal
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        // set name,currency,price and quantity of the item

        $item1 = new Item();
        $item1->setName('Lavie Handbag')
              ->setCurrency('USD')
              ->setQuantity(1)
              ->setPrice(40);

        $item2 = new Item();
        $item2->setName('Women Block heels')
              ->setCurrency('USD')
              ->setQuantity(1)
              ->setPrice(30);

        // create a new item list and assign the items to it
        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));

        // Disable all irrelevant PayPal aspects in payment
        $inputFields = new InputFields();
        $inputFields->setAllowNote(true)
                    ->setNoShipping(1)
                    ->setAddressOverride(0);

        $webProfile = new WebProfile();
        $webProfile->setName(uniqid())
                   ->setInputFields($inputFields)
                   ->setTemporary(true);

        $createProfile = $webProfile->create($this->apiContext);

        // set shipping and tax details
        $details = new Details();
        $details->setShipping(15.00)
                ->setTax(3.60)
                ->setSubtotal(70.00);

        // set the total price of the cart
        $amount = new Amount();
        $amount->setCurrency('USD')
               ->setTotal(88.60)
               ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($itemList)
                    ->setDescription('Your transaction description');

        $redirectURLs = new RedirectUrls();
        $redirectURLs->setReturnUrl(URL::to('status'))
                     ->setCancelUrl(URL::to('status'));

        $payment = new Payment();

        $payment->setIntent('Sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectURLs)
                ->setTransactions(array($transaction));

        $payment->setExperienceProfileId($createProfile->getId());

        $payment->create($this->apiContext);

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirectURL = $link->getHref();
                break;
            }
        }

        // store the payment ID into the session
        Session::put('paypalPaymentId', $payment->getId());

        if (isset($redirectURL)) {
            return Redirect::away($redirectURL);
        }

        Session::put('error', 'There was a problem processing your payment. Please contact support.');

        return Redirect::to('/payment');
    }

    public function getStatus()
    {
        $paymentId = Session::get('paypalPaymentId');

        // erase the payment ID from the session to avoid fraud
        Session::forget('paypalPaymentId');

        // If the payer ID or token isn't set, there was a corrupt response and instantly abort
        if (empty(Request::get('PayerID')) || empty(Request::get('token'))) {
            Session::put('error', 'There was a problem processing your payment. Please contact support.');
            return Redirect::to('/payment');
        }

        $payment = Payment::get($paymentId, $this->apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId(Request::get('PayerID'));

        $result = $payment->execute($execution, $this->apiContext);

        // Payment is processing but may still fail due e.g to insufficient funds

        if ($result->getState() == 'approved') {

            Session::put('success', 'Your payment was successful. Thank you.');

            return Redirect::to('/payment');
        }

        else{

            Session::put('error', 'There was a problem processing your payment. Please contact support.');

            return Redirect::to('/payment');
        }

    }

}
