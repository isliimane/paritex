<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\Product\CategoryInterface;
use App\Repositories\Interfaces\Admin\ReportsInterface;
use App\Repositories\Interfaces\UserInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $reports;
    private $categories;
    private $user;

    public function __construct(ReportsInterface       $reports,
                                CategoryInterface      $categories,
                                UserInterface          $user
                            )
    {
        $this->reports              = $reports;
        $this->categories           = $categories;
        $this->user                 = $user;
    }

    public function adminProducts(Request $request)
    {
        try {
            $categories = $this->categories->allCategory()->where('status', 1);
            $products = $this->reports->product($request, get_pagination('pagination'), 'for_admin','');

            return view('admin.reports.admin-product', compact('products', 'categories'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function stockProduct(Request $request)
    {
        try {
            $categories = $this->categories->allCategory()->where('status', 1);
            $all_filter_products = $this->reports->stockProduct($request, get_pagination('pagination'));
            return view('admin.reports.product-stock', compact('categories', 'all_filter_products'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function productWishlists(Request $request)
    {
        try {
            $categories = $this->categories->allCategory()->where('status', 1);
            $wishlist = $this->reports->wishlist($request, get_pagination('pagination'));
            return view('admin.reports.product-wishlist', compact('categories', 'wishlist'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function userSearches(Request $request)
    {
        try {
            $searches = $this->reports->searches($request, get_pagination('pagination'));
            return view('admin.reports.user-searches', compact('searches'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function walletRecharge(Request $request)
    {
        try {
            $walletRechargeHistory = $this->reports->walletRechargeHistory($request, get_pagination('pagination'));
            $selected_user = null;
            if (isset($request->u)):
                $selected_user = $this->user->get($request->u);
            endif;
            return view('admin.reports.wallet-recharge-history', compact('walletRechargeHistory', 'selected_user'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

}
