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
class Companies extends Model
{
    protected $table = 'vendor_companies';
	protected $primaryKey = 'vendor_company_id';

	protected $fillable = [
		'name','created_at','userId','vendor_type'
	];

    public function contacts()
	{
		return $this->hasMany(Contacts::class, 'vendor_company_id');
	}
    public function user()
	{
		return $this->belongsTo(User::class, 'userId');
	}
}
