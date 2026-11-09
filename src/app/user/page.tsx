'use client';

import { useState } from 'react';
import Link from 'next/link';
import RealQrCode from '@/components/real-qr-code';

export default function MemberPortalPage() {
  const [checkedIn, setCheckedIn] = useState(false);
  const [streak, setStreak] = useState(2);
  const [sessions, setSessions] = useState(18);

  const handleCheckIn = () => {
    if (!checkedIn) {
      setCheckedIn(true);
      setStreak((prev) => prev + 1);
      setSessions((prev) => prev + 1);
    }
  };

  return (
    <div className="min-h-screen w-full bg-black text-white p-6 sm:p-8 lg:p-10 space-y-8 selection:bg-brand-red selection:text-white">
      {/* Top Header Bar */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-900">
        <div className="flex items-center gap-3">
          <img
            src="/images/logo.png"
            alt="GymFlow Logo"
            className="h-10 w-auto object-contain drop-shadow-[0_0_8px_rgba(255,42,42,0.4)]"
            onError={(e) => {
              e.currentTarget.style.display = 'none';
            }}
          />
          <div>
            <Link href="/" className="font-heading text-2xl font-extrabold tracking-tight text-white">
              Gym<span className="text-brand-red">Flow</span>
              <span className="text-brand-red">.</span>
            </Link>
            <span className="text-[10px] text-zinc-400 font-medium block">
              Athlete Self-Service Portal &bull; Metropolis HQ
            </span>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <Link
            href="/app"
            className="px-4 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-xs font-bold text-zinc-300 hover:text-white border border-zinc-800 transition-colors flex items-center gap-2"
          >
            <i className="fa-solid fa-mobile-screen text-brand-red" />
            <span>Open PWA View</span>
          </Link>
          <Link
            href="/admin"
            className="px-4 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-xs font-bold text-zinc-300 hover:text-white border border-zinc-800 transition-colors"
          >
            Admin Cockpit
          </Link>
          <Link
            href="/"
            className="px-4 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-xs font-bold text-zinc-300 hover:text-white border border-zinc-800 transition-colors"
          >
            Logout
          </Link>
        </div>
      </div>

      {/* Athlete Status & Consistency Banner */}
      <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 flex flex-col md:flex-row items-center justify-between gap-6">
        <div className="flex items-center gap-5">
          <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-brand-red to-brand-red-dark flex items-center justify-center text-white text-2xl sm:text-3xl font-bold shadow-lg shadow-brand-red/30 border border-brand-red/40 shrink-0">
            MO
          </div>
          <div className="space-y-1">
            <div className="flex items-center gap-2">
              <h1 className="font-heading text-3xl sm:text-4xl font-bold text-white tracking-tight">
                Moeez Ahmad
              </h1>
              <span className="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-semibold border border-emerald-500/20">
                Active
              </span>
            </div>
            <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-400 font-mono">
              <span>Pass ID: <span className="text-white font-bold">2026-4</span></span>
              <span>&bull;</span>
              <span>Tier: <span className="text-brand-red font-bold">1 Month Package</span></span>
              <span>&bull;</span>
              <span>Cutoff: <span className="text-amber-400 font-bold">10th March 2026</span></span>
            </div>
          </div>
        </div>

        {/* 3 Metric Counters */}
        <div className="grid grid-cols-3 gap-4 w-full md:w-auto">
          <div className="p-4 rounded-2xl bg-black border border-zinc-900 text-center">
            <span className="text-[10px] font-semibold text-zinc-500 block">Streak</span>
            <span className="font-heading text-2xl font-bold text-amber-400 mt-0.5 block">
              🔥 {streak} Days
            </span>
          </div>

          <div className="p-4 rounded-2xl bg-black border border-zinc-900 text-center">
            <span className="text-[10px] font-semibold text-zinc-500 block">Month Sessions</span>
            <span className="font-heading text-2xl font-bold text-white mt-0.5 block">
              {sessions}
            </span>
          </div>

          <div className="p-4 rounded-2xl bg-black border border-zinc-900 text-center">
            <span className="text-[10px] font-semibold text-zinc-500 block">Pass Days</span>
            <span className="font-heading text-2xl font-bold text-emerald-400 mt-0.5 block">
              30 Left
            </span>
          </div>
        </div>
      </div>

      {/* Main Grid: Turnstile QR Pass + Membership Ledger */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Col 1: Turnstile Pass (Left 1 Col) */}
        <div className="p-8 rounded-3xl bg-zinc-950 border border-brand-red/40 text-center shadow-pass-glow space-y-6">
          <div className="flex items-center justify-between pb-3 border-b border-zinc-900 text-[10px] font-semibold">
            <span className="text-brand-red">Turnstile Credential</span>
            <span className="text-emerald-400 flex items-center gap-1.5">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
              Gate Synced
            </span>
          </div>

          {/* Real High-Contrast Scannable 2D QR Matrix */}
          <RealQrCode
            value="2026-4"
            size={210}
            downloadable={true}
            showFormatToggle={true}
          />

          <div>
            <span className="font-mono text-2xl font-bold text-white block">2026-4</span>
            <span className="text-xs text-zinc-500 block mt-1">
              Valid until: <span className="text-zinc-300 font-semibold">10th March 2026</span>
            </span>
          </div>

          <button
            onClick={handleCheckIn}
            disabled={checkedIn}
            className={`w-full py-4 rounded-2xl font-semibold tracking-wider text-xs transition-all ${
              checkedIn
                ? 'bg-emerald-950/80 text-emerald-400 border border-emerald-800 cursor-not-allowed'
                : 'bg-brand-red hover:bg-brand-red-hover text-white shadow-lg shadow-brand-red/30'
            }`}
          >
            {checkedIn ? '✓ Turnstile Access Granted Today' : 'Simulate Turnstile Gate Scan'}
          </button>
        </div>

        {/* Col 2 & 3: Workout Calendar + Ledger + Coach Bridge (Right 2 Cols) */}
        <div className="lg:col-span-2 space-y-8">
          {/* Monthly Attendance Calendar */}
          <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-heading text-2xl font-bold text-white">
                  February 2026 Check-in Calendar
                </h3>
                <span className="text-xs text-zinc-500">
                  {sessions} sessions logged &bull; 85% monthly compliance rate
                </span>
              </div>
              <span className="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20">
                {checkedIn ? 'Today: Verified Attended' : 'Today: Checked In'}
              </span>
            </div>

            {/* 14-day calendar grid */}
            <div className="grid grid-cols-7 gap-2 pt-2">
              {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map((d) => (
                <span key={d} className="text-center text-[11px] font-semibold text-zinc-600">
                  {d}
                </span>
              ))}

              {[
                { d: 1, attended: true },
                { d: 2, attended: true },
                { d: 3, attended: false },
                { d: 4, attended: true },
                { d: 5, attended: true },
                { d: 6, attended: true },
                { d: 7, attended: false },
                { d: 8, attended: true },
                { d: 9, attended: true },
                { d: 10, attended: true },
                { d: 11, attended: false },
                { d: 12, attended: true },
                { d: 13, attended: true },
                { d: 14, attended: true },
              ].map((item) => (
                <div
                  key={item.d}
                  className={`h-11 rounded-xl flex flex-col items-center justify-center font-mono text-xs font-bold transition-all ${
                    item.attended
                      ? 'bg-brand-red text-white shadow-md shadow-brand-red/30'
                      : 'bg-black border border-zinc-900 text-zinc-600'
                  }`}
                >
                  <span>{item.d}</span>
                  <span className="text-[8px] tracking-tighter">
                    {item.attended ? 'IN' : 'OFF'}
                  </span>
                </div>
              ))}
            </div>
          </div>

          {/* Subscription Ledger & Invoices */}
          <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6">
            <div className="flex items-center justify-between">
              <h3 className="font-heading text-2xl font-bold text-white">
                Subscription &amp; Invoices
              </h3>
              <span className="text-xs text-zinc-500">10th Monthly Cutoff</span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              {[
                {
                  id: 'GF-2026-091',
                  date: '10 Feb 2026',
                  tier: '1 Month Package',
                  amount: 'PKR 4,000',
                  method: 'EasyPaisa',
                  status: 'PAID',
                },
                {
                  id: 'GF-2026-042',
                  date: '10 Jan 2026',
                  tier: '1 Month Package',
                  amount: 'PKR 4,000',
                  method: 'Cash Desk',
                  status: 'PAID',
                },
                {
                  id: 'GF-2025-883',
                  date: '10 Dec 2025',
                  tier: '1 Month Package',
                  amount: 'PKR 3,500',
                  method: 'JazzCash',
                  status: 'PAID',
                },
              ].map((inv) => (
                <div key={inv.id} className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="text-[10px] text-zinc-500 font-mono">{inv.id}</span>
                    <span className="text-[9px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                      {inv.status}
                    </span>
                  </div>
                  <span className="font-mono text-lg font-bold text-white block">
                    {inv.amount}
                  </span>
                  <div className="text-[11px] text-zinc-400 flex justify-between">
                    <span>{inv.method}</span>
                    <span>{inv.date}</span>
                  </div>
                </div>
              ))}
            </div>

            {/* Coach WhatsApp Bridge */}
            <div className="p-4 rounded-2xl bg-black border border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg shrink-0">
                  <i className="fa-brands fa-whatsapp" />
                </div>
                <div>
                  <h4 className="text-xs font-bold text-white">
                    Direct Trainer Consultation Bridge
                  </h4>
                  <p className="text-[11px] text-zinc-400">
                    Connect directly with Coach Marcus (+92 325 9491349) for routine adjustments.
                  </p>
                </div>
              </div>

              <a
                href="https://wa.me/923259491349?text=Hello%20Coach%20Marcus,%20I%20am%20member%202026-4%20and%20would%20like%20a%20workout%20review."
                target="_blank"
                rel="noopener noreferrer"
                className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold tracking-wider transition-colors shrink-0 flex items-center gap-2"
              >
                <span>WhatsApp Coach</span>
                <i className="fa-solid fa-arrow-up-right-from-square text-[10px]" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

