'use client';

import { useState } from 'react';
import Link from 'next/link';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

export default function PricingPage() {
  const [openFaq, setOpenFaq] = useState<number | null>(0);

  const faqs = [
    {
      q: 'What payment methods are accepted in Pakistan (PKR)?',
      a: 'We accept Cash directly at the front reception counter, Pakistani digital mobile wallets including JazzCash and EasyPaisa, as well as direct IBFT online bank transfers. Receipts and digital invoices are issued instantaneously to your member app.',
    },
    {
      q: 'What is the strict 10th-of-the-month cutoff policy?',
      a: 'GymFlow subscriptions align with a standardized 10th-day billing cycle. Members renewing before or on the 10th enjoy uninterrupted turnstile gate clearance. Unpaid passes receive automated grace period notices and lock turnstile access starting the 11th.',
    },
    {
      q: 'How do the multi-month packages save money?',
      a: 'The 3-Month package reduces your effective rate to ₨ 7,500/month (saving PKR 3,000), while unlocking complimentary access to our Finnish sauna and cold plunge suites. The 6-Month package delivers our highest value at ₨ 7,000/month (saving PKR 9,000) plus 2 free guest passes per month.',
    },
    {
      q: 'Can I try the gym before committing?',
      a: 'Absolutely. We offer a 100% Free 1-Day Trial Pass for prospective athletes. You can tour our weight floor, test the Eleiko competition bars, and try our recovery lounge with zero obligation to purchase.',
    },
    {
      q: 'Are group workout classes included?',
      a: 'Yes, all active 3-Month and 6-Month memberships include unlimited reservations for our scheduled group training classes (HIIT Combat, Powerlifting Clinics, and Functional Mobility).',
    },
  ];

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1">
        {/* ================= 1. CINEMATIC HERO BANNER ================= */}
        <section className="relative pt-28 pb-20 px-6 text-center border-b border-zinc-900 overflow-hidden">
          <div
            className="absolute inset-0 bg-cover bg-center filter grayscale contrast-125 opacity-25 scale-105"
            style={{ backgroundImage: `url('/images/3844e94a9c5f8420999736d4aa364aac.jpg')` }}
          />
          <div className="absolute inset-0 bg-fluted-dark opacity-40 mix-blend-multiply pointer-events-none" />
          <div className="absolute inset-0 bg-gradient-to-b from-black via-black/85 to-black pointer-events-none" />
          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-brand-red/15 rounded-full blur-[140px] pointer-events-none" />

          <div className="max-w-4xl mx-auto relative z-10 space-y-4">
            <span className="inline-block text-[10px] font-bold tracking-widest text-brand-red px-3.5 py-1.5 rounded-full bg-brand-red/10 border border-brand-red/30">
              Transparent Value &bull; No Hidden Contracts
            </span>
            <h1 className="font-heading text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight tracking-tight">
              Membership Tiers<span className="text-brand-red">.</span>
            </h1>
            <p className="text-zinc-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
              No hidden initiation fees. No predatory financing. Just world-class training access, calibrated iron,
              and automated turnstile gate privileges.
            </p>

            <div className="pt-2 flex flex-wrap items-center justify-center gap-3">
              <Link
                href="/contact"
                className="h-12 px-7 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold shadow-md shadow-brand-red/30 transition-all flex items-center gap-2"
              >
                <span>Claim Free 1-Day Pass</span>
                <i className="fa-solid fa-arrow-right text-[10px]" />
              </Link>
              <a
                href="#plans"
                className="h-12 px-7 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white text-xs font-semibold border border-zinc-800 transition-all flex items-center"
              >
                Compare Packages
              </a>
            </div>
          </div>
        </section>

        {/* ================= 2. MEMBERSHIP TIERS (PKR 8,500 / 22,500 / 42,000) ================= */}
        <section id="plans" className="py-24 px-6 max-w-7xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            {/* 1 Month */}
            <div className="p-8 rounded-3xl bg-zinc-950 border border-zinc-900 flex flex-col justify-between hover:border-zinc-800 transition-all shadow-xl">
              <div>
                <span className="text-xs font-medium text-zinc-400 block mb-2">
                  Standard Monthly
                </span>
                <h3 className="font-heading text-2xl font-bold text-white mb-1 tracking-tight">
                  1-Month Starter
                </h3>
                <div className="font-heading text-4xl font-extrabold text-white mb-6 font-mono">
                  ₨ 8,500 <span className="text-xs text-zinc-500 font-sans">/ month</span>
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
                    <span>Locker &amp; Shower Amenities</span>
                  </li>
                  <li className="flex items-center gap-2 text-zinc-600">
                    <i className="fa-solid fa-xmark" />
                    <span>Sauna &amp; Cold Plunge Excluded</span>
                  </li>
                </ul>
              </div>
              <Link
                href="/admission"
                className="w-full py-3.5 rounded-full bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold text-center border border-zinc-800 transition-all block"
              >
                Apply for 1 Month
              </Link>
            </div>

            {/* 3 Months (Most Popular) */}
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
                  ₨ 22,500 <span className="text-xs text-zinc-500 font-sans">/ quarter</span>
                </div>
                <ul className="space-y-3 text-xs text-zinc-300 mb-8">
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Full Facility &amp; Specialized Equipment Access</span>
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
                    <span>Priority Turnstile Access Code</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>1-Tap Coach WhatsApp Consultation</span>
                  </li>
                </ul>
              </div>
              <Link
                href="/admission"
                className="w-full py-3.5 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold text-center transition-all shadow-lg shadow-brand-red/30 block"
              >
                Apply for 3 Months
              </Link>
            </div>

            {/* 6 Months */}
            <div className="p-8 rounded-3xl bg-zinc-950 border border-zinc-900 flex flex-col justify-between hover:border-zinc-800 transition-all shadow-xl">
              <div>
                <span className="text-xs font-medium text-zinc-400 block mb-2">
                  VIP Semi-Annual
                </span>
                <h3 className="font-heading text-2xl font-bold text-white mb-1 tracking-tight">
                  6-Month Elite
                </h3>
                <div className="font-heading text-4xl font-extrabold text-white mb-6 font-mono">
                  ₨ 42,000 <span className="text-xs text-zinc-500 font-sans">/ 6 months</span>
                </div>
                <ul className="space-y-3 text-xs text-zinc-400 mb-8">
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>All Perks Included in 3 Months</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Unlimited Thermal Sauna &amp; Cold Immersion</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>2 Complimentary Guest Passes Every Month</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Dedicated Permanent Locker Allocation</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-brand-red" />
                    <span>Quarterly DEXA Body Fat Composition Scan</span>
                  </li>
                </ul>
              </div>
              <Link
                href="/admission"
                className="w-full py-3.5 rounded-full bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold text-center border border-zinc-800 transition-all block"
              >
                Apply for 6 Months
              </Link>
            </div>
          </div>
        </section>

        {/* ================= 3. 10TH-CUTOFF POLICY CALLOUT ================= */}
        <section className="py-12 px-6 max-w-5xl mx-auto">
          <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-brand-red/30 flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="flex items-center gap-4">
              <div className="w-12 h-12 rounded-2xl bg-brand-red/10 border border-brand-red/30 flex items-center justify-center text-brand-red text-xl shrink-0">
                <i className="fa-solid fa-shield-halved" />
              </div>
              <div>
                <h4 className="text-sm font-bold text-white tracking-wide">
                  Automated 10th-Day Billing Cutoff Policy
                </h4>
                <p className="text-xs text-zinc-400 mt-1 leading-relaxed">
                  All memberships adhere to GymFlow&apos;s standard 10th-of-the-month cycle. Invoices are dispatched on the 1st,
                  with grace clearance extending through midnight of the 10th. EasyPaisa, JazzCash, and Cash at Front Desk are all supported.
                </p>
              </div>
            </div>

            <Link
              href="/contact"
              className="px-5 py-2.5 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-200 text-xs font-semibold border border-zinc-800 shrink-0 flex items-center gap-1.5"
            >
              <span>Have Questions?</span>
              <i className="fa-solid fa-arrow-right text-[10px]" />
            </Link>
          </div>
        </section>

        {/* ================= 4. FREQUENTLY ASKED QUESTIONS ================= */}
        <section className="py-20 px-6 bg-dark-900 border-t border-zinc-900">
          <div className="max-w-4xl mx-auto">
            <div className="text-center mb-12">
              <span className="text-xs font-semibold text-brand-red tracking-wide block mb-2">
                Clear Answers
              </span>
              <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
                Frequently Asked Questions<span className="text-brand-red">.</span>
              </h2>
            </div>

            <div className="space-y-3">
              {faqs.map((faq, idx) => (
                <div
                  key={idx}
                  className="rounded-2xl bg-zinc-950 border border-zinc-900 overflow-hidden transition-all"
                >
                  <button
                    onClick={() => setOpenFaq(openFaq === idx ? null : idx)}
                    className="w-full p-5 text-left flex items-center justify-between gap-4 text-xs font-semibold text-white hover:text-brand-red"
                  >
                    <span>{faq.q}</span>
                    <i
                      className={`fa-solid fa-chevron-down text-zinc-500 text-xs transition-transform ${
                        openFaq === idx ? 'rotate-180 text-brand-red' : ''
                      }`}
                    />
                  </button>
                  {openFaq === idx && (
                    <div className="px-5 pb-5 pt-1 text-xs text-zinc-400 leading-relaxed border-t border-zinc-900/60 animate-fadeIn">
                      {faq.a}
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        </section>
      </main>

      <PublicFooter />
    </div>
  );
}
