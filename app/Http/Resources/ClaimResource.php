<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'admin_response' => $this->admin_response,
            'response_date' => $this->response_date,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
            'order' => $this->whenLoaded('order', function () {
                return [
                    'id' => $this->order->id,
                    'code' => $this->order->code,
                    'date' => $this->order->created_at->format('Y-m-d')
                ];
            })
        ];
    }
}