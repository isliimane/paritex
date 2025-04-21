<?php

namespace App\Repositories;

use App\Models\Claim;
use App\Repositories\Interfaces\Admin\ClaimInterface;

class ClaimRepository implements ClaimInterface
{
    protected $model;

    public function __construct(Claim $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function userClaims($userId, $paginate = 10)
    {
        return $this->model->where('user_id', $userId)
                          ->with('order')
                          ->latest()
                          ->paginate($paginate);
    }

    public function allClaims($paginate = 10)
    {
        return $this->model->with(['user', 'order'])
                          ->latest()
                          ->paginate($paginate);
    }

    public function update($id, array $data)
    {
        $claim = $this->find($id);
        return $claim->update($data);
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}