<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe;
use Stripe_Customer;
use Stripe_Charge;
use Stripe_Token;
use Stripe\PaymentMethod;

use App\Models\User;

class PaymentController extends Controller
{
    public function registrationPayment(Request $request, $userId)
    {
        $validatedData = customValidate($request->all(), [
            'stripeToken' => 'required|string'
        ]);
        validateUserId($userId);
        $user = User::find($userId);
        $amount = 50 * 100;
        $errorMessage = "Payment processed failed. Your registration have been cancelled";

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $customer = Stripe_Customer::create([
            "name" => $user->name,
            'email' => $user->email,
            'source' => $validatedData['stripeToken']
        ]);

        if ($customer) {
            $charge = Stripe_Charge::create([
                'customer' => $customer->id,
                'amount' => $amount,
                'currency' => 'usd',
            ]);

            if ($charge) {
                $user->isVerified = true;
                $user->stripeId = $customer->id;
                $user->save();
                $user->payment()->create([
                    'amount' => $amount,
                    'type' => 'registration_fee'
                ]);
                $user->wallet()->create([
                    "balance" => 0
                ]);

                return $this->success(true, "Payment has been completed successfully", 200);
            } else {
                $user->delete();
                return $this->error($errorMessage, 400);
            }
        } else {
            $user->delete();
            return $this->error($errorMessage, 404);
        }
    }

    public function chargeWallet(Request $request, $userId)
    {

        $validatedData = customValidate($request->all(), [
            'stripeToken' => "required|string",
            "amount" => "required|integer|min:1"
        ]);

        validateUserId($userId);
        $user = User::find($userId);
        $amount = $validatedData['amount'] * 100;
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $charge = Stripe_Charge::create([
            'customer' => $user->stripeId,
            'amount' => $amount,
            'currency' => 'usd',
        ]);

        if ($charge) {
            $user->wallet()->update([
                'balance' => $user->wallet->balance + $amount
            ]);

            return $this->success(true, "Amount has been added in the wallet successfully", 200);
        } else {
            return $this->error('', 400);
        }
    }
}
