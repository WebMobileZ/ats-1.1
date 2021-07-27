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
class Contacts extends Model
{
    protected $table = 'vendor_company_contacts';
	protected $primaryKey = 'vendor_company_contact_id';

	protected $fillable = [
		'vendor_company_contact_id','vendor_company_id','contactName','contactMobile','contactEmail','created_at','userId'
	];

    public function companies()
	{
		return $this->belongsTo(Companies::class, 'vendor_company_id');
	}
}
