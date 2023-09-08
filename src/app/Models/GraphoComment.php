<?php
 
namespace AMoschou\Grapho\App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
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
    public function post(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
