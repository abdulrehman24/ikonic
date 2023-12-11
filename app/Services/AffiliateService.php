<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Merchant;
use App\Models\Affiliate;
use Illuminate\Support\Str;
use App\Mail\AffiliateCreated;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\AffiliateCreateException;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        if ($merchant->user->email === $email || Affiliate::where('merchant_id', $merchant->id)->whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists()) {
            throw new AffiliateCreateException('Email is already in use by the merchant or another affiliate for the same merchant');
        }

        // Create a new user for the affiliate
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt(Str::random(16)),
            'type' => User::TYPE_MERCHANT,
        ]);

        // Create a new discount code for the affiliate
        $discountCode = $this->apiService->createDiscountCode($merchant);

        // Create a new affiliate for the merchant
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id'=>$merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode['code'],
        ]);

        // Send an email to the affiliate
        Mail::to($affiliate->user)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
