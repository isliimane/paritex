<?php

namespace App\Repositories\Interfaces\Admin;

interface ReturnRequestRepositoryInterface
{
    public function createReturnRequest(array $data);
    public function getReturnRequestsForUser(int $userId);
    public function updateReturnRequestStatus(int $requestId, string $status);
    public function getReturnRequestById(int $requestId);
}