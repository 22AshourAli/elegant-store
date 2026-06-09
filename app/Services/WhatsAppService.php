<?php

namespace App\Services;

use App\Models\WhatsappLog;
use App\Models\User;

class WhatsAppService
{
    public function normalisePhone(?string $phone): ?string
    {
        if (empty($phone)) return null;

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $phone = '+20' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+20' . $phone;
        }

        return $phone;
    }

    public function waLink(string $phone, string $message = ''): string
    {
        $phone = ltrim($phone, '+');
        $text = $message ? '&text=' . urlencode($message) : '';

        return "https://api.whatsapp.com/send/?phone={$phone}{$text}&type=phone_number&app_absent=0";
    }

    public function logMessage(int $userId, int $sentBy, string $message, string $status = 'sent'): WhatsappLog
    {
        return WhatsappLog::create([
            'user_id' => $userId,
            'sent_by' => $sentBy,
            'message' => $message,
            'message_type' => 'marketing',
            'status' => $status,
            'sent_at' => now(),
        ]);
    }

    public function customerWaInfo(User $customer, string $message): ?array
    {
        $phone = $this->normalisePhone($customer->phone);
        if (!$phone) return null;

        return [
            'name' => $customer->name,
            'phone' => $phone,
            'wa_link' => $this->waLink($phone, $message),
        ];
    }
}