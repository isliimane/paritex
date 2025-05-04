<?php

namespace App\Repositories\Interfaces\Site;

interface ComplaintInterface{

    public function paginate($paginate);

    public function storeComplaint($request);

    public function reply($request);
}
