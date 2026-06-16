<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardExport implements FromArray, WithHeadings, WithTitle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            ['التقرير المالي', now()->format('Y-m-d')],
            [],
            ['البيان', 'القيمة'],
            ['إجمالي الطلبات', $this->data['totalOrders']],
            ['طلبات Online', $this->data['onlineOrders']],
            ['طلبات Offline', $this->data['offlineOrders']],
            ['إيراد المنتجات', number_format((int) round($this->data['totalProductRevenue'])) . ' EGP'],
            ['شحن محصل', number_format((int) round($this->data['totalShippingCollected'])) . ' EGP'],
            ['تكلفة البضاعة', number_format((int) round($this->data['totalCosts'])) . ' EGP'],
            ['مصروفات أخرى', number_format((int) round($this->data['totalManualExpenses'])) . ' EGP'],
            ['صافي الربح', number_format((int) round($this->data['netProfit'])) . ' EGP'],
            ['هامش الربح', $this->data['profitMargin'] . '%'],
            ['متوسط قيمة الطلب', number_format((int) round($this->data['aov'])) . ' EGP'],
        ];
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'تقرير المبيعات';
    }
}
