'use client';

import { useState } from 'react';
import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';
import RealQrCode from '@/components/real-qr-code';

export default function AddMemberPage() {
  // Form states
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('+92 ');
  const [gender, setGender] = useState('Male');
  const [emergencyContact, setEmergencyContact] = useState('');
  const [emergencyPhone, setEmergencyPhone] = useState('+92 ');

  const [selectedPlan, setSelectedPlan] = useState<'1m' | '3m' | '6m'>('3m');
  const [memberCode, setMemberCode] = useState('2026-005');
  const [startDate, setStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [paymentMethod, setPaymentMethod] = useState<'CASH' | 'EASYPAISA' | 'JAZZCASH' | 'BANK'>('EASYPAISA');
  const [paymentStatus, setPaymentStatus] = useState<'PAID' | 'PENDING'>('PAID');
  const [sendWhatsApp, setSendWhatsApp] = useState(true);

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isRegistered, setIsRegistered] = useState(false);

  // Plan pricing lookup
  const planDetails = {
    '1m': { name: '1 Month Standard', price: 8500, days: 30 },
    '3m': { name: '3 Month Pro Quarter', price: 22500, days: 90 },
    '6m': { name: '6 Month VIP Elite', price: 42000, days: 180 },
  };

  const currentPlan = planDetails[selectedPlan];

  // Calculate expiry date
  const calculateExpiry = () => {
    const d = new Date(startDate);
    d.setDate(d.getDate() + currentPlan.days);
    return d.toISOString().split('T')[0];
  };

  const expiryDate = calculateExpiry();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setTimeout(() => {
      setIsSubmitting(false);
      setIsRegistered(true);
    }, 800);
  };

  return (
    <div className="flex flex-col md:flex-row min-h-screen w-full bg-black text-white selection:bg-brand-red selection:text-white">
      <AdminSidebar />

      <main className="flex-1 p-6 sm:p-8 lg:p-10 w-full min-w-0 space-y-8 overflow-y-auto">
        {/* Header Bar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-900 pb-6">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <Link
                href="/admin/members"
                className="text-xs font-semibold text-zinc-500 hover:text-white transition-colors"
              >
                &larr; Members Directory
              </Link>
              <span className="text-zinc-600">&bull;</span>
              <span className="text-xs font-semibold text-brand-red tracking-wider">
                Enrollment Wizard
              </span>
            </div>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              Register New Athlete / Member
            </h1>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1 max-w-2xl">
              Create athlete identity, allocate biometric pass ID, activate turnstile gate clearance,
              and record initial onboarding dues.
            </p>
          </div>

          <div className="flex items-center gap-2">
            <span className="px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-xs font-mono text-zinc-400">
              Allocating: <span className="text-brand-red font-bold">{memberCode}</span>
            </span>
          </div>
        </div>

        {isRegistered ? (
          /* Registration Success State */
          <div className="p-8 sm:p-12 rounded-3xl bg-zinc-950 border border-brand-red/50 shadow-pass-glow text-center space-y-6 max-w-xl mx-auto animate-fadeIn">
            <div className="w-20 h-20 rounded-full bg-emerald-500/10 border-2 border-emerald-500 text-emerald-400 mx-auto flex items-center justify-center text-4xl">
              <i className="fa-solid fa-check" />
            </div>

            <div>
              <span className="text-[10px] font-semibold text-emerald-400 tracking-widest block">
                Athlete Onboarded Successfully
              </span>
              <h2 className="font-heading text-4xl font-bold text-white mt-1">
                {fullName || 'New Athlete'}
              </h2>
              <p className="text-xs text-zinc-400 mt-2">
                Member code <span className="font-mono text-white font-bold">{memberCode}</span> is
                now active on turnstile gates until{' '}
                <span className="text-brand-red font-bold">{expiryDate}</span>.
              </p>
            </div>

            {/* Quick Digital Pass Card */}
            <div className="p-6 rounded-2xl bg-black border border-zinc-800 text-center space-y-3">
              <RealQrCode
                value={memberCode}
                size={140}
                downloadable={true}
                showFormatToggle={true}
              />
              <span className="font-mono text-xl font-bold text-white block">{memberCode}</span>
              <span className="text-[10px] font-semibold text-zinc-500 block">
                {currentPlan.name} &bull; PKR {currentPlan.price.toLocaleString()}
              </span>
            </div>

            {sendWhatsApp && (
              <div className="p-3 rounded-xl bg-emerald-950/40 border border-emerald-900/60 text-emerald-300 text-xs flex items-center justify-center gap-2">
                <i className="fa-brands fa-whatsapp text-sm" />
                <span>Automated welcome message & QR Pass queued for WhatsApp dispatch.</span>
              </div>
            )}

            <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4">
              <Link
                href="/admin/members"
                className="w-full sm:w-auto px-6 py-3 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold tracking-wider transition-colors"
              >
                Go to Members List
              </Link>
              <button
                onClick={() => {
                  setIsRegistered(false);
                  setFullName('');
                  setEmail('');
                  setPhone('+92 ');
                  setMemberCode(`2026-${Math.floor(100 + Math.random() * 900)}`);
                }}
                className="w-full sm:w-auto px-6 py-3 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all"
              >
                + Register Another Member
              </button>
            </div>
          </div>
        ) : (
          /* Main 3-Column Registration Form Layout */
          <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Step 1 & 2: Form Inputs (Left 2 cols) */}
            <div className="lg:col-span-2 space-y-8">
              {/* Step 1: Athlete Personal Profile */}
              <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6">
                <div className="flex items-center gap-3 pb-4 border-b border-zinc-900">
                  <div className="w-8 h-8 rounded-xl bg-brand-red/20 text-brand-red flex items-center justify-center font-bold text-xs">
                    01
                  </div>
                  <div>
                    <h3 className="text-sm font-semibold tracking-wider text-white">
                      Athlete Personal Profile
                    </h3>
                    <p className="text-[11px] text-zinc-500">
                      Basic identification, phone for WhatsApp alerts, and emergency contacts.
                    </p>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                  <div className="sm:col-span-2">
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Full Name *
                    </label>
                    <input
                      type="text"
                      required
                      value={fullName}
                      onChange={(e) => setFullName(e.target.value)}
                      placeholder="e.g. Tariq Mehmood"
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Email Address *
                    </label>
                    <input
                      type="email"
                      required
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="athlete@gymflow.com"
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      WhatsApp Phone Number *
                    </label>
                    <input
                      type="text"
                      required
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      placeholder="+92 300 1234567"
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red font-mono"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Gender
                    </label>
                    <select
                      value={gender}
                      onChange={(e) => setGender(e.target.value)}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                    >
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Emergency Contact Name & Phone
                    </label>
                    <input
                      type="text"
                      value={emergencyContact}
                      onChange={(e) => setEmergencyContact(e.target.value)}
                      placeholder="Guardian / Friend (+92...)"
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                    />
                  </div>
                </div>
              </div>

              {/* Step 2: Membership Tier & Pass Configuration */}
              <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6">
                <div className="flex items-center gap-3 pb-4 border-b border-zinc-900">
                  <div className="w-8 h-8 rounded-xl bg-brand-red/20 text-brand-red flex items-center justify-center font-bold text-xs">
                    02
                  </div>
                  <div>
                    <h3 className="text-sm font-semibold tracking-wider text-white">
                      Membership Package & Turnstile Gate
                    </h3>
                    <p className="text-[11px] text-zinc-500">
                      Select subscription tier, effective start date, and allocated pass ID.
                    </p>
                  </div>
                </div>

                {/* Plan Selector Buttons */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  {(['1m', '3m', '6m'] as const).map((planKey) => {
                    const p = planDetails[planKey];
                    const isSelected = selectedPlan === planKey;
                    return (
                      <button
                        type="button"
                        key={planKey}
                        onClick={() => setSelectedPlan(planKey)}
                        className={`p-4 rounded-2xl text-left transition-all border ${
                          isSelected
                            ? 'bg-brand-red/10 border-brand-red shadow-pass-glow'
                            : 'bg-black border-zinc-800 hover:border-zinc-700'
                        }`}
                      >
                        <div className="flex items-center justify-between mb-2">
                          <span className="text-xs font-medium text-zinc-400">
                            {p.days} Days
                          </span>
                          {planKey === '3m' && (
                            <span className="text-[9px] font-bold text-brand-red bg-brand-red/20 px-1.5 py-0.5 rounded">
                              Popular
                            </span>
                          )}
                        </div>
                        <span className="text-sm font-bold text-white block">{p.name}</span>
                        <span className="font-heading text-xl font-bold text-emerald-400 font-mono mt-1 block">
                          PKR {p.price.toLocaleString()}
                        </span>
                      </button>
                    );
                  })}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs pt-2">
                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Allocated Pass ID *
                    </label>
                    <input
                      type="text"
                      required
                      value={memberCode}
                      onChange={(e) => setMemberCode(e.target.value)}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-brand-red font-mono font-bold focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Start Date
                    </label>
                    <input
                      type="date"
                      required
                      value={startDate}
                      onChange={(e) => setStartDate(e.target.value)}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red font-mono"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Auto-Calculated Expiry
                    </label>
                    <div className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-amber-400 font-mono font-bold">
                      {expiryDate}
                    </div>
                  </div>
                </div>
              </div>

              {/* Step 3: Billing & Dues */}
              <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6">
                <div className="flex items-center gap-3 pb-4 border-b border-zinc-900">
                  <div className="w-8 h-8 rounded-xl bg-brand-red/20 text-brand-red flex items-center justify-center font-bold text-xs">
                    03
                  </div>
                  <div>
                    <h3 className="text-sm font-semibold tracking-wider text-white">
                      Initial Dues & Payment Ledger
                    </h3>
                    <p className="text-[11px] text-zinc-500">
                      Record upfront payment and trigger automated WhatsApp notification.
                    </p>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Amount Due (PKR)
                    </label>
                    <div className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 font-mono font-bold text-emerald-400 text-sm">
                      ₨ {currentPlan.price.toLocaleString()}
                    </div>
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Payment Channel
                    </label>
                    <select
                      value={paymentMethod}
                      onChange={(e) => setPaymentMethod(e.target.value as any)}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                    >
                      <option value="EASYPAISA">EasyPaisa (Online)</option>
                      <option value="JAZZCASH">JazzCash (Online)</option>
                      <option value="CASH">Cash at Front Desk</option>
                      <option value="BANK">Bank Transfer</option>
                    </select>
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Payment Status
                    </label>
                    <select
                      value={paymentStatus}
                      onChange={(e) => setPaymentStatus(e.target.value as any)}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                    >
                      <option value="PAID">Full Payment Paid</option>
                      <option value="PENDING">Pending (Due by 10th)</option>
                    </select>
                  </div>
                </div>

                <div className="pt-2">
                  <label className="flex items-center gap-3 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={sendWhatsApp}
                      onChange={(e) => setSendWhatsApp(e.target.checked)}
                      className="accent-brand-red w-4 h-4 cursor-pointer"
                    />
                    <span className="text-xs text-zinc-300">
                      Send welcome pack with digital QR pass directly to WhatsApp ({phone})
                    </span>
                  </label>
                </div>
              </div>
            </div>

            {/* Right Column: Live Turnstile Pass Preview */}
            <div className="space-y-6">
              <div className="sticky top-6 space-y-6">
                <div className="p-6 rounded-3xl bg-zinc-950 border border-brand-red/40 shadow-pass-glow text-center space-y-5">
                  <div className="flex items-center justify-between pb-3 border-b border-zinc-900">
                    <span className="text-[9px] font-semibold tracking-widest text-brand-red">
                      Live Gate Pass Preview
                    </span>
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[9px] font-bold">
                      <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                      Active
                    </span>
                  </div>

                  {/* Real Scannable 2D QR Matrix */}
                  <RealQrCode
                    value={memberCode}
                    size={180}
                    downloadable={true}
                    showFormatToggle={true}
                  />

                  <div>
                    <h4 className="font-heading text-2xl font-bold text-white truncate">
                      {fullName || 'Athlete Name'}
                    </h4>
                    <span className="font-mono text-lg font-bold text-brand-red block">
                      {memberCode}
                    </span>
                    <span className="text-[10px] text-zinc-500 block mt-1">
                      {currentPlan.name} &bull; Exp: {expiryDate}
                    </span>
                  </div>

                  <div className="p-3 rounded-xl bg-black border border-zinc-900 text-left space-y-1.5 text-[11px]">
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Turnstile Gate:</span>
                      <span className="text-emerald-400 font-bold">Authorized</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Initial Dues:</span>
                      <span className="text-white font-mono font-bold">
                        ₨ {currentPlan.price.toLocaleString()} ({paymentStatus})
                      </span>
                    </div>
                  </div>
                </div>

                {/* Submit Action */}
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="w-full py-4 rounded-2xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-brand-red/30 transition-all disabled:opacity-50"
                >
                  <i className="fa-solid fa-id-card text-sm" />
                  <span>{isSubmitting ? 'Generating Credential...' : 'Register & Generate Pass'}</span>
                </button>
              </div>
            </div>
          </form>
        )}
      </main>
    </div>
  );
}

