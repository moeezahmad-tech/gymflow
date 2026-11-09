'use client';

import { useState } from 'react';
import Link from 'next/link';
import RealQrCode from '@/components/real-qr-code';

type Tab = 'home' | 'notifications' | 'pass' | 'billing' | 'profile';

interface GateLog {
  id: string;
  turnstile: string;
  timestamp: string;
  status: 'GRANTED' | 'SYNCED';
}

interface NotificationItem {
  id: string;
  title: string;
  body: string;
  time: string;
  read: boolean;
  type: 'gate' | 'streak' | 'billing' | 'announcement';
  icon: string;
}

export default function StandalonePwaApp() {
  const [activeTab, setActiveTab] = useState<Tab>('home');
  const [streak, setStreak] = useState(2);
  const [monthlySessions, setMonthlySessions] = useState(18);
  const [checkedInToday, setCheckedInToday] = useState(false);
  const [viewMode, setViewMode] = useState<'phone' | 'full'>('phone');

  // Mobile Auth Modal State
  const [showAuthModal, setShowAuthModal] = useState(false);
  const [authMode, setAuthMode] = useState<'login' | 'signup'>('login');
  const [authEmail, setAuthEmail] = useState('');
  const [authPassword, setAuthPassword] = useState('');
  const [authName, setAuthName] = useState('');
  const [authPhone, setAuthPhone] = useState('+92 ');
  const [authSuccess, setAuthSuccess] = useState<string | null>(null);

  // Dynamic user data
  const [currentUser, setCurrentUser] = useState({
    name: 'Moeez Ahmad',
    email: 'moeezahmad.tech@gmail.com',
    phone: '+92 325 9491349',
    memberId: '2026-4',
    plan: '1 Month Package',
  });

  const handleMobileAuth = (e: React.FormEvent) => {
    e.preventDefault();
    if (authMode === 'login') {
      const loggedName = authEmail ? authEmail.split('@')[0] : 'Athlete';
      setCurrentUser((prev) => ({
        ...prev,
        name: loggedName.charAt(0).toUpperCase() + loggedName.slice(1),
        email: authEmail || prev.email,
      }));
      setAuthSuccess('Signed in successfully!');
    } else {
      setCurrentUser({
        name: authName || 'New Athlete',
        email: authEmail || 'athlete@gymflow.io',
        phone: authPhone || '+92 300 0000000',
        memberId: `2026-${Math.floor(100 + Math.random() * 899)}`,
        plan: '3-Month Pro Athlete',
      });
      setAuthSuccess('Account created! Welcome to GymFlow.');
    }

    setTimeout(() => {
      setAuthSuccess(null);
      setShowAuthModal(false);
    }, 1200);
  };

  const [gateLogs, setGateLogs] = useState<GateLog[]>([
    { id: '1', turnstile: 'Main Entrance Turnstile #01', timestamp: 'Today, 06:45 PM', status: 'GRANTED' },
    { id: '2', turnstile: 'Heavy Iron Bay Turnstile #02', timestamp: 'Yesterday, 07:15 PM', status: 'GRANTED' },
    { id: '3', turnstile: 'Main Entrance Turnstile #01', timestamp: '08 Feb 2026, 06:30 PM', status: 'GRANTED' },
  ]);

  const [notifications, setNotifications] = useState<NotificationItem[]>([
    {
      id: 'notif_1',
      title: 'Turnstile Gate Access Granted',
      body: 'Main Entrance Turnstile #01 check-in verified at 06:45 PM. Have a strong session!',
      time: 'Just now',
      read: false,
      type: 'gate',
      icon: 'fa-door-open',
    },
    {
      id: 'notif_2',
      title: 'Consistency Streak Milestone 🔥',
      body: 'You are on a 2-day consecutive training streak! Push through to day 3 tomorrow.',
      time: '2 hours ago',
      read: false,
      type: 'streak',
      icon: 'fa-fire',
    },
    {
      id: 'notif_3',
      title: 'Membership Status Notice',
      body: '30 days remaining on your 1 Month Package. Next renewal cutoff is on the 10th.',
      time: '1 day ago',
      read: false,
      type: 'billing',
      icon: 'fa-clock',
    },
    {
      id: 'notif_4',
      title: 'Hypertrophy Bay #2 Open',
      body: 'Watson dumbbells and calibrated Olympic plates are now installed and ready for use.',
      time: '3 days ago',
      read: true,
      type: 'announcement',
      icon: 'fa-dumbbell',
    },
  ]);

  const unreadCount = notifications.filter((n) => !n.read).length;

  const markAllRead = () => {
    setNotifications((prev) => prev.map((n) => ({ ...n, read: true })));
  };

  const handleSimulateScan = () => {
    if (!checkedInToday) {
      setCheckedInToday(true);
      setStreak((prev) => prev + 1);
      setMonthlySessions((prev) => prev + 1);

      const newLog: GateLog = {
        id: Date.now().toString(),
        turnstile: 'Main Entrance Turnstile #01',
        timestamp: 'Just now (Live Scan)',
        status: 'GRANTED',
      };
      setGateLogs([newLog, ...gateLogs]);

      const newNotif: NotificationItem = {
        id: `notif_${Date.now()}`,
        title: 'Turnstile Gate Access Granted',
        body: 'Scan verified at Main Entrance Turnstile #01. Welcome back, Moeez!',
        time: 'Just now',
        read: false,
        type: 'gate',
        icon: 'fa-door-open',
      };
      setNotifications([newNotif, ...notifications]);
    }
  };

  return (
    <div className="min-h-screen bg-[#070709] text-white flex flex-col items-center justify-center p-0 sm:p-6 font-sans select-none">
      {/* Top Desktop Helper Bar (visible on desktop viewports) */}
      <div className="hidden sm:flex items-center justify-between w-full max-w-md mb-3 text-xs text-zinc-500 font-mono">
        <div className="flex items-center gap-2">
          <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
          <span>GymFlow PWA v2.0 Standalone</span>
        </div>
        <div className="flex items-center gap-3">
          <button
            onClick={() => setViewMode(viewMode === 'phone' ? 'full' : 'phone')}
            className="hover:text-white transition-colors"
          >
            {viewMode === 'phone' ? '🖥️ Full Width' : '📱 Mobile Frame'}
          </button>
          <Link href="/" className="hover:text-brand-red transition-colors">
            Exit to Web &rarr;
          </Link>
        </div>
      </div>

      {/* PWA Phone Shell */}
      <div
        className={`w-full bg-black text-white flex flex-col justify-between shadow-2xl relative transition-all duration-300 ${
          viewMode === 'phone'
            ? 'max-w-md sm:rounded-[40px] sm:border sm:border-zinc-800 min-h-[92vh] sm:max-h-[92vh] overflow-hidden'
            : 'max-w-2xl sm:rounded-3xl border border-zinc-800 min-h-screen'
        }`}
      >
        {/* Native Mobile Status Bar (Signal / Battery / Time) */}
        <div className="bg-zinc-950 px-6 pt-3 pb-1 flex items-center justify-between text-[11px] text-zinc-400 font-mono border-b border-zinc-900/60 shrink-0">
          <span>09:41</span>
          <div className="w-20 h-4 bg-zinc-900 rounded-full mx-auto hidden sm:block" />
          <div className="flex items-center gap-1.5 text-[10px]">
            <i className="fa-solid fa-signal" />
            <i className="fa-solid fa-wifi" />
            <i className="fa-solid fa-battery-full text-emerald-400" />
          </div>
        </div>

        {/* PWA App Top Header */}
        <header className="sticky top-0 z-40 bg-zinc-950/95 backdrop-blur-xl border-b border-zinc-900 px-5 py-3 flex items-center justify-between shrink-0">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-br from-brand-red to-brand-red-dark flex items-center justify-center font-bold text-white text-xs shadow-md border border-brand-red/40 shrink-0">
              {currentUser.name.substring(0, 2).toUpperCase()}
            </div>
            <div>
              <h1 className="text-xs font-bold text-white tracking-wider leading-tight">
                {currentUser.name}
              </h1>
              <span className="text-[10px] text-zinc-500 font-mono block">Pass ID: {currentUser.memberId}</span>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <button
              onClick={() => {
                setAuthMode('login');
                setShowAuthModal(true);
              }}
              className="px-2.5 py-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-[10px] font-semibold text-zinc-300 hover:text-white transition-colors"
            >
              Sign In
            </button>
            <button
              onClick={() => {
                setAuthMode('signup');
                setShowAuthModal(true);
              }}
              className="px-2.5 py-1 rounded-lg bg-brand-red hover:bg-brand-red-hover text-[10px] font-semibold text-white shadow-sm transition-colors"
            >
              Sign Up
            </button>
          </div>
        </header>

        {/* ================= PWA SCROLLABLE BODY ================= */}
        <main className="flex-1 p-5 space-y-6 pb-28 overflow-y-auto">
          {/* ================= TAB 1: WORKOUT & GATE HUB (Screen 13) ================= */}
          {activeTab === 'home' && (
            <div className="space-y-6 animate-fadeIn">
              {/* 3 Metric Counters */}
              <div className="grid grid-cols-3 gap-2.5">
                <div className="p-3.5 rounded-2xl bg-zinc-950 border border-zinc-900 text-center">
                  <span className="text-[9px] font-semibold text-zinc-500 block">Streak</span>
                  <span className="font-heading text-xl font-bold text-amber-400 block mt-0.5">
                    🔥 {streak} Days
                  </span>
                  <span className="text-[9px] text-zinc-500">Consistent</span>
                </div>

                <div className="p-3.5 rounded-2xl bg-zinc-950 border border-zinc-900 text-center">
                  <span className="text-[9px] font-semibold text-zinc-500 block">Month</span>
                  <span className="font-heading text-xl font-bold text-white block mt-0.5">
                    {monthlySessions}
                  </span>
                  <span className="text-[9px] text-zinc-500">Sessions</span>
                </div>

                <div className="p-3.5 rounded-2xl bg-zinc-950 border border-zinc-900 text-center">
                  <span className="text-[9px] font-semibold text-zinc-500 block">Pass Days</span>
                  <span className="font-heading text-xl font-bold text-emerald-400 block mt-0.5">
                    30
                  </span>
                  <span className="text-[9px] text-zinc-500">Days Left</span>
                </div>
              </div>

              {/* Active Plan Card */}
              <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 relative overflow-hidden space-y-3 shadow-lg">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-semibold text-brand-red tracking-wider">
                    Active Subscription
                  </span>
                  <span className="text-[10px] font-mono font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                    Paid
                  </span>
                </div>

                <div>
                  <h3 className="font-heading text-2xl font-bold text-white">
                    1 MONTH PACKAGE
                  </h3>
                  <span className="text-xs text-zinc-400 font-mono">
                    PKR 4,000 / month &bull; Full Turnstile Access
                  </span>
                </div>

                <div className="pt-2 border-t border-zinc-900 flex items-center justify-between text-[11px] text-zinc-400">
                  <span>Renewal Due:</span>
                  <span className="text-amber-400 font-bold">10th March 2026</span>
                </div>
              </div>

              {/* Attendance & Workout Calendar */}
              <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-4">
                <div className="flex items-center justify-between">
                  <div>
                    <h4 className="font-heading text-lg font-bold text-white">
                      Workout &amp; Gate Calendar
                    </h4>
                    <span className="text-[10px] text-zinc-500">February 2026</span>
                  </div>
                  <span className="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full border border-emerald-500/20">
                    {checkedInToday ? 'Present Today: 2 Sessions' : 'Today: Checked In'}
                  </span>
                </div>

                {/* Day Dots Grid */}
                <div className="grid grid-cols-7 gap-1.5 text-center text-xs">
                  {['M', 'T', 'W', 'T', 'F', 'S', 'S'].map((day, idx) => (
                    <span key={idx} className="text-[10px] font-bold text-zinc-600">
                      {day}
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
                      className={`h-8 rounded-xl flex items-center justify-center font-mono text-[11px] font-bold transition-all ${
                        item.attended
                          ? 'bg-brand-red text-white shadow-sm shadow-brand-red/40'
                          : 'bg-zinc-900 text-zinc-500'
                      }`}
                    >
                      {item.d}
                    </div>
                  ))}
                </div>
              </div>

              {/* Today's Focus Split */}
              <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-semibold text-zinc-500 tracking-wider">
                    Today&apos;s Workout Split
                  </span>
                  <span className="text-[10px] text-brand-red font-semibold">Chest &amp; Triceps</span>
                </div>

                <h4 className="font-heading text-xl font-bold text-white">
                  Heavy Hypertrophy Protocol A
                </h4>

                <div className="space-y-2 text-xs text-zinc-400">
                  <div className="flex items-center justify-between py-1 border-b border-zinc-900">
                    <span>1. Incline Barbell Press</span>
                    <span className="font-mono text-white">4 sets &times; 8 reps</span>
                  </div>
                  <div className="flex items-center justify-between py-1 border-b border-zinc-900">
                    <span>2. Watson Flat Dumbbell Press</span>
                    <span className="font-mono text-white">3 sets &times; 10 reps</span>
                  </div>
                  <div className="flex items-center justify-between py-1 border-b border-zinc-900">
                    <span>3. Cable Chest Flyes</span>
                    <span className="font-mono text-white">3 sets &times; 15 reps</span>
                  </div>
                  <div className="flex items-center justify-between py-1">
                    <span>4. Tricep Rope Extensions</span>
                    <span className="font-mono text-white">4 sets &times; 12 reps</span>
                  </div>
                </div>
              </div>

              {/* Quick Launch QR Button */}
              <button
                onClick={() => setActiveTab('pass')}
                className="w-full py-4 rounded-2xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-brand-red/30 transition-all"
              >
                <i className="fa-solid fa-qrcode text-sm" />
                <span>Open Turnstile Gate Pass</span>
              </button>
            </div>
          )}

          {/* ================= TAB 2: ALERTS & NOTIFICATIONS (Screen 14) ================= */}
          {activeTab === 'notifications' && (
            <div className="space-y-5 animate-fadeIn">
              <div className="flex items-center justify-between border-b border-zinc-900 pb-3">
                <div>
                  <h2 className="font-heading text-2xl font-bold text-white">
                    Alerts &amp; Activity Feed
                  </h2>
                  <span className="text-[11px] text-zinc-500">
                    {unreadCount} unread automated updates
                  </span>
                </div>

                {unreadCount > 0 && (
                  <button
                    onClick={markAllRead}
                    className="text-[10px] font-bold text-brand-red hover:text-white tracking-wider"
                  >
                    Mark all as read
                  </button>
                )}
              </div>

              <div className="space-y-3">
                {notifications.map((item) => (
                  <div
                    key={item.id}
                    className={`p-4 rounded-2xl border transition-all ${
                      item.read
                        ? 'bg-zinc-950 border-zinc-900 text-zinc-400'
                        : 'bg-zinc-950 border-brand-red/40 text-white shadow-sm'
                    }`}
                  >
                    <div className="flex items-start gap-3">
                      <div
                        className={`w-9 h-9 rounded-xl flex items-center justify-center text-sm shrink-0 ${
                          item.read
                            ? 'bg-zinc-900 text-zinc-500'
                            : 'bg-brand-red/20 text-brand-red border border-brand-red/30'
                        }`}
                      >
                        <i className={`fa-solid ${item.icon}`} />
                      </div>

                      <div className="flex-1 space-y-1">
                        <div className="flex items-center justify-between">
                          <h4 className="text-xs font-bold text-white leading-tight">
                            {item.title}
                          </h4>
                          <span className="text-[10px] text-zinc-500 font-mono shrink-0 ml-2">
                            {item.time}
                          </span>
                        </div>
                        <p className="text-[11px] text-zinc-400 leading-relaxed">{item.body}</p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* ================= TAB 3: GATE PASS (Screen 15) ================= */}
          {activeTab === 'pass' && (
            <div className="space-y-6 text-center animate-fadeIn">
              <div>
                <span className="text-[10px] font-bold tracking-wider text-brand-red block mb-1">
                  Dynamic Turnstile Access
                </span>
                <h2 className="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                  Digital Gate Pass<span className="text-brand-red">.</span>
                </h2>
              </div>

              {/* Glowing High-Contrast QR Card with Authentic Fluted Pleats */}
              <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-brand-red/40 shadow-pass-glow space-y-5 relative overflow-hidden">
                <div className="absolute inset-0 bg-fluted-dark opacity-35 mix-blend-multiply pointer-events-none" />
                <div className="flex items-center justify-between pb-3 border-b border-zinc-900 text-[10px] font-semibold relative z-10">
                  <span className="text-zinc-400">GymFlow Metropolis HQ</span>
                  <span className="text-emerald-400 flex items-center gap-1.5">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                    Turnstile Verified
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
                  <span className="text-[10px] text-zinc-500 font-semibold tracking-wider block mt-1">
                    Hold close to turnstile reader
                  </span>
                </div>

                {/* Simulate Check-in Button */}
                <button
                  onClick={handleSimulateScan}
                  disabled={checkedInToday}
                  className={`w-full py-4 rounded-2xl font-semibold tracking-wider text-xs transition-all ${
                    checkedInToday
                      ? 'bg-emerald-950/80 text-emerald-400 border border-emerald-800 cursor-not-allowed'
                      : 'bg-brand-red hover:bg-brand-red-hover text-white shadow-lg shadow-brand-red/30'
                  }`}
                >
                  {checkedInToday ? '✓ Access Granted Today (Turnstile Synced)' : 'Simulate Gate Scan Check-In'}
                </button>
              </div>

              {/* Recent Gate Logs */}
              <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 text-left space-y-3">
                <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
                  Recent Turnstile Scans
                </span>

                <div className="space-y-2">
                  {gateLogs.map((log) => (
                    <div
                      key={log.id}
                      className="flex items-center justify-between text-xs py-1.5 border-b border-zinc-900 last:border-0"
                    >
                      <div>
                        <span className="text-white font-medium block">{log.turnstile}</span>
                        <span className="text-[10px] text-zinc-500 font-mono">{log.timestamp}</span>
                      </div>
                      <span className="text-[10px] font-bold text-emerald-400 font-mono bg-emerald-500/10 px-2 py-0.5 rounded">
                        {log.status}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* ================= TAB 4: BILLING & RECEIPTS (Screen 16) ================= */}
          {activeTab === 'billing' && (
            <div className="space-y-6 animate-fadeIn">
              <div>
                <span className="text-[10px] font-semibold tracking-widest text-brand-red block mb-1">
                  Ledger &amp; Cutoffs
                </span>
                <h2 className="font-heading text-3xl font-bold text-white">
                  Membership &amp; Invoices
                </h2>
              </div>

              {/* Digital Pass Card */}
              <div className="p-6 rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-950 to-black border border-zinc-800 space-y-4 shadow-xl relative overflow-hidden">
                <div className="flex items-center justify-between relative z-10">
                  <div className="flex items-center gap-2">
                    <img
                      src="/images/logo.png"
                      alt="GymFlow"
                      className="h-6 w-auto object-contain"
                    />
                    <span className="font-heading text-xl font-bold text-white tracking-wider">
                      GYM<span className="text-brand-red">FLOW</span> PASS
                    </span>
                  </div>
                  <span className="px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-semibold">
                    Active
                  </span>
                </div>

                <div className="space-y-1 pt-2">
                  <span className="text-[10px] text-zinc-500 font-semibold tracking-wider block">
                    Current Tier
                  </span>
                  <h3 className="font-heading text-2xl font-bold text-white">
                    1 Month Package
                  </h3>
                  <span className="font-mono text-sm text-emerald-400 font-bold block">
                    ₨ 4,000 / Month
                  </span>
                </div>

                <div className="pt-3 border-t border-zinc-800 flex justify-between text-[11px] text-zinc-400">
                  <span>Next Cutoff: 10th March 2026</span>
                  <span className="font-mono text-zinc-300">ID: 2026-4</span>
                </div>
              </div>

              {/* Payment Receipts History */}
              <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-4">
                <div className="flex items-center justify-between">
                  <h4 className="font-heading text-lg font-bold text-white">
                    Payment History &amp; Receipts
                  </h4>
                  <span className="text-[10px] text-zinc-500">3 receipts</span>
                </div>

                <div className="space-y-3">
                  {[
                    {
                      id: 'GF-2026-091',
                      tier: '1 Month Package',
                      amount: 'PKR 4,000',
                      channel: 'EasyPaisa / Online',
                      date: '10 Feb 2026',
                      status: 'Paid',
                    },
                    {
                      id: 'GF-2026-042',
                      tier: '1 Month Package',
                      amount: 'PKR 4,000',
                      channel: 'Cash at Front Desk',
                      date: '10 Jan 2026',
                      status: 'Paid',
                    },
                    {
                      id: 'GF-2025-883',
                      tier: '1 Month Package',
                      amount: 'PKR 3,500',
                      channel: 'JazzCash / Online',
                      date: '10 Dec 2025',
                      status: 'Paid',
                    },
                  ].map((receipt) => (
                    <div
                      key={receipt.id}
                      className="p-3.5 rounded-2xl bg-black border border-zinc-900 flex items-center justify-between text-xs"
                    >
                      <div className="space-y-0.5">
                        <div className="flex items-center gap-2">
                          <span className="font-bold text-white">{receipt.tier}</span>
                          <span className="text-[9px] text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded font-mono font-bold">
                            {receipt.status}
                          </span>
                        </div>
                        <span className="text-[10px] text-zinc-500 font-mono block">
                          {receipt.id} &bull; {receipt.channel} &bull; {receipt.date}
                        </span>
                      </div>

                      <div className="text-right">
                        <span className="font-mono font-bold text-white block">
                          {receipt.amount}
                        </span>
                        <button
                          onClick={() => alert(`Downloading receipt #${receipt.id}...`)}
                          className="text-[10px] text-brand-red hover:underline font-bold"
                        >
                          PDF
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* ================= TAB 5: PROFILE & SETTINGS (Screen 17) ================= */}
          {activeTab === 'profile' && (
            <div className="space-y-6 animate-fadeIn">
              {/* Athlete Profile Header */}
              <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900 text-center space-y-3">
                <div className="w-20 h-20 rounded-3xl bg-gradient-to-br from-brand-red to-brand-red-dark flex items-center justify-center font-bold text-white text-2xl mx-auto shadow-lg shadow-brand-red/30 border border-brand-red/40">
                  MO
                </div>
                <div>
                  <h3 className="font-heading text-2xl font-bold text-white">
                    Moeez Ahmad
                  </h3>
                  <span className="text-xs text-zinc-400 font-mono block">
                    moeezahmad.tech@gmail.com
                  </span>
                  <span className="text-xs text-zinc-400 font-mono block mt-0.5">
                    +92 325 9491349
                  </span>
                </div>
                <div className="pt-2 flex items-center justify-center gap-2">
                  <span className="px-3 py-1 rounded-full bg-zinc-900 border border-zinc-800 text-[10px] font-mono text-brand-red font-bold">
                    Pass ID: 2026-4
                  </span>
                  <span className="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-bold text-emerald-400">
                    Active Athlete
                  </span>
                </div>
              </div>

              {/* Biometric & System Roadmap */}
              <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-3 text-xs">
                <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
                  Biometric Access Features (IaaS Roadmap)
                </span>

                <div className="space-y-2">
                  <div className="p-3 rounded-2xl bg-black border border-zinc-900 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <i className="fa-solid fa-fingerprint text-brand-red text-base" />
                      <div>
                        <span className="font-bold text-white block text-xs">
                          Thumbprint Turnstile ID
                        </span>
                        <span className="text-[10px] text-zinc-500">Hardware sync in progress</span>
                      </div>
                    </div>
                    <span className="text-[9px] font-bold text-zinc-500 bg-zinc-900 px-2 py-0.5 rounded">
                      Roadmap
                    </span>
                  </div>

                  <div className="p-3 rounded-2xl bg-black border border-zinc-900 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <i className="fa-solid fa-camera text-brand-red text-base" />
                      <div>
                        <span className="font-bold text-white block text-xs">
                          Face Identification Gate
                        </span>
                        <span className="text-[10px] text-zinc-500">Fast visual authentication</span>
                      </div>
                    </div>
                    <span className="text-[9px] font-bold text-zinc-500 bg-zinc-900 px-2 py-0.5 rounded">
                      Roadmap
                    </span>
                  </div>
                </div>
              </div>

              {/* System Shortcuts */}
              <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-2 text-xs">
                <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block mb-2">
                  Portal Shortcuts
                </span>

                <Link
                  href="/admin"
                  className="flex items-center justify-between p-3 rounded-2xl bg-black hover:bg-zinc-900 border border-zinc-900 text-zinc-300 hover:text-white transition-colors"
                >
                  <div className="flex items-center gap-2.5">
                    <i className="fa-solid fa-gauge-high text-brand-red" />
                    <span className="font-bold">Admin Management Console</span>
                  </div>
                  <span className="text-zinc-600">&rarr;</span>
                </Link>

                <Link
                  href="/"
                  className="flex items-center justify-between p-3 rounded-2xl bg-black hover:bg-zinc-900 border border-zinc-900 text-zinc-300 hover:text-white transition-colors"
                >
                  <div className="flex items-center gap-2.5">
                    <i className="fa-solid fa-globe text-brand-red" />
                    <span className="font-bold">Public Website</span>
                  </div>
                  <span className="text-zinc-600">&rarr;</span>
                </Link>
              </div>

              {/* Version & Sign Out */}
              <div className="text-center space-y-3 pt-2">
                <span className="text-[10px] text-zinc-600 font-mono block">
                  GymFlow PWA v2.0.0 &bull; Offline Service Worker Active
                </span>

                <Link
                  href="/"
                  className="inline-block py-2.5 px-6 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-white text-xs font-semibold tracking-wider transition-colors border border-zinc-800"
                >
                  Sign Out
                </Link>
              </div>
            </div>
          )}
        </main>

        {/* ================= NATIVE 5-TAB BOTTOM NAVIGATION BAR ================= */}
        <nav className="absolute bottom-0 inset-x-0 z-50 bg-zinc-950/95 backdrop-blur-xl border-t border-zinc-900 px-3 py-2 flex items-center justify-around">
          <button
            onClick={() => setActiveTab('home')}
            className={`flex flex-col items-center gap-1 py-1 px-2.5 transition-colors ${
              activeTab === 'home' ? 'text-brand-red' : 'text-zinc-500 hover:text-zinc-300'
            }`}
          >
            <i className="fa-solid fa-house text-sm" />
            <span className="text-[9px] font-semibold">Home</span>
          </button>

          <button
            onClick={() => setActiveTab('notifications')}
            className={`flex flex-col items-center gap-1 py-1 px-2.5 relative transition-colors ${
              activeTab === 'notifications' ? 'text-brand-red' : 'text-zinc-500 hover:text-zinc-300'
            }`}
          >
            <div className="relative">
              <i className="fa-solid fa-bell text-sm" />
              {unreadCount > 0 && (
                <span className="absolute -top-1 -right-1.5 w-3.5 h-3.5 rounded-full bg-brand-red text-white text-[8px] font-bold flex items-center justify-center">
                  {unreadCount}
                </span>
              )}
            </div>
            <span className="text-[9px] font-semibold">Alerts</span>
          </button>

          {/* Glowing Circular Center QR Pass Action */}
          <button
            onClick={() => setActiveTab('pass')}
            className={`flex flex-col items-center -mt-5 transition-transform ${
              activeTab === 'pass' ? 'scale-105' : 'hover:scale-105'
            }`}
          >
            <div
              className={`w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg transition-all ${
                activeTab === 'pass'
                  ? 'bg-brand-red shadow-brand-red/50 border-2 border-white/20'
                  : 'bg-zinc-900 text-zinc-400 border border-zinc-800 hover:text-white'
              }`}
            >
              <i className="fa-solid fa-qrcode text-lg" />
            </div>
            <span
              className={`text-[9px] font-semibold mt-1 ${
                activeTab === 'pass' ? 'text-brand-red' : 'text-zinc-500'
              }`}
            >
              Pass
            </span>
          </button>

          <button
            onClick={() => setActiveTab('billing')}
            className={`flex flex-col items-center gap-1 py-1 px-2.5 transition-colors ${
              activeTab === 'billing' ? 'text-brand-red' : 'text-zinc-500 hover:text-zinc-300'
            }`}
          >
            <i className="fa-solid fa-receipt text-sm" />
            <span className="text-[9px] font-semibold">Billing</span>
          </button>

          <button
            onClick={() => setActiveTab('profile')}
            className={`flex flex-col items-center gap-1 py-1 px-2.5 transition-colors ${
              activeTab === 'profile' ? 'text-brand-red' : 'text-zinc-500 hover:text-zinc-300'
            }`}
          >
            <i className="fa-solid fa-user text-sm" />
            <span className="text-[9px] font-semibold">Profile</span>
          </button>
        </nav>

        {/* ================= NATIVE MOBILE AUTHENTICATION MODAL (LOGIN & SIGN UP) ================= */}
        {showAuthModal && (
          <div className="absolute inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 animate-fadeIn">
            <div className="w-full max-w-sm bg-zinc-950 border border-zinc-800 rounded-3xl p-6 space-y-4 shadow-2xl relative">
              {/* Close Button */}
              <button
                type="button"
                onClick={() => setShowAuthModal(false)}
                className="absolute top-4 right-4 w-7 h-7 rounded-full bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center text-xs"
              >
                &times;
              </button>

              <div className="text-center space-y-1">
                <div className="w-10 h-10 rounded-2xl bg-brand-red/10 border border-brand-red/30 text-brand-red flex items-center justify-center mx-auto text-base">
                  <i className="fa-solid fa-mobile-screen" />
                </div>
                <h3 className="font-heading text-lg font-bold text-white">
                  {authMode === 'login' ? 'Athlete Mobile Sign In' : 'Join GymFlow PWA'}
                </h3>
                <p className="text-[11px] text-zinc-400">
                  {authMode === 'login'
                    ? 'Access your workout streak, gate logs, and profile'
                    : 'Create your mobile athlete profile and start training'}
                </p>
              </div>

              {/* Toggle Login vs Sign Up */}
              <div className="grid grid-cols-2 p-1 rounded-xl bg-black border border-zinc-900 text-xs">
                <button
                  type="button"
                  onClick={() => setAuthMode('login')}
                  className={`py-1.5 rounded-lg font-bold transition-all ${
                    authMode === 'login' ? 'bg-brand-red text-white' : 'text-zinc-500 hover:text-white'
                  }`}
                >
                  Log In
                </button>
                <button
                  type="button"
                  onClick={() => setAuthMode('signup')}
                  className={`py-1.5 rounded-lg font-bold transition-all ${
                    authMode === 'signup' ? 'bg-brand-red text-white' : 'text-zinc-500 hover:text-white'
                  }`}
                >
                  Sign Up
                </button>
              </div>

              {authSuccess && (
                <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs text-center flex items-center justify-center gap-2">
                  <i className="fa-solid fa-circle-check text-emerald-400" />
                  <span>{authSuccess}</span>
                </div>
              )}

              <form onSubmit={handleMobileAuth} className="space-y-3 text-xs">
                {authMode === 'signup' && (
                  <div>
                    <label className="text-[10px] text-zinc-400 block mb-1">Full Legal Name</label>
                    <input
                      type="text"
                      required
                      value={authName}
                      onChange={(e) => setAuthName(e.target.value)}
                      placeholder="e.g. Moeez Ahmad"
                      className="w-full bg-black border border-zinc-800 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-brand-red"
                    />
                  </div>
                )}

                <div>
                  <label className="text-[10px] text-zinc-400 block mb-1">Email Address</label>
                  <input
                    type="email"
                    required
                    value={authEmail}
                    onChange={(e) => setAuthEmail(e.target.value)}
                    placeholder="athlete@gymflow.io"
                    className="w-full bg-black border border-zinc-800 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-brand-red"
                  />
                </div>

                {authMode === 'signup' && (
                  <div>
                    <label className="text-[10px] text-zinc-400 block mb-1">WhatsApp Phone Number</label>
                    <input
                      type="tel"
                      required
                      value={authPhone}
                      onChange={(e) => setAuthPhone(e.target.value)}
                      placeholder="+92 300 0000000"
                      className="w-full bg-black border border-zinc-800 rounded-xl px-3 py-2 text-white text-xs font-mono focus:outline-none focus:border-brand-red"
                    />
                  </div>
                )}

                <div>
                  <label className="text-[10px] text-zinc-400 block mb-1">Password</label>
                  <input
                    type="password"
                    required
                    value={authPassword}
                    onChange={(e) => setAuthPassword(e.target.value)}
                    placeholder="••••••••"
                    className="w-full bg-black border border-zinc-800 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-brand-red"
                  />
                </div>

                <button
                  type="submit"
                  className="w-full py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white font-bold text-xs shadow-md shadow-brand-red/30 transition-all flex items-center justify-center gap-1.5 mt-2"
                >
                  <span>{authMode === 'login' ? 'Authenticate & Enter' : 'Create Mobile Account'}</span>
                  <i className="fa-solid fa-arrow-right text-[10px]" />
                </button>
              </form>

              <div className="text-center text-[10px] text-zinc-500 pt-1">
                <span>Metropolis Flagship HQ &bull; Biometric PWA Access</span>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}


