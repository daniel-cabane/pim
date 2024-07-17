<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = ['author_id', 'workshop_id', 'questions', 'options', 'status'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function format($includeWorkshopName = false)
    {
        $options = json_decode($this->options);
        $mainTitle = $options->language == 'fr' ? $options->title_fr : $options->title_en;
        $formatted = [
            'id' => $this->id,
            'mainTitle' => $mainTitle,
            'questions' => json_decode($this->questions),
            'options' => $options,
            'status' => $this->status,
            'author' => [
                'id' => $this->author_id,
                'name' => $this->author->formal_name
            ],
            'workshopId' => $this->workshop_id
        ];
        if($includeWorkshopName && $this->workshop){
            $workshopMainTitle = $this->workshop->language == 'fr' ? $this->workshop->title_fr : $this->workshop->title_en;
            $formatted['workshopName'] = $workshopMainTitle;
        }

        return $formatted;
    }
}
