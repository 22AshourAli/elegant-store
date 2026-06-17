@extends('admin.layouts.app')

@section('page-title', __('global.admin_payment_reconciliation'))

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-bold">&larr; {{ __('global.admin_back_to_reports') }}</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 text-center">
        <p class="text-sm text-slate-500 dark:text-slate-400 font-bold mb-1">{{ __('global.admin_total_payments') }}</p>
        <p class="text-3xl font-black text-brand-primary">{{ number_format($data['gross_revenue'] ?? 0) }} {{ __('global.currency') }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 text-center">
        <p class="text-sm text-slate-500 dark:text-slate-400 font-bold mb-1">{{ __('global.admin_total_fees') }}</p>
        <p class="text-3xl font-black text-rose-600">{{ number_format($data['total_gateway_fees'] ?? 0) }} {{ __('global.currency') }}</p>
        <p class="text-[10px] text-slate-400 mt-1">{{ __('global.admin_fee_percent_of_revenue', ['percent' => $data['fee_percent'] ?? 0]) }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6 text-center">
        <p class="text-sm text-slate-500 dark:text-slate-400 font-bold mb-1">{{ __('global.admin_net_amount') }}</p>
        <p class="text-3xl font-black text-emerald-600">{{ number_format($data['net_revenue'] ?? 0) }} {{ __('global.currency') }}</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-6">
    <h3 class="font-extrabold text-lg mb-4 text-slate-900 dark:text-white">{{ __('global.admin_by_gateway') }}</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="text-right px-4 py-3">{{ __('global.admin_gateway') }}</th>
                    <th class="text-center px-4 py-3">{{ __('global.admin_transactions') }}</th>
                    <th class="text-center px-4 py-3">{{ __('global.admin_total') }}</th>
                    <th class="text-center px-4 py-3">{{ __('global.admin_fees') }}</th>
                    <th class="text-center px-4 py-3">{{ __('global.admin_net') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse(($data['by_gateway'] ?? []) as $gw)
                <tr>
                    <td class="px-4 py-3 font-bold capitalize">{{ $gw->gateway }}</td>
                    <td class="px-4 py-3 text-center">{{ $gw->transaction_count }}</td>
                    <td class="px-4 py-3 text-center font-black">{{ number_format($gw->gross_amount) }} {{ __('global.currency') }}</td>
                    <td class="px-4 py-3 text-center text-rose-600 font-bold">{{ number_format($gw->total_fees) }} {{ __('global.currency') }}</td>
                    <td class="px-4 py-3 text-center text-emerald-600 font-black">{{ number_format($gw->net_amount) }} {{ __('global.currency') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-6 text-slate-400">{{ __('global.admin_no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection