'use client';

import { useState } from 'react';
import Link from 'next/link';
import AdminSidebar from '@/components/admin-sidebar';

interface AdmissionApplication {
  id: string;
  memberCode: string;
  fullName: string;
  fatherName: string;
  cnic: string;
  phone: string;
  email: string;
  dob: string;
  gender: string;
  bloodGroup: string;
  address: string;
  city: string;
  emergencyName: string;
  emergencyPhone: string;
  emergencyRelation: string;
  height: string;
  weight: string;
  primaryGoal: string;
  experience: string;
  medicalConditions: string[];
  medicalNotes?: string;
  plan: string;
  shift: string;
  locker: boolean;
  trainer: string;
  amount: number;
  paymentMode: 'EASYPAISA' | 'JAZZCASH' | 'CASH' | 'BANK';
  transactionRef: string;
  paymentStatus: 'PAID' | 'PENDING';
  status: 'PENDING_APPROVAL' | 'APPROVED' | 'REJECTED';
  submittedAt: string;
}

const INITIAL_ADMISSIONS: AdmissionApplication[] = [
  {
    id: 'ADM-2026-001',
    memberCode: '2026-482',
    fullName: 'Hamza Farooq',
    fatherName: 'Farooq Ahmed',
    cnic: '61101-8392104-5',
    phone: '+92 301 5552341',
    email: 'hamza.f@gmail.com',
    dob: '2001-04-12',
    gender: 'Male',
    bloodGroup: 'O+',
    address: 'House 14, Street 9, Sector F-8/2',
    city: 'Islamabad',
    emergencyName: 'Farooq Ahmed (Father)',
    emergencyPhone: '+92 300 9876543',
    emergencyRelation: 'Father',
    height: '5 ft 11 in',
    weight: '78 kg',
    primaryGoal: 'Muscle Hypertrophy & Bulking',
    experience: 'Intermediate (1-3 Years)',
    medicalConditions: [],
    plan: '3-Month Pro Athlete Plan (₨ 22,500)',
    shift: 'Prime Evening (05:00 PM - 11:00 PM)',
    locker: true,
    trainer: 'Coach Marcus Vance',
    amount: 22500,
    paymentMode: 'EASYPAISA',
    transactionRef: 'EP-9821449012',
    paymentStatus: 'PAID',
    status: 'PENDING_APPROVAL',
    submittedAt: 'Today, 02:40 PM',
  },
  {
    id: 'ADM-2026-002',
    memberCode: '2026-483',
    fullName: 'Ayesha Noor',
    fatherName: 'Tariq Noor',
    cnic: '61101-4491023-8',
    phone: '+92 333 4901293',
    email: 'ayesha.noor@outlook.com',
    dob: '1999-08-25',
    gender: 'Female',
    bloodGroup: 'B+',
    address: 'Apartment 4B, Silver Oak, Sector E-11',
    city: 'Islamabad',
    emergencyName: 'Dr. Tariq Noor',
    emergencyPhone: '+92 321 4443322',
    emergencyRelation: 'Father',
    height: '5 ft 5 in',
    weight: '58 kg',
    primaryGoal: 'Fat Loss & Body Recomposition',
    experience: 'Beginner (0-6 Months)',
    medicalConditions: ['Mild Asthma (Carries Inhaler)'],
    medicalNotes: 'Requires gradual warm-up; carries Ventolin inhaler during heavy cardio.',
    plan: '1-Month Starter Plan (₨ 8,500)',
    shift: 'Morning Shift (05:00 AM - 11:00 AM)',
    locker: false,
    trainer: 'Coach Sarah Jenkins',
    amount: 8500,
    paymentMode: 'CASH',
    transactionRef: 'Cash at Front Desk',
    paymentStatus: 'PENDING',
    status: 'PENDING_APPROVAL',
    submittedAt: 'Today, 01:15 PM',
  },
  {
    id: 'ADM-2026-003',
    memberCode: '2026-484',
    fullName: 'Bilal Khan',
    fatherName: 'Naveed Khan',
    cnic: '37405-1928374-1',
    phone: '+92 345 8899221',
    email: 'bilalkhan.fit@gmail.com',
    dob: '1997-11-03',
    gender: 'Male',
    bloodGroup: 'A+',
    address: 'Plot 88, Bahria Phase 7',
    city: 'Rawalpindi',
    emergencyName: 'Usman Khan',
    emergencyPhone: '+92 345 1112233',
    emergencyRelation: 'Brother',
    height: '6 ft 1 in',
    weight: '89 kg',
    primaryGoal: 'Powerlifting & Compound Strength',
    experience: 'Advanced Lifter (3+ Years)',
    medicalConditions: ['Lower Lumbar Strain (Resolved 2024)'],
    plan: '6-Month Elite Membership (₨ 42,000)',
    shift: 'Afternoon Shift (12:00 PM - 05:00 PM)',
    locker: true,
    trainer: 'Self Directed',
    amount: 42000,
    paymentMode: 'JAZZCASH',
    transactionRef: 'JC-8839012399',
    paymentStatus: 'PAID',
    status: 'PENDING_APPROVAL',
    submittedAt: 'Today, 11:30 AM',
  },
  {
    id: 'ADM-2026-004',
    memberCode: '2026-480',
    fullName: 'Zainab Qureshi',
    fatherName: 'Qureshi Bashir',
    cnic: '61101-9988221-4',
    phone: '+92 321 9988776',
    email: 'zainab.q@gmail.com',
    dob: '2002-02-18',
    gender: 'Female',
    bloodGroup: 'AB+',
    address: 'House 55, Street 12, Sector G-10/4',
    city: 'Islamabad',
    emergencyName: 'Mrs. Bashir',
    emergencyPhone: '+92 321 5556677',
    emergencyRelation: 'Mother',
    height: '5 ft 4 in',
    weight: '54 kg',
    primaryGoal: 'Combat Strike & Boxing Conditioning',
    experience: 'Intermediate (1-3 Years)',
    medicalConditions: [],
    plan: '3 Month Package (₨ 10,000)',
    shift: 'Prime Evening (05:00 PM - 11:00 PM)',
    locker: false,
    trainer: 'Coach Marcus Vance',
    amount: 10000,
    paymentMode: 'EASYPAISA',
    transactionRef: 'EP-4491028399',
    paymentStatus: 'PAID',
    status: 'APPROVED',
    submittedAt: 'Yesterday, 06:10 PM',
  },
];

export default function AdminAdmissionsPage() {
  const [admissions, setAdmissions] = useState<AdmissionApplication[]>(INITIAL_ADMISSIONS);
  const [statusFilter, setStatusFilter] = useState<'ALL' | 'PENDING_APPROVAL' | 'APPROVED' | 'REJECTED'>('ALL');
  const [search, setSearch] = useState('');
  const [selectedApplicant, setSelectedApplicant] = useState<AdmissionApplication | null>(null);

  // Filter
  const filtered = admissions.filter((a) => {
    const matchesStatus = statusFilter === 'ALL' || a.status === statusFilter;
    const matchesSearch =
      a.fullName.toLowerCase().includes(search.toLowerCase()) ||
      a.memberCode.toLowerCase().includes(search.toLowerCase()) ||
      a.cnic.includes(search) ||
      a.phone.includes(search);
    return matchesStatus && matchesSearch;
  });

  const handleApprove = (id: string) => {
    setAdmissions((prev) =>
      prev.map((a) =>
        a.id === id ? { ...a, status: 'APPROVED', paymentStatus: 'PAID' } : a
      )
    );
    if (selectedApplicant && selectedApplicant.id === id) {
      setSelectedApplicant({ ...selectedApplicant, status: 'APPROVED', paymentStatus: 'PAID' });
    }
  };

  const handleReject = (id: string) => {
    if (confirm('Are you sure you want to reject this admission application?')) {
      setAdmissions((prev) =>
        prev.map((a) => (a.id === id ? { ...a, status: 'REJECTED' } : a))
      );
      if (selectedApplicant && selectedApplicant.id === id) {
        setSelectedApplicant({ ...selectedApplicant, status: 'REJECTED' });
      }
    }
  };

  const handleMarkPaymentPaid = (id: string) => {
    setAdmissions((prev) =>
      prev.map((a) => (a.id === id ? { ...a, paymentStatus: 'PAID' } : a))
    );
    if (selectedApplicant && selectedApplicant.id === id) {
      setSelectedApplicant({ ...selectedApplicant, paymentStatus: 'PAID' });
    }
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
                Intake Desk
              </span>
              <span className="text-zinc-600">&bull;</span>
              <span className="text-xs text-zinc-400">Online Applicant Verification</span>
            </div>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
              New Admissions &amp; Applications
            </h1>
            <p className="text-xs sm:text-sm text-zinc-400 mt-1 max-w-2xl">
              Review incoming online enrollment dossiers, verify CNIC &amp; medical conditions,
              reconcile initial payments, and activate turnstile gate credentials.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <Link
              href="/admin/members-add"
              className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-200 text-xs font-semibold tracking-wider border border-zinc-800 transition-colors flex items-center gap-2"
            >
              <i className="fa-solid fa-plus text-brand-red" />
              <span>Manual Desk Walk-In</span>
            </Link>
          </div>
        </div>

        {/* 4 Metric KPI Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Total Applications
            </span>
            <span className="font-heading text-3xl font-bold text-white mt-1 block">
              {admissions.length} Dossiers
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Online Intake Roster</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-brand-red/40 shadow-sm">
            <span className="text-[10px] font-semibold text-brand-red tracking-wider block">
              Pending Approval
            </span>
            <span className="font-heading text-3xl font-bold text-brand-red mt-1 block">
              {admissions.filter((a) => a.status === 'PENDING_APPROVAL').length} Requiring Action
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">Gate pass unreleased</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Initial Dues Intake
            </span>
            <span className="font-heading text-3xl font-bold text-emerald-400 font-mono mt-1 block">
              ₨ {admissions.reduce((acc, curr) => acc + curr.amount, 0).toLocaleString()}
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">EasyPaisa, JazzCash, Cash</span>
          </div>

          <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900">
            <span className="text-[10px] font-semibold text-zinc-500 tracking-wider block">
              Medical Flagged
            </span>
            <span className="font-heading text-3xl font-bold text-amber-400 mt-1 block">
              {admissions.filter((a) => a.medicalConditions.length > 0).length} Athletes
            </span>
            <span className="text-[10px] text-zinc-400 mt-1 block">PAR-Q screening required</span>
          </div>
        </div>

        {/* Filter & Search Bar */}
        <div className="p-4 rounded-2xl bg-zinc-950 border border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2 overflow-x-auto w-full sm:w-auto text-xs font-semibold">
            {[
              { id: 'ALL', label: 'All Applications' },
              { id: 'PENDING_APPROVAL', label: 'Pending Approval' },
              { id: 'APPROVED', label: 'Approved & Active' },
              { id: 'REJECTED', label: 'Rejected' },
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setStatusFilter(tab.id as any)}
                className={`px-3.5 py-2 rounded-xl transition-all whitespace-nowrap ${
                  statusFilter === tab.id
                    ? 'bg-brand-red text-white shadow-md shadow-brand-red/30'
                    : 'bg-zinc-900 text-zinc-400 hover:text-white'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>

          <div className="relative w-full sm:w-80">
            <i className="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-500 text-xs" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by name, CNIC, pass ID..."
              className="w-full bg-black border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red font-mono"
            />
          </div>
        </div>

        {/* Applications List Table */}
        <div className="rounded-3xl bg-zinc-950 border border-zinc-900 overflow-hidden shadow-xl">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-black text-[10px] font-semibold text-zinc-500 border-b border-zinc-900">
                <tr>
                  <th className="py-4 px-4">Applicant &amp; Pass ID</th>
                  <th className="py-4 px-4">CNIC &amp; Phone</th>
                  <th className="py-4 px-4">Tier &amp; Shift</th>
                  <th className="py-4 px-4">Health / PAR-Q</th>
                  <th className="py-4 px-4">Payment &amp; Dues</th>
                  <th className="py-4 px-4">Status</th>
                  <th className="py-4 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-900">
                {filtered.map((app) => (
                  <tr key={app.id} className="hover:bg-zinc-900/50 transition-colors">
                    {/* Applicant & Pass ID */}
                    <td className="py-4 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-red to-brand-red-dark text-white font-bold text-xs flex items-center justify-center shrink-0">
                          {app.fullName.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                          <span className="font-bold text-white block">{app.fullName}</span>
                          <span className="text-[10px] text-zinc-500 font-mono">
                            Pass ID: <span className="text-brand-red font-bold">{app.memberCode}</span>
                          </span>
                        </div>
                      </div>
                    </td>

                    {/* CNIC & Phone */}
                    <td className="py-4 px-4 font-mono text-[11px]">
                      <span className="text-white block">{app.cnic}</span>
                      <span className="text-zinc-400 text-[10px]">{app.phone}</span>
                    </td>

                    {/* Tier & Shift */}
                    <td className="py-4 px-4">
                      <span className="font-bold text-white block">{app.plan.split('(')[0]}</span>
                      <span className="text-[10px] text-zinc-500 block truncate max-w-[150px]">
                        {app.shift.split('(')[0]}
                      </span>
                    </td>

                    {/* Health Screening / PAR-Q */}
                    <td className="py-4 px-4">
                      {app.medicalConditions.length > 0 ? (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold">
                          <i className="fa-solid fa-triangle-exclamation text-[9px]" />
                          <span>Flagged ({app.medicalConditions.length})</span>
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold">
                          <i className="fa-solid fa-check text-[9px]" />
                          <span>Cleared</span>
                        </span>
                      )}
                    </td>

                    {/* Payment & Dues */}
                    <td className="py-4 px-4 font-mono">
                      <span className="font-bold text-white block">₨ {app.amount.toLocaleString()}</span>
                      <div className="flex items-center gap-1.5 mt-0.5">
                        <span className="text-[10px] text-zinc-400">{app.paymentMode}</span>
                        <span
                          className={`text-[9px] px-1.5 py-0.2 rounded font-semibold ${
                            app.paymentStatus === 'PAID'
                              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                              : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                          }`}
                        >
                          {app.paymentStatus}
                        </span>
                      </div>
                    </td>

                    {/* Status */}
                    <td className="py-4 px-4">
                      <span
                        className={`inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold tracking-wider ${
                          app.status === 'APPROVED'
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                            : app.status === 'PENDING_APPROVAL'
                            ? 'bg-brand-red/10 text-brand-red border border-brand-red/30 animate-pulse'
                            : 'bg-zinc-800 text-zinc-500 border border-zinc-700'
                        }`}
                      >
                        {app.status === 'PENDING_APPROVAL' ? 'Pending Review' : app.status}
                      </span>
                    </td>

                    {/* Actions */}
                    <td className="py-4 px-4 text-right space-x-2">
                      <button
                        onClick={() => setSelectedApplicant(app)}
                        className="py-1.5 px-3 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white text-[11px] font-semibold tracking-wider border border-zinc-800 transition-colors"
                      >
                        View Dossier
                      </button>

                      {app.status === 'PENDING_APPROVAL' && (
                        <button
                          onClick={() => handleApprove(app.id)}
                          className="py-1.5 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold tracking-wider shadow-md shadow-emerald-600/30 transition-all"
                        >
                          Approve
                        </button>
                      )}
                    </td>
                  </tr>
                ))}

                {filtered.length === 0 && (
                  <tr>
                    <td colSpan={7} className="py-12 text-center text-zinc-500">
                      No admission applications found matching current criteria.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </main>

      {/* FULL APPLICANT DOSSIER POPUP MODAL */}
      {selectedApplicant && (
        <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 animate-fadeIn">
          <div className="bg-zinc-950 border border-zinc-800 rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto shadow-2xl">
            {/* Modal Header */}
            <div className="flex items-center justify-between pb-4 border-b border-zinc-900">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-red to-brand-red-dark flex items-center justify-center text-white font-bold text-lg">
                  {selectedApplicant.fullName.substring(0, 2).toUpperCase()}
                </div>
                <div>
                  <h3 className="font-heading text-2xl font-bold text-white">
                    {selectedApplicant.fullName}
                  </h3>
                  <span className="text-xs text-zinc-400 font-mono">
                    Pass ID: <span className="text-brand-red font-bold">{selectedApplicant.memberCode}</span> &bull; App ID: {selectedApplicant.id}
                  </span>
                </div>
              </div>

              <button
                onClick={() => setSelectedApplicant(null)}
                className="w-8 h-8 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center text-xs"
              >
                ✕
              </button>
            </div>

            {/* Dossier Tabs / Breakdown */}
            <div className="space-y-6 text-xs">
              {/* Section 1: Legal & Contacts */}
              <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-3">
                <span className="text-[10px] font-semibold text-brand-red tracking-wider block">
                  Legal Identity &amp; Contact Records
                </span>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 font-mono">
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Father / Guardian:</span>
                    <span className="text-white">{selectedApplicant.fatherName}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">CNIC / B-Form:</span>
                    <span className="text-white">{selectedApplicant.cnic}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Blood Group:</span>
                    <span className="text-brand-red font-bold">{selectedApplicant.bloodGroup}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">WhatsApp Mobile:</span>
                    <span className="text-emerald-400">{selectedApplicant.phone}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Date of Birth:</span>
                    <span className="text-white">{selectedApplicant.dob} ({selectedApplicant.gender})</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">City:</span>
                    <span className="text-white">{selectedApplicant.city}</span>
                  </div>
                  <div className="col-span-2 sm:col-span-3">
                    <span className="text-zinc-500 text-[10px] block">Residential Address:</span>
                    <span className="text-zinc-300 font-sans">{selectedApplicant.address}</span>
                  </div>
                  <div className="col-span-2 sm:col-span-3 pt-2 border-t border-zinc-900">
                    <span className="text-zinc-500 text-[10px] block">Emergency Notification:</span>
                    <span className="text-white font-sans">
                      {selectedApplicant.emergencyName} ({selectedApplicant.emergencyRelation}) &bull;{' '}
                      <span className="font-mono text-emerald-400">{selectedApplicant.emergencyPhone}</span>
                    </span>
                  </div>
                </div>
              </div>

              {/* Section 2: Physical & Health Flags */}
              <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-3">
                <span className="text-[10px] font-semibold text-brand-red tracking-wider block">
                  Physical Profile &amp; Medical Clearance
                </span>
                <div className="grid grid-cols-3 gap-3 font-mono">
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Height:</span>
                    <span className="text-white">{selectedApplicant.height}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Weight:</span>
                    <span className="text-white">{selectedApplicant.weight}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Experience:</span>
                    <span className="text-white">{selectedApplicant.experience}</span>
                  </div>
                </div>

                <div className="pt-2 border-t border-zinc-900">
                  <span className="text-zinc-500 text-[10px] block">Primary Goal:</span>
                  <span className="text-brand-red font-bold">{selectedApplicant.primaryGoal}</span>
                </div>

                {selectedApplicant.medicalConditions.length > 0 ? (
                  <div className="p-3 rounded-xl bg-amber-950/40 border border-amber-900/60 text-amber-300 space-y-1">
                    <span className="font-bold block text-[11px]">⚠️ Medical Disclosures:</span>
                    {selectedApplicant.medicalConditions.map((med, idx) => (
                      <div key={idx} className="flex items-center gap-2">
                        <i className="fa-solid fa-circle-dot text-[8px]" />
                        <span>{med}</span>
                      </div>
                    ))}
                    {selectedApplicant.medicalNotes && (
                      <p className="text-[10px] text-amber-400/90 italic pt-1">
                        Note: &ldquo;{selectedApplicant.medicalNotes}&rdquo;
                      </p>
                    )}
                  </div>
                ) : (
                  <div className="p-2.5 rounded-xl bg-emerald-950/30 border border-emerald-900/40 text-emerald-400 text-[11px] flex items-center gap-2">
                    <i className="fa-solid fa-check" />
                    <span>PAR-Q Medical Screening: Cleared with no pre-existing restrictions.</span>
                  </div>
                )}
              </div>

              {/* Section 3: Package, Shift & Payment Status */}
              <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-3">
                <span className="text-[10px] font-semibold text-brand-red tracking-wider block">
                  Package Privileges &amp; Fee Status
                </span>

                <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Subscribed Tier:</span>
                    <span className="text-white font-bold">{selectedApplicant.plan}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Training Shift:</span>
                    <span className="text-white">{selectedApplicant.shift}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Locker Reserved:</span>
                    <span className="text-white">{selectedApplicant.locker ? 'Yes (Allocated)' : 'No'}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Total Dues:</span>
                    <span className="font-mono text-emerald-400 font-bold text-sm">
                      PKR {selectedApplicant.amount.toLocaleString()}
                    </span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Payment Channel:</span>
                    <span className="text-white font-mono">{selectedApplicant.paymentMode}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 text-[10px] block">Payment Status:</span>
                    <span
                      className={`font-mono font-bold ${
                        selectedApplicant.paymentStatus === 'PAID' ? 'text-emerald-400' : 'text-amber-400'
                      }`}
                    >
                      {selectedApplicant.paymentStatus}
                    </span>
                  </div>
                </div>

                {selectedApplicant.transactionRef && (
                  <div className="text-[10px] text-zinc-400 font-mono pt-1">
                    Trx Reference: <span className="text-white font-bold">{selectedApplicant.transactionRef}</span>
                  </div>
                )}
              </div>
            </div>

            {/* Modal Actions */}
            <div className="pt-4 border-t border-zinc-900 flex flex-wrap items-center justify-between gap-3">
              <a
                href={`https://wa.me/${selectedApplicant.phone.replace(/[^0-9]/g, '')}?text=Hello%20${encodeURIComponent(selectedApplicant.fullName)},%20this%20is%20GymFlow%20Metropolis%20Reception%20regarding%20your%20admission%20pass%20${selectedApplicant.memberCode}.`}
                target="_blank"
                rel="noopener noreferrer"
                className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-emerald-600 text-emerald-400 hover:text-white text-xs font-semibold tracking-wider transition-colors flex items-center gap-2 border border-zinc-800"
              >
                <i className="fa-brands fa-whatsapp text-sm" />
                <span>WhatsApp Applicant</span>
              </a>

              <div className="flex items-center gap-2">
                {selectedApplicant.paymentStatus === 'PENDING' && (
                  <button
                    onClick={() => handleMarkPaymentPaid(selectedApplicant.id)}
                    className="px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-amber-400 text-xs font-semibold tracking-wider border border-zinc-800"
                  >
                    Mark Cash Paid
                  </button>
                )}

                {selectedApplicant.status === 'PENDING_APPROVAL' && (
                  <>
                    <button
                      onClick={() => handleReject(selectedApplicant.id)}
                      className="px-4 py-2.5 rounded-xl bg-red-950/40 hover:bg-red-900 text-red-400 text-xs font-semibold tracking-wider border border-red-900/40"
                    >
                      Reject
                    </button>
                    <button
                      onClick={() => handleApprove(selectedApplicant.id)}
                      className="px-5 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
                    >
                      <i className="fa-solid fa-check text-xs" />
                      <span>Approve &amp; Activate Pass</span>
                    </button>
                  </>
                )}

                {selectedApplicant.status === 'APPROVED' && (
                  <span className="px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
                    ✓ Turnstile Pass Active
                  </span>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}


