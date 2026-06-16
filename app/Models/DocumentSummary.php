<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSummary extends Model
{
    protected $fillable = [
        'user_id',
        'document_id',
        'summary',
        'model_used',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
