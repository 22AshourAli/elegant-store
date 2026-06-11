<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {--to= : Recipient email address (defaults to MAIL_FROM_ADDRESS)}';
    protected $description = 'Test SMTP connectivity and send a test email to verify Gmail configuration';

    public function handle(): int
    {
        $this->info('=== Mail Configuration Test ===');
        $this->newLine();

        // 1. Print current configuration
        $mailer   = config('mail.default');
        $host     = config('mail.mailers.smtp.host');
        $port     = config('mail.mailers.smtp.port');
        $username = config('mail.mailers.smtp.username');
        $from     = config('mail.from.address');
        $enc      = config('mail.mailers.smtp.encryption');

        $this->line("Default mailer : <comment>{$mailer}</comment>");
        $this->line("SMTP host      : <comment>{$host}</comment>");
        $this->line("SMTP port      : <comment>{$port}</comment>");
        $this->line("SMTP username  : <comment>{$username}</comment>");
        $this->line("Encryption     : <comment>{$enc}</comment>");
        $this->line("From address   : <comment>{$from}</comment>");
        $this->newLine();

        // 2. Guard: warn if mailer is not smtp
        if ($mailer !== 'smtp') {
            $this->warn("MAIL_MAILER is set to '{$mailer}', not 'smtp'. Emails will NOT be delivered via Gmail.");
            $this->warn("Set MAIL_MAILER=smtp in your Railway environment variables.");
            $this->newLine();
        }

        // 3. Guard: warn if host looks like localhost
        if (empty($host) || $host === '127.0.0.1' || $host === 'localhost') {
            $this->error("MAIL_HOST is '{$host}'. This is a local placeholder — Gmail SMTP will not work.");
            $this->error("Set MAIL_HOST=smtp.gmail.com in your Railway environment variables.");
            return 1;
        }

        // 4. TCP connectivity check
        $this->line("Testing TCP connection to {$host}:{$port} ...");
        $socket = @fsockopen($host, (int) $port, $errno, $errstr, 10);
        if ($socket === false) {
            $this->error("TCP connection FAILED: [{$errno}] {$errstr}");
            $this->error("Railway may be blocking outbound SMTP on port {$port}. Try port 465 (SSL) or 587 (TLS).");
            return 1;
        }
        fclose($socket);
        $this->info("TCP connection OK.");
        $this->newLine();

        // 5. Attempt to send a real test email
        $to = $this->option('to') ?: $from;
        if (empty($to)) {
            $this->error("No recipient address. Pass --to=you@example.com or set MAIL_FROM_ADDRESS.");
            return 1;
        }

        $this->line("Sending test email to <comment>{$to}</comment> ...");

        try {
            Mail::raw(
                "This is a test email from Elegant Store sent at " . now()->toDateTimeString() . ".\n\n"
                . "If you received this, Gmail SMTP is configured correctly on Railway.",
                function (Message $message) use ($to, $from) {
                    $message->to($to)
                            ->subject('[Elegant Store] SMTP Test — ' . now()->toDateTimeString())
                            ->from($from, config('mail.from.name'));
                }
            );

            $this->info("Test email sent successfully to {$to}.");
            $this->info("Check the inbox (and spam folder) to confirm delivery.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send test email.");
            $this->error("Error: " . $e->getMessage());
            $this->newLine();
            $this->line("<comment>Common causes:</comment>");
            $this->line("  • MAIL_USERNAME / MAIL_PASSWORD are wrong or missing");
            $this->line("  • Gmail 'App Password' not used (2FA accounts require an App Password)");
            $this->line("  • 'Less secure app access' disabled and no App Password set");
            $this->line("  • MAIL_ENCRYPTION mismatch (use 'tls' for port 587, 'ssl' for port 465)");
            $this->line("  • Railway firewall blocking outbound SMTP");
            return 1;
        }
    }
}
