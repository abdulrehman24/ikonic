<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // Extract the 'from' and 'to' dates from the request
        $fromDate = $request->from;
        $toDate = $request->to;

        // Calculate the order statistics based on the date range
        $orderCount = Order::whereBetween('created_at', [$fromDate, $toDate])->count();
        $unpaidCommissions = Order::where('affiliate_id', '!=', null)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('commission_owed');
           
        $revenue = Order::whereBetween('created_at', [$fromDate, $toDate])->sum('subtotal');
    
        // Return JSON
        return response()->json([
            'count' => $orderCount,
            'commissions_owed' => $unpaidCommissions,
            'revenue' => $revenue,
        ]);
    }
    
}
