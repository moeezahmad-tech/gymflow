<?php
/**
 * GymFlow - Automated Payment Reminder & Messaging Engine
 * Formatted for SMS Gateways (Twilio / HTTP SMS) and WhatsApp Business API
 */

if (!defined('APP_INIT')) {
    require_once __DIR__ . '/app.php';
}
require_once __DIR__ . '/database.php';

class NotificationEngine {
    
    // Default Messaging Templates
    const TEMPLATE_SMS_DUE_TODAY = 'Dear {{member_name}}, today is {{due_date}}, please pay your gym membership fee of PKR {{amount}} to continue your 24/7 access. Pay online at: {{payment_url}} - Gym Flow';
    
    const TEMPLATE_SMS_OVERDUE = 'URGENT: Dear {{member_name}}, your Gym Flow fee of PKR {{amount}} was due on {{due_date}} and is OVERDUE. Settle online to avoid turnstile pause: {{payment_url}}';

    const TEMPLATE_WHATSAPP_DUE_TODAY = "⚡ *Gym Flow Membership Alert* ⚡\n\nDear *{{member_name}}*,\n\nThis is a reminder that your *{{plan_name}}* subscription is due today (*{{due_date}}*) for *PKR {{amount}}*.\n\nTo ensure uninterrupted 24/7 access to our fitness sanctuary, please settle your fee via your Member Portal:\n👉 {{payment_url}}\n\nFront Desk: {{gym_phone}}\n_Stay Strong & Keep Flowing!_ 🏋️";

    const TEMPLATE_WHATSAPP_OVERDUE = "🚨 *URGENT: Gym Flow Access Notice* 🚨\n\nDear *{{member_name}}*,\n\nYour membership fee of *PKR {{amount}}* was due on *{{due_date}}* ({{days_overdue}} days ago) and is currently *OVERDUE*.\n\nYour 24/7 turnstile keycard clearance may be paused until settled.\n\n👉 Settle Online Instantly: {{payment_url}}\n\nNeed assistance? Reply to this message or call {{gym_phone}}.";

    /**
     * Replace template placeholders with real member/payment data
     */
    public static function formatMessage(string $template, array $data): string {
        $search = [
            '{{member_name}}',
            '{{due_date}}',
            '{{amount}}',
            '{{plan_name}}',
            '{{payment_url}}',
            '{{gym_phone}}',
            '{{days_overdue}}',
            '{{member_code}}'
        ];

        $paymentUrl = BASE_URL . '/login.php';
        $gymPhone   = '+92 300 1234567';

        $replace = [
            $data['full_name'] ?? 'Member',
            !empty($data['due_date']) ? date('M j, Y', strtotime($data['due_date'])) : date('M j, Y'),
            number_format((float)($data['amount'] ?? 8000), 2),
            $data['plan_name'] ?? '3 Month Package',
            $paymentUrl,
            $gymPhone,
            max(1, (int)($data['days_overdue'] ?? 1)),
            $data['member_code'] ?? 'GF-98234'
        ];

        return str_replace($search, $replace, $template);
    }

    /**
     * Dispatch SMS Message (Simulated & Gateway Ready)
     */
    public static function sendSMS(string $phone, string $message, ?int $userId = null, ?int $paymentId = null): array {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Log to database
        self::logNotification($userId, $paymentId, 'sms', $phone, $message, 'sent');

        return [
            'success'   => true,
            'channel'   => 'sms',
            'recipient' => $phone,
            'message'   => $message,
            'status'    => 'delivered',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Dispatch WhatsApp Message (Simulated & Meta Cloud / Twilio API Ready)
     */
    public static function sendWhatsApp(string $phone, string $message, ?int $userId = null, ?int $paymentId = null): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Log to database
        self::logNotification($userId, $paymentId, 'whatsapp', $phone, $message, 'sent');

        // Create direct WhatsApp Click-to-Chat deep link
        $waLink = "https://api.whatsapp.com/send?phone=" . urlencode($cleanPhone) . "&text=" . urlencode($message);

        return [
            'success'   => true,
            'channel'   => 'whatsapp',
            'recipient' => $phone,
            'message'   => $message,
            'wa_link'   => $waLink,
            'status'    => 'delivered',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Automated Cron Scan: Find memberships due today or overdue and dispatch alerts
     */
    public static function scanAndSendDailyReminders(): array {
        $dispatched = [];
        
        try {
            $db = getDB();

            // Find all pending & overdue fees where due_date <= today
            $stmt = $db->query("
                SELECT p.id AS payment_id, p.amount, p.due_date, p.status AS pay_status,
                       u.id AS user_id, u.full_name, u.email, u.phone, u.member_code,
                       mp.name AS plan_name,
                       DATEDIFF(CURDATE(), p.due_date) AS days_overdue
                FROM payments p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN memberships m ON p.membership_id = m.id
                LEFT JOIN membership_plans mp ON m.plan_id = mp.id
                WHERE p.status IN ('pending', 'overdue')
                  AND p.due_date IS NOT NULL
                  AND p.due_date <= CURDATE()
                ORDER BY p.due_date ASC
            ");
            $duesList = $stmt->fetchAll();

            foreach ($duesList as $due) {
                $isOverdue = ($due['pay_status'] === 'overdue' || (int)$due['days_overdue'] > 0);
                
                // Select appropriate template
                $smsText = $isOverdue 
                    ? self::formatMessage(self::TEMPLATE_SMS_OVERDUE, $due)
                    : self::formatMessage(self::TEMPLATE_SMS_DUE_TODAY, $due);

                $waText = $isOverdue
                    ? self::formatMessage(self::TEMPLATE_WHATSAPP_OVERDUE, $due)
                    : self::formatMessage(self::TEMPLATE_WHATSAPP_DUE_TODAY, $due);

                $recipientPhone = !empty($due['phone']) ? $due['phone'] : '+923001234567';

                // Dispatch SMS
                $smsRes = self::sendSMS($recipientPhone, $smsText, (int)$due['user_id'], (int)$due['payment_id']);
                // Dispatch WhatsApp
                $waRes  = self::sendWhatsApp($recipientPhone, $waText, (int)$due['user_id'], (int)$due['payment_id']);

                $dispatched[] = [
                    'member'     => $due['full_name'],
                    'phone'      => $recipientPhone,
                    'amount'     => $due['amount'],
                    'due_date'   => $due['due_date'],
                    'is_overdue' => $isOverdue,
                    'sms'        => $smsRes,
                    'whatsapp'   => $waRes
                ];
            }

        } catch (Exception $e) {
            error_log("Cron scan error: " . $e->getMessage());
        }

        return $dispatched;
    }

    /**
     * Record dispatch entry in notifications_log
     */
    private static function logNotification(?int $userId, ?int $paymentId, string $channel, string $recipient, string $message, string $status): void {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO notifications_log (user_id, payment_id, channel, recipient, message, status, sent_at)
                VALUES (:uid, :pid, :chan, :rec, :msg, :stat, NOW())
            ");
            $stmt->execute([
                ':uid'  => $userId ?: 1,
                ':pid'  => $paymentId,
                ':chan' => $channel,
                ':rec'  => $recipient,
                ':msg'  => $message,
                ':stat' => $status
            ]);
        } catch (Exception $e) {
            // Table may not exist yet in demo mode
            error_log("Notification log error: " . $e->getMessage());
        }
    }
}
