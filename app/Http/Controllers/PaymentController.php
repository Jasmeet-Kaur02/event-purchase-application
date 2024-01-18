<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe;
use Stripe_Customer;
use Stripe_Charge;
use Stripe_InvalidRequestError;
use Stripe_CardError;

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
        $errorMessage = "Payment process failed. Your registration has been cancelled";

        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $customer = Stripe_Customer::create([
                "name" => $user->name,
                'email' => $user->email,
                'source' => $validatedData['stripeToken']
            ]);

            try {
                Stripe_Charge::create([
                    'customer' => $customer->id,
                    'amount' => $amount,
                    'currency' => 'usd',
                ]);

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
            } catch (Stripe_CardError | Stripe_InvalidRequestError $e) {
                $user->tokens()->delete();
                $user->delete();
                return $this->error($errorMessage, 400);
            }
        } catch (Stripe_CardError | Stripe_InvalidRequestError $e) {
            $user->tokens()->delete();
            $user->delete();
            return $this->error($errorMessage, 400);
        }
    }

    public function chargeWallet(Request $request, $userId)
    {
        $validatedData = customValidate($request->all(), [
            "amount" => "required|integer|min:1"
        ]);

        validateUserId($userId);
        $user = User::find($userId);
        $amount = $validatedData['amount'] * 100;
        $wallet = $user->wallet;
        $balance = $wallet->balance;
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            Stripe_Charge::create([
                'customer' => $user->stripeId,
                'amount' => $amount,
                'currency' => 'usd',
            ]);

            $wallet->balance = $balance + $amount;
            $wallet->save();
            $user->payment()->create([
                'amount' => $amount,
                'type' => 'wallet_charge'
            ]);
            return $this->success(true, "Amount has been added in the wallet successfully", 200);
        } catch (Stripe_CardError | Stripe_InvalidRequestError $e) {
            return $this->error("Error processing the request. Please try again", 400);
        }
    }
}
