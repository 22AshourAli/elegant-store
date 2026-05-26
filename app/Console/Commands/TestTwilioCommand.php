<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTwilioCommand extends Command
{
    protected $signature = 'twilio:test {--to=} {--message=}' ;
    protected $description = 'Send a test WhatsApp message via Twilio using configured env vars';

    public function handle(): int
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_WHATSAPP_FROM');

        if (!$sid || !$token || !$from) {
            $this->error('TWILIO_SID, TWILIO_AUTH_TOKEN or TWILIO_WHATSAPP_FROM not configured in environment.');
            return 1;
        }

        $to = $this->option('to') ?: config('store.admin_whatsapp', env('ADMIN_WHATSAPP', env('ADMIN_PHONE')));
        if (!$to) {
            $this->error('No destination number provided via --to and no admin number configured.');
            return 1;
        }

        $message = $this->option('message') ?: 'This is a test message from Elegant Store at ' . now();

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $response = Http::withBasicAuth($sid, $token)->asForm()->post($url, [
            'From' => 'whatsapp:' . $from,
            'To' => 'whatsapp:' . $to,
            'Body' => $message,
        ]);

        if ($response->successful()) {
            $this->info('WhatsApp test message sent successfully to ' . $to);
            $this->line('Response: ' . $response->body());
            return 0;
        }

        $this->error('Failed to send message. HTTP status: ' . $response->status());
        $this->line('Response: ' . $response->body());
        return 1;
    }
}
