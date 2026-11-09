'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

interface AthleteProfile {
  fullName: string;
  email: string;
  phone: string;
  memberId: string;
  plan: string;
  joinedDate: string;
  shift: string;
  emergencyContact: string;
  emergencyPhone: string;
}

export default function ProfilePage() {
  const [profile, setProfile] = useState<AthleteProfile>({
    fullName: 'Moeez Ahmad',
    email: 'moeezahmad.tech@gmail.com',
    phone: '+92 325 9491349',
    memberId: '2026-4',
    plan: '3-Month Pro Athlete Plan',
    joinedDate: 'Jan 15, 2026',
    shift: 'Prime Evening (05:00 PM - 11:00 PM)',
    emergencyContact: 'Hamza Farooq (Brother)',
    emergencyPhone: '+92 312 4567890',
  });

  const [isEditing, setIsEditing] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);
  const [coachingApp, setCoachingApp] = useState<{
    referenceId: string;
    selectedCoach: string;
    goal: string;
    frequency: string;
    status: string;
  } | null>(null);

  // Load from localStorage if registered/logged in
  useEffect(() => {
    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem('gymflow_current_user');
      if (stored) {
        try {
          const parsed = JSON.parse(stored);
          setProfile((prev) => ({
            ...prev,
            fullName: parsed.fullName || prev.fullName,
            email: parsed.email || prev.email,
            phone: parsed.phone || prev.phone,
            memberId: parsed.memberId || prev.memberId,
            plan:
              parsed.plan === '1m'
                ? '1 Month Package'
                : parsed.plan === '3m'
                ? '3-Month Pro Athlete Plan'
                : parsed.plan === '6m'
                ? '6-Month Elite Membership'
                : prev.plan,
          }));
        } catch (e) {}
      }

      const storedCoach = localStorage.getItem('gymflow_coaching_application');
      if (storedCoach) {
        try {
          setCoachingApp(JSON.parse(storedCoach));
        } catch (e) {}
      }
    }
  }, []);

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    setIsEditing(false);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 4000);

    if (typeof window !== 'undefined') {
      localStorage.setItem('gymflow_current_user', JSON.stringify(profile));
    }
  };

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1 py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-8">
        {/* Profile Header Hero */}
        <div className="p-6 sm:p-10 rounded-3xl bg-zinc-950 border border-zinc-900 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
          {/* Subtle Red Ambient Glow */}
          <div className="absolute top-0 right-0 w-80 h-80 bg-brand-red/10 rounded-full blur-3xl pointer-events-none" />

          <div className="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left relative z-10">
            <div className="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-gradient-to-br from-brand-red to-brand-red-dark flex items-center justify-center font-bold text-white text-3xl shadow-xl shadow-brand-red/30 border border-brand-red/40 shrink-0">
              {profile.fullName.substring(0, 2).toUpperCase()}
            </div>

            <div className="space-y-1.5">
              <div className="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                <h1 className="font-heading text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                  {profile.fullName}
                </h1>
                <span className="px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold">
                  Active Athlete
                </span>
              </div>

              <p className="text-xs sm:text-sm text-zinc-400 font-mono">
                {profile.email} &bull; {profile.phone}
              </p>

              <div className="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1 text-xs font-mono">
                <span className="px-3 py-1 rounded-full bg-black border border-zinc-800 text-brand-red font-bold">
                  Pass ID: {profile.memberId}
                </span>
                <span className="px-3 py-1 rounded-full bg-black border border-zinc-800 text-zinc-300">
                  Tier: {profile.plan}
                </span>
                <span className="px-3 py-1 rounded-full bg-black border border-zinc-800 text-amber-400">
                  Cutoff: 10th of Every Month
                </span>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3 relative z-10 shrink-0">
            <button
              onClick={() => setIsEditing(!isEditing)}
              className="px-5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-200 hover:text-white text-xs font-semibold tracking-wider border border-zinc-800 transition-colors flex items-center gap-2"
            >
              <i className="fa-solid fa-pen text-brand-red text-xs" />
              <span>{isEditing ? 'Cancel Edit' : 'Edit Profile'}</span>
            </button>

            <Link
              href="/app"
              className="px-5 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
            >
              <i className="fa-solid fa-mobile-screen text-xs" />
              <span>Open Mobile PWA</span>
            </Link>
          </div>
        </div>

        {/* Saved Success Notification */}
        {savedSuccess && (
          <div className="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm flex items-center gap-3 animate-fadeIn">
            <i className="fa-solid fa-circle-check text-emerald-400 text-lg" />
            <span>Profile settings updated and synced with your athlete record!</span>
          </div>
        )}

        {/* 3-Column Athlete Cockpit Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          {/* Left Column: Editable Athlete Dossier (8 cols) */}
          <div className="lg:col-span-8 rounded-3xl bg-zinc-950 border border-zinc-900 p-6 sm:p-8 space-y-6">
            <div className="flex items-center justify-between pb-4 border-b border-zinc-900">
              <div>
                <h2 className="font-heading text-xl sm:text-2xl font-bold text-white tracking-tight">
                  Athlete Dossier &amp; Identity
                </h2>
                <p className="text-xs text-zinc-400 mt-0.5">
                  Official membership details registered at Metropolis Flagship HQ.
                </p>
              </div>

              <span className="text-[10px] font-mono text-zinc-500">
                Verified: {profile.joinedDate}
              </span>
            </div>

            {isEditing ? (
              <form onSubmit={handleSave} className="space-y-4 text-xs">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Full Legal Name
                    </label>
                    <input
                      type="text"
                      required
                      value={profile.fullName}
                      onChange={(e) => setProfile({ ...profile, fullName: e.target.value })}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Email Address
                    </label>
                    <input
                      type="email"
                      required
                      value={profile.email}
                      onChange={(e) => setProfile({ ...profile, email: e.target.value })}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      WhatsApp Mobile Number
                    </label>
                    <input
                      type="tel"
                      required
                      value={profile.phone}
                      onChange={(e) => setProfile({ ...profile, phone: e.target.value })}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white font-mono focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Preferred Training Shift
                    </label>
                    <select
                      value={profile.shift}
                      onChange={(e) => setProfile({ ...profile, shift: e.target.value })}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                    >
                      <option value="Early Morning (05:00 AM - 11:00 AM)">
                        Early Morning (05:00 AM - 11:00 AM)
                      </option>
                      <option value="Afternoon Wave (12:00 PM - 05:00 PM)">
                        Afternoon Wave (12:00 PM - 05:00 PM)
                      </option>
                      <option value="Prime Evening (05:00 PM - 11:00 PM)">
                        Prime Evening (05:00 PM - 11:00 PM)
                      </option>
                    </select>
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Emergency Contact Person
                    </label>
                    <input
                      type="text"
                      value={profile.emergencyContact}
                      onChange={(e) => setProfile({ ...profile, emergencyContact: e.target.value })}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Emergency Phone Number
                    </label>
                    <input
                      type="tel"
                      value={profile.emergencyPhone}
                      onChange={(e) => setProfile({ ...profile, emergencyPhone: e.target.value })}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white font-mono focus:outline-none focus:border-brand-red"
                    />
                  </div>
                </div>

                <div className="pt-4 flex items-center justify-end gap-3 border-t border-zinc-900">
                  <button
                    type="button"
                    onClick={() => setIsEditing(false)}
                    className="px-5 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-semibold transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="px-6 py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold shadow-md shadow-brand-red/30 transition-all"
                  >
                    Save Changes
                  </button>
                </div>
              </form>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs font-mono">
                <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-1">
                  <span className="text-[10px] text-zinc-500 font-sans block">Full Legal Name</span>
                  <span className="text-white font-bold text-sm font-sans">{profile.fullName}</span>
                </div>

                <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-1">
                  <span className="text-[10px] text-zinc-500 font-sans block">Primary Email</span>
                  <span className="text-zinc-200 text-xs">{profile.email}</span>
                </div>

                <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-1">
                  <span className="text-[10px] text-zinc-500 font-sans block">WhatsApp Mobile</span>
                  <span className="text-zinc-200 text-xs">{profile.phone}</span>
                </div>

                <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-1">
                  <span className="text-[10px] text-zinc-500 font-sans block">Training Wave / Shift</span>
                  <span className="text-zinc-200 text-xs font-sans font-semibold">{profile.shift}</span>
                </div>

                <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-1">
                  <span className="text-[10px] text-zinc-500 font-sans block">Emergency Contact</span>
                  <span className="text-zinc-200 text-xs font-sans">{profile.emergencyContact}</span>
                </div>

                <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-1">
                  <span className="text-[10px] text-zinc-500 font-sans block">Emergency Phone</span>
                  <span className="text-zinc-200 text-xs">{profile.emergencyPhone}</span>
                </div>
              </div>
            )}

            {/* Quick Service Links */}
            <div className="pt-4 border-t border-zinc-900 flex flex-wrap items-center justify-between gap-4">
              <div className="flex items-center gap-2 text-xs text-zinc-400">
                <i className="fa-solid fa-shield text-brand-red text-sm" />
                <span>Biometric turnstile authorization in good standing.</span>
              </div>

              <div className="flex items-center gap-3">
                <Link
                  href="/admission"
                  className="text-xs text-zinc-400 hover:text-white font-semibold underline"
                >
                  Submit New Admission
                </Link>
                <span className="text-zinc-600">&bull;</span>
                <Link
                  href="/contact"
                  className="text-xs text-brand-red hover:underline font-semibold"
                >
                  Contact Desk Support &rarr;
                </Link>
              </div>
            </div>
          </div>

          {/* Right Column: Membership Status & Turnstile Sync (4 cols) */}
          <div className="lg:col-span-4 space-y-6">
            {/* Membership Tier Status Card */}
            <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-[10px] font-semibold text-zinc-500 tracking-wider">
                  Membership Status
                </span>
                <span className="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-mono text-[9px] font-bold">
                  ACTIVE
                </span>
              </div>

              <div>
                <span className="text-xs text-zinc-400 block">Enrolled Plan</span>
                <h3 className="font-heading text-2xl font-bold text-white mt-0.5">
                  {profile.plan}
                </h3>
              </div>

              <div className="p-3.5 rounded-2xl bg-black border border-zinc-900 space-y-2 text-xs">
                <div className="flex justify-between">
                  <span className="text-zinc-400">Next Cutoff Date:</span>
                  <span className="text-amber-400 font-mono font-bold">10th March 2026</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-zinc-400">Turnstile Clearance:</span>
                  <span className="text-emerald-400 font-bold">24/7 Unrestricted</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-zinc-400">Recovery Suite Access:</span>
                  <span className="text-zinc-200">Sauna &amp; Ice Immersion</span>
                </div>
              </div>

              <a
                href={`https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20am%20${encodeURIComponent(profile.fullName)}%20(ID:%20${profile.memberId})%20and%20want%20to%20renew%20my%20plan.`}
                target="_blank"
                rel="noreferrer"
                className="w-full py-3 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-200 hover:text-white text-xs font-semibold border border-zinc-800 transition-colors flex items-center justify-center gap-2"
              >
                <i className="fa-brands fa-whatsapp text-emerald-400 text-sm" />
                <span>Renew via Front Desk</span>
              </a>
            </div>

            {/* 1-on-1 Coaching Card */}
            <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-[10px] font-semibold text-brand-red tracking-wider">
                  1-on-1 Coaching &amp; Mentorship
                </span>
                {coachingApp ? (
                  <span className="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 font-mono text-[9px] font-bold">
                    REVIEWING
                  </span>
                ) : (
                  <span className="px-2 py-0.5 rounded-full bg-zinc-900 text-zinc-400 font-mono text-[9px]">
                    AVAILABLE
                  </span>
                )}
              </div>

              {coachingApp ? (
                <div className="space-y-3 text-xs">
                  <div>
                    <span className="text-[10px] text-zinc-400 block">Assigned Coach</span>
                    <h4 className="font-heading text-lg font-bold text-white mt-0.5">
                      {coachingApp.selectedCoach}
                    </h4>
                  </div>
                  <div className="p-3 rounded-2xl bg-black border border-zinc-900 space-y-1.5 font-mono text-[11px]">
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Focus:</span>
                      <span className="text-zinc-200 truncate max-w-[140px]">{coachingApp.goal}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Frequency:</span>
                      <span className="text-zinc-200">{coachingApp.frequency.split('(')[0]}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Ref ID:</span>
                      <span className="text-brand-red font-bold">{coachingApp.referenceId}</span>
                    </div>
                  </div>
                  <a
                    href={`https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20am%20${encodeURIComponent(profile.fullName)}%20(Ref:%20${coachingApp.referenceId})%20regarding%20my%20coaching%20request%20with%20${encodeURIComponent(coachingApp.selectedCoach)}.`}
                    target="_blank"
                    rel="noreferrer"
                    className="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-2"
                  >
                    <i className="fa-brands fa-whatsapp text-sm" />
                    <span>Message Coach on WhatsApp</span>
                  </a>
                </div>
              ) : (
                <div className="space-y-3">
                  <p className="text-xs text-zinc-400 leading-relaxed">
                    Accelerate your physical potential with custom RPE wave protocols, kinetic movement screens, and nutrition blueprinting.
                  </p>
                  <Link
                    href="/coaching"
                    className="w-full py-2.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-md shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
                  >
                    <i className="fa-solid fa-user-ninja text-xs" />
                    <span>Apply for 1-on-1 Coach</span>
                  </Link>
                </div>
              )}
            </div>

            {/* Quick App Shortcut Card */}
            <div className="p-6 rounded-3xl bg-zinc-950 border border-brand-red/30 space-y-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-2xl bg-brand-red/10 border border-brand-red/30 flex items-center justify-center text-brand-red">
                  <i className="fa-solid fa-mobile-screen text-base" />
                </div>
                <div>
                  <h4 className="font-heading text-base font-bold text-white">
                    Athlete Companion App
                  </h4>
                  <p className="text-[11px] text-zinc-400">
                    Scan turnstiles, view workout streaks &amp; notifications.
                  </p>
                </div>
              </div>

              <Link
                href="/app"
                className="w-full py-3 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-md shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
              >
                <span>Launch Mobile App Experience</span>
                <i className="fa-solid fa-arrow-right text-[10px]" />
              </Link>
            </div>
          </div>
        </div>
      </main>

      <PublicFooter />
    </div>
  );
}

