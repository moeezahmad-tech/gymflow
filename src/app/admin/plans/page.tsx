'use client';

import { useState } from 'react';
import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';

interface Plan {
  id: string;
  name: string;
  durationMonths: number;
  durationDays: number;
  price: number;
  badge?: string;
  isPopular?: boolean;
  status: 'ACTIVE' | 'ARCHIVED';
  features: string[];
  activeMembers: number;
}

const INITIAL_PLANS: Plan[] = [
  {
    id: 'plan_1m',
    name: '1-MONTH STARTER PLAN',
    durationMonths: 1,
    durationDays: 30,
    price: 8500,
    badge: 'STANDARD',
    isPopular: false,
    status: 'ACTIVE',
    features: [
      '30 Days Turnstile Gate Access',
      'Free Locker & Shower Access',
      'Standard Weight Floor & Cardio Deck',
      'WhatsApp Payment Cutoff Alerts (10th)',
      'Digital QR Pass Generation',
    ],
    activeMembers: 34,
  },
  {
    id: 'plan_3m',
    name: '3-MONTH PRO ATHLETE PLAN',
    durationMonths: 3,
    durationDays: 90,
    price: 22500,
    badge: 'MOST POPULAR • SAVE PKR 3,000',
    isPopular: true,
    status: 'ACTIVE',
    features: [
      'Save PKR 3,000 compared to monthly',
      '90 Days Priority Turnstile Access',
      'Personalized Nutrition Macro Sheet',
      'Monthly Coach Body Composition Scan',
      'Finnish Sauna & Cold Plunge Included',
      'WhatsApp Payment Cutoff Alerts (10th)',
    ],
    activeMembers: 76,
  },
  {
    id: 'plan_6m',
    name: '6-MONTH ELITE MEMBERSHIP',
    durationMonths: 6,
    durationDays: 180,
    price: 42000,
    badge: 'VIP ELITE • SAVE PKR 9,000',
    isPopular: false,
    status: 'ACTIVE',
    features: [
      'Save PKR 9,000 compared to monthly',
      '180 Days VIP Unrestricted Access',
      'Dedicated Trainer Consultation',
      '2 Free Guest Passes / Month',
      'Unlimited Cold Plunge & Finnish Sauna',
      'Quarterly DEXA Body Fat Scan',
    ],
    activeMembers: 28,
  },
];

export default function AdminPlansPage() {
  const [plans, setPlans] = useState<Plan[]>(INITIAL_PLANS);
  const [showModal, setShowModal] = useState(false);
  const [editingPlan, setEditingPlan] = useState<Plan | null>(null);

  // Form state
  const [formName, setFormName] = useState('');
  const [formPrice, setFormPrice] = useState(4000);
  const [formDurationDays, setFormDurationDays] = useState(30);
  const [formBadge, setFormBadge] = useState('');
  const [formFeatures, setFormFeatures] = useState('');
  const [isPopular, setIsPopular] = useState(false);

  const openCreateModal = () => {
    setEditingPlan(null);
    setFormName('');
    setFormPrice(4000);
    setFormDurationDays(30);
    setFormBadge('');
    setFormFeatures('Turnstile Gate Access\nLocker & Shower\nCardio Deck');
    setIsPopular(false);
    setShowModal(true);
  };

  const openEditModal = (plan: Plan) => {
    setEditingPlan(plan);
    setFormName(plan.name);
    setFormPrice(plan.price);
    setFormDurationDays(plan.durationDays);
    setFormBadge(plan.badge || '');
    setFormFeatures(plan.features.join('\n'));
    setIsPopular(!!plan.isPopular);
    setShowModal(true);
  };

  const handleSavePlan = (e: React.FormEvent) => {
    e.preventDefault();
    const splitFeatures = formFeatures
      .split('\n')
      .map((f) => f.trim())
      .filter(Boolean);

    if (editingPlan) {
      setPlans((prev) =>
        prev.map((p) =>
          p.id === editingPlan.id
            ? {
                ...p,
                name: formName,
                price: Number(formPrice),
                durationDays: Number(formDurationDays),
                durationMonths: Math.round(Number(formDurationDays) / 30),
                badge: formBadge || undefined,
                isPopular,
                features: splitFeatures,
              }
            : p
        )
      );
    } else {
      const newPlan: Plan = {
        id: `plan_${Date.now()}`,
        name: formName,
        price: Number(formPrice),
        durationDays: Number(formDurationDays),
        durationMonths: Math.round(Number(formDurationDays) / 30),
        badge: formBadge || undefined,
        isPopular,
        features: splitFeatures,
        status: 'ACTIVE',
        activeMembers: 0,
      };
      setPlans((prev) => [...prev, newPlan]);
    }
    setShowModal(false);
  };

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to remove this package tier?')) {
      setPlans((prev) => prev.filter((p) => p.id !== id));
    }
  };

  const toggleStatus = (id: string) => {
    setPlans((prev) =>
      prev.map((p) =>
        p.id === id ? { ...p, status: p.status === 'ACTIVE' ? 'ARCHIVED' : 'ACTIVE' } : p
      )
    );
  };

  return (
    <div className="flex flex-col md:flex-row min-h-screen w-full bg-black text-white selection:bg-brand-red selection:text-white">
      <AdminSidebar />

      <main className="flex-1 p-6 sm:p-8 lg:p-10 w-full min-w-0 space-y-8 overflow-y-auto">
        {/* Top Header Bar */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-900 pb-6">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="text-xs font-semibold text-brand-red tracking-wider">
                Subscription Architect
              </span>
              <span className="text-zinc-600">&bull;</span>
              <span className="text-xs text-zinc-400">Live Turnstile Sync</span>
            </div>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              Membership Packages &amp; Pricing Tiers
            </h1>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1 max-w-2xl">
              Configure subscription terms, automated pass expiry, and turnstile privileges.
              Changes reflect immediately on public checkout and member PWA renewals.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={openCreateModal}
              className="px-5 py-3 rounded-2xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider flex items-center gap-2 shadow-lg shadow-brand-red/30 transition-all"
            >
              <i className="fa-solid fa-plus text-xs" />
              <span>+ Add New Package</span>
            </button>
          </div>
        </div>

        {/* Stats Row */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Active Tiers
            </span>
            <span className="font-heading text-3xl font-bold text-white mt-1 block">
              {plans.filter((p) => p.status === 'ACTIVE').length} Packages
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Syncing with Turnstile</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Most Popular
            </span>
            <span className="font-heading text-3xl font-bold text-brand-red mt-1 block">
              3 MONTHS
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">76 active athletes</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Avg Member Value
            </span>
            <span className="font-heading text-3xl font-bold text-emerald-400 font-mono mt-1 block">
              ₨ 8,500
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Quarterly blended ARPU</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Renewal Cutoff
            </span>
            <span className="font-heading text-3xl font-bold text-amber-400 mt-1 block">
              10th Cutoff
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Grace period: 3 days</span>
          </div>
        </div>

        {/* Packages Cards Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {plans.map((plan) => (
            <div
              key={plan.id}
              className={`rounded-3xl p-7 flex flex-col justify-between transition-all relative ${
                plan.isPopular
                  ? 'bg-zinc-950 border-2 border-brand-red shadow-pass-glow'
                  : 'bg-zinc-950 border border-zinc-900 hover:border-zinc-800'
              } ${plan.status === 'ARCHIVED' ? 'opacity-60' : ''}`}
            >
              {plan.badge && (
                <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                  <span
                    className={`px-3 py-1 rounded-full text-[10px] font-semibold tracking-wider ${
                      plan.isPopular
                        ? 'bg-brand-red text-white shadow-md shadow-brand-red/40'
                        : 'bg-zinc-800 text-zinc-300 border border-zinc-700'
                    }`}
                  >
                    {plan.badge}
                  </span>
                </div>
              )}

              <div>
                {/* Header info */}
                <div className="flex items-center justify-between mb-4 mt-2">
                  <span className="text-[11px] font-mono font-bold text-zinc-500 tracking-widest">
                    {plan.durationDays} DAYS DURATION
                  </span>
                  <span
                    onClick={() => toggleStatus(plan.id)}
                    className={`cursor-pointer px-2 py-0.5 rounded-full text-[10px] font-semibold transition-colors ${
                      plan.status === 'ACTIVE'
                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                        : 'bg-zinc-800 text-zinc-400 border border-zinc-700'
                    }`}
                  >
                    {plan.status}
                  </span>
                </div>

                <h3 className="font-heading text-3xl font-bold text-white tracking-wide mb-2">
                  {plan.name}
                </h3>

                <div className="mb-6 pb-6 border-b border-zinc-900">
                  <span className="font-heading text-4xl sm:text-5xl font-bold text-white font-mono">
                    PKR {plan.price.toLocaleString()}
                  </span>
                  <span className="text-xs text-zinc-500 block mt-1">
                    Turnstile gate credential included
                  </span>
                </div>

                {/* Features List */}
                <div className="space-y-3 mb-8">
                  <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
                    Included Privileges:
                  </span>
                  {plan.features.map((feature, idx) => (
                    <div key={idx} className="flex items-start gap-2.5 text-xs text-zinc-300">
                      <i className="fa-solid fa-check text-brand-red text-xs mt-0.5 shrink-0" />
                      <span>{feature}</span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Card Footer Actions */}
              <div className="pt-6 border-t border-zinc-900/80 space-y-3">
                <div className="flex items-center justify-between text-xs text-zinc-400 pb-2">
                  <span>Enrolled Athletes:</span>
                  <span className="font-mono font-bold text-white">{plan.activeMembers} members</span>
                </div>

                <div className="grid grid-cols-2 gap-2">
                  <button
                    onClick={() => openEditModal(plan)}
                    className="py-2.5 px-3 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold tracking-wider transition-colors flex items-center justify-center gap-1.5 border border-zinc-800"
                  >
                    <i className="fa-solid fa-pen-to-square text-[10px]" />
                    <span>Edit Plan</span>
                  </button>

                  <button
                    onClick={() => handleDelete(plan.id)}
                    className="py-2.5 px-3 rounded-xl bg-red-950/40 hover:bg-red-900/60 text-red-400 text-xs font-semibold tracking-wider transition-colors flex items-center justify-center gap-1.5 border border-red-900/50"
                  >
                    <i className="fa-solid fa-trash text-[10px]" />
                    <span>Delete</span>
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Plan Guidelines / Policy Banner */}
        <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900 flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 rounded-2xl bg-brand-red/10 border border-brand-red/30 flex items-center justify-center text-brand-red text-xl shrink-0">
              <i className="fa-solid fa-shield-halved" />
            </div>
            <div>
              <h4 className="text-sm font-bold text-white tracking-wider">
                Automated 10th-Cutoff Grace Policy
              </h4>
              <p className="text-xs text-zinc-400 mt-0.5">
                All package terms honor GymFlow's monthly 10th-day cutoff. Unpaid subscriptions
                automatically soft-lock turnstile gates on the 11th at 00:01 AM.
              </p>
            </div>
          </div>

          <Link
            href="/admin/billing"
            className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-200 text-xs font-semibold tracking-wider border border-zinc-800 shrink-0"
          >
            Review Ledger & Cutoffs &rarr;
          </Link>
        </div>
      </main>

      {/* Edit / Create Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 animate-fadeIn">
          <div className="bg-zinc-950 border border-zinc-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl">
            <div className="flex items-center justify-between border-b border-zinc-900 pb-4">
              <h3 className="font-heading text-2xl font-bold text-white">
                {editingPlan ? 'Edit Membership Plan' : 'Add New Package'}
              </h3>
              <button
                onClick={() => setShowModal(false)}
                className="w-8 h-8 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center text-xs"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSavePlan} className="space-y-4 text-xs">
              <div>
                <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                  Package Name
                </label>
                <input
                  type="text"
                  required
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder="e.g. 12 MONTH ANNUAL PASS"
                  className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                    Price (PKR)
                  </label>
                  <input
                    type="number"
                    required
                    value={formPrice}
                    onChange={(e) => setFormPrice(Number(e.target.value))}
                    className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white font-mono focus:outline-none focus:border-brand-red"
                  />
                </div>

                <div>
                  <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                    Duration (Days)
                  </label>
                  <input
                    type="number"
                    required
                    value={formDurationDays}
                    onChange={(e) => setFormDurationDays(Number(e.target.value))}
                    className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white font-mono focus:outline-none focus:border-brand-red"
                  />
                </div>
              </div>

              <div>
                <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                  Promotional Badge Label (Optional)
                </label>
                <input
                  type="text"
                  value={formBadge}
                  onChange={(e) => setFormBadge(e.target.value)}
                  placeholder="e.g. BEST VALUE • SAVE PKR 2,000"
                  className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                />
              </div>

              <div>
                <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                  Package Features (One per line)
                </label>
                <textarea
                  rows={4}
                  value={formFeatures}
                  onChange={(e) => setFormFeatures(e.target.value)}
                  placeholder="Enter features line by line"
                  className="w-full bg-black border border-zinc-800 rounded-xl p-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red resize-none"
                />
              </div>

              <div className="flex items-center gap-2 pt-2">
                <input
                  type="checkbox"
                  id="popularCheck"
                  checked={isPopular}
                  onChange={(e) => setIsPopular(e.target.checked)}
                  className="accent-brand-red w-4 h-4 cursor-pointer"
                />
                <label htmlFor="popularCheck" className="text-zinc-300 cursor-pointer">
                  Highlight as &quot;Most Popular&quot; with red border & glow
                </label>
              </div>

              <div className="pt-4 border-t border-zinc-900 flex justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 font-semibold tracking-wider"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-6 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white font-semibold tracking-wider shadow-lg shadow-brand-red/30"
                >
                  Save Package
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

