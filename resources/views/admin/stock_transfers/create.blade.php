@extends('admin.layouts.app')
@section('page-title', __('global.admin_add_transfer'))
@section('content')
<div class="bg-white dark:bg-gray-800 rounded shadow p-6 max-w-4xl mx-auto">
    <form action="{{ route('admin.stock-transfers.store') }}" method="POST" x-data="stForm()">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_from_branch') }} <span class="text-red-500">*</span></label>
                <select name="from_branch_id" required class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                    <option value="">{{ __('global.admin_select') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('from_branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('global.admin_to_branch') }} <span class="text-red-500">*</span></label>
                <select name="to_branch_id" required class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                    <option value="">{{ __('global.admin_select') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('to_branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">{{ __('global.admin_notes') }}</label>
            <textarea name="notes" rows="2" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">{{ old('notes') }}</textarea>
        </div>

        <h3 class="text-lg font-bold mb-3">{{ __('global.admin_items') }}</h3>

        <template x-for="(item, index) in items" :key="index">
            <div class="flex gap-2 mb-2 items-end">
                <div class="flex-1">
                    <select :name="'items[' + index + '][variant_id]'" x-model="item.variant_id" required class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">{{ __('global.admin_select') }}</option>
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->sku }} - {{ $variant->product->name ?? '' }} ({{ $variant->color }} / {{ $variant->size }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" min="1" required placeholder="{{ __('global.admin_qty') }}" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                </div>
                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 p-2">&times;</button>
            </div>
        </template>

        <button type="button" @click="addItem()" class="text-indigo-600 hover:text-indigo-800 text-sm mb-4">{{ __('global.admin_add_item') }}</button>

        <div class="flex gap-2 mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">{{ __('global.admin_save') }}</button>
            <a href="{{ route('admin.stock-transfers.index') }}" class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500">{{ __('global.admin_cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function stForm() {
    return {
        items: [{ variant_id: '', quantity: 1 }],
        addItem() { this.items.push({ variant_id: '', quantity: 1 }); },
        removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); }
    }
}
</script>
@endpush
