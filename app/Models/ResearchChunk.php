<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'stud_file_id',
        'chunk_index',
        'section',
        'page_number',
        'content',
        'token_count',
        'content_hash',
        'embedding_model',
        'embedding_version',
    ];

    // `embedding` (pgvector) is intentionally not fillable/cast here — Eloquent
    // has no native vector type. Writes go through a raw `::vector` cast
    // (see App\Services\Research\EmbeddingService), and similarity queries
    // are raw SQL using the `<=>` cosine-distance operator.

    public function studFile()
    {
        return $this->belongsTo(UserFile::class, 'stud_file_id');
    }
}
