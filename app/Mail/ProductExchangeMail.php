<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Mail\Mailable;

class ProductExchangeMail extends Mailable
{
    public $returnRequest;

    public function __construct(ReturnRequest $returnRequest)
    {
        $this->returnRequest = $returnRequest;
    }

    public function build()
    {
        return $this->subject('Échange de produit pour votre commande #'.$this->returnRequest->order_id)
            ->markdown('emails.product-exchange')
            ->with([
                'request' => $this->returnRequest
            ]);
    }
} 