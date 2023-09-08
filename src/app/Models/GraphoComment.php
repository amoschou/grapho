<?php
 
namespace AMoschou\Grapho\App\Models;
 
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraphoComment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grapho_comments';

    /**
     * Get the author of the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
