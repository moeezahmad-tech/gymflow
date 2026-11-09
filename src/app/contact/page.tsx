'use client';

import { useState } from 'react';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

export default function ContactPage() {
  const [step, setStep] = useState<1 | 2>(1);
  const [submitted, setSubmitted] = useState(false);
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    phone: '',
    program: 'General Membership Inquiry',
    message: '',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});
  const [errorBanner, setErrorBanner] = useState<string | null>(null);

  const validateStep1 = () => {
    const errs: Record<string, string> = {};

    if (!formData.fullName.trim() || formData.fullName.trim().length < 2) {
      errs.fullName = 'Please enter your full name.';
    }

    if (!formData.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email.trim())) {
      errs.email = 'Please enter a valid email address.';
    }

    const cleanPhone = formData.phone.replace(/[^0-9]/g, '');
    if (!cleanPhone || cleanPhone.length < 9) {
      errs.phone = 'Please enter a valid WhatsApp mobile number (e.g. +92 325 9491349).';
    }

    setErrors(errs);

    if (Object.keys(errs).length > 0) {
      setErrorBanner('Please fill in all required fields in Step 1 before proceeding to the last step.');
      return false;
    }

    setErrorBanner(null);
    return true;
  };

  const handleNext = () => {
    if (!validateStep1()) return;
    setStep(2);
  };

  const handleStepClick = (targetStep: 1 | 2) => {
    if (targetStep === 2) {
      if (!validateStep1()) return;
    }
    setStep(targetStep);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!validateStep1()) {
      setStep(1);
      return;
    }
    if (!formData.message.trim()) {
      setErrors((prev) => ({ ...prev, message: 'Please write your message or fitness question.' }));
      return;
    }
    setSubmitted(true);
  };

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
              We Are Here For You &bull; 24/7 Support
            </span>
            <h1 className="font-heading text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight tracking-tight">
              Get In Touch<span className="text-brand-red">.</span>
            </h1>
            <p className="text-zinc-300 text-sm sm:text-base leading-relaxed max-w-xl mx-auto">
              Have questions about memberships, private coaching, or group workouts? Drop by our Metropolis flagship
              or connect with our reception desk via WhatsApp.
            </p>
          </div>
        </section>

        {/* ================= 2. TWO-COLUMN CONTACT & LOCATION SECTION ================= */}
        <section className="py-24 px-6 max-w-7xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            {/* Left: Contact Form (7 cols) */}
            <div className="lg:col-span-7 rounded-3xl bg-zinc-950 border border-zinc-900 p-8 sm:p-10 space-y-6 shadow-xl">
              <div>
                <span className="text-xs font-semibold text-brand-red tracking-wide block mb-1">
                  Direct Line
                </span>
                <h2 className="font-heading text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                  Send Us A Message<span className="text-brand-red">.</span>
                </h2>
                <p className="text-xs text-zinc-400 mt-1">
                  Our front-desk team responds within 2 hours during operational times.
                </p>
              </div>

              {/* 2-Step Progress Indicator */}
              {!submitted && (
                <div className="grid grid-cols-2 gap-3 text-xs pt-1">
                  <button
                    type="button"
                    onClick={() => handleStepClick(1)}
                    className={`p-3 rounded-2xl border text-left flex items-center gap-3 transition-all ${
                      step === 1
                        ? 'bg-zinc-900/90 border-brand-red text-white shadow-pass-glow'
                        : 'bg-zinc-950 border-zinc-900 text-zinc-400 hover:text-white'
                    }`}
                  >
                    <div
                      className={`w-7 h-7 rounded-xl flex items-center justify-center font-bold text-xs ${
                        step === 1 ? 'bg-brand-red text-white' : 'bg-emerald-500/20 text-emerald-400'
                      }`}
                    >
                      {step === 2 ? <i className="fa-solid fa-check text-[10px]" /> : '1'}
                    </div>
                    <div>
                      <span className="text-[10px] text-zinc-500 block font-semibold uppercase tracking-wider">
                        Step 01
                      </span>
                      <span className="text-xs font-bold text-white block">Contact Information</span>
                    </div>
                  </button>

                  <button
                    type="button"
                    onClick={() => handleStepClick(2)}
                    className={`p-3 rounded-2xl border text-left flex items-center gap-3 transition-all ${
                      step === 2
                        ? 'bg-zinc-900/90 border-brand-red text-white shadow-pass-glow'
                        : 'bg-zinc-950 border-zinc-900 text-zinc-500 hover:text-zinc-300'
                    }`}
                  >
                    <div
                      className={`w-7 h-7 rounded-xl flex items-center justify-center font-bold text-xs ${
                        step === 2 ? 'bg-brand-red text-white' : 'bg-zinc-900 text-zinc-500'
                      }`}
                    >
                      2
                    </div>
                    <div>
                      <span className="text-[10px] text-zinc-500 block font-semibold uppercase tracking-wider">
                        Step 02 (Final)
                      </span>
                      <span className="text-xs font-bold text-white block">Inquiry &amp; Message</span>
                    </div>
                  </button>
                </div>
              )}

              {/* Validation Alert Banner */}
              {errorBanner && (
                <div className="p-4 rounded-2xl bg-brand-red/10 border border-brand-red/40 text-brand-red text-xs flex items-center justify-between animate-fadeIn">
                  <div className="flex items-center gap-3">
                    <i className="fa-solid fa-triangle-exclamation text-base shrink-0" />
                    <span>{errorBanner}</span>
                  </div>
                  <button
                    type="button"
                    onClick={() => setErrorBanner(null)}
                    className="text-zinc-400 hover:text-white px-2 py-0.5 text-xs"
                  >
                    &times;
                  </button>
                </div>
              )}

              {submitted ? (
                <div className="p-8 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 space-y-3 text-center animate-fadeIn">
                  <div className="w-14 h-14 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl mx-auto">
                    <i className="fa-solid fa-check" />
                  </div>
                  <h3 className="font-heading text-2xl font-bold text-white">
                    Message Dispatched Successfully!
                  </h3>
                  <p className="text-xs text-emerald-300/80 max-w-sm mx-auto">
                    Thank you, <span className="font-bold text-white">{formData.fullName}</span>. An athlete advisor has received your request and will reach out via WhatsApp shortly.
                  </p>
                  <button
                    onClick={() => {
                      setSubmitted(false);
                      setStep(1);
                      setFormData({
                        fullName: '',
                        email: '',
                        phone: '',
                        program: 'General Membership Inquiry',
                        message: '',
                      });
                      setErrors({});
                      setErrorBanner(null);
                    }}
                    className="mt-4 px-6 py-2.5 rounded-full bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold transition-colors"
                  >
                    Send Another Message
                  </button>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="space-y-4 text-xs">
                  {/* STEP 1: CONTACT INFORMATION */}
                  {step === 1 && (
                    <div className="space-y-4 animate-fadeIn">
                      <div>
                        <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                          Full Name *
                        </label>
                        <input
                          type="text"
                          required
                          value={formData.fullName}
                          onChange={(e) => {
                            setFormData({ ...formData, fullName: e.target.value });
                            if (errors.fullName) setErrors((prev) => ({ ...prev, fullName: '' }));
                          }}
                          placeholder="e.g. Tariq Mehmood"
                          className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                            errors.fullName
                              ? 'border-brand-red bg-brand-red/5'
                              : 'border-zinc-800 focus:border-brand-red'
                          }`}
                        />
                        {errors.fullName && (
                          <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                            {errors.fullName}
                          </span>
                        )}
                      </div>

                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                          <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                            Email Address *
                          </label>
                          <input
                            type="email"
                            required
                            value={formData.email}
                            onChange={(e) => {
                              setFormData({ ...formData, email: e.target.value });
                              if (errors.email) setErrors((prev) => ({ ...prev, email: '' }));
                            }}
                            placeholder="athlete@gymflow.com"
                            className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                              errors.email
                                ? 'border-brand-red bg-brand-red/5'
                                : 'border-zinc-800 focus:border-brand-red'
                            }`}
                          />
                          {errors.email && (
                            <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                              {errors.email}
                            </span>
                          )}
                        </div>

                        <div>
                          <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                            WhatsApp Number *
                          </label>
                          <input
                            type="tel"
                            required
                            value={formData.phone}
                            onChange={(e) => {
                              setFormData({ ...formData, phone: e.target.value });
                              if (errors.phone) setErrors((prev) => ({ ...prev, phone: '' }));
                            }}
                            placeholder="+92 325 9491349"
                            className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none font-mono transition-colors ${
                              errors.phone
                                ? 'border-brand-red bg-brand-red/5'
                                : 'border-zinc-800 focus:border-brand-red'
                            }`}
                          />
                          {errors.phone && (
                            <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                              {errors.phone}
                            </span>
                          )}
                        </div>
                      </div>

                      <div className="pt-2">
                        <button
                          type="button"
                          onClick={handleNext}
                          className="w-full h-12 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
                        >
                          <span>Proceed to Step 2: Message &amp; Inquiry</span>
                          <i className="fa-solid fa-arrow-right text-[10px]" />
                        </button>
                      </div>
                    </div>
                  )}

                  {/* STEP 2: INQUIRY TOPIC & MESSAGE (THE LAST ONE) */}
                  {step === 2 && (
                    <div className="space-y-4 animate-fadeIn">
                      <div className="p-3 rounded-xl bg-black border border-zinc-900 flex items-center justify-between text-xs">
                        <div>
                          <span className="text-[10px] text-zinc-500 block">Sender Info:</span>
                          <span className="font-bold text-white">
                            {formData.fullName} ({formData.phone})
                          </span>
                        </div>
                        <button
                          type="button"
                          onClick={() => setStep(1)}
                          className="text-[11px] text-brand-red hover:underline font-semibold"
                        >
                          Edit Step 1 &rarr;
                        </button>
                      </div>

                      <div>
                        <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                          Inquiry Topic
                        </label>
                        <select
                          value={formData.program}
                          onChange={(e) => setFormData({ ...formData, program: e.target.value })}
                          className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                        >
                          <option value="General Membership Inquiry">General Membership Inquiry</option>
                          <option value="1 Month Package (PKR 8,500)">1 Month Package (PKR 8,500)</option>
                          <option value="3 Month Package (PKR 22,500)">3 Month Package (PKR 22,500)</option>
                          <option value="6 Month Package (PKR 42,000)">6 Month Package (PKR 42,000)</option>
                          <option value="Free 1-Day Trial Pass">Free 1-Day Trial Pass</option>
                          <option value="Private Coach Consultation">Private Coach Consultation</option>
                        </select>
                      </div>

                      <div>
                        <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                          Your Message or Fitness Goal *
                        </label>
                        <textarea
                          rows={4}
                          required
                          value={formData.message}
                          onChange={(e) => {
                            setFormData({ ...formData, message: e.target.value });
                            if (errors.message) setErrors((prev) => ({ ...prev, message: '' }));
                          }}
                          placeholder="Tell us about your background, training targets, or questions..."
                          className={`w-full bg-black border rounded-xl p-3 text-white placeholder-zinc-600 focus:outline-none resize-none transition-colors ${
                            errors.message
                              ? 'border-brand-red bg-brand-red/5'
                              : 'border-zinc-800 focus:border-brand-red'
                          }`}
                        />
                        {errors.message && (
                          <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                            {errors.message}
                          </span>
                        )}
                      </div>

                      <div className="flex items-center gap-3 pt-2">
                        <button
                          type="button"
                          onClick={() => setStep(1)}
                          className="w-1/3 h-12 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-semibold border border-zinc-800 transition-colors flex items-center justify-center gap-1.5"
                        >
                          <i className="fa-solid fa-arrow-left text-[10px]" />
                          <span>Back</span>
                        </button>

                        <button
                          type="submit"
                          className="w-2/3 h-12 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
                        >
                          <span>Send Message to Reception</span>
                          <i className="fa-solid fa-paper-plane text-[10px]" />
                        </button>
                      </div>
                    </div>
                  )}
                </form>
              )}
            </div>

            {/* Right: Metropolis HQ Card with Real Photos (5 cols) */}
            <div className="lg:col-span-5 space-y-6">
              {/* Flagship Photo Card */}
              <div className="rounded-3xl overflow-hidden bg-zinc-950 border border-zinc-900 shadow-xl group">
                <div
                  className="h-56 bg-cover bg-center group-hover:scale-105 transition-transform duration-700 relative"
                  style={{ backgroundImage: `url('/images/5850549eb38c7367d272b6e65219bd0d.jpg')` }}
                >
                  <div className="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-transparent" />
                  <span className="absolute top-4 left-4 px-3 py-1 rounded-full bg-black/80 backdrop-blur-md border border-zinc-800 text-[10px] font-semibold text-brand-red">
                    Flagship Facility
                  </span>
                </div>

                <div className="p-6 space-y-4 -mt-4 relative z-10">
                  <div>
                    <span className="text-xs font-medium text-zinc-500 block">
                      Physical Location
                    </span>
                    <h3 className="font-heading text-2xl font-bold text-white tracking-tight">
                      Metropolis Boulevard
                    </h3>
                    <p className="text-xs text-zinc-400 mt-1">
                      Plot 42-C, Commercial Sector 4, Metropolis Boulevard, Islamabad, Pakistan.
                    </p>
                  </div>

                  <div className="pt-2 border-t border-zinc-900 space-y-2 text-xs text-zinc-300">
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Mon - Fri Hours:</span>
                      <span className="font-bold text-white">05:00 AM - 11:00 PM</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Weekend Hours:</span>
                      <span className="font-bold text-white">07:00 AM - 09:00 PM</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-zinc-500">Pass Holders:</span>
                      <span className="font-bold text-emerald-400">24/7 Turnstile Clearance</span>
                    </div>
                  </div>

                  <div className="pt-3 border-t border-zinc-900 space-y-2">
                    <a
                      href="https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20have%20an%20inquiry%20regarding%20membership."
                      target="_blank"
                      rel="noopener noreferrer"
                      className="w-full h-11 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition-all"
                    >
                      <i className="fa-brands fa-whatsapp text-sm" />
                      <span>Instant WhatsApp Chat</span>
                    </a>

                    <a
                      href="tel:+923259491349"
                      className="w-full h-11 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white font-semibold text-xs flex items-center justify-center gap-2 border border-zinc-800 transition-colors"
                    >
                      <i className="fa-solid fa-phone text-xs" />
                      <span>Call +92 325 9491349</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>

      <PublicFooter />
    </div>
  );
}
