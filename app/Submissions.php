<?php

/**
 * Created by Reliese Model.
 */

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Jobs
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
class Submissions extends Model
{
    protected $table = 'submissions';
    protected $primaryKey = 'submission_id';

    protected $fillable = [
        'userId',
        'consultant_id','vendorEmail',
        'vendor_company_contact_id',
        'clientId',
        'actualRate',
        'submissionRate',
        'submissionStatus',
        'userId',
        'timezone', 'scheduleDate'
    ];

    public function user_details()
    {
        return $this->belongsTo(User::class, 'userId');
    }
    public function consultant()
    {
        return $this->belongsTo(Consultants::class, 'consultant_id');
    }
    public function contactList()
    {
        return $this->belongsTo(Contacts::class, 'vendor_company_contact_id');
    }
    public function clients()
    {
        return $this->belongsTo(Clients::class, 'clientId');
    }

}
