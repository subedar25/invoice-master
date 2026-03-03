<?php

namespace App\Core\Email\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Core\Email\Services\GenericEmail;
use Illuminate\Mail\Mailable;
use App\Models\Setting;
use App\Models\SmtpAccount;

class EmailService
{
    /**
     * Send an email using the GenericEmail mailable.
     *
     * @param string|array $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @param array $options
     * @return bool
     */
    public function send($to, string $subject, string $view, array $data = [], array $options = []): bool
    {
        try {

        $mailerName = null;
        $smtpId = $options['send_smtp_id'] ?? config('mail.send_smtp_id');

        if ($smtpId) {
          $smtp_setting  = SmtpAccount::find($smtpId);
    
          if($smtp_setting){
            Config::set("mail.mailers.{$smtp_setting->smtp_from_name}", [
                'transport' => 'smtp',
                'host' => $smtp_setting->smtp_host,
                'port' => $smtp_setting->smtp_port,
                'encryption' => $smtp_setting->smtp_secure,
                'username' => $smtp_setting->smtp_username,
                'password' => $smtp_setting->smtp_password,
            ]);
            $mailerName = $smtp_setting->smtp_from_name;

          }
        }
 
        
            $mailable = new GenericEmail($subject, $view, $data);

            // Set recipients
            $mailable->to($this->formatRecipients($to));

            // Set CC if provided
            if (!empty($options['cc'])) {
                $mailable->cc($this->formatRecipients($options['cc']));
            }

            // Set BCC if provided
            if (!empty($options['bcc'])) {
                $mailable->bcc($this->formatRecipients($options['bcc']));
            }

            // Set attachments if provided
            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_string($attachment)) {
                        // Simple path to file
                        $mailable->attach($attachment);
                    } elseif (is_array($attachment) && isset($attachment['path'])) {
                        // Advanced attachment with options
                        $mailable->attach(
                            $attachment['path'],
                            [
                                'as' => $attachment['as'] ?? basename($attachment['path']),
                                'mime' => [ $attachment['mime'] => null ],
                            ]
                        );
                    }
                }
            }
            
            // Set reply-to if provided
            if (!empty($options['replyTo'])) {
                $mailable->replyTo($options['replyTo']['address'], $options['replyTo']['name'] ?? null);
            }

            // Send the email (use custom mailer if provided)
            if (!empty($mailerName)) {
                Mail::mailer($mailerName)->send($mailable);
            } else {
                Mail::send($mailable);
            }

            return true;
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Email sending failed: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Send an email using a fully configured Mailable object.
     * This gives you more control if needed.
     *
     * @param Mailable $mailable
     * @return bool
     */
    public function sendMailable(Mailable $mailable): bool
    {
        try {
            Mail::send($mailable);
            return true;
        } catch (\Exception $e) {
            Log::error('Mailable sending failed: ' . $e->getMessage(), [
                'mailable' => get_class($mailable),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Format recipients to be an array of email addresses.
     *
     * @param string|array $recipients
     * @return array
     */
    private function formatRecipients($recipients): array
    {
        if (is_string($recipients)) {
            return [$recipients];
        }

        if (is_array($recipients)) {
            // Handle arrays like ['email@example.com', 'other@example.com' => 'Other Name']
            return array_keys($recipients);
        }

        return [];
    }

}
