<?php

/**
 * Created by Reliese Model.
 */

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Journal
 *
 * @property int $journalId
 * @property string $journalName
 * @property string $journalStatus
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection|JournalEventTaxConfig[] $journal_event_tax_configs
 *
 * @package App\Models
 */
class UserAssign extends Model
{
    protected $table = 'user_assigns';
	protected $primaryKey = 'user_assign_id';

	protected $fillable = [
		'userId','assign_id'
	];

    public function user()
    {
        return $this->belongsTo(User::class, 'assign_id');
    }

}
