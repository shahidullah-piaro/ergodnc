<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class UserResource extends JsonResource
{

    //public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id'=>(string)$this->id,
            'name'=>$this->name,
            'email'=>$this->email,
            'fathers_name'=>$this->fathers_name,
            'mothers_name'=>$this->mothers_name,
            'start_date' => (new \DateTime($this->start_date))->format('Y-m-d'),
            'end_date' => (new \DateTime($this->end_date))->format('Y-m-d'),
            'nid'=>$this->nid,
            'image_url' => $this->image ? URL::to($this->image) : null,
            'file_url' => $this->file ? URL::to($this->file) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
