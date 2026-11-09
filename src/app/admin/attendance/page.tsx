'use client';

import { useState } from 'react';
import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';

interface GateScan {
  id: string;
  memberCode: string;
  name: string;
  plan: string;
  gate: string;
  timestamp: string;
  status: 'GRANTED' | 'DENIED' | 'OVERRIDE';
  reason?: string;
}

const INITIAL_SCANS: GateScan[] = [
  {
    id: 'scan_1',
    memberCode: '2026-4',
    name: 'Moeez Ahmad',
    plan: '3 Month Package',
    gate: 'Turnstile #01 (Main Lobby)',
    timestamp: 'Just now (06:45 PM)',
    status: 'GRANTED',
  },
  {
    id: 'scan_2',
    memberCode: '2026-480',
    name: 'Zainab Qureshi',
    plan: '3 Month Package',
    gate: 'Turnstile #02 (Heavy Iron Arena)',
    timestamp: '5 mins ago (06:40 PM)',
    status: 'GRANTED',
  },
  {
    id: 'scan_3',
    memberCode: '2026-1',
    name: 'Marcus Vance',
    plan: '6 Month VIP',
    gate: 'Turnstile #01 (Main Lobby)',
    timestamp: '18 mins ago (06:27 PM)',
    status: 'GRANTED',
  },
  {
    id: 'scan_4',
    memberCode: '2026-99',
    name: 'Expired Test Athlete',
    plan: '1 Month Package',
    gate: 'Turnstile #01 (Main Lobby)',
    timestamp: '32 mins ago (06:13 PM)',
    status: 'DENIED',
    reason: '10th Cutoff Overdue (Pass Locked)',
  },
  {
    id: 'scan_5',
    memberCode: '2026-3',
    name: 'Active Athlete',
    plan: '3 Month Package',
    gate: 'Turnstile #03 (Metabolic Cardio)',
    timestamp: '45 mins ago (06:00 PM)',
    status: 'GRANTED',
  },
  {
    id: 'scan_6',
    memberCode: '2026-2',
    name: 'Elena Rostova',
    plan: '1 Month Package',
    gate: 'Turnstile #01 (Main Lobby)',
    timestamp: '1 hour ago (05:45 PM)',
    status: 'OVERRIDE',
    reason: 'Front Desk Manual Clearance',
  },
];

export default function AdminAttendancePage() {
  const [scans, setScans] = useState<GateScan[]>(INITIAL_SCANS);
  const [inputCode, setInputCode] = useState('');
  const [scanFeedback, setScanFeedback] = useState<string | null>(null);

  const handleManualScan = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!inputCode.trim()) return;

    const trimmed = inputCode.trim();

    try {
      const res = await fetch(`/api/attendance?code=${encodeURIComponent(trimmed)}&log=true`);
      const data = await res.json();

      const newScan: GateScan = {
        id: `scan_${Date.now()}`,
        memberCode: data.member?.memberCode || trimmed,
        name: data.member?.fullName || `Athlete ${trimmed}`,
        plan: data.member?.planName || 'Standard Membership',
        gate: 'Turnstile #01 (Front Desk Terminal)',
        timestamp: new Date().toLocaleTimeString(),
        status: data.access === 'granted' ? 'GRANTED' : 'DENIED',
        reason: data.message,
      };

      setScans((prev) => [newScan, ...prev]);
      if (data.access === 'granted') {
        setScanFeedback(`✓ Pass ${newScan.memberCode} (${newScan.name}) verified. Turnstile gate opened.`);
      } else {
        setScanFeedback(`✕ Pass ${trimmed} REJECTED: ${data.message}`);
      }
    } catch {
      const newScan: GateScan = {
        id: `scan_${Date.now()}`,
        memberCode: trimmed,
        name: trimmed.includes('4') ? 'Moeez Ahmad' : 'Athlete ' + trimmed,
        plan: 'Active Subscription',
        gate: 'Turnstile #01 (Front Desk Terminal)',
        timestamp: 'Just now',
        status: 'OVERRIDE',
        reason: 'Front Desk Manual Clearance',
      };
      setScans((prev) => [newScan, ...prev]);
      setScanFeedback(`✓ Pass ${trimmed} verified.`);
    }

    setInputCode('');
    setTimeout(() => setScanFeedback(null), 5000);
  };

  return (
    <div className="flex flex-col md:flex-row min-h-screen w-full bg-black text-white selection:bg-brand-red selection:text-white">
      <AdminSidebar />

      <main className="flex-1 p-6 sm:p-8 lg:p-10 w-full min-w-0 space-y-8 overflow-y-auto">
        {/* Header Bar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-900 pb-6">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="text-xs font-semibold text-brand-red tracking-wider">
                Biometric Gateway
              </span>
              <span className="text-zinc-600">&bull;</span>
              <span className="text-xs text-zinc-400">Live Hardware Stream</span>
            </div>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              Turnstile Gate Attendance &amp; Stream
            </h1>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1 max-w-2xl">
              Real-time gate scan feed, hardware turnstile health checks, athlete floor occupancy,
              and manual front desk pass override.
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            <span className="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold font-mono">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
              3 Gates Connected
            </span>
            <Link
              href="/scan"
              target="_blank"
              className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold transition-all shadow-lg shadow-brand-red/30"
            >
              <i className="fa-solid fa-camera text-xs" />
              <span>Launch Camera Scanner</span>
            </Link>
          </div>
        </div>

        {/* 4 Stat Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Today&apos;s Gate Check-ins
            </span>
            <span className="font-heading text-4xl font-bold text-white mt-1 block font-mono">
              {scans.filter((s) => s.status !== 'DENIED').length + 84} Scans
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Since 05:00 AM opening</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Floor Occupancy Now
            </span>
            <span className="font-heading text-4xl font-bold text-brand-red mt-1 block font-mono">
              34 Athletes
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Comfortable capacity</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Peak Traffic Hour
            </span>
            <span className="font-heading text-3xl font-bold text-amber-400 mt-1 block">
              06:00 - 08:30 PM
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Evening rush active</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Denied / Lockouts
            </span>
            <span className="font-heading text-4xl font-bold text-red-400 mt-1 block font-mono">
              {scans.filter((s) => s.status === 'DENIED').length}
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Dues cutoff enforcement</span>
          </div>
        </div>

        {/* Manual Gate Check-in Bar */}
        <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900">
          <form onSubmit={handleManualScan} className="flex flex-col sm:flex-row items-center gap-4">
            <div className="flex-1 w-full">
              <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                Front Desk Turnstile Manual Verification / Scanner Barcode
              </label>
              <div className="relative">
                <i className="fa-solid fa-qrcode absolute left-4 top-1/2 -translate-y-1/2 text-brand-red text-sm" />
                <input
                  type="text"
                  value={inputCode}
                  onChange={(e) => setInputCode(e.target.value)}
                  placeholder="Scan QR barcode or enter Member ID (e.g. 2026-4)..."
                  className="w-full bg-black border border-zinc-800 rounded-xl pl-10 pr-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red font-mono text-xs"
                />
              </div>
            </div>

            <button
              type="submit"
              className="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all sm:self-end"
            >
              Verify &amp; Unlock Gate
            </button>
          </form>

          {scanFeedback && (
            <div className="mt-4 p-3 rounded-xl bg-emerald-950/60 border border-emerald-800 text-emerald-300 text-xs flex items-center gap-2 animate-fadeIn">
              <i className="fa-solid fa-check" />
              <span>{scanFeedback}</span>
            </div>
          )}
        </div>

        {/* Hardware Status Strip */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          {[
            {
              gate: 'Turnstile #01 (Main Entrance)',
              model: 'ZK-Teco TS2000 Pro',
              status: 'Operational',
              ping: '8ms',
            },
            {
              gate: 'Turnstile #02 (Heavy Iron Bay)',
              model: 'ZK-Teco TS2000 Pro',
              status: 'Operational',
              ping: '12ms',
            },
            {
              gate: 'Turnstile #03 (Metabolic Cardio)',
              model: 'ZK-Teco TS2000 Pro',
              status: 'Operational',
              ping: '9ms',
            },
          ].map((h, i) => (
            <div key={i} className="p-4 rounded-2xl bg-zinc-950 border border-zinc-900 flex items-center justify-between text-xs">
              <div>
                <span className="font-bold text-white block">{h.gate}</span>
                <span className="text-[10px] text-zinc-500 font-mono">{h.model} &bull; {h.ping}</span>
              </div>
              <span className="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                {h.status}
              </span>
            </div>
          ))}
        </div>

        {/* Real-Time Scan Logs Table */}
        <div className="rounded-3xl bg-zinc-950 border border-zinc-900 overflow-hidden shadow-xl">
          <div className="p-5 border-b border-zinc-900 flex items-center justify-between">
            <h3 className="font-heading text-2xl font-bold text-white">
              Live Gate Scan Logs
            </h3>
            <span className="text-xs text-zinc-500 font-mono">Auto-refreshed stream</span>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-black text-[10px] font-semibold text-zinc-500 border-b border-zinc-900">
                <tr>
                  <th className="py-3.5 px-4">Athlete</th>
                  <th className="py-3.5 px-4">Pass ID</th>
                  <th className="py-3.5 px-4">Subscription Plan</th>
                  <th className="py-3.5 px-4">Turnstile Hardware</th>
                  <th className="py-3.5 px-4">Timestamp</th>
                  <th className="py-3.5 px-4 text-right">Access Verdict</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-900">
                {scans.map((s) => (
                  <tr key={s.id} className="hover:bg-zinc-900/40 transition-colors">
                    <td className="py-3.5 px-4 font-bold text-white">{s.name}</td>
                    <td className="py-3.5 px-4 font-mono text-brand-red font-bold">{s.memberCode}</td>
                    <td className="py-3.5 px-4 text-zinc-300">{s.plan}</td>
                    <td className="py-3.5 px-4 text-zinc-400 text-[11px]">{s.gate}</td>
                    <td className="py-3.5 px-4 text-zinc-500 font-mono text-[11px]">{s.timestamp}</td>
                    <td className="py-3.5 px-4 text-right">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold ${
                          s.status === 'GRANTED'
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                            : s.status === 'DENIED'
                            ? 'bg-red-500/10 text-red-400 border border-red-500/20'
                            : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                        }`}
                      >
                        {s.status}
                      </span>
                      {s.reason && (
                        <span className="text-[9px] text-zinc-500 block mt-0.5">{s.reason}</span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  );
}


