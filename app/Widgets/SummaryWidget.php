<?php
namespace App\Widgets;

use App\Widgets\BaseWidget;

class SummaryWidget extends BaseWidget
{
    public string $name = 'Summary';
    public string $permission = 'dashboard.view';

    public function data(): array
    {
        // Eventually, these will come from models
        return [
            'summary' => [
                'quotes_today'   => rand(10, 30),
                'invoices_today' => rand(5, 20),
                'new_users'      => rand(1, 5),
                'open_tasks'     => rand(2, 8),
            ],
            'chart' => [
                'labels' => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                'values' => [12,19,3,5,2,3,8],
            ],
        ];
    }
}
