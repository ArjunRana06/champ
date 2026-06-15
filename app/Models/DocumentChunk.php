<?php
// app/Models/DocumentChunk.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = ['document_id', 'chunk_index', 'content', 'vector_id'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
