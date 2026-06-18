@extends('admin.layouts.app')
@section('page-title', __('global.admin_customers'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 overflow-hidden">
    {{-- Header --}}
    <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h3 class="font-extrabold text-lg">{{ __('global.admin_customers') }} <span class="text-slate-400 text-sm font-bold">({{ $customers->count() }})</span></h3>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('global.search') }}..." class="border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white w-48">
            <button type="submit" class="bg-brand-primary text-white px-4 py-2 rounded-lg hover:bg-brand-hover transition font-bold text-sm">{{ __('global.search') }}</button>
            @if ($search)
                <a href="{{ route('admin.customers.index') }}" class="border border-slate-200 dark:border-slate-600 text-slate-500 px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition text-sm font-bold">{{ __('global.cancel') }}</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="text-right px-4 py-3">{{ __('global.admin_name') }}</th>
                    <th class="text-right px-4 py-3">{{ __('global.admin_phone') }}</th>
                    <th class="text-right px-4 py-3 hidden md:table-cell">{{ __('global.email') }}</th>
                    <th class="text-center px-4 py-3 hidden md:table-cell">{{ __('global.admin_orders') }}</th>
                    <th class="text-center px-4 py-3">{{ __('global.admin_total_spent') }}</th>
                    <th class="text-center px-4 py-3 hidden md:table-cell">{{ __('global.admin_date') }}</th>
                    <th class="text-center px-4 py-3">{{ __('global.admin_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition cursor-pointer even:bg-gray-50/50 dark:even:bg-gray-700/20"
                        onclick="openCustomerModal({{ $customer->id }})">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-brand-primary/10 text-brand-primary dark:bg-accent/20 dark:text-accent flex items-center justify-center text-xs font-black flex-shrink-0">
                                    {{ mb_substr($customer->name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold truncate">{{ $customer->name }}</p>
                                    <div class="flex gap-1 mt-0.5">
                                        @if(!empty($customer->email))
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">{{ __('global.admin_online') }}</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">{{ __('global.admin_offline') }}</span>
                                        @endif
                                        @if(($customer->orders_count ?? 0) > 0)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">{{ __('global.admin_buyer') }}</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">{{ __('global.admin_new_customer') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $customer->phone ?? '---' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs hidden md:table-cell">{{ $customer->email ?? '---' }}</td>
                        <td class="px-4 py-3 text-center font-bold hidden md:table-cell">{{ $customer->orders_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-center font-black text-brand-primary">{{ number_format($customer->total_spent ?? 0) }} {{ __('global.currency') }}</td>
                        <td class="px-4 py-3 text-center text-xs text-slate-500 hidden md:table-cell">{{ $customer->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.orders.index') }}?user_id={{ $customer->id }}" class="text-[10px] text-indigo-500 hover:text-indigo-700 font-bold transition">{{ __('global.admin_view_orders') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400 font-bold">{{ __('global.admin_no_customers') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($customers->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $customers->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
async function openCustomerModal(id) {
    const existing = document.getElementById('customer-modal');
    if (existing) existing.remove();

    const res = await fetch(`{{ url('admin/customers/') }}/${id}`);
    if (!res.ok) return alert('{{ __('global.admin_error') }}');
    const data = await res.json();

    const ordersHtml = data.orders.length === 0
        ? `<p class="text-sm text-slate-400 text-center py-6">{{ __('global.admin_no_orders') }}</p>`
        : `<div class="space-y-2">` + data.orders.map(o => `
            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 border border-slate-100 dark:border-slate-700">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold">#${o.id}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold ${o.order_type === 'offline' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300'}">${o.order_type === 'offline' ? '{{ __('global.admin_offline') }}' : '{{ __('global.admin_online') }}'}</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">${o.created_at} · ${o.items_count} {{ __('global.admin_items') }} · ${o.payment_method}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-black">${o.total.toLocaleString()} {{ __('global.currency') }}</p>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold ${o.status === 'confirmed' || o.status === 'delivered' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : o.status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-500'}">${o.status}</span>
                </div>
            </div>
        `).join('') + `</div>`;

    const modal = document.createElement('div');
    modal.id = 'customer-modal';
    modal.innerHTML = `
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             onclick="document.getElementById('customer-modal')?.remove()">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-14 h-14 rounded-full bg-brand-primary/10 text-brand-primary dark:bg-accent/20 dark:text-accent flex items-center justify-center text-sm font-black shrink-0 border-2 border-slate-200 dark:border-slate-600">
                        {{ mb_substr('${data.name}', 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-extrabold text-lg">${data.name}</h3>
                        <div class="flex gap-1 mt-1">
                            ${data.email
                                ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">{{ __('global.admin_online') }}</span>`
                                : `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">{{ __('global.admin_offline_customer') }}</span>`
                            }
                            ${data.orders_count > 0
                                ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">{{ __('global.admin_has_purchased') }}</span>`
                                : `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">{{ __('global.admin_new_customer') }}</span>`
                            }
                        </div>
                        <p class="text-sm text-slate-500"><span>${data.phone || '---'}</span>${data.email ? ' · ' + data.email : ''}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('global.admin_customer_since') }} ${data.created_at}</p>
                    </div>
                    <button onclick="document.getElementById('customer-modal')?.remove()" class="text-slate-400 hover:text-slate-600 transition shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3 text-center">
                        <p class="text-2xl font-black text-brand-primary">${data.orders_count}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ __('global.admin_orders') }}</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3 text-center">
                        <p class="text-lg font-black text-emerald-600">${data.total_spent.toLocaleString()} {{ __('global.currency') }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ __('global.admin_total_spent') }}</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3 text-center">
                        <p class="text-lg font-black text-amber-600">${data.orders.length > 0 ? data.orders[0].created_at : '---'}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ __('global.admin_last_order') }}</p>
                    </div>
                </div>
                <h4 class="font-bold text-sm mb-3">{{ __('global.admin_order_history') }}</h4>
                ${ordersHtml}
                <div class="mt-4 flex gap-2">
                    <a href="{{ url('admin/orders') }}?user_id=${data.id}" class="flex-1 text-center py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition">{{ __('global.admin_view_orders') }}</a>
                    <button onclick="document.getElementById('customer-modal')?.remove()" class="flex-1 py-2.5 rounded-xl bg-brand-primary text-white text-sm font-extrabold hover:bg-brand-hover transition">{{ __('global.close') }}</button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modal);
}
</script>
@endpush
@endsection
