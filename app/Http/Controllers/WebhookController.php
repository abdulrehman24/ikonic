<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Extract necessary data from the request
        $data = [
            'order_id' => $request->order_id,
            'discount_code' => $request->discount_code,
            "subtotal_price" => $request->subtotal_price,
            "merchant_domain" => $request->merchant_domain
        ];

        $response = $this->orderService->processOrder($data);

        return response()->json(['message' => $response]);
    }

}
