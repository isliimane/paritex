<?php
// app/Repositories/Interfaces/ClaimInterface.php
namespace App\Repositories\Interfaces;

interface ClaimInterface
{
    public function create(array $data);
    public function find($id);
    public function userClaims($userId, $paginate = 10);
    public function allClaims($paginate = 10);
    public function update($id, array $data);
    public function delete($id);
}