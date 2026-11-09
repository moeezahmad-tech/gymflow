'use client';

import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';

export default function AdminMessagesPage() {
  const inquiries = [
    {
      id: 1,
      name: 'Hamza Khan',
      email: 'hamza.k@gmail.com',
      phone: '+92 312 4567890',
      program: 'Powerlifting & Hypertrophy',
      message: 'Looking to join the morning 6 AM powerlifting wave. Are Eleiko racks available?',
      date: 'Oct 11, 2026',
      status: 'unread',
    },
    {
      id: 2,
      name: 'Zainab Bibi',
      email: 'zainab.fit@yahoo.com',
      phone: '+92 333 5678901',
      program: 'Boxing & Strike Combatives',
      message: 'Is there a private women training session for boxing and HIIT on weekends?',
      date: 'Oct 10, 2026',
      status: 'unread',
    },
    {
      id: 3,
      name: 'Bilal Tariq',
      email: 'bilal.t@hotmail.com',
      phone: '+92 345 6789012',
      program: 'General Membership Inquiry',
      message: 'Would like to book a 1-day free trial pass for tomorrow evening around 6 PM.',
      date: 'Oct 10, 2026',
      status: 'unread',
    },
  ];

  return (
    <div className="min-h-screen w-full bg-black text-white flex flex-col md:flex-row selection:bg-brand-red selection:text-white">
      <AdminSidebar />

      <main className="flex-1 p-6 sm:p-8 lg:p-10 w-full min-w-0 overflow-y-auto space-y-8">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-zinc-900">
          <div>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Client Inquiries &amp; Messages
            </h1>
            <p className="text-xs text-zinc-500 mt-1">
              Inbound lead captures from public website and trial bookings.
            </p>
          </div>

          <Link
            href="/admin#email-broadcasting"
            className="px-4 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2 self-start sm:self-auto"
          >
            <i className="fa-solid fa-paper-plane text-xs" />
            <span>Open Email Broadcast Console &rarr;</span>
          </Link>
        </div>

        {/* 3 Metric Pills from Group 2.png */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
          <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-widest block mb-1">
              Total Inbound Leads
            </span>
            <span className="font-heading text-5xl font-bold text-white block">3</span>
          </div>

          <div className="p-6 rounded-3xl bg-zinc-950 border border-brand-red/40 shadow-lg shadow-brand-red/10">
            <span className="text-[10px] font-semibold text-brand-red tracking-widest block mb-1">
              Unread Action Items
            </span>
            <span className="font-heading text-5xl font-bold text-brand-red block">3</span>
          </div>

          <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-widest block mb-1">
              Resolved Leads
            </span>
            <span className="font-heading text-5xl font-bold text-emerald-400 block">0</span>
          </div>
        </div>

        {/* Messages List Table */}
        <div className="rounded-3xl bg-zinc-950 border border-zinc-900 overflow-hidden">
          <div className="p-5 border-b border-zinc-900 flex justify-between items-center">
            <h2 className="font-heading text-2xl font-bold text-white">Inquiries Inbox</h2>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="text-[10px] font-semibold text-zinc-500 bg-zinc-900/60 border-b border-zinc-900">
                <tr>
                  <th className="py-3 px-4">Date</th>
                  <th className="py-3 px-4">Prospect</th>
                  <th className="py-3 px-4">Program Interest</th>
                  <th className="py-3 px-4">Message Snippet</th>
                  <th className="py-3 px-4">Status</th>
                  <th className="py-3 px-4 text-right">Reply</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-900/60 text-zinc-300">
                {inquiries.map((inq) => (
                  <tr key={inq.id} className="hover:bg-zinc-900/30 transition-colors">
                    <td className="py-4 px-4 text-zinc-500 whitespace-nowrap">{inq.date}</td>
                    <td className="py-4 px-4">
                      <span className="font-bold text-white block">{inq.name}</span>
                      <span className="text-[10px] text-zinc-400">{inq.phone}</span>
                    </td>
                    <td className="py-4 px-4 text-white font-medium">{inq.program}</td>
                    <td className="py-4 px-4 text-zinc-400 max-w-xs truncate">{inq.message}</td>
                    <td className="py-4 px-4">
                      <span className="px-2.5 py-1 rounded-full bg-brand-red/10 text-brand-red text-[10px] font-semibold border border-brand-red/20">
                        Unread
                      </span>
                    </td>
                    <td className="py-4 px-4 text-right space-x-2">
                      <a
                        href={`https://wa.me/${inq.phone.replace(/[^0-9]/g, '')}`}
                        target="_blank"
                        rel="noreferrer"
                        className="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold inline-flex items-center gap-1.5"
                      >
                        <i className="fa-brands fa-whatsapp" />
                        <span>WhatsApp</span>
                      </a>
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

