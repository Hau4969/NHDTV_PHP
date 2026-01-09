<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'vouchers';
    protected $guarded = [];

    // Scope để dùng trong query (khuyên dùng)
    public function scopeIsActive($q)
    {
        $now = now();
        return $q->where('status','active')
            ->where(fn($q)=>$q->whereNull('start_at')->orWhere('start_at','<=',$now))
            ->where(fn($q)=>$q->whereNull('end_at')->orWhere('end_at','>=',$now))
            ->where(fn($q)=>$q->whereNull('usage_limit')->orWhereColumn('used_count','<','usage_limit'));
    }

    // Method để check 1 bản ghi (nếu cần dùng ở chỗ store)
    public function isActive(): bool
    {
        $now = now();
        if ($this->status !== 'active') return false;
        if (!is_null($this->start_at) && $this->start_at > $now) return false;
        if (!is_null($this->end_at) && $this->end_at < $now) return false;
        if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) return false;
        return true;
    }
}
