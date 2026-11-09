'use client';

import { useState } from 'react';
import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';

interface Invoice {
  id: string;
  code: string;
  name: string;
  phone: string;
  plan: string;
  amount: number;
  channel: 'EasyPaisa' | 'JazzCash' | 'Cash at Desk' | 'Bank Transfer';
  trxId: string;
  date: string;
  cutoff: string;
  status: 'Paid' | 'Pending' | 'Overdue';
}

const INITIAL_INVOICES: Invoice[] = [
  {
    id: 'INV-2026-101',
    code: '2026-4',
    name: 'Moeez Ahmad',
    phone: '+92 325 9491349',
    plan: '3-Month Pro Athlete Plan',
    amount: 22500,
    channel: 'EasyPaisa',
    trxId: 'EP-998822104',
    date: '10 Feb 2026',
    cutoff: '10th May 2026',
    status: 'Paid',
  },
  {
    id: 'INV-2026-102',
    code: '2026-482',
    name: 'Hamza Farooq',
    phone: '+92 301 5552341',
    plan: '3-Month Pro Athlete Plan',
    amount: 22500,
    channel: 'EasyPaisa',
    trxId: 'EP-9821449012',
    date: '09 Feb 2026',
    cutoff: '10th May 2026',
    status: 'Paid',
  },
  {
    id: 'INV-2026-103',
    code: '2026-484',
    name: 'Bilal Khan',
    phone: '+92 345 8899221',
    plan: '6-Month Elite Membership',
    amount: 42000,
    channel: 'JazzCash',
    trxId: 'JC-8839012399',
    date: '09 Feb 2026',
    cutoff: '10th Aug 2026',
    status: 'Paid',
  },
  {
    id: 'INV-2026-104',
    code: '2026-480',
    name: 'Zainab Qureshi',
    phone: '+92 321 9988776',
    plan: '3-Month Pro Athlete Plan',
    amount: 22500,
    channel: 'EasyPaisa',
    trxId: 'EP-4491028399',
    date: '08 Feb 2026',
    cutoff: '10th May 2026',
    status: 'Paid',
  },
  {
    id: 'INV-2026-105',
    code: '2026-1',
    name: 'Marcus Vance',
    phone: '+92 300 1234567',
    plan: '6-Month Elite Membership',
    amount: 42000,
    channel: 'Cash at Desk',
    trxId: 'CSH-09281',
    date: '01 Feb 2026',
    cutoff: '10th Aug 2026',
    status: 'Paid',
  },
  {
    id: 'INV-2026-106',
    code: '2026-2',
    name: 'Elena Rostova',
    phone: '+92 300 2345678',
    plan: '1-Month Starter Plan',
    amount: 8500,
    channel: 'Cash at Desk',
    trxId: 'Pending Verification',
    date: 'Due 10 Feb 2026',
    cutoff: '10th Feb 2026',
    status: 'Pending',
  },
];

export default function AdminBillingPage() {
  const [invoices, setInvoices] = useState<Invoice[]>(INITIAL_INVOICES);
  const [search, setSearch] = useState('');
  const [channelFilter, setChannelFilter] = useState('all');
  const [showModal, setShowModal] = useState(false);

  // New Payment Form State
  const [formName, setFormName] = useState('');
  const [formCode, setFormCode] = useState('2026-');
  const [formAmount, setFormAmount] = useState(4000);
  const [formPlan, setFormPlan] = useState('1 Month Standard');
  const [formChannel, setFormChannel] = useState<'EasyPaisa' | 'JazzCash' | 'Cash at Desk' | 'Bank Transfer'>('Cash at Desk');
  const [formTrx, setFormTrx] = useState('');

  const totalCollected = invoices
    .filter((inv) => inv.status === 'Paid')
    .reduce((sum, inv) => sum + inv.amount, 0);

  const totalPending = invoices
    .filter((inv) => inv.status === 'Pending')
    .reduce((sum, inv) => sum + inv.amount, 0);

  const handleRecordPayment = (e: React.FormEvent) => {
    e.preventDefault();
    const newInv: Invoice = {
      id: `INV-2026-${Math.floor(200 + Math.random() * 800)}`,
      code: formCode,
      name: formName,
      phone: '+92 300 0000000',
      plan: formPlan,
      amount: Number(formAmount),
      channel: formChannel,
      trxId: formTrx || `CSH-${Date.now().toString().slice(-5)}`,
      date: 'Today (Desk Entry)',
      cutoff: '10th of Next Month',
      status: 'Paid',
    };
    setInvoices([newInv, ...invoices]);
    setShowModal(false);
    setFormName('');
    setFormTrx('');
  };

  const markPaid = (id: string) => {
    setInvoices((prev) =>
      prev.map((inv) => (inv.id === id ? { ...inv, status: 'Paid' } : inv))
    );
  };

  const filteredInvoices = invoices.filter((inv) => {
    const matchesSearch =
      inv.name.toLowerCase().includes(search.toLowerCase()) ||
      inv.code.includes(search) ||
      inv.id.toLowerCase().includes(search.toLowerCase());
    const matchesChannel = channelFilter === 'all' || inv.channel === channelFilter;
    return matchesSearch && matchesChannel;
  });

  return (
    <div className="min-h-screen w-full bg-black text-white flex flex-col md:flex-row selection:bg-brand-red selection:text-white">
      <AdminSidebar />

      <main className="flex-1 p-6 sm:p-8 lg:p-10 w-full min-w-0 overflow-y-auto space-y-8">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-900">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="text-xs font-semibold text-brand-red tracking-wider">
                Financial Ledger
              </span>
              <span className="text-zinc-600">&bull;</span>
              <span className="text-xs text-zinc-400">Strict 10th-Cutoff Cycle</span>
            </div>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Membership Billing &amp; Ledger
            </h1>
            <p className="text-xs text-zinc-500 mt-1">
              Reconcile EasyPaisa, JazzCash, and Front-Desk Cash intakes, track 10th-cutoff receivables, and issue receipts.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => setShowModal(true)}
              className="px-5 py-3 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
            >
              <i className="fa-solid fa-plus text-xs" />
              <span>+ Record Payment</span>
            </button>
          </div>
        </div>

        {/* 4 Metric Banners */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold tracking-widest text-zinc-500 block">
              Total Month Collections
            </span>
            <span className="font-heading text-4xl font-bold text-emerald-400 mt-1 block font-mono">
              ₨ {totalCollected.toLocaleString()}
            </span>
            <span className="text-[10px] text-zinc-500 mt-1 block">
              Settled &amp; Turnstile Synced
            </span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold tracking-widest text-zinc-500 block">
              Pending 10th Cutoff
            </span>
            <span className="font-heading text-4xl font-bold text-amber-400 mt-1 block font-mono">
              ₨ {totalPending.toLocaleString()}
            </span>
            <span className="text-[10px] text-zinc-500 mt-1 block">
              Due by midnight of 10th
            </span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold tracking-widest text-zinc-500 block">
              Active Invoices
            </span>
            <span className="font-heading text-4xl font-bold text-white mt-1 block font-mono">
              {invoices.length} Slips
            </span>
            <span className="text-[10px] text-zinc-500 mt-1 block">
              Universal monthly ledger
            </span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold tracking-widest text-zinc-500 block">
              Turnstile Lockouts
            </span>
            <span className="font-heading text-4xl font-bold text-red-400 mt-1 block font-mono">
              0 Delinquent
            </span>
            <span className="text-[10px] text-zinc-500 mt-1 block">
              Grace clearance active
            </span>
          </div>
        </div>

        {/* Search & Filter Bar */}
        <div className="p-4 rounded-2xl bg-zinc-950 border border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="relative w-full sm:w-80">
            <i className="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-500 text-xs" />
            <input
              type="text"
              placeholder="Search by invoice ID, member, code..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full bg-black border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red font-mono"
            />
          </div>

          <div className="flex items-center gap-3 w-full sm:w-auto">
            <select
              value={channelFilter}
              onChange={(e) => setChannelFilter(e.target.value)}
              className="bg-black border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-zinc-300 focus:outline-none focus:border-brand-red"
            >
              <option value="all">All Payment Channels</option>
              <option value="EasyPaisa">EasyPaisa</option>
              <option value="JazzCash">JazzCash</option>
              <option value="Cash at Desk">Cash at Desk</option>
              <option value="Bank Transfer">Bank Transfer</option>
            </select>
          </div>
        </div>

        {/* Ledger Table */}
        <div className="rounded-3xl bg-zinc-950 border border-zinc-900 overflow-hidden shadow-xl">
          <div className="p-5 border-b border-zinc-900 flex justify-between items-center">
            <h2 className="font-heading text-2xl font-bold text-white">
              Invoice Ledger &amp; Audit Trail
            </h2>
            <button
              onClick={() => alert('Exporting encrypted ledger as CSV...')}
              className="px-3.5 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-xs font-bold text-zinc-300 border border-zinc-800 tracking-wider"
            >
              Export CSV
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-black text-[10px] font-semibold text-zinc-500 border-b border-zinc-900">
                <tr>
                  <th className="py-4 px-4">Invoice ID</th>
                  <th className="py-4 px-4">Athlete &amp; Pass ID</th>
                  <th className="py-4 px-4">Package Term</th>
                  <th className="py-4 px-4">Amount &amp; Channel</th>
                  <th className="py-4 px-4">Trx Reference</th>
                  <th className="py-4 px-4">Status</th>
                  <th className="py-4 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-900">
                {filteredInvoices.map((inv) => (
                  <tr key={inv.id} className="hover:bg-zinc-900/40 transition-colors">
                    {/* Invoice ID */}
                    <td className="py-4 px-4 font-mono font-bold text-white">{inv.id}</td>

                    {/* Athlete & Pass ID */}
                    <td className="py-4 px-4">
                      <span className="font-bold text-white block">{inv.name}</span>
                      <span className="text-[10px] font-mono text-brand-red font-bold">
                        {inv.code}
                      </span>
                    </td>

                    {/* Package */}
                    <td className="py-4 px-4">
                      <span className="text-zinc-300 block">{inv.plan}</span>
                      <span className="text-[10px] text-zinc-500 font-mono">Next Cutoff: {inv.cutoff}</span>
                    </td>

                    {/* Amount & Channel */}
                    <td className="py-4 px-4 font-mono">
                      <span className="font-bold text-emerald-400 block">
                        ₨ {inv.amount.toLocaleString()}
                      </span>
                      <span className="text-[10px] text-zinc-400">{inv.channel}</span>
                    </td>

                    {/* Trx Reference */}
                    <td className="py-4 px-4 font-mono text-zinc-400 text-[11px]">
                      {inv.trxId}
                    </td>

                    {/* Status */}
                    <td className="py-4 px-4">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold font-mono ${
                          inv.status === 'Paid'
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                            : 'bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse'
                        }`}
                      >
                        {inv.status}
                      </span>
                    </td>

                    {/* Actions */}
                    <td className="py-4 px-4 text-right space-x-2">
                      {inv.status === 'Pending' && (
                        <button
                          onClick={() => markPaid(inv.id)}
                          className="py-1 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-semibold tracking-wider shadow-sm"
                        >
                          Mark Paid
                        </button>
                      )}

                      <a
                        href={`https://wa.me/${inv.phone.replace(/[^0-9]/g, '')}?text=Dear%20${encodeURIComponent(inv.name)},%20your%20GymFlow%20payment%20of%20PKR%20${inv.amount.toLocaleString()}%20for%20${encodeURIComponent(inv.plan)}%20has%20been%20recorded%20(Receipt%20${inv.id}).`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="py-1 px-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-emerald-400 text-[11px] font-bold border border-zinc-800 inline-block transition-colors"
                        title="Send WhatsApp Receipt"
                      >
                        <i className="fa-brands fa-whatsapp" />
                      </a>

                      <button
                        onClick={() => alert(`Printing official receipt for invoice ${inv.id}...`)}
                        className="py-1 px-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-[10px] font-semibold tracking-wider border border-zinc-800 transition-colors"
                      >
                        PDF
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </main>

      {/* RECORD FRONT-DESK PAYMENT MODAL */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 animate-fadeIn">
          <div className="bg-zinc-950 border border-zinc-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-zinc-900">
              <h3 className="font-heading text-2xl font-bold text-white">
                Record Front-Desk Payment
              </h3>
              <button
                onClick={() => setShowModal(false)}
                className="w-8 h-8 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center text-xs"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleRecordPayment} className="space-y-4 text-xs">
              <div>
                <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                  Athlete Full Name *
                </label>
                <input
                  type="text"
                  required
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder="e.g. Tariq Mehmood"
                  className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                    Member Pass ID *
                  </label>
                  <input
                    type="text"
                    required
                    value={formCode}
                    onChange={(e) => setFormCode(e.target.value)}
                    placeholder="2026-4"
                    className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-brand-red font-mono font-bold focus:outline-none focus:border-brand-red"
                  />
                </div>

                <div>
                  <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                    Amount Paid (PKR) *
                  </label>
                  <input
                    type="number"
                    required
                    value={formAmount}
                    onChange={(e) => setFormAmount(Number(e.target.value))}
                    className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-emerald-400 font-mono font-bold focus:outline-none focus:border-brand-red"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                    Subscription Tier
                  </label>
                  <select
                    value={formPlan}
                    onChange={(e) => setFormPlan(e.target.value)}
                    className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                  >
                    <option value="1-Month Starter Plan">1-Month Starter Plan (₨ 8,500)</option>
                    <option value="3-Month Pro Athlete Plan">3-Month Pro Athlete Plan (₨ 22,500)</option>
                    <option value="6-Month Elite Membership">6-Month Elite Membership (₨ 42,000)</option>
                  </select>
                </div>

                <div>
                  <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                    Payment Channel
                  </label>
                  <select
                    value={formChannel}
                    onChange={(e) => setFormChannel(e.target.value as any)}
                    className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                  >
                    <option value="Cash at Desk">Cash at Front Desk</option>
                    <option value="EasyPaisa">EasyPaisa</option>
                    <option value="JazzCash">JazzCash</option>
                    <option value="Bank Transfer">Bank Transfer (IBFT)</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                  Transaction Reference / Trx ID
                </label>
                <input
                  type="text"
                  value={formTrx}
                  onChange={(e) => setFormTrx(e.target.value)}
                  placeholder="e.g. CSH-Desk-01 or EasyPaisa Trx ID"
                  className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red font-mono"
                />
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
                  Record &amp; Issue Receipt
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

