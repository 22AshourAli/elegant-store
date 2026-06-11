<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BulkMarketingMail;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class WhatsappMarketingController extends Controller
{
    public function __construct(
        private readonly WhatsAppService $whatsApp
    ) {}

    public function bulkForm()
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $withEmail = User::where('role', 'customer')
            ->whereNotNull('email')->where('email', '!=', '')->count();
        $withPhone = User::where('role', 'customer')
            ->whereNotNull('phone')->where('phone', '!=', '')->count();
        $previousBuyers = User::where('role', 'customer')
            ->whereHas('orders', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->count();

        return view('admin.whatsapp.bulk', [
            'totalCustomers' => $totalCustomers,
            'withEmail' => $withEmail,
            'withPhone' => $withPhone,
            'withoutEmail' => $totalCustomers - $withEmail,
            'previousBuyers' => $previousBuyers,
        ]);
    }

    public function sendBulk(Request $request)
    {
        set_time_limit(120);
        $request->validate([
            'channel' => 'required|in:whatsapp,email,mixed',
            'audience' => 'required|in:all,online,offline,previous_buyers',
            'subject' => 'required_if:channel,email|string|max:255',
            'message' => 'required|string|max:10000',
        ]);

        $query = User::where('role', 'customer');

        if ($request->audience === 'previous_buyers') {
            $query->whereHas('orders', fn($q) => $q->where('status', '!=', 'cancelled'));
        } elseif ($request->audience === 'online') {
            $query->online();
        } elseif ($request->audience === 'offline') {
            $query->offline();
        }

        $customers = $query->get();

        if ($customers->isEmpty()) {
            return back()->with('error', 'لا يوجد عملاء مطابقين للاختيار.');
        }

        if ($request->channel === 'email') {
            return $this->sendBulkEmail($customers, $request->message);
        }

        if ($request->channel === 'mixed') {
            $withEmail = $customers->filter(fn($c) => !empty($c->email));
            $withoutEmail = $customers->filter(fn($c) => empty($c->email));

            $emailResult = $this->sendBulkEmail($withEmail, $request->message, true);

            $results = $withoutEmail
                ->map(fn($c) => $this->whatsApp->customerWaInfo($c, $request->message))
                ->filter()
                ->values()
                ->toArray();

            $stats = [
                'total' => $customers->count(),
                'email_sent' => $emailResult['sent'],
                'email_failed' => $emailResult['failed'],
                'wa_links' => count($results),
            ];

            return view('admin.whatsapp.bulk-results', compact('results', 'stats'));
        }

        $results = $customers
            ->map(fn($c) => $this->whatsApp->customerWaInfo($c, $request->message))
            ->filter()
            ->values()
            ->toArray();

        $stats = [
            'total' => $customers->count(),
            'wa_links' => count($results),
        ];

        return view('admin.whatsapp.bulk-results', compact('results', 'stats'));
    }

    private function sendBulkEmail($customers, string $message, bool $returnStats = false)
    {
        $sent = 0;
        $failed = 0;

        foreach ($customers as $customer) {
            if (empty($customer->email)) {
                $failed++;
                continue;
            }
            try {
                $start = microtime(true);
                Mail::mailer('smtp')->to($customer->email)->send(new BulkMarketingMail($customer, $message));
                $elapsed = round(microtime(true) - $start, 2);
                \Log::info("Bulk email sent to {$customer->email} in {$elapsed}s");
                $this->whatsApp->logMessage($customer->id, auth()->id(), $message);
                $sent++;
            } catch (\Exception $e) {
                \Log::error("Bulk email failed for {$customer->id}: " . $e->getMessage());
                $failed++;
            }
        }

        if ($returnStats) {
            return ['sent' => $sent, 'failed' => $failed];
        }

        return redirect()->route('admin.whatsapp.index')
            ->with('success', "تم إرسال {$sent} رسالة بنجاح" . ($failed ? "، فشل {$failed}" : ''));
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
        $info = $this->whatsApp->customerWaInfo($user, '');

        return view('admin.whatsapp.show', compact('customer', 'totalOrders', 'totalSpent', 'logs') + [
            'waPhone' => $info['phone'] ?? '',
            'waLink' => $info['wa_link'] ?? '',
        ]);
    }

    public function sendMessage(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $info = $this->whatsApp->customerWaInfo($user, $request->message);

        if (!$info) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'رقم الهاتف غير متوفر لهذا العميل.'], 422);
            }
            return back()->with('error', 'رقم الهاتف غير متوفر لهذا العميل.');
        }

        $this->whatsApp->logMessage($user->id, auth()->id(), $request->message);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'wa_link' => $info['wa_link'],
                'phone' => $info['phone'],
                'customer_name' => $info['name'],
            ]);
        }

        return redirect()->away($info['wa_link']);
    }

    public function nextInLine()
    {
        $messagedIds = WhatsappLog::pluck('user_id')->unique();

        $next = User::where('role', 'customer')
            ->whereNotIn('id', $messagedIds)
            ->select('users.*')
            ->selectSub(fn($q) => $q->selectRaw('COALESCE(SUM(total), 0)')->from('orders')->whereColumn('orders.user_id', 'users.id'), 'total_spent')
            ->orderByDesc('total_spent')
            ->first();

        if (!$next) {
            $next = User::where('role', 'customer')
                ->select('users.*')
                ->selectSub(fn($q) => $q->selectRaw('COALESCE(SUM(total), 0)')->from('orders')->whereColumn('orders.user_id', 'users.id'), 'total_spent')
                ->orderByDesc('total_spent')
                ->first();
        }

        return redirect()->route('admin.whatsapp.show', ['user' => $next->id]);
    }

    public function markSent(User $user)
    {
        $this->whatsApp->logMessage($user->id, auth()->id(), 'تم الإرسال عبر واتساب');

        return back()->with('success', 'تم تسجيل الإرسال بنجاح.');
    }
}