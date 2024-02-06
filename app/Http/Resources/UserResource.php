<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

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
            // //public_image_url
            //'image_url' => $this->image ? URL::to($this->image) : null,
            //local_image_url
            'image_url' => $this->image ? env('APP_URL') . Storage::url($this->image) : null,
            'pdf_url' => $this->file ? env('APP_URL') . Storage::url($this->file) : null,
            'audio_url' => $this->audio ? env('APP_URL') . Storage::url($this->audio) : null,
            'video_url' => $this->video ? env('APP_URL') . Storage::url($this->video) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
