<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->BookID,
            'title' => $this->Title,
            'author' => $this->Author,
            'price' => $this->Price,
            'stock' => $this->StockQuantity,
            'image' => $this->Image,
            'category_id' => $this->CategoryID,
            'category' => $this->category ? $this->category->Name : null,
            'created_at' => $this->CreatedAt,
        ];
    }
}