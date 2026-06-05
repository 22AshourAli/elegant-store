<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['branch_id', 'category', 'description', 'amount', 'expense_date', 'created_by'];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public const CATEGORIES = ['salaries', 'rent', 'damaged_items', 'utilities', 'other'];

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'salaries' => 'رواتب',
            'rent' => 'إيجار',
            'damaged_items' => 'توالف',
            'utilities' => 'مرافق',
            'other' => 'أخرى',
            default => $category,
        };
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
