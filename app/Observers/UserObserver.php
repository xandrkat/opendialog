<?php

namespace App\Observers;

use App\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Propaganistas\LaravelPhone\PhoneNumber;
use Srmklive\Authy\Facades\Authy;

class UserObserver
{
    public function creating(User $user)
    {
        $this->updatePhone($user);
    }

    public function updating(User $user)
    {
        if ($user->isDirty('phone_number')) {
            $this->updatePhone($user);
        }
    }

    /**
     * Split phone number and register user for 2fa
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    private function updatePhone(User $user)
    {
        $phone = PhoneNumber::make($user->phone_number);
        $user->setAuthPhoneInformation($phone->getPhoneNumberInstance()->getCountryCode(), $phone->formatNational());

        try {
           // Second parameter enforces SMS.
            Authy::getProvider()->register($user, true);
        } catch (\Exception $e) {
            app(ExceptionHandler::class)->report($e);
            return response()->json(['error' => ['Unable To Register User For 2 Factor Authentication']], 422);
        }
    }
}
