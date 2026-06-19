<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class DashboardExport extends DefaultValueBinder implements FromArray, WithCustomValueBinder, WithTitle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            ['Elegant Store — Financial Report', now()->format('Y-m-d')],
            [],
            ['Metric', 'Value'],
            ['Total Orders', $this->data['totalOrders']],
            ['Online Orders', $this->data['onlineOrders']],
            ['Offline Orders', $this->data['offlineOrders']],
            ['Product Revenue', number_format((int) round($this->data['totalProductRevenue'])) . ' EGP'],
            ['Shipping Collected', number_format((int) round($this->data['totalShippingCollected'])) . ' EGP'],
            ['Cost of Goods Sold', number_format((int) round($this->data['totalCosts'])) . ' EGP'],
            ['Other Expenses', number_format((int) round($this->data['totalManualExpenses'])) . ' EGP'],
            ['Net Profit', number_format((int) round($this->data['netProfit'])) . ' EGP'],
            ['Profit Margin', $this->data['profitMargin'] . '%'],
            ['Average Order Value', number_format((int) round($this->data['aov'])) . ' EGP'],
        ];
    }

    public function title(): string
    {
        return 'Financial Report';
    }
}
