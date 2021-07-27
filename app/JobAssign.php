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
class JobAssign extends Model
{
    protected $table = 'job_assigns';
	protected $primaryKey = 'job_assign_id';

	protected $fillable = [
		'job_id','assign_id'
	];
	public function jobs()
	{
		return $this->belongsTo(Job::class, 'job_id');
	}

    public function users()
    {
        return $this->belongsTo(User::class, 'assign_id');
    }

}
