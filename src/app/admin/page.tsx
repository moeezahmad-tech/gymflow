'use client';

import { useState } from 'react';
import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';

interface MemberRecipient {
  id: string;
  name: string;
  code: string;
  email: string;
  phone: string;
  category: 'current' | 'new' | 'old';
  plan: string;
  amountDue: string;
  cutoffDate: string;
  lastCheckIn: string;
  streakDays: number;
}

interface DispatchLog {
  id: string;
  timestamp: string;
  audience: string;
  recipientCount: number;
  subject: string;
  status: 'Delivered (Mock)';
}

export default function AdminOverviewPage() {
  // Operational mock data
  const recentPayments = [
    {
      id: 'INV-2026-101',
      name: 'Moeez Ahmad',
      code: '2026-4',
      plan: '3-Month Pro Athlete Plan',
      amount: 'PKR 22,500',
      channel: 'EasyPaisa',
      time: '12 mins ago',
      status: 'Paid',
    },
    {
      id: 'INV-2026-102',
      name: 'Hamza Farooq',
      code: '2026-482',
      plan: '3-Month Pro Athlete Plan',
      amount: 'PKR 22,500',
      channel: 'EasyPaisa',
      time: '45 mins ago',
      status: 'Paid',
    },
    {
      id: 'INV-2026-103',
      name: 'Bilal Khan',
      code: '2026-484',
      plan: '6-Month Elite Membership',
      amount: 'PKR 42,000',
      channel: 'JazzCash',
      time: '2 hours ago',
      status: 'Paid',
    },
    {
      id: 'INV-2026-104',
      name: 'Ayesha Noor',
      code: '2026-483',
      plan: '1-Month Starter Plan',
      amount: 'PKR 8,500',
      channel: 'Cash Desk',
      time: '3 hours ago',
      status: 'Pending',
    },
  ];

  const recentScans = [
    {
      code: '2026-4',
      name: 'Moeez Ahmad',
      gate: 'Turnstile #01 (Lobby)',
      time: '06:45 PM',
      status: 'GRANTED',
    },
    {
      code: '2026-480',
      name: 'Zainab Qureshi',
      gate: 'Turnstile #02 (Heavy Iron)',
      time: '06:40 PM',
      status: 'GRANTED',
    },
    {
      code: '2026-1',
      name: 'Marcus Vance',
      gate: 'Turnstile #01 (Lobby)',
      time: '06:27 PM',
      status: 'GRANTED',
    },
    {
      code: '2026-3',
      name: 'Active Athlete',
      gate: 'Turnstile #03 (Cardio)',
      time: '06:00 PM',
      status: 'GRANTED',
    },
  ];

  const pendingAdmissions = [
    {
      id: 'ADM-2026-001',
      name: 'Hamza Farooq',
      cnic: '61101-8392104-5',
      plan: '3-Month Pro Athlete Plan',
      shift: 'Evening (05-11 PM)',
      amount: 'PKR 22,500 (EasyPaisa)',
      submitted: 'Today, 02:40 PM',
    },
    {
      id: 'ADM-2026-002',
      name: 'Ayesha Noor',
      cnic: '61101-4491023-8',
      plan: '1-Month Starter Plan',
      shift: 'Morning (05-11 AM)',
      amount: 'PKR 8,500 (Cash at Desk)',
      submitted: 'Today, 01:15 PM',
    },
    {
      id: 'ADM-2026-003',
      name: 'Bilal Khan',
      cnic: '37405-1928374-1',
      plan: '6-Month Elite Membership',
      shift: 'Afternoon (12-05 PM)',
      amount: 'PKR 42,000 (JazzCash)',
      submitted: 'Today, 11:30 AM',
    },
  ];

  // ================= EMAIL BROADCASTING SYSTEM STATE =================
  const mockMembers: MemberRecipient[] = [
    // Current Active Members
    {
      id: 'm1',
      name: 'Moeez Ahmad',
      code: '2026-4',
      email: 'moeez.ahmad@gymflow.io',
      phone: '+92 325 9491349',
      category: 'current',
      plan: '3-Month Pro Athlete Plan',
      amountDue: 'PKR 22,500',
      cutoffDate: 'Feb 10, 2026',
      lastCheckIn: 'Today, 06:45 PM',
      streakDays: 14,
    },
    {
      id: 'm2',
      name: 'Zainab Qureshi',
      code: '2026-480',
      email: 'zainab.q@gmail.com',
      phone: '+92 333 5678901',
      category: 'current',
      plan: '6-Month Elite Membership',
      amountDue: 'PKR 42,000',
      cutoffDate: 'Feb 10, 2026',
      lastCheckIn: 'Today, 06:40 PM',
      streakDays: 8,
    },
    {
      id: 'm3',
      name: 'Marcus Vance',
      code: '2026-1',
      email: 'marcus.v@gymflow.io',
      phone: '+92 312 9988771',
      category: 'current',
      plan: 'Annual Titan Access',
      amountDue: 'PKR 75,000',
      cutoffDate: 'Dec 31, 2026',
      lastCheckIn: 'Today, 06:27 PM',
      streakDays: 42,
    },
    {
      id: 'm4',
      name: 'Usman Tariq',
      code: '2026-312',
      email: 'usman.tariq@yahoo.com',
      phone: '+92 345 1122334',
      category: 'current',
      plan: 'Monthly Strength Tier',
      amountDue: 'PKR 8,500',
      cutoffDate: 'Feb 10, 2026',
      lastCheckIn: 'Yesterday, 07:15 PM',
      streakDays: 5,
    },
    {
      id: 'm5',
      name: 'Sarah Jenkins',
      code: '2026-255',
      email: 'sarah.j@outlook.com',
      phone: '+92 300 7766554',
      category: 'current',
      plan: '3-Month Pro Athlete Plan',
      amountDue: 'PKR 22,500',
      cutoffDate: 'Feb 10, 2026',
      lastCheckIn: 'Feb 07, 2026',
      streakDays: 3,
    },

    // New Members (Admitted within last 30 days)
    {
      id: 'm6',
      name: 'Hamza Farooq',
      code: '2026-482',
      email: 'hamza.farooq@live.com',
      phone: '+92 312 4567890',
      category: 'new',
      plan: '3-Month Pro Athlete Plan',
      amountDue: 'PKR 22,500',
      cutoffDate: 'Feb 10, 2026 (Grace)',
      lastCheckIn: 'Yesterday, 05:30 PM',
      streakDays: 2,
    },
    {
      id: 'm7',
      name: 'Ayesha Noor',
      code: '2026-483',
      email: 'ayesha.noor@gmail.com',
      phone: '+92 321 8899001',
      category: 'new',
      plan: '1-Month Starter Plan',
      amountDue: 'PKR 8,500',
      cutoffDate: 'Mar 10, 2026',
      lastCheckIn: 'Today, 01:15 PM',
      streakDays: 1,
    },
    {
      id: 'm8',
      name: 'Bilal Khan',
      code: '2026-484',
      email: 'bilal.khan@icloud.com',
      phone: '+92 345 6789012',
      category: 'new',
      plan: '6-Month Elite Membership',
      amountDue: 'PKR 42,000',
      cutoffDate: 'Aug 10, 2026',
      lastCheckIn: 'Today, 11:30 AM',
      streakDays: 1,
    },
    {
      id: 'm9',
      name: 'Daniyal Raza',
      code: '2026-485',
      email: 'daniyal.raza@gmail.com',
      phone: '+92 334 2233445',
      category: 'new',
      plan: 'Monthly Strength Tier',
      amountDue: 'PKR 8,500',
      cutoffDate: 'Mar 10, 2026',
      lastCheckIn: 'Awaiting Check-in',
      streakDays: 0,
    },

    // Old Members (Expired / Lapsed)
    {
      id: 'm10',
      name: 'Tariq Mehmood',
      code: '2025-104',
      email: 'tariq.m99@gmail.com',
      phone: '+92 301 5566778',
      category: 'old',
      plan: 'Expired Pro Athlete',
      amountDue: 'PKR 0',
      cutoffDate: 'Lapsed Nov 10, 2025',
      lastCheckIn: 'Nov 06, 2025',
      streakDays: 0,
    },
    {
      id: 'm11',
      name: 'Farhan Siddiqui',
      code: '2025-88',
      email: 'farhan.siddiqui@hotmail.com',
      phone: '+92 322 9900112',
      category: 'old',
      plan: 'Expired Monthly Tier',
      amountDue: 'PKR 0',
      cutoffDate: 'Lapsed Dec 10, 2025',
      lastCheckIn: 'Dec 02, 2025',
      streakDays: 0,
    },
    {
      id: 'm12',
      name: 'Mahnoor Ali',
      code: '2025-41',
      email: 'mahnoor.ali@gmail.com',
      phone: '+92 336 4455667',
      category: 'old',
      plan: 'Expired 6-Month Elite',
      amountDue: 'PKR 0',
      cutoffDate: 'Lapsed Jan 10, 2026',
      lastCheckIn: 'Jan 04, 2026',
      streakDays: 0,
    },
  ];

  type AudienceFilter = 'all' | 'current' | 'new' | 'old' | 'selected';

  const [audienceFilter, setAudienceFilter] = useState<AudienceFilter>('current');
  const [selectedMemberIds, setSelectedMemberIds] = useState<string[]>(['m1', 'm2', 'm6']);
  const [searchQuery, setSearchQuery] = useState('');
  const [previewMemberId, setPreviewMemberId] = useState<string>('m1');
  const [activeTab, setActiveTab] = useState<'preview' | 'composer'>('preview');

  // Email Content States
  const [emailSubject, setEmailSubject] = useState(
    '10th-Cutoff Notice: GymFlow Membership Renewal & Gate Pass Status'
  );
  const [emailBody, setEmailBody] = useState(
    `Dear {{name}},\n\nThis is an automated notification from Metropolis Flagship HQ regarding your {{plan}} (Member Code: {{member_id}}).\n\nYour monthly cutoff cycle is set to {{cutoff_date}}. To guarantee uninterrupted optical turnstile gate access, please clear your outstanding invoice of {{amount_due}} prior to the 10th-cutoff deadline.\n\nYou can pay online instantly via EasyPaisa / JazzCash or visit the reception counter at Metropolis HQ.\n\nKeep pushing the iron,\nGymFlow Operations Team`
  );

  const [isSending, setIsSending] = useState(false);
  const [sendSuccessMessage, setSendSuccessMessage] = useState<string | null>(null);
  const [dispatchLogs, setDispatchLogs] = useState<DispatchLog[]>([
    {
      id: 'LOG-8821',
      timestamp: 'Today, 04:30 PM',
      audience: 'Current Members',
      recipientCount: 5,
      subject: '10th-Cutoff Reminder: February Cycle Renewal',
      status: 'Delivered (Mock)',
    },
  ]);

  // Pre-configured email templates
  const templates = [
    {
      id: 'cutoff_reminder',
      name: '10th Cutoff Reminder',
      category: 'current',
      subject: '10th-Cutoff Notice: GymFlow Membership Renewal & Gate Pass Status',
      body: `Dear {{name}},\n\nThis is an automated notification from Metropolis Flagship HQ regarding your {{plan}} (Member Code: {{member_id}}).\n\nYour monthly cutoff cycle is set to {{cutoff_date}}. To guarantee uninterrupted optical turnstile gate access, please clear your outstanding invoice of {{amount_due}} prior to the 10th-cutoff deadline.\n\nYou can pay online instantly via EasyPaisa / JazzCash or visit the reception counter at Metropolis HQ.\n\nKeep pushing the iron,\nGymFlow Operations Team`,
    },
    {
      id: 'welcome_pwa',
      name: 'Welcome & PWA Setup',
      category: 'new',
      subject: 'Welcome to GymFlow Metropolis HQ Your Turnstile Pass is Ready',
      body: `Welcome to the squad, {{name}}!\n\nYour enrollment under {{plan}} has been verified. Your permanent athlete identity code is {{member_id}}.\n\nNext Steps:\n1. Open your browser and install the GymFlow PWA on your home screen.\n2. Log in with your phone number and present your dynamic optical QR pass at Gate #01 to scan in.\n3. Complete your complimentary functional movement screening with Head Coach Marcus.\n\nSee you on the gym floor,\nMetropolis HQ Front Desk`,
    },
    {
      id: 'we_miss_you',
      name: 'We Miss You (20% Off)',
      category: 'old',
      subject: 'Special 20% Re-Activation Privilege for {{name}} at GymFlow',
      body: `Hi {{name}},\n\nWe noticed you haven't scanned in since {{last_checkin}}. The barbells are missing you, and our new plate-loaded Hammer Strength expansion is officially online!\n\nReactivate your training this week with promo code TITAN20 to get 20% off any 3-Month or 6-Month pass.\n\nYour member profile {{member_id}} is preserved. Simply reply to this email or visit our front desk to resume immediately.\n\nBest regards,\nGymFlow Team`,
    },
    {
      id: 'streak_kudos',
      name: 'Consistency Kudos',
      category: 'current',
      subject: 'Outstanding Consistency: {{streak}} Check-in Streak Achieved!',
      body: `Salute, {{name}}!\n\nOur gate sensors recorded that you've logged {{streak}} check-ins this cycle under your {{plan}}.\n\nTrue progress is forged in routine. Keep inspiring the Metropolis lifting floor!\n\nTrain hard,\nGymFlow Performance Labs`,
    },
  ];

  const applyTemplate = (tmpl: (typeof templates)[0]) => {
    setEmailSubject(tmpl.subject);
    setEmailBody(tmpl.body);
    if (tmpl.category === 'current' || tmpl.category === 'new' || tmpl.category === 'old') {
      setAudienceFilter(tmpl.category);
    }
  };

  // Filter members according to selected filter & search
  const filteredMembers = mockMembers.filter((m) => {
    // 1. Audience filter
    if (audienceFilter === 'current' && m.category !== 'current') return false;
    if (audienceFilter === 'new' && m.category !== 'new') return false;
    if (audienceFilter === 'old' && m.category !== 'old') return false;
    if (audienceFilter === 'selected' && !selectedMemberIds.includes(m.id)) return false;

    // 2. Search query filter
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      const matchName = m.name.toLowerCase().includes(q);
      const matchCode = m.code.toLowerCase().includes(q);
      const matchEmail = m.email.toLowerCase().includes(q);
      return matchName || matchCode || matchEmail;
    }

    return true;
  });

  // Calculate count badges
  const currentCount = mockMembers.filter((m) => m.category === 'current').length;
  const newCount = mockMembers.filter((m) => m.category === 'new').length;
  const oldCount = mockMembers.filter((m) => m.category === 'old').length;
  const allCount = mockMembers.length;
  const selectedCount = selectedMemberIds.length;

  // Toggle single member selection
  const toggleMemberSelection = (id: string) => {
    setSelectedMemberIds((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  // Select all currently visible members
  const selectAllVisible = () => {
    const visibleIds = filteredMembers.map((m) => m.id);
    const combined = Array.from(new Set([...selectedMemberIds, ...visibleIds]));
    setSelectedMemberIds(combined);
  };

  // Clear all selections
  const clearSelection = () => {
    setSelectedMemberIds([]);
  };

  // Active recipient for preview
  const previewTarget =
    mockMembers.find((m) => m.id === previewMemberId) || filteredMembers[0] || mockMembers[0];

  // Helper to replace live template variables
  const getRenderedContent = (rawText: string, member: MemberRecipient) => {
    return rawText
      .replace(/{{name}}/g, member.name)
      .replace(/{{member_id}}/g, member.code)
      .replace(/{{plan}}/g, member.plan)
      .replace(/{{cutoff_date}}/g, member.cutoffDate)
      .replace(/{{amount_due}}/g, member.amountDue)
      .replace(/{{last_checkin}}/g, member.lastCheckIn)
      .replace(/{{streak}}/g, `${member.streakDays}-Day`);
  };

  // Target count to send
  const recipientsToSend = filteredMembers;

  // Mock send broadcast execution
  const handleSendBroadcast = () => {
    if (recipientsToSend.length === 0) return;

    setIsSending(true);
    setSendSuccessMessage(null);

    setTimeout(() => {
      setIsSending(false);
      const audienceLabel =
        audienceFilter === 'all'
          ? 'All Members'
          : audienceFilter === 'current'
            ? 'Current Members'
            : audienceFilter === 'new'
              ? 'New Members'
              : audienceFilter === 'old'
                ? 'Old Members'
                : 'Selected Members';

      const newLog: DispatchLog = {
        id: `LOG-${Math.floor(1000 + Math.random() * 9000)}`,
        timestamp: 'Just now',
        audience: audienceLabel,
        recipientCount: recipientsToSend.length,
        subject: emailSubject,
        status: 'Delivered (Mock)',
      };

      setDispatchLogs([newLog, ...dispatchLogs]);
      setSendSuccessMessage(
        `Mock broadcast successfully sent to ${recipientsToSend.length} athlete(s) via GymFlow Mail Relay!`
      );
    }, 900);
  };

  return (
    <div className="min-h-screen w-full bg-black text-white flex flex-col md:flex-row selection:bg-brand-red selection:text-white">
      <AdminSidebar />

      <main className="flex-1 p-6 sm:p-8 lg:p-10 w-full min-w-0 overflow-y-auto space-y-8">
        {/* Top Operational Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-900">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="text-xs font-semibold text-brand-red tracking-wider">
                Control Cockpit
              </span>
              <span className="text-zinc-600">&bull;</span>
              <span className="text-xs text-zinc-400">Metropolis Flagship HQ</span>
            </div>
            <h1 className="font-heading text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
              Gym Operations Overview
            </h1>
            <p className="text-xs text-zinc-500 mt-0.5">
              Live athlete roster, revenue intake, 10th-cutoff cycle, and real-time turnstile sync.
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-3">
            <a
              href="#email-broadcasting"
              className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-brand-red text-xs font-semibold tracking-wider border border-zinc-800 transition-colors flex items-center gap-2"
            >
              <i className="fa-solid fa-paper-plane text-brand-red" />
              <span>Email Dispatcher</span>
            </a>

            <Link
              href="/admin/admissions"
              className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-200 text-xs font-semibold tracking-wider border border-zinc-800 transition-colors flex items-center gap-2"
            >
              <i className="fa-solid fa-user-check text-brand-red" />
              <span>Review Admissions (3)</span>
            </Link>

            <Link
              href="/admin/members-add"
              className="px-4 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
            >
              <i className="fa-solid fa-user-plus text-xs" />
              <span>+ Add Walk-In</span>
            </Link>
          </div>
        </div>

        {/* 4 Primary Operational KPI Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {/* 1. Active Athletes */}
          <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 flex items-center justify-between">
            <div>
              <span className="text-[10px] font-semibold tracking-widest text-zinc-500 block mb-1">
                Active Athletes
              </span>
              <span className="font-heading text-4xl sm:text-5xl font-bold text-white block">
                142
              </span>
              <span className="text-[10px] text-emerald-400 font-semibold block mt-1">
                +14 new this month
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-brand-red/10 border border-brand-red/30 flex items-center justify-center text-brand-red text-xl">
              <i className="fa-solid fa-users" />
            </div>
          </div>

          {/* 2. Month Revenue in PKR */}
          <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 flex items-center justify-between">
            <div>
              <span className="text-[10px] font-semibold tracking-widest text-zinc-500 block mb-1">
                February Intake
              </span>
              <span className="font-heading text-3xl sm:text-4xl font-bold text-emerald-400 block font-mono">
                PKR 480k
              </span>
              <span className="text-[10px] text-zinc-400 font-semibold block mt-1">
                10th-Cutoff Cycle Active
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-xl">
              <i className="fa-solid fa-receipt" />
            </div>
          </div>

          {/* 3. New Online Admissions Alert */}
          <Link
            href="/admin/admissions"
            className="p-5 rounded-3xl bg-zinc-950 border border-brand-red/50 hover:border-brand-red transition-all flex items-center justify-between shadow-pass-glow group cursor-pointer"
          >
            <div>
              <span className="text-[10px] font-semibold tracking-widest text-brand-red block mb-1">
                New Admissions
              </span>
              <span className="font-heading text-4xl sm:text-5xl font-bold text-brand-red block">
                3 Pending
              </span>
              <span className="text-[10px] text-zinc-300 group-hover:text-white font-semibold block mt-1">
                Click to review dossiers &rarr;
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-brand-red/20 border border-brand-red/40 flex items-center justify-center text-brand-red text-xl">
              <i className="fa-solid fa-user-clock" />
            </div>
          </Link>

          {/* 4. Turnstile Gate Scans Today */}
          <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 flex items-center justify-between">
            <div>
              <span className="text-[10px] font-semibold tracking-widest text-zinc-500 block mb-1">
                Gate Scans Today
              </span>
              <span className="font-heading text-4xl sm:text-5xl font-bold text-white block font-mono">
                89
              </span>
              <span className="text-[10px] text-emerald-400 font-semibold block mt-1">
                34 on gym floor now
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl">
              <i className="fa-solid fa-door-open" />
            </div>
          </div>
        </div>

        {/* ================= PENDING ADMISSIONS BANNER ================= */}
        <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-4">
          <div className="flex items-center justify-between pb-3 border-b border-zinc-900">
            <div className="flex items-center gap-2.5">
              <span className="w-2.5 h-2.5 rounded-full bg-brand-red animate-pulse" />
              <h2 className="font-heading text-xl sm:text-2xl font-bold text-white tracking-tight">
                Incoming Online Admissions Queue
              </h2>
            </div>
            <Link
              href="/admin/admissions"
              className="text-xs text-brand-red font-semibold tracking-wider hover:underline"
            >
              Open Full Dossier Manager &rarr;
            </Link>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {pendingAdmissions.map((adm) => (
              <div
                key={adm.id}
                className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-3 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center justify-between mb-1">
                    <span className="font-bold text-white text-sm">{adm.name}</span>
                    <span className="text-[9px] font-mono text-zinc-500">{adm.id}</span>
                  </div>
                  <span className="text-xs text-brand-red font-bold block">{adm.plan}</span>
                  <span className="text-[11px] text-zinc-400 block font-mono">{adm.shift}</span>
                  <span className="text-[11px] text-emerald-400 block font-mono mt-1 font-bold">
                    {adm.amount}
                  </span>
                </div>

                <div className="pt-2 border-t border-zinc-900/80 flex items-center justify-between">
                  <span className="text-[10px] text-zinc-500">{adm.submitted}</span>
                  <Link
                    href="/admin/admissions"
                    className="px-3 py-1.5 rounded-lg bg-zinc-900 hover:bg-brand-red text-zinc-300 hover:text-white text-[10px] font-semibold tracking-wider transition-colors"
                  >
                    Review &amp; Approve
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* ================= 2-COLUMN OPERATIONAL GRID ================= */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          {/* Left: Real-Time Gate Scans (6 cols) */}
          <div className="lg:col-span-6 rounded-3xl bg-zinc-950 border border-zinc-900 p-6 space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-zinc-900">
              <div className="flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                <h3 className="font-heading text-xl font-bold text-white tracking-tight">
                  Live Turnstile Scans
                </h3>
              </div>
              <Link href="/admin/attendance" className="text-xs text-brand-red font-bold hover:underline">
                View All &rarr;
              </Link>
            </div>

            <div className="space-y-2.5">
              {recentScans.map((scan, idx) => (
                <div
                  key={idx}
                  className="p-3 rounded-2xl bg-black border border-zinc-900 flex items-center justify-between text-xs"
                >
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-xl bg-zinc-900 text-brand-red flex items-center justify-center font-bold text-[10px]">
                      {scan.name.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                      <span className="font-bold text-white block">{scan.name}</span>
                      <span className="text-[10px] text-zinc-500 font-mono">
                        {scan.code} &bull; {scan.gate}
                      </span>
                    </div>
                  </div>

                  <div className="text-right">
                    <span className="text-[10px] font-mono text-zinc-400 block">{scan.time}</span>
                    <span className="text-[9px] font-bold font-mono text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                      {scan.status}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Right: Recent Invoices & Collections (6 cols) */}
          <div className="lg:col-span-6 rounded-3xl bg-zinc-950 border border-zinc-900 p-6 space-y-4">
            <div className="flex justify-between items-center pb-3 border-b border-zinc-900">
              <h3 className="font-heading text-xl font-bold text-white tracking-tight">
                Recent Ledger Intake
              </h3>
              <Link href="/admin/billing" className="text-xs text-brand-red font-bold hover:underline">
                Ledger History &rarr;
              </Link>
            </div>

            <div className="space-y-2.5">
              {recentPayments.map((p, idx) => (
                <div
                  key={idx}
                  className="p-3 rounded-2xl bg-black border border-zinc-900 flex items-center justify-between text-xs"
                >
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="font-bold text-white">{p.name}</span>
                      <span className="text-[10px] text-zinc-500 font-mono">({p.code})</span>
                    </div>
                    <span className="text-[10px] text-zinc-400 block font-mono">
                      {p.plan} &bull; {p.channel}
                    </span>
                  </div>

                  <div className="text-right">
                    <span className="font-mono font-bold text-white block">{p.amount}</span>
                    <span
                      className={`text-[9px] font-bold font-mono px-1.5 py-0.5 rounded ${p.status === 'Paid'
                          ? 'bg-emerald-500/10 text-emerald-400'
                          : 'bg-amber-500/10 text-amber-400'
                        }`}
                    >
                      {p.status}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* ========================================================================= */}
        {/* ============= MEMBER EMAIL SENDING & BROADCAST MOCK SECTION ============= */}
        {/* ========================================================================= */}
        <section
          id="email-broadcasting"
          className="rounded-3xl bg-zinc-950 border border-zinc-900 p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden"
        >
          {/* Subtle Ambient Red Glow */}
          <div className="absolute top-0 right-0 w-96 h-96 bg-brand-red/5 rounded-full blur-3xl pointer-events-none" />

          {/* Section Header */}
          <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-zinc-900">
            <div>
              <div className="flex items-center gap-2 mb-1.5">
                <span className="px-2.5 py-0.5 rounded-full bg-brand-red/10 border border-brand-red/30 text-[10px] font-bold text-brand-red tracking-wider">
                  Member Communication Hub
                </span>
                <span className="text-xs text-zinc-500">&bull;</span>
                <span className="text-xs text-emerald-400 font-mono flex items-center gap-1.5">
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                  Mock SMTP Relay Active
                </span>
              </div>
              <h2 className="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Email Sending &amp; Broadcast Dispatcher
              </h2>
              <p className="text-xs sm:text-sm text-zinc-400 mt-1 max-w-2xl">
                Inspect recipient member details, filter audiences (old, current, new, selected, and all), customize personalized message templates, and dispatch test broadcasts.
              </p>
            </div>

            {/* Quick Metrics Bar */}
            <div className="flex items-center gap-4 bg-black/60 p-3 rounded-2xl border border-zinc-900">
              <div className="text-center px-2">
                <span className="text-[10px] text-zinc-500 block font-semibold">Active Roster</span>
                <span className="text-base font-bold text-white font-mono">{allCount}</span>
              </div>
              <div className="h-6 w-px bg-zinc-800" />
              <div className="text-center px-2">
                <span className="text-[10px] text-zinc-500 block font-semibold">Selected</span>
                <span className="text-base font-bold text-brand-red font-mono">{selectedCount}</span>
              </div>
              <div className="h-6 w-px bg-zinc-800" />
              <div className="text-center px-2">
                <span className="text-[10px] text-zinc-500 block font-semibold">Next Cutoff</span>
                <span className="text-xs font-bold text-amber-400 font-mono">Feb 10</span>
              </div>
            </div>
          </div>

          {/* Success Notification Alert */}
          {sendSuccessMessage && (
            <div className="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm flex items-center justify-between animate-fadeIn">
              <div className="flex items-center gap-3">
                <i className="fa-solid fa-circle-check text-emerald-400 text-lg" />
                <span>{sendSuccessMessage}</span>
              </div>
              <button
                onClick={() => setSendSuccessMessage(null)}
                className="text-zinc-400 hover:text-white px-2 py-1 text-xs"
              >
                &times; Dismiss
              </button>
            </div>
          )}

          {/* Audience Filter Bar & Search */}
          <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            {/* Filter Tabs */}
            <div className="flex flex-wrap items-center gap-2">
              {/* 1. Current Members */}
              <button
                type="button"
                onClick={() => setAudienceFilter('current')}
                className={`px-3.5 py-2 rounded-xl text-xs font-semibold tracking-wider transition-all flex items-center gap-2 ${audienceFilter === 'current'
                    ? 'bg-brand-red text-white shadow-lg shadow-brand-red/30'
                    : 'bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 border border-zinc-800'
                  }`}
              >
                <i className="fa-solid fa-user-check text-[11px]" />
                <span>Current Members</span>
                <span
                  className={`px-1.5 py-0.5 rounded-full text-[10px] font-mono ${audienceFilter === 'current'
                      ? 'bg-black/30 text-white'
                      : 'bg-zinc-800 text-zinc-400'
                    }`}
                >
                  {currentCount}
                </span>
              </button>

              {/* 2. New Members */}
              <button
                type="button"
                onClick={() => setAudienceFilter('new')}
                className={`px-3.5 py-2 rounded-xl text-xs font-semibold tracking-wider transition-all flex items-center gap-2 ${audienceFilter === 'new'
                    ? 'bg-brand-red text-white shadow-lg shadow-brand-red/30'
                    : 'bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 border border-zinc-800'
                  }`}
              >
                <i className="fa-solid fa-sparkles text-[11px]" />
                <span>New Members</span>
                <span
                  className={`px-1.5 py-0.5 rounded-full text-[10px] font-mono ${audienceFilter === 'new'
                      ? 'bg-black/30 text-white'
                      : 'bg-zinc-800 text-zinc-400'
                    }`}
                >
                  {newCount}
                </span>
              </button>

              {/* 3. Old Members */}
              <button
                type="button"
                onClick={() => setAudienceFilter('old')}
                className={`px-3.5 py-2 rounded-xl text-xs font-semibold tracking-wider transition-all flex items-center gap-2 ${audienceFilter === 'old'
                    ? 'bg-brand-red text-white shadow-lg shadow-brand-red/30'
                    : 'bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 border border-zinc-800'
                  }`}
              >
                <i className="fa-solid fa-clock-rotate-left text-[11px]" />
                <span>Old Members</span>
                <span
                  className={`px-1.5 py-0.5 rounded-full text-[10px] font-mono ${audienceFilter === 'old'
                      ? 'bg-black/30 text-white'
                      : 'bg-zinc-800 text-zinc-400'
                    }`}
                >
                  {oldCount}
                </span>
              </button>

              {/* 4. Selected Members */}
              <button
                type="button"
                onClick={() => setAudienceFilter('selected')}
                className={`px-3.5 py-2 rounded-xl text-xs font-semibold tracking-wider transition-all flex items-center gap-2 ${audienceFilter === 'selected'
                    ? 'bg-brand-red text-white shadow-lg shadow-brand-red/30'
                    : 'bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 border border-zinc-800'
                  }`}
              >
                <i className="fa-solid fa-square-check text-[11px]" />
                <span>Selected Members</span>
                <span
                  className={`px-1.5 py-0.5 rounded-full text-[10px] font-mono ${audienceFilter === 'selected'
                      ? 'bg-black/30 text-white'
                      : 'bg-zinc-800 text-zinc-400'
                    }`}
                >
                  {selectedCount}
                </span>
              </button>

              {/* 5. All Members */}
              <button
                type="button"
                onClick={() => setAudienceFilter('all')}
                className={`px-3.5 py-2 rounded-xl text-xs font-semibold tracking-wider transition-all flex items-center gap-2 ${audienceFilter === 'all'
                    ? 'bg-brand-red text-white shadow-lg shadow-brand-red/30'
                    : 'bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 border border-zinc-800'
                  }`}
              >
                <i className="fa-solid fa-users text-[11px]" />
                <span>All Members</span>
                <span
                  className={`px-1.5 py-0.5 rounded-full text-[10px] font-mono ${audienceFilter === 'all'
                      ? 'bg-black/30 text-white'
                      : 'bg-zinc-800 text-zinc-400'
                    }`}
                >
                  {allCount}
                </span>
              </button>
            </div>

            {/* Search Input & Batch Select */}
            <div className="flex items-center gap-2.5">
              <div className="relative">
                <i className="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-xs" />
                <input
                  type="text"
                  placeholder="Filter name, code, or email..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-8 pr-3 py-2 rounded-xl bg-black border border-zinc-800 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red w-52 sm:w-64"
                />
              </div>

              <button
                type="button"
                onClick={selectAllVisible}
                title="Select all visible recipients in current view"
                className="px-2.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-[11px] font-semibold text-zinc-300 transition-colors"
              >
                Select All
              </button>

              {selectedCount > 0 && (
                <button
                  type="button"
                  onClick={clearSelection}
                  title="Clear selected checkmarks"
                  className="px-2.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-[11px] font-semibold text-zinc-400 hover:text-zinc-200 transition-colors"
                >
                  Clear ({selectedCount})
                </button>
              )}
            </div>
          </div>

          {/* Preset Template Quick-Selector Pills */}
          <div className="p-4 rounded-2xl bg-black/50 border border-zinc-900 flex flex-wrap items-center gap-2.5">
            <span className="text-[11px] font-semibold text-zinc-400 mr-2 flex items-center gap-1.5">
              <i className="fa-solid fa-bolt text-brand-red" />
              Quick Templates:
            </span>
            {templates.map((tmpl) => (
              <button
                key={tmpl.id}
                type="button"
                onClick={() => applyTemplate(tmpl)}
                className="px-3 py-1.5 rounded-lg bg-zinc-900/90 hover:bg-brand-red hover:text-white border border-zinc-800 text-zinc-300 text-xs font-medium transition-all"
              >
                {tmpl.name}
              </button>
            ))}
          </div>

          {/* ================= MAIN 2-COLUMN EMAIL WORKBENCH ================= */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {/* LEFT: Recipient Details Table (7 Cols) */}
            <div className="lg:col-span-7 rounded-2xl bg-black border border-zinc-900 overflow-hidden flex flex-col justify-between">
              <div>
                <div className="p-4 border-b border-zinc-900 flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <span className="font-heading text-sm font-bold text-white tracking-tight">
                      Target Recipients In Queue
                    </span>
                    <span className="px-2 py-0.5 rounded-full bg-zinc-900 text-zinc-400 text-[10px] font-mono">
                      {filteredMembers.length} Athlete(s)
                    </span>
                  </div>
                  <span className="text-[11px] text-zinc-500">
                    Click row to preview personalized email
                  </span>
                </div>

                <div className="overflow-x-auto max-h-[480px] overflow-y-auto divide-y divide-zinc-900/70">
                  {filteredMembers.length === 0 ? (
                    <div className="p-12 text-center text-zinc-500">
                      <i className="fa-solid fa-inbox text-3xl mb-3 text-zinc-600 block" />
                      <p className="text-xs">No members match the current filter or search criteria.</p>
                      {audienceFilter === 'selected' && (
                        <p className="text-[11px] text-zinc-600 mt-1">
                          Tip: Switch to &ldquo;All Members&rdquo; or &ldquo;Current Members&rdquo; and check recipients to include them.
                        </p>
                      )}
                    </div>
                  ) : (
                    <table className="w-full text-left text-xs">
                      <thead className="text-[10px] font-semibold text-zinc-500 bg-zinc-950/80 sticky top-0 z-10 border-b border-zinc-900">
                        <tr>
                          <th className="py-2.5 px-3 w-8">
                            <span className="sr-only">Select</span>
                          </th>
                          <th className="py-2.5 px-3">Athlete</th>
                          <th className="py-2.5 px-3">Email &amp; Phone</th>
                          <th className="py-2.5 px-3">Category</th>
                          <th className="py-2.5 px-3">Plan / Due</th>
                          <th className="py-2.5 px-3">Cutoff / Last Scan</th>
                          <th className="py-2.5 px-3 text-right">Action</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-zinc-900/60">
                        {filteredMembers.map((member) => {
                          const isSelected = selectedMemberIds.includes(member.id);
                          const isPreviewing = previewTarget.id === member.id;

                          return (
                            <tr
                              key={member.id}
                              onClick={() => setPreviewMemberId(member.id)}
                              className={`cursor-pointer transition-colors ${isPreviewing
                                  ? 'bg-brand-red/10 border-l-2 border-brand-red'
                                  : 'hover:bg-zinc-900/50'
                                }`}
                            >
                              {/* Checkbox */}
                              <td
                                className="py-3 px-3"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  toggleMemberSelection(member.id);
                                }}
                              >
                                <input
                                  type="checkbox"
                                  checked={isSelected}
                                  onChange={() => { }}
                                  className="w-4 h-4 rounded border-zinc-700 bg-black text-brand-red focus:ring-brand-red accent-brand-red cursor-pointer"
                                />
                              </td>

                              {/* Athlete Name & Code */}
                              <td className="py-3 px-3">
                                <div className="flex items-center gap-2">
                                  <div className="w-7 h-7 rounded-lg bg-zinc-900 text-zinc-300 border border-zinc-800 flex items-center justify-center font-bold text-[10px]">
                                    {member.name.substring(0, 2).toUpperCase()}
                                  </div>
                                  <div>
                                    <span className="font-bold text-white block leading-tight">
                                      {member.name}
                                    </span>
                                    <span className="text-[10px] text-zinc-500 font-mono">
                                      {member.code}
                                    </span>
                                  </div>
                                </div>
                              </td>

                              {/* Email & Phone */}
                              <td className="py-3 px-3">
                                <span className="text-zinc-300 block font-mono text-[11px]">
                                  {member.email}
                                </span>
                                <span className="text-[10px] text-zinc-500 font-mono">
                                  {member.phone}
                                </span>
                              </td>

                              {/* Category Badge */}
                              <td className="py-3 px-3">
                                {member.category === 'current' && (
                                  <span className="px-2 py-0.5 rounded-full text-[9px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Current
                                  </span>
                                )}
                                {member.category === 'new' && (
                                  <span className="px-2 py-0.5 rounded-full text-[9px] font-bold font-mono bg-sky-500/10 text-sky-400 border border-sky-500/20">
                                    New Entry
                                  </span>
                                )}
                                {member.category === 'old' && (
                                  <span className="px-2 py-0.5 rounded-full text-[9px] font-bold font-mono bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    Old / Lapsed
                                  </span>
                                )}
                              </td>

                              {/* Plan & Amount Due */}
                              <td className="py-3 px-3">
                                <span className="text-zinc-300 block text-[11px] font-medium leading-tight">
                                  {member.plan}
                                </span>
                                <span className="text-[10px] text-zinc-400 font-mono">
                                  Due: {member.amountDue}
                                </span>
                              </td>

                              {/* Cutoff / Last Checkin */}
                              <td className="py-3 px-3">
                                <span className="text-zinc-400 block text-[11px] font-mono leading-tight">
                                  {member.cutoffDate}
                                </span>
                                <span className="text-[10px] text-zinc-500">
                                  {member.lastCheckIn}
                                </span>
                              </td>

                              {/* Action */}
                              <td className="py-3 px-3 text-right">
                                <button
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    setPreviewMemberId(member.id);
                                    setActiveTab('preview');
                                  }}
                                  className={`px-2 py-1 rounded-lg text-[10px] font-semibold tracking-wider transition-colors ${isPreviewing
                                      ? 'bg-brand-red text-white'
                                      : 'bg-zinc-900 text-zinc-400 hover:text-white'
                                    }`}
                                >
                                  {isPreviewing ? 'Viewing' : 'Inspect'}
                                </button>
                              </td>
                            </tr>
                          );
                        })}
                      </tbody>
                    </table>
                  )}
                </div>
              </div>

              {/* Table Footer with Audience Summary */}
              <div className="p-3 bg-zinc-950/80 border-t border-zinc-900 text-[11px] text-zinc-400 flex items-center justify-between">
                <div>
                  Queue filter: <span className="font-bold text-white capitalize">{audienceFilter}</span> &bull;{' '}
                  <span className="text-emerald-400">{filteredMembers.length} in queue</span>
                </div>
                <div className="flex items-center gap-2">
                  <span className="text-zinc-500 font-mono">
                    Recipient target: {previewTarget.name} ({previewTarget.code})
                  </span>
                </div>
              </div>
            </div>

            {/* RIGHT: Dynamic Email Composer & Live Interactive Preview (5 Cols) */}
            <div className="lg:col-span-5 rounded-2xl bg-black border border-zinc-900 p-5 flex flex-col justify-between space-y-4">
              {/* Tab Switcher: Composer vs Preview */}
              <div className="flex items-center justify-between pb-3 border-b border-zinc-900">
                <div className="flex items-center gap-1 bg-zinc-900/80 p-1 rounded-xl border border-zinc-800">
                  <button
                    type="button"
                    onClick={() => setActiveTab('preview')}
                    className={`px-3 py-1 rounded-lg text-xs font-semibold tracking-wider transition-all flex items-center gap-1.5 ${activeTab === 'preview'
                        ? 'bg-brand-red text-white shadow'
                        : 'text-zinc-400 hover:text-white'
                      }`}
                  >
                    <i className="fa-solid fa-eye text-[10px]" />
                    <span>Live Preview</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => setActiveTab('composer')}
                    className={`px-3 py-1 rounded-lg text-xs font-semibold tracking-wider transition-all flex items-center gap-1.5 ${activeTab === 'composer'
                        ? 'bg-brand-red text-white shadow'
                        : 'text-zinc-400 hover:text-white'
                      }`}
                  >
                    <i className="fa-solid fa-pen-to-square text-[10px]" />
                    <span>Edit Composer</span>
                  </button>
                </div>

                <div className="text-[11px] text-zinc-400 flex items-center gap-1 font-mono">
                  <span className="text-zinc-600">Target:</span>
                  <span className="text-brand-red font-bold">{previewTarget.name}</span>
                </div>
              </div>

              {/* TAB 1: Live Interactive Email Preview */}
              {activeTab === 'preview' && (
                <div className="space-y-4">
                  {/* Email Chrome Container */}
                  <div className="rounded-2xl bg-zinc-950 border border-zinc-800/80 overflow-hidden shadow-xl text-xs">
                    {/* Fake Email Envelope Header */}
                    <div className="bg-zinc-900/90 p-3.5 border-b border-zinc-800 space-y-1.5">
                      <div className="flex items-center justify-between text-[11px]">
                        <span className="text-zinc-500">From:</span>
                        <span className="text-zinc-300 font-mono">
                          GymFlow Dispatch Relay &lt;ops@gymflow.io&gt;
                        </span>
                      </div>
                      <div className="flex items-center justify-between text-[11px]">
                        <span className="text-zinc-500">To:</span>
                        <div className="flex items-center gap-1.5 font-mono">
                          <span className="text-white font-bold">{previewTarget.name}</span>
                          <span className="text-zinc-400">&lt;{previewTarget.email}&gt;</span>
                        </div>
                      </div>
                      <div className="flex items-start justify-between text-[11px] pt-1">
                        <span className="text-zinc-500 shrink-0 mr-2">Subject:</span>
                        <span className="text-white font-semibold text-right break-words">
                          {getRenderedContent(emailSubject, previewTarget)}
                        </span>
                      </div>
                    </div>

                    {/* Email Card Interior (Brand Styling) */}
                    <div className="p-5 bg-black space-y-4">
                      {/* Brand Header */}
                      <div className="flex items-center justify-between pb-3 border-b border-zinc-900">
                        <div className="flex items-center gap-2">
                          <div className="w-6 h-6 rounded-lg bg-brand-red flex items-center justify-center text-white font-bold text-xs">
                            G
                          </div>
                          <span className="font-heading font-extrabold text-sm text-white">
                            Gym<span className="text-brand-red">Flow.</span>
                          </span>
                        </div>
                        <span className="text-[10px] text-zinc-500 font-mono uppercase tracking-wider">
                          Official Notification
                        </span>
                      </div>

                      {/* Recipient Details Pill */}
                      <div className="p-3 rounded-xl bg-zinc-950 border border-zinc-900 flex items-center justify-between">
                        <div>
                          <span className="text-[10px] text-zinc-500 block">Athlete Dossier</span>
                          <span className="text-xs font-bold text-white">
                            {previewTarget.name} &bull; {previewTarget.code}
                          </span>
                        </div>
                        <div className="text-right">
                          <span className="text-[10px] text-zinc-500 block">Status</span>
                          <span className="text-[10px] font-bold text-emerald-400 font-mono capitalize">
                            {previewTarget.category} Member
                          </span>
                        </div>
                      </div>

                      {/* Rendered Body Text */}
                      <div className="text-zinc-300 leading-relaxed space-y-3 font-normal text-xs whitespace-pre-line bg-zinc-950/40 p-4 rounded-xl border border-zinc-900">
                        {getRenderedContent(emailBody, previewTarget)}
                      </div>

                      {/* Call to Action Button in Email */}
                      <div className="pt-2 text-center">
                        <span className="inline-block px-5 py-2.5 rounded-xl bg-brand-red text-white font-bold text-xs shadow-md shadow-brand-red/30 tracking-wider">
                          Access Digital Pass &amp; Portal &rarr;
                        </span>
                      </div>

                      {/* Email Footer */}
                      <div className="pt-4 border-t border-zinc-900 text-center space-y-1">
                        <span className="text-[10px] text-zinc-600 block">
                          Metropolis Flagship HQ &bull; Plot 42-C Commercial Sector 4 &bull; +92 325 9491349
                        </span>
                        <span className="text-[9px] text-zinc-700 block font-mono">
                          Automated transaction receipt ID: GF-DISPATCH-{previewTarget.code}-2026
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* TAB 2: Dynamic Template Composer */}
              {activeTab === 'composer' && (
                <div className="space-y-4">
                  <div>
                    <label className="text-[11px] font-bold text-zinc-400 block mb-1">
                      Email Subject Line
                    </label>
                    <input
                      type="text"
                      value={emailSubject}
                      onChange={(e) => setEmailSubject(e.target.value)}
                      className="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <label className="text-[11px] font-bold text-zinc-400 block">
                        Email Body (Markdown / Dynamic Variables)
                      </label>
                      <span className="text-[10px] text-zinc-500">
                        Insert tags below for live personalization
                      </span>
                    </div>

                    <textarea
                      rows={9}
                      value={emailBody}
                      onChange={(e) => setEmailBody(e.target.value)}
                      className="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-xs text-zinc-200 focus:outline-none focus:border-brand-red font-mono leading-relaxed resize-none"
                    />
                  </div>

                  {/* Variable Pills */}
                  <div>
                    <span className="text-[10px] font-bold text-zinc-500 block mb-1.5">
                      Click to insert template tag:
                    </span>
                    <div className="flex flex-wrap gap-1.5">
                      {[
                        '{{name}}',
                        '{{member_id}}',
                        '{{plan}}',
                        '{{cutoff_date}}',
                        '{{amount_due}}',
                        '{{last_checkin}}',
                        '{{streak}}',
                      ].map((tag) => (
                        <button
                          key={tag}
                          type="button"
                          onClick={() => setEmailBody((prev) => `${prev} ${tag}`)}
                          className="px-2 py-1 rounded bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-brand-red text-[10px] font-mono border border-zinc-800 transition-colors"
                        >
                          {tag}
                        </button>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* Broadcast Execution Bar */}
              <div className="pt-3 border-t border-zinc-900 space-y-3">
                <div className="flex items-center justify-between text-xs">
                  <span className="text-zinc-400">
                    Dispatch Target:{' '}
                    <span className="text-white font-bold capitalize">
                      {audienceFilter === 'selected'
                        ? `${selectedCount} Selected Members`
                        : `${recipientsToSend.length} in ${audienceFilter} filter`}
                    </span>
                  </span>
                  <span className="text-[11px] font-mono text-zinc-500">
                    Delivery ETA: ~0.8s
                  </span>
                </div>

                <button
                  type="button"
                  disabled={isSending || recipientsToSend.length === 0}
                  onClick={handleSendBroadcast}
                  className="w-full py-3 rounded-xl bg-brand-red hover:bg-brand-red-hover disabled:opacity-50 text-white font-bold text-xs tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
                >
                  {isSending ? (
                    <>
                      <i className="fa-solid fa-spinner fa-spin text-sm" />
                      <span>Transmitting Mock Email Broadcast...</span>
                    </>
                  ) : (
                    <>
                      <i className="fa-solid fa-paper-plane text-xs" />
                      <span>
                        Send Mock Email Broadcast ({recipientsToSend.length} Athlete
                        {recipientsToSend.length === 1 ? '' : 's'})
                      </span>
                    </>
                  )}
                </button>
              </div>

              {/* Mock Broadcast History Log */}
              <div className="pt-2">
                <span className="text-[10px] font-bold text-zinc-500 uppercase tracking-widest block mb-2">
                  Recent Mock Dispatches
                </span>
                <div className="space-y-1.5 max-h-32 overflow-y-auto">
                  {dispatchLogs.map((log) => (
                    <div
                      key={log.id}
                      className="p-2 rounded-xl bg-zinc-950 border border-zinc-900 flex items-center justify-between text-[11px]"
                    >
                      <div className="truncate mr-2">
                        <span className="text-white font-semibold block truncate">
                          {log.subject}
                        </span>
                        <span className="text-[9px] text-zinc-500 font-mono">
                          {log.audience} &bull; {log.recipientCount} recipient(s) &bull; {log.timestamp}
                        </span>
                      </div>
                      <span className="px-2 py-0.5 rounded text-[9px] font-bold font-mono bg-emerald-500/10 text-emerald-400 shrink-0">
                        {log.status}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>
  );
}
