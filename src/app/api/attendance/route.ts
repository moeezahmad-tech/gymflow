import { NextResponse } from 'next/server';
import { db } from '@/database';
import { users, attendance } from '@/database/schema';
import { eq } from 'drizzle-orm';

/**
 * Normalizes member code from various QR code formats:
 * - Direct: "2026-4"
 * - URL: "http://localhost:3000/scan?code=2026-4"
 * - Prefix: "GYMFLOW:PASS:2026-4"
 */
function cleanMemberCode(raw: string): string {
  let cleaned = raw.trim();
  if (cleaned.includes('code=')) {
    try {
      const url = new URL(cleaned, 'http://localhost');
      const codeParam = url.searchParams.get('code');
      if (codeParam) cleaned = codeParam;
    } catch {
      const match = cleaned.match(/code=([^&]+)/);
      if (match) cleaned = match[1];
    }
  } else if (cleaned.includes(':')) {
    const parts = cleaned.split(':');
    cleaned = parts[parts.length - 1];
  }
  return decodeURIComponent(cleaned).trim();
}

/**
 * Next.js Serverless Route Handler: Turnstile Verification & Logging
 * GET /api/attendance?code=2026-4&log=true
 */
export async function GET(request: Request) {
  try {
    const { searchParams } = new URL(request.url);
    const rawCode = searchParams.get('code') || searchParams.get('memberCode');
    const shouldLog = searchParams.get('log') !== 'false';

    if (!rawCode) {
      return NextResponse.json(
        { success: false, message: 'Member code is required as ?code=...' },
        { status: 400 }
      );
    }

    const memberCode = cleanMemberCode(rawCode);

    // Verify Member in Neon DB
    const member = await db.query.users.findFirst({
      where: eq(users.memberCode, memberCode),
      with: {
        subscriptions: {
          with: {
            plan: true,
          },
        },
      },
    });

    if (!member) {
      return NextResponse.json(
        {
          success: false,
          access: 'denied',
          memberCode,
          message: `Unknown member code "${memberCode}". Not registered in system.`,
        },
        { status: 404 }
      );
    }

    if (member.status !== 'active') {
      if (shouldLog) {
        await db.insert(attendance).values({
          userId: member.id,
          entryType: 'qr_scanner',
          status: 'denied',
          notes: `Access denied: Member status is ${member.status}`,
        });
      }

      return NextResponse.json(
        {
          success: false,
          access: 'denied',
          member: {
            id: member.id,
            fullName: member.fullName,
            memberCode: member.memberCode,
            status: member.status,
            avatar: member.avatar,
          },
          message: `Access Denied: Membership status is ${member.status.toUpperCase()}. Visit front desk.`,
        },
        { status: 403 }
      );
    }

    // Access granted
    if (shouldLog) {
      await db.insert(attendance).values({
        userId: member.id,
        entryType: 'qr_scanner',
        status: 'granted',
        notes: 'Turnstile verified & unlocked',
      });
    }

    const latestSub = member.subscriptions?.[0];

    return NextResponse.json({
      success: true,
      access: 'granted',
      timestamp: new Date().toISOString(),
      member: {
        id: member.id,
        fullName: member.fullName,
        memberCode: member.memberCode,
        email: member.email,
        phone: member.phone,
        status: member.status,
        avatar: member.avatar,
        planName: latestSub?.plan?.name || 'Strength & Conditioning Tier',
        subscriptionStatus: latestSub?.status || 'active',
        expiryDate: latestSub?.endDate ? new Date(latestSub.endDate).toLocaleDateString() : 'Active (Paid)',
      },
      message: `Access Granted. Welcome to GymFlow, ${member.fullName}!`,
    });
  } catch (error: any) {
    console.error('[Attendance API GET Error]:', error);
    return NextResponse.json(
      { success: false, message: 'Internal turnstile processing error.' },
      { status: 500 }
    );
  }
}

/**
 * POST /api/attendance
 * Body: { memberCode: string, entryType?: 'qr_scanner' | 'rfid_keycard' | 'manual' }
 */
export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { memberCode: rawCode, entryType = 'qr_scanner' } = body;

    if (!rawCode) {
      return NextResponse.json(
        { success: false, message: 'Member code is required.' },
        { status: 400 }
      );
    }

    const memberCode = cleanMemberCode(rawCode);

    // 1. Verify Member Exists & Is Active
    const member = await db.query.users.findFirst({
      where: eq(users.memberCode, memberCode),
      with: {
        subscriptions: {
          with: {
            plan: true,
          },
        },
      },
    });

    if (!member) {
      return NextResponse.json(
        { success: false, access: 'denied', memberCode, message: `Unknown member code "${memberCode}".` },
        { status: 404 }
      );
    }

    if (member.status !== 'active') {
      // Log Denied Access
      await db.insert(attendance).values({
        userId: member.id,
        entryType,
        status: 'denied',
        notes: `Access denied: Member status is ${member.status}`,
      });

      return NextResponse.json(
        {
          success: false,
          access: 'denied',
          member: {
            id: member.id,
            fullName: member.fullName,
            memberCode: member.memberCode,
            status: member.status,
          },
          message: `Access Denied: Membership is ${member.status}. Please visit reception.`,
        },
        { status: 403 }
      );
    }

    // 2. Log Granted Access
    await db.insert(attendance).values({
      userId: member.id,
      entryType,
      status: 'granted',
      notes: 'Turnstile access granted',
    });

    const latestSub = member.subscriptions?.[0];

    return NextResponse.json({
      success: true,
      access: 'granted',
      timestamp: new Date().toISOString(),
      member: {
        id: member.id,
        fullName: member.fullName,
        memberCode: member.memberCode,
        status: member.status,
        planName: latestSub?.plan?.name || 'Strength & Conditioning Tier',
      },
      message: `Welcome, ${member.fullName}! Turnstile gate unlocked.`,
    });
  } catch (error: any) {
    console.error('[Attendance API Error]:', error);
    return NextResponse.json(
      { success: false, message: 'Internal turnstile processing error.' },
      { status: 500 }
    );
  }
}
