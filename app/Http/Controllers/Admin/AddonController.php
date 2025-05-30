<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddonInstallRequest;
use App\Repositories\Interfaces\Admin\AddonInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AddonController extends Controller
{
    protected $addon;

    public function __construct(AddonInterface $addon){
        $this->addon = $addon;
    }

}
