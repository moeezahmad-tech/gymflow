import { NextResponse } from 'next/server';
import { db } from '@/database';
import { users, payments, notificationsLog } from '@/database/schema';
import { eq, and, sql } from 'drizzle-orm';

/**
 * Next.js Serverless Automated Cron: 10th-Cutoff Billing & Reminders Scanner
 * GET /api/cron/payment-reminders?secret=CRON_SECRET
 */
export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const authHeader = request.headers.get('authorization');
  const secret = searchParams.get('secret') || authHeader?.replace('Bearer ', '');

  // 1. Verify Secret
  if (process.env.CRON_SECRET && secret !== process.env.CRON_SECRET) {
    return NextResponse.json({ success: false, message: 'Unauthorized cron trigger.' }, { status: 401 });
  }

  try {
    // 2. Scan for Overdue or Expiring Payments
    const pendingPayments = await db.query.payments.findMany({
      where: eq(payments.status, 'pending'),
      with: {
        user: true,
      },
    });

    let remindersQueued = 0;

    for (const payment of pendingPayments) {
      if (!payment.user || !payment.user.phone) continue;

      // Log notification entry in notifications_log
      await db.insert(notificationsLog).values({
        userId: payment.userId,
        paymentId: payment.id,
        channel: 'whatsapp',
        recipient: payment.user.phone,
        message: `Hello ${payment.user.fullName}, this is a reminder from GymFlow. Your monthly gym dues of ₨ ${payment.amount} are scheduled for the 10th cutoff. Please renew at the desk or via JazzCash/EasyPaisa.`,
        status: 'sent',
      });

      remindersQueued++;
    }

    return NextResponse.json({
      success: true,
      timestamp: new Date().toISOString(),
      pendingScanned: pendingPayments.length,
      remindersQueued,
      message: 'Automated 10th-cutoff payment scan complete.',
    });
  } catch (error: any) {
    console.error('[Cron Error]:', error);
    return NextResponse.json({ success: false, message: 'Cron execution failed.' }, { status: 500 });
  }
}

