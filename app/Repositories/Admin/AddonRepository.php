<?php

namespace App\Repositories\Admin;

use App\Models\Addon;
use App\Repositories\Interfaces\Admin\AddonInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AddonRepository implements AddonInterface
{
    public function all()
    {
        return Addon::latest();
    }

    public function paginate($limit)
    {
        return $this->all()->paginate($limit);
    }

    public function get($id)
    {
        return Addon::find($id);
    }

    public function activePlugin()
    {
        return Addon::where('status', 1)->pluck('addon_identifier')->toArray();
    }

    public function activeAddons()
    {
        return Addon::where('status', 1)->selectRaw('id,name,addon_identifier,version')->get();
    }

}
