<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consultants extends Model
{

	protected $primaryKey = 'consultant_id';
    protected $table = 'reports';
    protected $fillable = [
      'consultant_type','expected_rate','feedback', 'keywords','type', 'admin_status','job_id','first_name','last_name','user_status','consultant_email','consultant_mobile_number','visaType','userId','technology_id','consultant_status','city','state','willingLocation','documentsCollected','rate','resource','ssn','availability','skypeId','schedule_date','adminStatus','timezone','linkedInUrl','bestContactNumber','experience','resume','tax_type','company_mobile_number','company_email','vendor_company_name','job_id'
    ];
    protected $appends = ['keywords'];

/*
	public function newQuery($excludeDeleted = true) {
        return parent::newQuery($excludeDeleted)
            ->where('consultant_type', '=', 'A');
    } 
	*/

	public function user()
	{
		return $this->belongsTo(User::class, 'userId');
	}
    public function withJobs()
	{
		return $this->belongsTo(Jobs::class, 'job_id');
	}
    public function technologies()
	{
		return $this->belongsTo(Technologies::class, 'technology_id');
	}
    public function submissions()
	{
		return $this->hasMany(Submissions::class, 'consultant_id');
	}
    public function vendor_add()
	{
		return $this->hasMany(Submissions::class, 'consultant_id');
    }
	public function vendor_cout()
	{
		return $this->hasMany(Submissions::class ,'consultant_id');
    }
    public function vendor_int()
	{
		return $this->belongsToMany(Submissions::class ,'consultant_id');
    }

    public function vendor_consultant()
	{
		return $this->belongsTo(Submissions::class, 'consultant_id');
    }
    public function getkeywordsAttribute()
    {

        return unserialize($this->attributes['keywords']);
    }
}
