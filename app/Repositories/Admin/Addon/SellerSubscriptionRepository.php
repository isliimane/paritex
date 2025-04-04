<?php

namespace App\Repositories\Admin\Addon;

use App\Models\SellerPackage;
use App\Models\SellerSubscription;
class SellerSubscriptionRepository
{
    public function all()
    {
        return SellerSubscription::latest();
    }

    public function paginate($limit,$data=[])
    {
        return SellerSubscription::with('package','user.sellerProfile')->whereHas('user.sellerProfile')->whereHas('package')->when(arrayCheck('status',$data), function ($query) use ($data){
            $query->where('status',$data['status']);
        })->when(arrayCheck('user_id',$data), function ($query) use ($data){
            $query->where('user_id',$data['user_id']);
        })->when(arrayCheck('offline',$data), function ($query) use ($data){
            $query->where('payment_method','offline_method');
        })->when(!arrayCheck('offline',$data), function ($query) use ($data){
            $query->where('payment_method','!=','offline_method');
        })->when(arrayCheck('q',$data), function ($query) use ($data){
            $query->where(function ($query) use ($data){
                $query->orWhereHas('user.sellerProfile',function ($query) use ($data){
                    $query->where('shop_name','like','%'.$data['q'].'%');
                })->orWhereHas('package.packageLanguages',function ($query) use ($data){
                    $query->where('title','like','%'.$data['q'].'%');
                })->orWhere('amount','like','%'.$data['q'].'%')
                    ->orWhere('payment_method','like','%'.$data['q'].'%')
                    ->orWhere('trx_id','like','%'.$data['q'].'%');
            });
        })->when(arrayCheck('s',$data), function ($query) use ($data){
            if ($data['s'] == 'oldest_on_top')
            {
                $query->oldest();
            }
            elseif ($data['s'] == 'price_high')
            {
                $query->orderBy('amount','desc');
            }
            elseif ($data['s'] == 'price_low')
            {
                $query->orderBy('amount','asc');
            }
            else
            {
                $query->latest();
            }
        })->latest()->paginate($limit);
    }

    public function find($id)
    {
        return SellerSubscription::find($id);
    }

    public function store($data)
    {
        return SellerSubscription::create($data);
    }

    public function update($data,$id)
    {
        $package = $this->find($id);

        $package->update($data);

        return $package;
    }

    public function statusChange($request): bool
    {
        $activeSubscription = $this->find($request['id']);
        $status = $request['status'] == 1 ? 'published' : 'unpublished';
        $activeSubscription->user->products()->update([
            'status' => $status
        ]);

        $activeSubscription->status    = $request['status'];
        $activeSubscription->save();
        return true;
    }

    public function destroy($id): int
    {
        return SellerSubscription::destroy($id);
    }
}
