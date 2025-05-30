<?php

namespace App\Repositories\Interfaces\Admin;

interface AddonInterface{

    public function get($id);

    public function all();


    public function paginate($limit);

    public function activePlugin();

    public function activeAddons();
}
