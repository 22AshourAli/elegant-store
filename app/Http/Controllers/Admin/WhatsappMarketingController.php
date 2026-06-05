<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BulkMarketingMail;
use App\Models\User;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WhatsappMarketingController extends Controller
{
    public function bulkForm()
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $previousBuyers = User::where('role', 'customer')
            ->whereHas('orders', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->count();

        return view('admin.whatsapp.bulk', compact('totalCustomers', 'previousBuyers'));
    }

    public function sendBulk(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:whatsapp,email',
            'audience' => 'required|in:all,previous_buyers',
            'subject' => 'required_if:channel,email|string|max:255',
            'message' => 'required|string|max:10000',
        ]);

        $query = User::where('role', 'customer');

        if ($request->audience === 'previous_buyers') {
            $query->whereHas('orders', fn($q) => $q->where('status', '!=', 'cancelled'));
        }

        $customers = $query->get();

        if ($customers->isEmpty()) {
            return back()->with('error', 'لا يوجد عملاء مطابقين للاختيار.');
        }

        if ($request->channel === 'email') {
            $sent = 0;
            $failed = 0;
            foreach ($customers as $customer) {
                try {
                    Mail::to($customer->email)->send(new BulkMarketingMail($customer, $request->message));
                    WhatsappLog::create([
                        'user_id' => $customer->id,
                        'sent_by' => auth()->id(),
                        'message' => $request->message,
                        'message_type' => 'marketing',
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    $sent++;
                } catch (\Exception $e) {
                    \Log::error("Bulk email failed for {$customer->id}: " . $e->getMessage());
                    $failed++;
                }
            }
            return redirect()->route('admin.whatsapp.index')
                ->with('success', "تم إرسال {$sent} رسالة بنجاح" . ($failed ? "، فشل {$failed}" : ''));
        }

        // WhatsApp: generate links and show results page
        $results = $customers->map(function ($customer) use ($request) {
            $phone = preg_replace('/[^0-9]/', '', $customer->phone);
            if (str_starts_with($phone, '00')) {
                $phone = '+' . substr($phone, 2);
            } elseif (str_starts_with($phone, '0')) {
                $phone = '+20' . substr($phone, 1);
            } elseif (!str_starts_with($phone, '+')) {
                $phone = '+20' . $phone;
            }
            return [
                'name' => $customer->name,
                'phone' => $phone,
                'wa_link' => 'https://wa.me/' . $phone . '?text=' . urlencode($request->message),
            ];
        })->filter(fn($r) => !empty($r['phone']));

        return view('admin.whatsapp.bulk-results', compact('results', 'request'));
    }

    public function index()
    {
        $customers = User::where('role', 'customer')
            ->select('users.*')
            ->selectSub(function ($q) {
                $q->selectRaw('COALESCE(COUNT(*), 0)')
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id');
            }, 'total_orders')
            ->selectSub(function ($q) {
                $q->selectRaw('COALESCE(SUM(total), 0)')
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id');
            }, 'total_spent')
            ->orderByDesc('total_spent')
            ->paginate(20);

        $customerIds = $customers->pluck('id');
        $latestLogs = WhatsappLog::whereIn('user_id', $customerIds)
            ->select('user_id', 'status', 'sent_at', 'message')
            ->orderBy('sent_at', 'desc')
            ->get()
            ->groupBy('user_id')
            ->map(fn($logs) => $logs->first());

        return view('admin.whatsapp.index', compact('customers', 'latestLogs'));
    }

    public function show(User $user)
    {
        $user->load('orders');
        $totalOrders = $user->orders->count();
        $totalSpent = (int) $user->orders->sum('total');
        $logs = WhatsappLog::where('user_id', $user->id)
            ->with('sentBy')
            ->latest()
            ->get();

        $customer = $user;

        return view('admin.whatsapp.show', compact('customer', 'totalOrders', 'totalSpent', 'logs'));
    }

    public function sendMessage(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $user->phone);
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $phone = '+20' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+20' . $phone;
        }

        $waLink = 'https://wa.me/' . $phone . '?text=' . urlencode($request->message);

        WhatsappLog::create([
            'user_id' => $user->id,
            'sent_by' => auth()->id(),
            'message' => $request->message,
            'message_type' => 'marketing',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->away($waLink);
    }

    public function nextInLine()
    {
        $messagedCustomerIds = WhatsappLog::pluck('user_id')->unique();

        $nextCustomer = User::where('role', 'customer')
            ->whereNotIn('id', $messagedCustomerIds)
            ->select('users.*')
            ->selectSub(function ($q) {
                $q->selectRaw('COALESCE(SUM(total), 0)')
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id');
            }, 'total_spent')
            ->orderByDesc('total_spent')
            ->first();

        if (!$nextCustomer) {
            $nextCustomer = User::where('role', 'customer')
                ->select('users.*')
                ->selectSub(function ($q) {
                    $q->selectRaw('COALESCE(SUM(total), 0)')
                        ->from('orders')
                        ->whereColumn('orders.user_id', 'users.id');
                }, 'total_spent')
                ->orderByDesc('total_spent')
                ->first();
        }

        return redirect()->route('admin.whatsapp.show', ['user' => $nextCustomer->id]);
    }

    public function markSent(User $user)
    {
        $customer = $user;

        WhatsappLog::create([
            'user_id' => $user->id,
            'sent_by' => auth()->id(),
            'message' => 'تم الإرسال عبر واتساب',
            'message_type' => 'marketing',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'تم تسجيل الإرسال بنجاح.');
    }
}
