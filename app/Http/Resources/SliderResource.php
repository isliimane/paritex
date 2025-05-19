<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class   SliderResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id'                    => $this->id,
            'action_type'           => $this->action_type,
            'action_to'             => $this->action_type == 'url' ? $this->link: (int)$this->link,
            'banner'                => getFileLink('970x400',$this->bg_image),
        ];
        if($this->action_type == 'category')
        {
            $data['title'] = $this->category->getTranslation('title',apiLanguage($request->lang));
        }
        if($this->action_type == 'brand')
        {
            $data['title'] = $this->brand->getTranslation('title',apiLanguage($request->lang));
        }
        return $data;
    }
}
