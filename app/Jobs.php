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
class Jobs extends Model
{
    protected $table = 'jobs';
	protected $primaryKey = 'job_id';

	protected $fillable = [
		'job_code','job_title','technology_id','client_id','userId',
        'city','state','client_bill_rate','pay_rate','experience','visa_type','prime_vendor_id','partner_id',
       'work_type','main_requirement','description','duration','responsibilities','created_at','status','vendor_company_id','mandatoryskills','w2_rate'
	];

    protected $appends = ['visatype','mandatoryskills','assign_id'];


	public function user()
	{
		return $this->belongsTo(User::class, 'userId');
	}
    public function technologies()
	{
		return $this->belongsTo(Technologies::class, 'technology_id');
	}
    public function clients()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }
    public function assignee()
	{
		return $this->hasMany(JobAssign::class, 'job_id');
	}
    public function benchtalents()
	{
		return $this->hasMany(Consultants::class, 'job_id');
	}
    public function benchtalentswhere()
	{
		return $this->hasMany(Consultants::class, 'job_id');
	}
    public function benchtalentswhereclient(){
        return $this->hasMany(Consultants::class, 'job_id');
    }
    public function getvisatypeAttribute()
    {

        return unserialize($this->attributes['visa_type']);
    }
    public function partner()
	{
		return $this->belongsTo(Partners::class, 'vendor_company_id');
	}

    public function primevendor()
	{
		return $this->belongsTo(PrimeVendors::class, 'vendor_company_id');
	}
    public function getmandatoryskillsAttribute()
    {

        return unserialize($this->attributes['mandatoryskills']);
    }

    public function getAssignIdAttribute()
    {
        return $this->assignee->pluck('assign_id');
    }
}
