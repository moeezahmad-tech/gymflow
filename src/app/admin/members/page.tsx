'use client';

import { useState } from 'react';
import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';

interface MemberRecord {
  code: string;
  name: string;
  fatherName: string;
  cnic: string;
  email: string;
  phone: string;
  plan: string;
  shift: string;
  bloodGroup: string;
  status: 'active' | 'due' | 'locked';
  gateStatus: 'ENABLED' | 'LOCKED';
  joined: string;
  cutoffDue: string;
  streak: number;
  totalCheckins: number;
  medicalConditions: string[];
  emergencyContact: string;
}

const INITIAL_MEMBERS: MemberRecord[] = [
  {
    code: '2026-4',
    name: 'Moeez Ahmad',
    fatherName: 'Ahmad Raza',
    cnic: '61101-3829104-1',
    email: 'moeezahmad.tech@gmail.com',
    phone: '+92 325 9491349',
    plan: '3 Month Pro Quarter',
    shift: 'Prime Evening (05-11 PM)',
    bloodGroup: 'B+',
    status: 'active',
    gateStatus: 'ENABLED',
    joined: 'Jan 10, 2026',
    cutoffDue: '10th March 2026',
    streak: 14,
    totalCheckins: 38,
    medicalConditions: [],
    emergencyContact: 'Ahmad Raza (Father) +92 300 1234567',
  },
  {
    code: '2026-480',
    name: 'Zainab Qureshi',
    fatherName: 'Qureshi Bashir',
    cnic: '61101-9988221-4',
    email: 'zainab.q@gmail.com',
    phone: '+92 321 9988776',
    plan: '3 Month Pro Quarter',
    shift: 'Prime Evening (05-11 PM)',
    bloodGroup: 'AB+',
    status: 'active',
    gateStatus: 'ENABLED',
    joined: 'Feb 08, 2026',
    cutoffDue: '10th May 2026',
    streak: 3,
    totalCheckins: 5,
    medicalConditions: [],
    emergencyContact: 'Mrs. Bashir (Mother) +92 321 5556677',
  },
  {
    code: '2026-1',
    name: 'Marcus Vance',
    fatherName: 'Vance Senior',
    cnic: '61101-1928374-9',
    email: 'admin@gymflow.com',
    phone: '+92 300 1234567',
    plan: '6 Month VIP Semi-Annual',
    shift: '24/7 Unrestricted',
    bloodGroup: 'O+',
    status: 'active',
    gateStatus: 'ENABLED',
    joined: 'Sep 15, 2025',
    cutoffDue: '10th March 2026',
    streak: 42,
    totalCheckins: 112,
    medicalConditions: [],
    emergencyContact: 'Sarah Vance (Spouse) +92 300 7654321',
  },
  {
    code: '2026-2',
    name: 'Elena Rostova',
    fatherName: 'Rostov Dimitri',
    cnic: '61101-5544332-1',
    email: 'staff@gymflow.com',
    phone: '+92 300 2345678',
    plan: '1 Month Standard',
    shift: 'Morning Shift (05-11 AM)',
    bloodGroup: 'A+',
    status: 'due',
    gateStatus: 'ENABLED',
    joined: 'Oct 01, 2025',
    cutoffDue: '10th Feb 2026 (Due Tomorrow)',
    streak: 8,
    totalCheckins: 24,
    medicalConditions: ['Previous Shoulder Dislocation'],
    emergencyContact: 'Dimitri Rostov +92 300 8889990',
  },
  {
    code: '2026-3',
    name: 'Active Athlete',
    fatherName: 'Nadeem Akhtar',
    cnic: '61101-7788990-2',
    email: 'member@gymflow.com',
    phone: '+92 300 3456789',
    plan: '3 Month Pro Quarter',
    shift: 'Afternoon Shift (12-05 PM)',
    bloodGroup: 'O-',
    status: 'active',
    gateStatus: 'ENABLED',
    joined: 'Dec 05, 2025',
    cutoffDue: '10th March 2026',
    streak: 11,
    totalCheckins: 29,
    medicalConditions: [],
    emergencyContact: 'Nadeem Akhtar +92 300 1122334',
  },
];

export default function AdminMembersDirectoryPage() {
  const [members, setMembers] = useState<MemberRecord[]>(INITIAL_MEMBERS);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [selectedMember, setSelectedMember] = useState<MemberRecord | null>(null);

  const toggleGateAccess = (code: string) => {
    setMembers((prev) =>
      prev.map((m) =>
        m.code === code
          ? {
              ...m,
              gateStatus: m.gateStatus === 'ENABLED' ? 'LOCKED' : 'ENABLED',
              status: m.gateStatus === 'ENABLED' ? 'locked' : 'active',
            }
          : m
      )
    );
    if (selectedMember && selectedMember.code === code) {
      setSelectedMember({
        ...selectedMember,
        gateStatus: selectedMember.gateStatus === 'ENABLED' ? 'LOCKED' : 'ENABLED',
        status: selectedMember.gateStatus === 'ENABLED' ? 'locked' : 'active',
      });
    }
  };

  const filteredMembers = members.filter((m) => {
    const matchesSearch =
      m.name.toLowerCase().includes(search.toLowerCase()) ||
      m.code.includes(search) ||
      m.cnic.includes(search) ||
      m.email.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = statusFilter === 'all' || m.status === statusFilter;
    return matchesSearch && matchesStatus;
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
                Athlete Roster
              </span>
              <span className="text-zinc-600">&bull;</span>
              <span className="text-xs text-zinc-400">Total Enrolled: {members.length}</span>
            </div>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Members Directory
            </h1>
            <p className="text-xs text-zinc-500 mt-1">
              Search athlete profiles, view health declarations, manage turnstile clearance, and inspect 10th cutoff dues.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <Link
              href="/admin/members-add"
              className="px-5 py-3 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
            >
              <i className="fa-solid fa-user-plus text-xs" />
              <span>+ Enroll Walk-In Member</span>
            </Link>
          </div>
        </div>

        {/* Filter & Search Bar */}
        <div className="p-4 rounded-2xl bg-zinc-950 border border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="relative w-full sm:w-80">
            <i className="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-500 text-xs" />
            <input
              type="text"
              placeholder="Search by Name, Pass ID, CNIC..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full bg-black border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red font-mono"
            />
          </div>

          <div className="flex items-center gap-3 w-full sm:w-auto">
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="bg-black border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-zinc-300 focus:outline-none focus:border-brand-red"
            >
              <option value="all">All Member Statuses</option>
              <option value="active">Active Pass Holders</option>
              <option value="due">Payment Due on 10th</option>
              <option value="locked">Gate Locked / Suspended</option>
            </select>
          </div>
        </div>

        {/* Members Table */}
        <div className="rounded-3xl bg-zinc-950 border border-zinc-900 overflow-hidden shadow-xl">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-black text-[10px] font-semibold text-zinc-500 border-b border-zinc-900">
                <tr>
                  <th className="py-4 px-4">Pass ID &amp; Athlete</th>
                  <th className="py-4 px-4">CNIC &amp; Blood Group</th>
                  <th className="py-4 px-4">Contact Phone</th>
                  <th className="py-4 px-4">Tier &amp; Shift</th>
                  <th className="py-4 px-4">10th Cutoff Due</th>
                  <th className="py-4 px-4">Gate Clearance</th>
                  <th className="py-4 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-900">
                {filteredMembers.map((m) => (
                  <tr key={m.code} className="hover:bg-zinc-900/40 transition-colors">
                    {/* Pass ID & Athlete */}
                    <td className="py-4 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-red to-brand-red-dark text-white font-bold text-xs flex items-center justify-center shrink-0">
                          {m.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                          <span className="font-bold text-white block">{m.name}</span>
                          <span className="text-[10px] font-mono text-brand-red font-bold">
                            {m.code}
                          </span>
                        </div>
                      </div>
                    </td>

                    {/* CNIC & Blood Group */}
                    <td className="py-4 px-4 font-mono text-[11px]">
                      <span className="text-white block">{m.cnic}</span>
                      <span className="text-[10px] text-brand-red font-bold">Blood: {m.bloodGroup}</span>
                    </td>

                    {/* Phone */}
                    <td className="py-4 px-4 font-mono text-[11px] text-zinc-300">
                      {m.phone}
                    </td>

                    {/* Tier & Shift */}
                    <td className="py-4 px-4">
                      <span className="font-bold text-white block">{m.plan}</span>
                      <span className="text-[10px] text-zinc-500 block truncate max-w-[140px]">
                        {m.shift}
                      </span>
                    </td>

                    {/* Cutoff Due */}
                    <td className="py-4 px-4 font-mono text-[11px]">
                      <span
                        className={
                          m.status === 'due'
                            ? 'text-amber-400 font-bold block'
                            : 'text-zinc-400 block'
                        }
                      >
                        {m.cutoffDue}
                      </span>
                      <span className="text-[9px] text-zinc-500">
                        🔥 {m.streak}d streak &bull; {m.totalCheckins} check-ins
                      </span>
                    </td>

                    {/* Gate Clearance */}
                    <td className="py-4 px-4">
                      <button
                        onClick={() => toggleGateAccess(m.code)}
                        className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono transition-colors ${
                          m.gateStatus === 'ENABLED'
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20'
                            : 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20'
                        }`}
                      >
                        <span
                          className={`w-1.5 h-1.5 rounded-full ${
                            m.gateStatus === 'ENABLED' ? 'bg-emerald-400 animate-pulse' : 'bg-red-400'
                          }`}
                        />
                        <span>{m.gateStatus}</span>
                      </button>
                    </td>

                    {/* Actions */}
                    <td className="py-4 px-4 text-right space-x-2">
                      <button
                        onClick={() => setSelectedMember(m)}
                        className="py-1.5 px-3 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white text-[11px] font-semibold tracking-wider border border-zinc-800 transition-colors"
                      >
                        View Profile
                      </button>

                      <a
                        href={`https://wa.me/${m.phone.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(m.name)},%20this%20is%20GymFlow%20Metropolis%20Reception.`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="py-1.5 px-2.5 rounded-lg bg-emerald-950/40 hover:bg-emerald-900/60 text-emerald-400 text-[11px] font-bold border border-emerald-900/50 inline-block transition-colors"
                        title="Chat on WhatsApp"
                      >
                        <i className="fa-brands fa-whatsapp" />
                      </a>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </main>

      {/* FULL ATHLETE PROFILE MODAL */}
      {selectedMember && (
        <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 animate-fadeIn">
          <div className="bg-zinc-950 border border-zinc-800 rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto shadow-2xl">
            {/* Modal Header */}
            <div className="flex items-center justify-between pb-4 border-b border-zinc-900">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-red to-brand-red-dark flex items-center justify-center text-white font-bold text-lg">
                  {selectedMember.name.substring(0, 2).toUpperCase()}
                </div>
                <div>
                  <h3 className="font-heading text-2xl font-bold text-white">
                    {selectedMember.name}
                  </h3>
                  <span className="text-xs text-zinc-400 font-mono">
                    Pass ID: <span className="text-brand-red font-bold">{selectedMember.code}</span> &bull; Plan: {selectedMember.plan}
                  </span>
                </div>
              </div>

              <button
                onClick={() => setSelectedMember(null)}
                className="w-8 h-8 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center text-xs"
              >
                ✕
              </button>
            </div>

            {/* Profile Content */}
            <div className="space-y-4 text-xs">
              <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-2.5 font-mono">
                <span className="text-[10px] font-semibold text-brand-red tracking-wider block font-sans">
                  Identity &amp; Bio Information
                </span>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Father Name:</span>
                    <span className="text-white">{selectedMember.fatherName}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">CNIC:</span>
                    <span className="text-white">{selectedMember.cnic}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Blood Group:</span>
                    <span className="text-brand-red font-bold">{selectedMember.bloodGroup}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">WhatsApp:</span>
                    <span className="text-emerald-400">{selectedMember.phone}</span>
                  </div>
                  <div className="col-span-2">
                    <span className="text-zinc-500 text-[10px] block">Emergency Contact:</span>
                    <span className="text-white font-sans">{selectedMember.emergencyContact}</span>
                  </div>
                </div>
              </div>

              {/* Attendance & Streak Stats */}
              <div className="grid grid-cols-3 gap-3 text-center">
                <div className="p-3.5 rounded-2xl bg-black border border-zinc-900">
                  <span className="text-[9px] font-semibold text-zinc-500 block">Streak</span>
                  <span className="font-heading text-2xl font-bold text-amber-400 mt-0.5 block">
                    🔥 {selectedMember.streak}d
                  </span>
                </div>
                <div className="p-3.5 rounded-2xl bg-black border border-zinc-900">
                  <span className="text-[9px] font-semibold text-zinc-500 block">Check-ins</span>
                  <span className="font-heading text-2xl font-bold text-white mt-0.5 block">
                    {selectedMember.totalCheckins}
                  </span>
                </div>
                <div className="p-3.5 rounded-2xl bg-black border border-zinc-900">
                  <span className="text-[9px] font-semibold text-zinc-500 block">Turnstile</span>
                  <span
                    className={`font-heading text-lg font-bold mt-0.5 block ${
                      selectedMember.gateStatus === 'ENABLED' ? 'text-emerald-400' : 'text-red-400'
                    }`}
                  >
                    {selectedMember.gateStatus}
                  </span>
                </div>
              </div>

              {/* 10th Cutoff Billing Card */}
              <div className="p-4 rounded-2xl bg-black border border-zinc-900 flex items-center justify-between">
                <div>
                  <span className="text-[10px] font-semibold text-zinc-500 block">Next 10th Cutoff Due</span>
                  <span className="text-sm font-bold text-white font-mono mt-0.5 block">{selectedMember.cutoffDue}</span>
                </div>

                <a
                  href={`https://wa.me/${selectedMember.phone.replace(/[^0-9]/g, '')}?text=Dear%20${encodeURIComponent(selectedMember.name)},%20this%20is%20a%20courtesy%20reminder%20from%20GymFlow%20Metropolis%20that%20your%20monthly%20subscription%20renewal%20cutoff%20is%20on%20the%2010th.`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-3.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-amber-400 text-xs font-semibold tracking-wider border border-zinc-800 flex items-center gap-1.5 transition-colors"
                >
                  <i className="fa-brands fa-whatsapp text-xs" />
                  <span>Send Cutoff Notice</span>
                </a>
              </div>
            </div>

            {/* Actions */}
            <div className="pt-4 border-t border-zinc-900 flex items-center justify-between">
              <button
                onClick={() => toggleGateAccess(selectedMember.code)}
                className={`px-4 py-2.5 rounded-xl text-xs font-semibold tracking-wider transition-colors ${
                  selectedMember.gateStatus === 'ENABLED'
                    ? 'bg-red-950/60 hover:bg-red-900 text-red-400 border border-red-900'
                    : 'bg-emerald-600 hover:bg-emerald-500 text-white'
                }`}
              >
                {selectedMember.gateStatus === 'ENABLED' ? 'Lock Turnstile Pass' : 'Unlock Turnstile Pass'}
              </button>

              <button
                onClick={() => setSelectedMember(null)}
                className="px-5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold tracking-wider"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

