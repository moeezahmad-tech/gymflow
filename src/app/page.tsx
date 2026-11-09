'use client';

import { useState } from 'react';
import Link from 'next/link';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';
import RealQrCode from '@/components/real-qr-code';

export default function HomePage() {
  const [inquiryName, setInquiryName] = useState('');
  const [inquiryEmail, setInquiryEmail] = useState('');
  const [inquiryPhone, setInquiryPhone] = useState('');
  const [inquiryProgram, setInquiryProgram] = useState('Strength & Conditioning');
  const [inquirySubmitted, setInquirySubmitted] = useState(false);

  const [newsletterEmail, setNewsletterEmail] = useState('');
  const [newsletterSuccess, setNewsletterSuccess] = useState(false);

  const handleInquirySubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (inquiryName && inquiryEmail) {
      setInquirySubmitted(true);
      setTimeout(() => setInquirySubmitted(false), 6000);
      setInquiryName('');
      setInquiryEmail('');
      setInquiryPhone('');
    }
  };

  const handleNewsletterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (newsletterEmail) {
      setNewsletterSuccess(true);
      setTimeout(() => setNewsletterSuccess(false), 5000);
      setNewsletterEmail('');
    }
  };

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1">
        {/* ================= 1. HERO BANNER: THE INTELLIGENT BRAIN FOR YOUR GYM ================= */}
        <section className="relative min-h-[92vh] flex items-center justify-center pt-24 pb-24 px-6 overflow-hidden">
          {/* Authentic High-Res Athlete Training Image Background */}
          <div
            className="absolute inset-0 bg-cover bg-center bg-no-repeat scale-105 filter contrast-125 brightness-[0.35] transition-transform duration-1000"
            style={{
              backgroundImage: `url('/images/download.png')`,
            }}
          />

          {/* Authentic Fluted Vertical Pleats Texture Overlay Matching Reference Image */}
          <div className="absolute inset-0 bg-fluted-dark opacity-50 mix-blend-multiply pointer-events-none" />

          {/* Dark Athletic Gradient Overlays */}
          <div className="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-black/60 pointer-events-none" />
          <div className="absolute inset-0 bg-gradient-to-r from-black/95 via-transparent to-black/95 pointer-events-none" />

          {/* Crimson Ambient Lighting Halos */}
          <div className="absolute top-10 left-1/2 -translate-x-1/2 w-[750px] h-[750px] bg-brand-red/25 rounded-full blur-[170px] pointer-events-none" />
          <div className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-black to-transparent pointer-events-none" />

          <div className="max-w-5xl mx-auto text-center relative z-10 space-y-6">
            {/* Pill Badge matching Nexora design */}
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-red/10 border border-brand-red/30 backdrop-blur-md animate-fadeIn">
              <span className="text-xs">🔥</span>
              <span className="text-xs font-medium text-zinc-300">
                Flagship Athletic Facility <span className="text-white font-semibold">Metropolis HQ</span> is open 24/7
              </span>
              <i className="fa-solid fa-chevron-right text-[10px] text-brand-red ml-0.5" />
            </div>

            {/* Headline matching Nexora: 'The Strength & Conditioning Sanctuary Built for Velocity' with clean text wrapping */}
            <h1 className="font-heading text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-[-0.035em] text-white leading-[1.12] max-w-4xl mx-auto">
              The Strength Sanctuary <br className="hidden sm:inline" />
              Built for <span className="text-brand-red">Peak Performance</span>
            </h1>

            {/* Value Proposition Subtitle */}
            <p className="max-w-2xl mx-auto text-base sm:text-lg text-zinc-400 font-normal leading-relaxed">
              Olympic-spec Eleiko bumper plates, Hammer Strength bays, expert coaches, and 24/7 automated biometric turnstiles. Transform your raw physical potential.
            </p>

            {/* Primary & Secondary Dual CTAs with clean fixed heights and perfect gap */}
            <div className="flex flex-wrap items-center justify-center gap-4 pt-3">
              <Link
                href="/admission"
                className="h-12 px-8 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold tracking-wide transition-all shadow-lg shadow-brand-red/30 hover:scale-[1.02] flex items-center justify-center gap-2.5"
              >
                <span>Start Free Trial</span>
                <i className="fa-solid fa-arrow-right text-xs" />
              </Link>

              <Link
                href="/contact"
                className="h-12 px-8 rounded-full bg-zinc-900/90 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-sm font-semibold tracking-wide transition-all backdrop-blur-md flex items-center justify-center gap-2.5"
              >
                <i className="fa-regular fa-calendar text-xs text-zinc-400" />
                <span>Book Gym Tour</span>
              </Link>
            </div>

            {/* ================= 2. NEXORA BENCHMARK: 3 HERO METRICS CARDS (100% GYM CONTENT) ================= */}
            <div className="pt-12 max-w-6xl mx-auto">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-5 text-left">
                {/* Metric Card 1: Total Iron Moved */}
                <div className="p-6 rounded-3xl bg-zinc-950/80 border border-zinc-900/90 hover:border-zinc-800 transition-all backdrop-blur-xl relative group">
                  <div className="w-10 h-10 rounded-xl bg-brand-red/10 border border-brand-red/20 flex items-center justify-center text-brand-red mb-4">
                    <i className="fa-solid fa-dumbbell text-sm" />
                  </div>
                  <span className="text-xs font-medium text-zinc-400 block mb-1">Total Iron Moved</span>
                  <div className="flex items-baseline gap-2">
                    <span className="font-heading text-3xl sm:text-4xl font-extrabold text-white tracking-tight">2.45 M kg</span>
                  </div>
                  <div className="mt-3 flex items-center gap-1.5 text-xs font-semibold text-brand-red">
                    <i className="fa-solid fa-arrow-trend-up text-[10px]" />
                    <span>32% overload vs last 7 days</span>
                  </div>
                </div>

                {/* Metric Card 2: Athlete Training Sessions */}
                <div className="p-6 rounded-3xl bg-zinc-950/80 border border-zinc-900/90 hover:border-zinc-800 transition-all backdrop-blur-xl relative group">
                  <div className="w-10 h-10 rounded-xl bg-brand-red/10 border border-brand-red/20 flex items-center justify-center text-brand-red mb-4">
                    <i className="fa-solid fa-fire-flame-curved text-sm" />
                  </div>
                  <span className="text-xs font-medium text-zinc-400 block mb-1">Workouts Logged</span>
                  <div className="flex items-baseline gap-2">
                    <span className="font-heading text-3xl sm:text-4xl font-extrabold text-white tracking-tight">18,200+</span>
                  </div>
                  <div className="mt-3 flex items-center gap-1.5 text-xs font-semibold text-brand-red">
                    <i className="fa-solid fa-arrow-trend-up text-[10px]" />
                    <span>24% attendance vs last 7 days</span>
                  </div>
                </div>

                {/* Metric Card 3: Gym Floor Volume with Glowing Red Wave Chart */}
                <div className="p-6 rounded-3xl bg-zinc-950/80 border border-zinc-900/90 hover:border-zinc-800 transition-all backdrop-blur-xl relative overflow-hidden flex flex-col justify-between">
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-xs font-medium text-zinc-400">Gym Floor Volume</span>
                      <span className="text-[10px] font-mono text-zinc-500">20k Sets</span>
                    </div>
                    <div className="flex items-baseline gap-2">
                      <span className="font-heading text-3xl sm:text-4xl font-extrabold text-white tracking-tight">18.6 K</span>
                    </div>
                    <div className="mt-1 flex items-center gap-1.5 text-xs font-semibold text-brand-red">
                      <i className="fa-solid fa-arrow-trend-up text-[10px]" />
                      <span>28% peak training velocity</span>
                    </div>
                  </div>

                  {/* Red Wave Area Sparkline */}
                  <div className="mt-4 relative">
                    <svg viewBox="0 0 320 80" className="w-full h-16 overflow-visible" fill="none">
                      <defs>
                        <linearGradient id="nexoraRedGradient" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="0%" stopColor="#ff2a2a" stopOpacity="0.45" />
                          <stop offset="100%" stopColor="#ff2a2a" stopOpacity="0.0" />
                        </linearGradient>
                      </defs>
                      {/* Area Fill */}
                      <path
                        d="M0,60 Q40,55 80,45 T160,35 T240,20 T320,12 L320,80 L0,80 Z"
                        fill="url(#nexoraRedGradient)"
                      />
                      {/* Vibrant Red Stroke Line */}
                      <path
                        d="M0,60 Q40,55 80,45 T160,35 T240,20 T320,12"
                        stroke="#ff2a2a"
                        strokeWidth="2.5"
                        strokeLinecap="round"
                      />
                    </svg>
                    <div className="flex items-center justify-between text-[9px] font-mono text-zinc-500 mt-1">
                      <span>Mon</span>
                      <span>Tue</span>
                      <span>Wed</span>
                      <span>Thu</span>
                      <span>Fri</span>
                      <span>Sat</span>
                      <span>Sun</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ================= 3. NEXORA BENCHMARK: COMPLETE ATHLETIC ECOSYSTEM ================= */}
        <section className="py-24 px-6 max-w-7xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            {/* Left Narrative (5 cols) */}
            <div className="lg:col-span-5 space-y-6 text-left">
              <span className="text-xs font-semibold text-brand-red tracking-wide block">
                Complete Athletic Ecosystem
              </span>

              <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-[1.12] tracking-tight">
                Everything you need to turn effort into <span className="text-brand-red">dominance</span>
              </h2>

              <p className="text-zinc-400 text-sm sm:text-base leading-relaxed">
                GymFlow Metropolis unifies competition-grade powerlifting platforms, high-density plate-loaded Hammer Strength circuits, certified strength coaches, and real-time turnstile tracking all under one iconic roof.
              </p>

              <div>
                <Link
                  href="/programs"
                  className="inline-flex items-center gap-2.5 h-12 px-7 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold tracking-wide transition-all shadow-lg shadow-brand-red/30 hover:scale-[1.02]"
                >
                  <span>Explore Training Programs</span>
                  <i className="fa-solid fa-arrow-right text-xs" />
                </Link>
              </div>
            </div>

            {/* Right Mock UI Dashboard Cockpit Panel (7 cols) */}
            <div className="lg:col-span-7 rounded-3xl bg-zinc-950/90 border border-zinc-800/90 p-5 backdrop-blur-xl shadow-2xl space-y-4">
              {/* Header Tab Bar */}
              <div className="flex items-center justify-between pb-3 border-b border-zinc-900">
                <div className="flex items-center gap-3">
                  <span className="text-xs font-semibold text-white">Gym Floor Zones</span>
                  <Link href="/facilities" className="text-[10px] text-zinc-500 hover:text-brand-red font-mono transition-colors">
                    View zones &rarr;
                  </Link>
                </div>
                <div className="flex items-center gap-1.5">
                  <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                  <span className="text-[10px] font-mono text-zinc-400">34 Athletes on Floor Now</span>
                </div>
              </div>

              {/* Top 3 Mini Metrics */}
              <div className="grid grid-cols-3 gap-3">
                <div className="p-3 rounded-2xl bg-black border border-zinc-900">
                  <span className="text-[10px] text-zinc-400 block">Active Athletes</span>
                  <div className="flex items-baseline gap-1 mt-0.5">
                    <span className="font-heading text-lg font-bold text-white">142</span>
                  </div>
                  <span className="text-[9px] text-emerald-400 font-semibold block mt-0.5">↗ 14 new this month</span>
                </div>

                <div className="p-3 rounded-2xl bg-black border border-zinc-900">
                  <span className="text-[10px] text-zinc-400 block">Avg Session</span>
                  <div className="flex items-baseline gap-1 mt-0.5">
                    <span className="font-heading text-lg font-bold text-white">68 min</span>
                  </div>
                  <span className="text-[9px] text-emerald-400 font-semibold block mt-0.5">↗ Peak hypertrophy</span>
                </div>

                <div className="p-3 rounded-2xl bg-black border border-zinc-900">
                  <span className="text-[10px] text-zinc-400 block">PR Success Rate</span>
                  <div className="flex items-baseline gap-1 mt-0.5">
                    <span className="font-heading text-lg font-bold text-white">99.4%</span>
                  </div>
                  <span className="text-[9px] text-emerald-400 font-semibold block mt-0.5">↗ Overload target hit</span>
                </div>
              </div>

              {/* Status Filter Badges */}
              <div className="flex flex-wrap items-center gap-2 pt-1">
                <span className="px-3 py-1 rounded-full bg-zinc-900 border border-zinc-800 text-[10px] font-medium text-zinc-300 flex items-center gap-1">
                  <span className="text-brand-red text-xs">✦</span> Heavy Iron
                </span>
                <span className="px-3 py-1 rounded-full bg-zinc-900 border border-zinc-800 text-[10px] font-medium text-zinc-300 flex items-center gap-1">
                  <span className="text-brand-red text-xs">✦</span> Powerlifting
                </span>
                <span className="px-3 py-1 rounded-full bg-zinc-900 border border-zinc-800 text-[10px] font-medium text-zinc-300 flex items-center gap-1">
                  <span className="text-brand-red text-xs">✦</span> Hammer Strength
                </span>
                <span className="px-3 py-1 rounded-full bg-zinc-900 border border-zinc-800 text-[10px] font-medium text-zinc-300 flex items-center gap-1">
                  <span className="text-brand-red text-xs">✦</span> Combat &amp; Boxing
                </span>
                <span className="px-3 py-1 rounded-full bg-zinc-900 border border-zinc-800 text-[10px] font-medium text-zinc-300 flex items-center gap-1">
                  <span className="text-brand-red text-xs">✦</span> Recovery Sauna
                </span>
              </div>

              {/* Athlete Daily Tactical Workout Split Mock */}
              <div className="rounded-2xl bg-black border border-zinc-900 p-4 font-mono text-[11px] space-y-1.5 relative">
                <div className="flex items-center justify-between pb-2 mb-2 border-b border-zinc-900 text-[10px] text-zinc-500">
                  <span className="flex items-center gap-1.5 text-zinc-300">
                    <i className="fa-solid fa-clipboard-check text-brand-red" />
                    Daily Tactical Strength Protocol
                  </span>
                  <span className="text-brand-red font-semibold font-mono">Coach Marcus Vance</span>
                </div>
                <p className="text-zinc-300">
                  <span className="text-brand-red font-semibold">01.</span> Competition Barbell Back Squat <span className="text-white font-semibold">5 Sets &times; 5 Reps</span> @ 82.5% 1RM
                </p>
                <p className="text-zinc-300">
                  <span className="text-brand-red font-semibold">02.</span> Incline Dumbbell Bench Press <span className="text-white font-semibold">4 Sets &times; 8-10 Reps</span> (RPE 8.5)
                </p>
                <p className="text-zinc-300">
                  <span className="text-brand-red font-semibold">03.</span> Weighted Romanian Deadlift <span className="text-white font-semibold">4 Sets &times; 10 Reps</span> (Tempo 3-0-1)
                </p>
                <p className="text-zinc-300">
                  <span className="text-brand-red font-semibold">04.</span> Hammer Strength High Row <span className="text-white font-semibold">3 Sets &times; 12 Reps</span> to mechanical failure
                </p>
                <p className="text-zinc-500 text-[10px] pt-0.5">
                  &bull; Progressive overload logged instantly on athlete companion pass
                </p>

                <div className="pt-3 flex justify-end">
                  <Link
                    href="/admission"
                    className="px-4 py-1.5 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-[11px] font-bold flex items-center gap-1.5 shadow-md shadow-brand-red/30 transition-transform active:scale-95"
                  >
                    <i className="fa-solid fa-check text-[9px]" />
                    <span>Start This Protocol</span>
                  </Link>
                </div>
              </div>

              {/* Bottom Split: Top Gym PR Records & Certified Arsenal */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                {/* Top PR Records */}
                <div className="p-3 rounded-2xl bg-black border border-zinc-900 space-y-2">
                  <div className="flex items-center justify-between text-[10px] text-zinc-400 font-semibold">
                    <span>Facility Personal Bests</span>
                    <span className="text-brand-red font-mono">Verified PRs</span>
                  </div>
                  <div className="space-y-1.5 text-[11px] font-mono">
                    <div className="flex justify-between items-center text-zinc-300">
                      <span>Deadlift (Raw)</span>
                      <span className="text-white font-bold">280 kg</span>
                    </div>
                    <div className="flex justify-between items-center text-zinc-300">
                      <span>Barbell Back Squat</span>
                      <span className="text-white font-bold">235 kg</span>
                    </div>
                    <div className="flex justify-between items-center text-zinc-300">
                      <span>Competition Bench</span>
                      <span className="text-white font-bold">175 kg</span>
                    </div>
                    <div className="flex justify-between items-center text-zinc-300">
                      <span>Clean &amp; Jerk</span>
                      <span className="text-white font-bold">140 kg</span>
                    </div>
                  </div>
                </div>

                {/* Equipment Arsenal */}
                <div className="p-3 rounded-2xl bg-black border border-zinc-900 space-y-2">
                  <div className="flex items-center justify-between text-[10px] text-zinc-400 font-semibold">
                    <span>Certified Arsenal</span>
                    <span className="text-zinc-500 font-mono">Zone 01-04</span>
                  </div>
                  <div className="flex items-center gap-2 pt-1">
                    <span className="px-2 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 text-[10px] font-bold">
                      Eleiko
                    </span>
                    <span className="px-2 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 text-[10px] font-bold">
                      Hammer Strength
                    </span>
                    <span className="px-2 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 text-[10px] font-bold">
                      Rogue
                    </span>
                    <span className="px-2 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 text-[10px] font-bold">
                      Concept2
                    </span>
                  </div>
                  <div className="pt-1 flex items-center gap-1.5 text-[10px] text-emerald-400 font-mono">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400" />
                    <span>64 Professional Training Stations Online</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ================= 4. NEXORA BENCHMARK: A SANCTUARY THAT SCALES WITH YOUR AMBITION (3 CARDS) ================= */}
        <section className="py-24 px-6 max-w-7xl mx-auto border-t border-zinc-900/60">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <span className="text-xs font-semibold text-brand-red tracking-wide block mb-3">
              Uncompromising Standards
            </span>
            <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
              A facility that <span className="text-brand-red">scales</span> with your ambition
            </h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {/* Card 1: Solid Vibrant Red Card (Engineered for Heavy Iron) */}
            <div className="p-7 rounded-3xl bg-brand-red text-white shadow-2xl shadow-brand-red/25 flex flex-col justify-between space-y-6 relative overflow-hidden">
              <div className="space-y-4">
                <div className="w-10 h-10 rounded-full bg-black/20 flex items-center justify-center text-white">
                  <i className="fa-solid fa-dumbbell text-sm" />
                </div>
                <span className="text-xs font-semibold tracking-wide opacity-85 block">
                  Raw Strength
                </span>
                <h3 className="font-heading text-2xl font-bold tracking-tight text-white leading-snug">
                  Engineered for Heavy Iron
                </h3>
                <p className="text-xs text-white/90 leading-relaxed">
                  Calibrated Eleiko competition bumper plates, knurled Texas power bars, and sound-dampening deadlift platforms designed for maximal loading.
                </p>
              </div>

              {/* Vertical Bar Chart Visual (Tonnage By Day) */}
              <div className="pt-6">
                <div className="flex items-end justify-between gap-2 h-28 px-2 border-b border-white/20 pb-2">
                  <div className="w-full bg-white/40 rounded-t-sm h-[45%]" />
                  <div className="w-full bg-white/50 rounded-t-sm h-[60%]" />
                  <div className="w-full bg-white/60 rounded-t-sm h-[50%]" />
                  <div className="w-full bg-white/70 rounded-t-sm h-[75%]" />
                  <div className="w-full bg-white/80 rounded-t-sm h-[65%]" />
                  <div className="w-full bg-white/90 rounded-t-sm h-[90%]" />
                  <div className="w-full bg-white rounded-t-sm h-[100%]" />
                </div>
                <div className="flex justify-between text-[9px] font-mono text-white/75 mt-2 px-1">
                  <span>Mon</span>
                  <span>Tue</span>
                  <span>Wed</span>
                  <span>Thu</span>
                  <span>Fri</span>
                  <span>Sat</span>
                  <span>Sun</span>
                </div>
              </div>
            </div>

            {/* Card 2: Dark Scalability Card (Built for Progressive Overload) */}
            <div className="p-7 rounded-3xl bg-zinc-950 border border-zinc-800/90 hover:border-zinc-700 transition-all flex flex-col justify-between space-y-6">
              <div className="space-y-4">
                <div className="w-10 h-10 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-300">
                  <i className="fa-solid fa-arrow-trend-up text-sm" />
                </div>
                <span className="text-xs font-semibold tracking-wide text-zinc-400 block">
                  Scientific Progression
                </span>
                <h3 className="font-heading text-2xl font-bold tracking-tight text-white leading-snug">
                  Built for Progressive Overload
                </h3>
                <p className="text-xs text-zinc-400 leading-relaxed">
                  Seamlessly scale from your very first 20kg empty barbell to 220kg+ personal bests with periodized strength coaching.
                </p>
              </div>

              {/* Ascending Line Chart Visual */}
              <div className="pt-6">
                <div className="relative h-28 flex flex-col justify-between">
                  <div className="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20">
                    <div className="border-b border-zinc-700 w-full" />
                    <div className="border-b border-zinc-700 w-full" />
                    <div className="border-b border-zinc-700 w-full" />
                    <div className="border-b border-zinc-700 w-full" />
                  </div>
                  <svg viewBox="0 0 240 90" className="w-full h-24 overflow-visible" fill="none">
                    <path
                      d="M10,80 L55,75 L100,60 L145,55 L190,30 L235,15"
                      stroke="#ffffff"
                      strokeWidth="2"
                      strokeLinecap="round"
                    />
                    <circle cx="10" cy="80" r="3" fill="#ffffff" />
                    <circle cx="55" cy="75" r="3" fill="#ffffff" />
                    <circle cx="100" cy="60" r="3" fill="#ffffff" />
                    <circle cx="145" cy="55" r="3" fill="#ffffff" />
                    <circle cx="190" cy="30" r="3" fill="#ffffff" />
                    <circle cx="235" cy="15" r="3" fill="#ff2a2a" />
                  </svg>
                </div>
                <div className="flex justify-between text-[9px] font-mono text-zinc-500 mt-2">
                  <span>20kg</span>
                  <span>60kg</span>
                  <span>100kg</span>
                  <span>140kg</span>
                  <span>180kg</span>
                  <span>220kg+</span>
                </div>
              </div>
            </div>

            {/* Card 3: Dark Reliability Card (24/7 Turnstile Biometrics) */}
            <div className="p-7 rounded-3xl bg-zinc-950 border border-zinc-800/90 hover:border-zinc-700 transition-all flex flex-col justify-between space-y-6">
              <div className="space-y-4">
                <div className="w-10 h-10 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-300">
                  <i className="fa-solid fa-shield-halved text-sm" />
                </div>
                <span className="text-xs font-semibold tracking-wide text-zinc-400 block">
                  Turnstile Reliability
                </span>
                <h3 className="font-heading text-2xl font-bold tracking-tight text-white leading-snug">
                  24/7 Turnstile Biometrics
                </h3>
                <p className="text-xs text-zinc-400 leading-relaxed">
                  99.99% turnstile gate uptime. Train early at 5 AM or midnight with your instant offline PWA optical pass and high-security facility control.
                </p>
              </div>

              {/* Donut Gauge Ring Visual */}
              <div className="pt-4 flex flex-col items-center">
                <div className="relative w-28 h-28 flex items-center justify-center">
                  <svg viewBox="0 0 100 100" className="w-full h-full -rotate-90">
                    {/* Background Ring */}
                    <circle
                      cx="50"
                      cy="50"
                      r="40"
                      stroke="#27272a"
                      strokeWidth="9"
                      fill="none"
                    />
                    {/* Glowing Red Active Progress Ring */}
                    <circle
                      cx="50"
                      cy="50"
                      r="40"
                      stroke="#ff2a2a"
                      strokeWidth="9"
                      fill="none"
                      strokeDasharray="251.2"
                      strokeDashoffset="8"
                      strokeLinecap="round"
                    />
                  </svg>
                  <div className="absolute text-center">
                    <span className="font-heading text-base font-extrabold text-white block">99.99%</span>
                    <span className="text-[9px] font-mono text-zinc-400 block">Access Uptime</span>
                  </div>
                </div>

                <div className="flex items-center gap-4 text-[10px] font-mono text-zinc-400 mt-3">
                  <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-brand-red" /> 24/7 Open
                  </span>
                  <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-zinc-700" /> Maintenance
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ================= 5. INTERACTIVE MEMBER PWA PASS & SCANNER DEMO ================= */}
        <section className="py-20 px-6 max-w-6xl mx-auto border-t border-zinc-900/60">
          <div className="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            <div className="md:col-span-6 space-y-4 text-left">
              <span className="text-xs font-semibold text-brand-red tracking-wide block">
                Athlete Companion Pass
              </span>
              <h2 className="font-heading text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                Real Turnstile Pass Experience<span className="text-brand-red">.</span>
              </h2>
              <p className="text-xs sm:text-sm text-zinc-400 leading-relaxed">
                Test the exact athlete experience. Instant scannable 2D QR matrix that works 100% offline at any optical turnstile or reception desk scanner.
              </p>
              <div className="pt-2 flex flex-wrap gap-3">
                <Link
                  href="/app"
                  className="h-11 px-6 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-brand-red/30 transition-transform active:scale-95"
                >
                  <i className="fa-solid fa-mobile-screen text-xs" />
                  <span>Launch Athlete PWA</span>
                </Link>
                <Link
                  href="/scan"
                  className="h-11 px-6 rounded-full bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-300 text-xs font-bold flex items-center gap-2 transition-colors"
                >
                  <i className="fa-solid fa-camera text-xs text-brand-red" />
                  <span>Test Gate Scanner</span>
                </Link>
              </div>
            </div>

            <div className="md:col-span-6 flex justify-center">
              <div className="w-full max-w-sm rounded-3xl bg-zinc-950 border border-brand-red/40 p-5 shadow-pass-glow backdrop-blur-xl relative overflow-hidden group">
                <div
                  className="h-64 rounded-2xl bg-cover bg-center relative overflow-hidden"
                  style={{ backgroundImage: `url('/images/4fc91843bcba0dc0451cadb72bbd2777.jpg')` }}
                >
                  <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />
                  <div className="absolute top-3 left-3 right-3 flex items-center justify-between text-[10px] font-semibold">
                    <span className="px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-md border border-zinc-800 text-brand-red font-mono">
                      PASS &bull; 2026-4
                    </span>
                    <span className="px-2.5 py-1 rounded-full bg-emerald-500/20 backdrop-blur-md border border-emerald-500/30 text-emerald-400 font-mono flex items-center gap-1">
                      <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                      Active
                    </span>
                  </div>

                  <div className="absolute bottom-3 left-3 right-3">
                    <div className="p-3 rounded-xl bg-black/85 backdrop-blur-md border border-zinc-800 flex items-center justify-between">
                      <div>
                        <span className="text-[10px] text-zinc-400 block font-mono">Moeez Ahmad</span>
                        <span className="text-xs font-bold text-white block">Strength &bull; Tier 1</span>
                      </div>
                      <span className="text-[11px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-full font-mono">
                        🔥 14-Day Streak
                      </span>
                    </div>
                  </div>
                </div>

                <div className="pt-4 flex items-center justify-between text-xs text-zinc-400">
                  <span className="flex items-center gap-1.5 font-mono text-[11px]">
                    <i className="fa-solid fa-bolt text-brand-red text-xs" />
                    Sub-second gate unlock
                  </span>
                  <Link
                    href="/app"
                    className="text-xs text-brand-red hover:underline font-semibold"
                  >
                    Open PWA Pass &rarr;
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </section>



        {/* ================= 6. FACILITIES & SPECIALIZED TRAINING DISCIPLINES ================= */}
        <section className="py-20 px-6 max-w-7xl mx-auto">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <span className="text-xs font-bold tracking-widest text-brand-red block mb-2">
              World-Class Training Sanctuary
            </span>
            <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
              Facilities &amp; Disciplines<span className="text-brand-red">.</span>
            </h2>
            <p className="text-xs sm:text-sm text-zinc-400 mt-2">
              Competition-grade iron, specialized athletic bays, and restorative contrast therapy.
            </p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {/* Tile 1 */}
            <div className="relative rounded-3xl overflow-hidden border border-zinc-900 group h-64 sm:h-72">
              <div
                className="absolute inset-0 bg-cover bg-center group-hover:scale-110 transition-transform duration-700"
                style={{ backgroundImage: `url('/images/5731fdc96ee11e2f0f0eb3ce394ac372.jpg')` }}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />
              <div className="absolute bottom-4 left-4 right-4 z-10">
                <span className="text-xs font-semibold text-brand-red block">
                  Zone 01
                </span>
                <h4 className="font-heading text-lg font-bold text-white tracking-tight">
                  Olympic Heavy Iron
                </h4>
                <p className="text-[10px] text-zinc-400 mt-0.5">Competition bumper plates &amp; calibrated racks</p>
              </div>
            </div>

            {/* Tile 2 */}
            <div className="relative rounded-3xl overflow-hidden border border-zinc-900 group h-64 sm:h-72">
              <div
                className="absolute inset-0 bg-cover bg-center group-hover:scale-110 transition-transform duration-700"
                style={{ backgroundImage: `url('/images/3a0b6f51dcf0a779bafcf757c6943e51.jpg')` }}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />
              <div className="absolute bottom-4 left-4 right-4 z-10">
                <span className="text-xs font-semibold text-brand-red block">
                  Zone 02
                </span>
                <h4 className="font-heading text-lg font-bold text-white tracking-tight">
                  Calibrated Dumbbells
                </h4>
                <p className="text-[10px] text-zinc-400 mt-0.5">Solid urethane pairs up to 60kg</p>
              </div>
            </div>

            {/* Tile 3 */}
            <div className="relative rounded-3xl overflow-hidden border border-zinc-900 group h-64 sm:h-72">
              <div
                className="absolute inset-0 bg-cover bg-center group-hover:scale-110 transition-transform duration-700"
                style={{ backgroundImage: `url('/images/5850549eb38c7367d272b6e65219bd0d.jpg')` }}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />
              <div className="absolute bottom-4 left-4 right-4 z-10">
                <span className="text-xs font-semibold text-brand-red block">
                  Zone 03
                </span>
                <h4 className="font-heading text-lg font-bold text-white tracking-tight">
                  Hammer Strength Bay
                </h4>
                <p className="text-[10px] text-zinc-400 mt-0.5">Iso-lateral plate-loaded hypertrophy circuit</p>
              </div>
            </div>

            {/* Tile 4 */}
            <div className="relative rounded-3xl overflow-hidden border border-zinc-900 group h-64 sm:h-72">
              <div
                className="absolute inset-0 bg-cover bg-center group-hover:scale-110 transition-transform duration-700"
                style={{ backgroundImage: `url('/images/006db77edf3f68260cdaab3daf9a5afb.jpg')` }}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />
              <div className="absolute bottom-4 left-4 right-4 z-10">
                <span className="text-xs font-semibold text-emerald-400 block">
                  Zone 04
                </span>
                <h4 className="font-heading text-lg font-bold text-white tracking-tight">
                  Compound Leg Press
                </h4>
                <p className="text-[10px] text-zinc-400 mt-0.5">45-degree linear roller platform</p>
              </div>
            </div>
          </div>
        </section>

        {/* ================= 7. MEMBERSHIP TIERS IN PAKISTANI RUPEE (PKR) ================= */}
        <section className="py-24 px-6 max-w-7xl mx-auto">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <span className="text-xs font-bold tracking-widest text-brand-red block mb-2">
              Transparent Athletic Packages
            </span>
            <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
              Membership Plans (PKR)<span className="text-brand-red">.</span>
            </h2>
            <p className="text-xs text-zinc-400 mt-2">
              Strict 10th-of-the-month cutoff policy. Zero surprise fees. Transparent Pakistani Rupee pricing.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            {/* 1 Month Starter */}
            <div className="p-8 rounded-3xl bg-zinc-950 border border-zinc-900 flex flex-col justify-between hover:border-zinc-800 transition-all">
              <div>
                <span className="text-xs font-medium text-zinc-400 block mb-2">
                  Standard Monthly
                </span>
                <h3 className="font-heading text-2xl font-bold text-white mb-1 tracking-tight">
                  1-Month Starter
                </h3>
                <div className="font-heading text-4xl font-extrabold text-white mb-6 font-mono">
                  ₨ 8,500 <span className="text-xs text-zinc-400 font-sans font-normal">/ month</span>
                </div>
                <ul className="space-y-3 text-xs text-zinc-400 mb-8">
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>24/7 Turnstile Gate Pass (Smartphone QR)</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Full Gym Floor &amp; Heavy Iron Access</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Workout Streak &amp; Attendance Tracking</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Locker &amp; Shower Access</span>
                  </li>
                  <li className="flex items-center gap-2 text-zinc-600">
                    <i className="fa-solid fa-xmark" />
                    <span>Sauna &amp; Cold Plunge Excluded</span>
                  </li>
                </ul>
              </div>
              <Link
                href="/admission"
                className="w-full py-3 rounded-full bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold text-center border border-zinc-800 transition-all block"
              >
                Apply for 1 Month
              </Link>
            </div>

            {/* 3 Months Pro Athlete (Most Popular) */}
            <div className="p-8 rounded-3xl bg-zinc-950 border-2 border-brand-red flex flex-col justify-between relative shadow-pass-glow">
              <span className="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-brand-red text-white text-[10px] font-semibold tracking-wider shadow-md">
                Most Popular &bull; Save PKR 3,000
              </span>
              <div>
                <span className="text-xs font-medium text-brand-red block mb-2">
                  Best Value Quarter
                </span>
                <h3 className="font-heading text-2xl font-bold text-white mb-1 tracking-tight">
                  3-Month Pro Athlete
                </h3>
                <div className="font-heading text-4xl font-extrabold text-brand-red mb-6 font-mono">
                  ₨ 22,500 <span className="text-xs text-zinc-400 font-sans font-normal">/ quarter</span>
                </div>
                <ul className="space-y-3 text-xs text-zinc-300 mb-8">
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Full Gym &amp; Specialized Bays Access</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Finnish Sauna &amp; Cold Plunge Included</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>1 Free Certified Coach Body Assessment</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>1-Tap Direct Coach WhatsApp Bridge</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Priority Turnstile Clearance</span>
                  </li>
                </ul>
              </div>
              <Link
                href="/admission"
                className="w-full py-3 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold text-center transition-all shadow-lg shadow-brand-red/30 block"
              >
                Apply for 3 Months
              </Link>
            </div>

            {/* 6 Months Elite */}
            <div className="p-8 rounded-3xl bg-zinc-950 border border-zinc-900 flex flex-col justify-between hover:border-zinc-800 transition-all">
              <div>
                <span className="text-xs font-medium text-zinc-400 block mb-2">
                  VIP Semi-Annual
                </span>
                <h3 className="font-heading text-2xl font-bold text-white mb-1 tracking-tight">
                  6-Month Elite
                </h3>
                <div className="font-heading text-4xl font-extrabold text-white mb-6 font-mono">
                  ₨ 42,000 <span className="text-xs text-zinc-400 font-sans font-normal">/ 6 months</span>
                </div>
                <ul className="space-y-3 text-xs text-zinc-400 mb-8">
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>All Perks Included in 3 Months</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Unlimited Thermal Sauna &amp; Ice Immersion</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>2 Complimentary Guest Passes Every Month</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Dedicated Permanent Locker Box</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Quarterly DEXA Body Fat Composition Scan</span>
                  </li>
                </ul>
              </div>
              <Link
                href="/admission"
                className="w-full py-3 rounded-full bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold text-center border border-zinc-800 transition-all block"
              >
                Apply for 6 Months
              </Link>
            </div>
          </div>
        </section>

        {/* ================= 8. INTERACTIVE LEAD CAPTURE & INQUIRY FORM ================= */}
        <section className="py-20 px-6 max-w-7xl mx-auto">
          <div className="p-8 sm:p-12 rounded-3xl bg-zinc-950 border border-zinc-900 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            {/* Form */}
            <div className="space-y-6">
              <span className="text-xs font-bold tracking-widest text-brand-red block">
                Visit Our Facility
              </span>
              <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
                Schedule A Demo &amp; 1-Day Pass<span className="text-brand-red">.</span>
              </h2>
              <p className="text-xs text-zinc-400">
                Tour our gym floor, test our optical turnstile gates, and consult with our certified coaches.
              </p>

              {inquirySubmitted ? (
                <div className="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-800 text-emerald-300 text-xs flex items-center gap-3">
                  <i className="fa-solid fa-circle-check text-base" />
                  <span>Thank you! Your inquiry has been received. Our team will contact you via WhatsApp shortly.</span>
                </div>
              ) : (
                <form onSubmit={handleInquirySubmit} className="space-y-3 text-xs">
                  <input
                    type="text"
                    required
                    value={inquiryName}
                    onChange={(e) => setInquiryName(e.target.value)}
                    placeholder="Your Full Name"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red transition-colors"
                  />
                  <input
                    type="email"
                    required
                    value={inquiryEmail}
                    onChange={(e) => setInquiryEmail(e.target.value)}
                    placeholder="Your Email Address"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red transition-colors"
                  />
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input
                      type="tel"
                      value={inquiryPhone}
                      onChange={(e) => setInquiryPhone(e.target.value)}
                      placeholder="WhatsApp (+92 325 9491349)"
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-red font-mono transition-colors"
                    />
                    <select
                      value={inquiryProgram}
                      onChange={(e) => setInquiryProgram(e.target.value)}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red transition-colors"
                    >
                      <option value="Strength & Conditioning">Strength &amp; Conditioning</option>
                      <option value="Bodybuilding & Hypertrophy">Bodybuilding &amp; Hypertrophy</option>
                      <option value="HIIT & Boxing">HIIT &amp; Boxing</option>
                      <option value="Thermal Recovery & Mobility">Thermal Recovery &amp; Mobility</option>
                    </select>
                  </div>

                  <button
                    type="submit"
                    className="w-full py-3.5 rounded-full bg-brand-red hover:bg-brand-red-hover text-white font-semibold text-sm shadow-lg shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
                  >
                    <span>Request Free 1-Day Pass</span>
                    <i className="fa-solid fa-arrow-right text-xs" />
                  </button>
                </form>
              )}
            </div>

            {/* Location & Real Image Card */}
            <div className="rounded-3xl bg-zinc-900 border border-zinc-800 p-8 space-y-5 relative overflow-hidden">
              <div
                className="absolute inset-0 bg-cover bg-center opacity-25 filter contrast-125"
                style={{ backgroundImage: `url('/images/5850549eb38c7367d272b6e65219bd0d.jpg')` }}
              />

              <div className="relative z-10 space-y-4">
                <span className="text-[10px] font-mono font-medium text-brand-red tracking-wider block">
                  Flagship Headquarters
                </span>
                <h3 className="font-heading text-2xl font-bold text-white tracking-tight">
                  Metropolis Boulevard
                </h3>
                <p className="text-xs text-zinc-300 leading-relaxed">
                  Plot 42-C, Commercial Sector 4, Metropolis Boulevard, Islamabad, Pakistan.
                </p>

                <div className="pt-2 space-y-1.5 text-xs text-zinc-300">
                  <div>
                    <span className="text-zinc-400 font-medium">Hours:</span> Mon - Fri 05:00 AM - 11:00 PM
                  </div>
                  <div>
                    <span className="text-zinc-400 font-medium">Weekends:</span> Sat - Sun 07:00 AM - 09:00 PM
                  </div>
                  <div>
                    <span className="text-zinc-400 font-medium">Direct Desk:</span> +92 325 9491349
                  </div>
                </div>

                <div className="pt-3 flex flex-wrap items-center gap-3">
                  <a
                    href="https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20would%20like%20to%20visit%20the%20gym."
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/25 text-xs font-bold transition-all"
                  >
                    <i className="fa-brands fa-whatsapp text-sm" />
                    <span>WhatsApp Front Desk</span>
                  </a>
                  <Link
                    href="/admission"
                    className="inline-flex items-center gap-1.5 text-xs font-bold text-zinc-400 hover:text-white"
                  >
                    <span>Online Admission Slip</span>
                    <i className="fa-solid fa-arrow-right text-[10px]" />
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ================= 9. NEWSLETTER SUBSCRIPTION BAR ================= */}
        <section className="border-t border-zinc-900 bg-black py-12 px-6">
          <div className="max-w-4xl mx-auto text-center space-y-4">
            <h3 className="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Subscribe To GymFlow Intelligence<span className="text-brand-red">.</span>
            </h3>
            <p className="text-xs text-zinc-400 max-w-md mx-auto">
              Receive periodized training protocols, nutrition blueprints, and announcements straight to your inbox.
            </p>

            {newsletterSuccess ? (
              <div className="p-3 max-w-md mx-auto rounded-xl bg-emerald-950/60 border border-emerald-800 text-emerald-300 text-xs font-bold">
                ✓ You have successfully subscribed to the GymFlow newsletter!
              </div>
            ) : (
              <form onSubmit={handleNewsletterSubmit} className="flex flex-col sm:flex-row gap-2 max-w-md mx-auto pt-2">
                <input
                  type="email"
                  required
                  value={newsletterEmail}
                  onChange={(e) => setNewsletterEmail(e.target.value)}
                  placeholder="Enter your email address..."
                  className="flex-1 bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-xs text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                />
                <button
                  type="submit"
                  className="px-6 py-3 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold transition-all shadow-md shadow-brand-red/30"
                >
                  Subscribe
                </button>
              </form>
            )}
          </div>
        </section>
      </main>

      <PublicFooter />
    </div>
  );
}
