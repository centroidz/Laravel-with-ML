<?php

// app/Models/Reviewer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviewer extends Model
{
    use HasFactory;

    // The columns that can be mass-assigned (user_id, summary, questions)
    protected $fillable = [
        'user_id',
        'summary',
        'questions',
        'audio_path',
    ];

    // Casts ensure the data type is correct when retrieved from the database.
    // The 'questions' column is stored as TEXT/LONGTEXT but retrieved as an array (JSON).
    protected $casts = [
        'questions' => 'array',
    ];

    /**
     * Get the user that owns the reviewer item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
