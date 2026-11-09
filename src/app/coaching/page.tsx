'use client';

import { useState, useEffect, Suspense } from 'react';
import Link from 'next/link';
import { useSearchParams } from 'next/navigation';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

const coaches = [
  {
    id: 'marcus-vance',
    name: 'Coach Marcus Vance',
    role: 'Head of Hypertrophy & Powerlifting',
    experience: '12+ Years Experience • CSCS Certified',
    specialty: 'Compound Movement Biomechanics & RPE Wave Loading',
    bio: 'Former competitive powerlifter and national hypertrophy consultant. Marcus coaches athletes through kinetic bar path analysis, progressive overload wave mechanics, and safe neurological failure protocols.',
    image: '/images/5731fdc96ee11e2f0f0eb3ce394ac372.jpg',
    slots: '2 Slots Open for February',
    badge: 'Strength Lead',
  },
  {
    id: 'sarah-jenkins',
    name: 'Coach Sarah Jenkins',
    role: 'Olympic Weightlifting & Combat Prep',
    experience: '9+ Years Experience • USAW L2 / Boxing Specialist',
    specialty: 'Snatch/Clean Dynamics, Rotational Core & Ring Conditioning',
    bio: 'National weightlifting medalist specializing in explosive kinetic transfer, combat striking torque, and joint-friendly high-velocity athletic conditioning.',
    image: '/images/4fc91843bcba0dc0451cadb72bbd2777.jpg',
    slots: '1 Slot Open for February',
    badge: 'Olympic & Combat',
  },
  {
    id: 'david-cole',
    name: 'Coach David Cole',
    role: 'Conditioning & Body Recomposition',
    experience: '8+ Years Experience • EXOS Performance Specialist',
    specialty: 'VO2 Max Conditioning, Heart-Rate Periodization & Mobility',
    bio: 'David combines heart-rate interval training on Concept2 rowers and Rogue Echo bikes with functional hypertrophy accessory splits to shed visceral fat and build engine capacity.',
    image: '/images/006db77edf3f68260cdaab3daf9a5afb.jpg',
    slots: '3 Slots Open for February',
    badge: 'Metabolic Engine',
  },
  {
    id: 'amina-tariq',
    name: 'Lead Nutritionist Amina Tariq',
    role: 'Clinical Sports Nutrition & Hormonal Blueprinting',
    experience: '7+ Years Experience • ISSN Sports Dietitian',
    specialty: 'Micronutrient Partitioning, Intra-Workout Fueling & Body Fat Drops',
    bio: 'Amina customizes calorie partitioning and hydration timing tailored to your daily training volume, accelerating recovery without severe metabolic slowdown.',
    image: '/images/3a0b6f51dcf0a779bafcf757c6943e51.jpg',
    slots: '4 Slots Open for February',
    badge: 'Sports Nutrition',
  },
];

function CoachingFormContent() {
  const searchParams = useSearchParams();
  const coachParam = searchParams.get('coach');

  // Form Fields
  const [fullName, setFullName] = useState('');
  const [phone, setPhone] = useState('+92 ');
  const [email, setEmail] = useState('');
  const [memberId, setMemberId] = useState('');
  const [selectedCoach, setSelectedCoach] = useState('Coach Marcus Vance');
  const [goal, setGoal] = useState('Hypertrophy & Muscular Density');
  const [frequency, setFrequency] = useState('3 Sessions / Week (Recommended)');
  const [experience, setExperience] = useState('Intermediate (1-3 Years)');
  const [preferredShift, setPreferredShift] = useState('Prime Evening (05:00 PM - 09:00 PM)');
  const [injuries, setInjuries] = useState('None');
  const [notes, setNotes] = useState('');

  // UI Flow State
  const [step, setStep] = useState(1);
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [referenceId] = useState(`COACH-${Math.floor(1000 + Math.random() * 9000)}`);

  // Pre-populate with query param or logged in session
  useEffect(() => {
    if (coachParam) {
      const matched = coaches.find(
        (c) => c.name.toLowerCase().includes(coachParam.toLowerCase()) || c.id === coachParam
      );
      if (matched) setSelectedCoach(matched.name);
    }

    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem('gymflow_current_user');
      if (stored) {
        try {
          const user = JSON.parse(stored);
          if (user.fullName) setFullName(user.fullName);
          if (user.email) setEmail(user.email);
          if (user.phone) setPhone(user.phone);
          if (user.memberId) setMemberId(user.memberId);
        } catch (e) {}
      }
    }
  }, [coachParam]);

  const validateStep1 = () => {
    const errs: Record<string, string> = {};
    if (!fullName.trim() || fullName.trim().length < 2) {
      errs.fullName = 'Please enter your full name.';
    }
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    if (!cleanPhone || cleanPhone.length < 9) {
      errs.phone = 'Please enter a valid WhatsApp phone number.';
    }
    if (!email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
      errs.email = 'Please enter a valid email address.';
    }
    setErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const handleNext = () => {
    if (step === 1) {
      if (!validateStep1()) return;
    }
    setStep(step + 1);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!validateStep1()) {
      setStep(1);
      return;
    }

    // Save coaching request to localStorage
    if (typeof window !== 'undefined') {
      const coachingApp = {
        referenceId,
        fullName,
        phone,
        email,
        memberId: memberId || 'Guest Athlete',
        selectedCoach,
        goal,
        frequency,
        experience,
        preferredShift,
        injuries,
        notes,
        status: 'PENDING_COACH_REVIEW',
        appliedAt: new Date().toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
        }),
      };
      localStorage.setItem('gymflow_coaching_application', JSON.stringify(coachingApp));
    }

    setIsSubmitted(true);
  };

  const activeCoachDetails =
    coaches.find((c) => c.name === selectedCoach) || coaches[0];

  const whatsappMessage = `Hello GymFlow Metropolis! I have submitted my 1-on-1 Coaching Application.
Ref ID: ${referenceId}
Athlete: ${fullName}
WhatsApp: ${phone}
Coach Requested: ${selectedCoach}
Primary Goal: ${goal}
Frequency: ${frequency}
Preferred Shift: ${preferredShift}
Member ID: ${memberId || 'New Enrollee'}`;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      {/* ================= SUCCESS CELEBRATION SCREEN ================= */}
      {isSubmitted ? (
        <div className="max-w-3xl mx-auto space-y-8 animate-fadeIn">
          <div className="p-8 sm:p-12 rounded-3xl bg-zinc-950 border border-brand-red/50 shadow-pass-glow text-center space-y-6">
            <div className="w-20 h-20 rounded-full bg-emerald-500/10 border-2 border-emerald-500 text-emerald-400 mx-auto flex items-center justify-center text-3xl">
              <i className="fa-solid fa-check" />
            </div>

            <div className="space-y-2">
              <span className="text-[10px] font-bold text-emerald-400 tracking-widest uppercase">
                Application Received &bull; Under Coach Review
              </span>
              <h2 className="font-heading text-3xl sm:text-4xl font-extrabold text-white">
                Coaching Request Submitted!
              </h2>
              <p className="text-xs sm:text-sm text-zinc-400 max-w-lg mx-auto">
                Your application has been dispatched to <strong className="text-white">{selectedCoach}</strong>.
                Your allocated coaching reference is <span className="font-mono text-brand-red font-bold">{referenceId}</span>.
              </p>
            </div>

            {/* Application Summary Card */}
            <div className="p-6 sm:p-8 rounded-2xl bg-black border border-zinc-800 text-left space-y-5">
              <div className="flex items-center justify-between pb-4 border-b border-zinc-800">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 rounded-2xl bg-brand-red/10 border border-brand-red/30 flex items-center justify-center text-brand-red font-bold">
                    <i className="fa-solid fa-user-ninja text-lg" />
                  </div>
                  <div>
                    <h3 className="font-heading text-lg font-bold text-white">{selectedCoach}</h3>
                    <span className="text-[10px] text-zinc-400 font-mono">{activeCoachDetails.role}</span>
                  </div>
                </div>
                <span className="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-mono font-bold">
                  Reviewing Within 24h
                </span>
              </div>

              <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
                <div>
                  <span className="text-[10px] text-zinc-500 block">Athlete Name</span>
                  <span className="text-white font-bold">{fullName}</span>
                </div>
                <div>
                  <span className="text-[10px] text-zinc-500 block">Target Discipline</span>
                  <span className="text-brand-red font-bold truncate block">{goal}</span>
                </div>
                <div>
                  <span className="text-[10px] text-zinc-500 block">Commitment</span>
                  <span className="text-zinc-200">{frequency.split('(')[0]}</span>
                </div>
                <div>
                  <span className="text-[10px] text-zinc-500 block">Preferred Wave</span>
                  <span className="text-emerald-400">{preferredShift.split('(')[0]}</span>
                </div>
              </div>
            </div>

            {/* Actions */}
            <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
              <a
                href={`https://wa.me/923259491349?text=${encodeURIComponent(whatsappMessage)}`}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition-all"
              >
                <i className="fa-brands fa-whatsapp text-base" />
                <span>Notify Coach on WhatsApp</span>
              </a>

              <Link
                href="/profile"
                className="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-zinc-900 hover:bg-zinc-800 text-zinc-200 hover:text-white text-xs font-bold tracking-wider border border-zinc-800 transition-colors flex items-center justify-center gap-2"
              >
                <i className="fa-solid fa-user text-xs text-brand-red" />
                <span>View in Athlete Profile</span>
              </Link>
            </div>
          </div>
        </div>
      ) : (
        /* ================= MAIN APPLICATION FORM WORKFLOW ================= */
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
          {/* Left Form Column (8 Cols) */}
          <div className="lg:col-span-8 space-y-8">
            {/* Step Pills */}
            <div className="flex items-center gap-3 text-xs">
              <button
                type="button"
                onClick={() => setStep(1)}
                className={`px-4 py-2 rounded-full border transition-all flex items-center gap-2 font-medium ${
                  step === 1
                    ? 'bg-brand-red text-white border-brand-red shadow-sm'
                    : 'bg-zinc-950 text-zinc-400 border-zinc-800 hover:text-white'
                }`}
              >
                <span className="w-4 h-4 rounded-full bg-black/30 flex items-center justify-center text-[10px]">1</span>
                <span>Athlete Dossier</span>
              </button>

              <i className="fa-solid fa-chevron-right text-[10px] text-zinc-600" />

              <button
                type="button"
                onClick={() => {
                  if (validateStep1()) setStep(2);
                }}
                className={`px-4 py-2 rounded-full border transition-all flex items-center gap-2 font-medium ${
                  step === 2
                    ? 'bg-brand-red text-white border-brand-red shadow-sm'
                    : 'bg-zinc-950 text-zinc-400 border-zinc-800 hover:text-white'
                }`}
              >
                <span className="w-4 h-4 rounded-full bg-black/30 flex items-center justify-center text-[10px]">2</span>
                <span>Coach &amp; Objectives</span>
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
              {/* STEP 1: ATHLETE CREDENTIALS */}
              {step === 1 && (
                <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6 animate-fadeIn">
                  <div className="border-b border-zinc-900 pb-4">
                    <span className="text-[10px] font-semibold text-brand-red tracking-widest block mb-1">
                      Step 01 of 02
                    </span>
                    <h2 className="font-heading text-2xl sm:text-3xl font-bold text-white">
                      Athlete Identity &amp; Contact
                    </h2>
                    <p className="text-xs text-zinc-400 mt-1">
                      Provide your official contact coordinates so your assigned coach can review your intake profile.
                    </p>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Full Legal Name *
                      </label>
                      <input
                        type="text"
                        required
                        value={fullName}
                        onChange={(e) => {
                          setFullName(e.target.value);
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

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        WhatsApp Mobile Number *
                      </label>
                      <input
                        type="tel"
                        required
                        value={phone}
                        onChange={(e) => {
                          setPhone(e.target.value);
                          if (errors.phone) setErrors((prev) => ({ ...prev, phone: '' }));
                        }}
                        placeholder="+92 300 1234567"
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

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Email Address *
                      </label>
                      <input
                        type="email"
                        required
                        value={email}
                        onChange={(e) => {
                          setEmail(e.target.value);
                          if (errors.email) setErrors((prev) => ({ ...prev, email: '' }));
                        }}
                        placeholder="athlete@gymflow.io"
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
                        Current Member ID (Optional)
                      </label>
                      <input
                        type="text"
                        value={memberId}
                        onChange={(e) => setMemberId(e.target.value)}
                        placeholder="e.g. 2026-4 or New Member"
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none font-mono focus:border-brand-red"
                      />
                    </div>
                  </div>

                  <div className="pt-4 border-t border-zinc-900 flex justify-end">
                    <button
                      type="button"
                      onClick={handleNext}
                      className="px-8 py-3.5 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
                    >
                      <span>Next: Select Coach &amp; Goals</span>
                      <i className="fa-solid fa-arrow-right text-[10px]" />
                    </button>
                  </div>
                </div>
              )}

              {/* STEP 2: COACH & PERFORMANCE OBJECTIVES */}
              {step === 2 && (
                <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6 animate-fadeIn">
                  <div className="border-b border-zinc-900 pb-4">
                    <span className="text-[10px] font-semibold text-brand-red tracking-widest block mb-1">
                      Step 02 of 02
                    </span>
                    <h2 className="font-heading text-2xl sm:text-3xl font-bold text-white">
                      Target Discipline &amp; Coach Preference
                    </h2>
                    <p className="text-xs text-zinc-400 mt-1">
                      Configure your athletic goals, weekly frequency, and coach pairing.
                    </p>
                  </div>

                  {/* Coach Selection Cards */}
                  <div className="space-y-3">
                    <label className="text-xs font-medium text-zinc-400 block">
                      Choose Your Preferred Coach *
                    </label>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                      {coaches.map((c) => {
                        const isSelected = selectedCoach === c.name;
                        return (
                          <div
                            key={c.id}
                            onClick={() => setSelectedCoach(c.name)}
                            className={`p-4 rounded-2xl border cursor-pointer transition-all flex items-center gap-3.5 ${
                              isSelected
                                ? 'bg-zinc-900/90 border-2 border-brand-red shadow-pass-glow'
                                : 'bg-black border-zinc-800 hover:border-zinc-700'
                            }`}
                          >
                            <div
                              className="w-12 h-12 rounded-xl bg-cover bg-center shrink-0 border border-zinc-800"
                              style={{ backgroundImage: `url('${c.image}')` }}
                            />
                            <div className="overflow-hidden">
                              <div className="flex items-center gap-2">
                                <h4 className="font-heading text-sm font-bold text-white truncate">
                                  {c.name}
                                </h4>
                              </div>
                              <span className="text-[10px] text-brand-red font-semibold block truncate">
                                {c.role}
                              </span>
                              <span className="text-[9px] text-zinc-500 font-mono block">
                                {c.slots}
                              </span>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>

                  {/* Training Discipline & Commitment */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Primary Athletic Focus *
                      </label>
                      <select
                        value={goal}
                        onChange={(e) => setGoal(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                      >
                        <option value="Hypertrophy & Muscular Density">Hypertrophy &amp; Muscular Density</option>
                        <option value="Powerlifting & Maximal Compound Strength">Powerlifting &amp; Maximal Strength</option>
                        <option value="Combat Boxing & Striking Conditioning">Combat Boxing &amp; Strike Conditioning</option>
                        <option value="Fat Loss & High-Volume Recomposition">Fat Loss &amp; Body Recomposition</option>
                        <option value="Athletic Mobility & Biomechanical Prehab">Athletic Mobility &amp; Prehab</option>
                      </select>
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Weekly Coaching Frequency *
                      </label>
                      <select
                        value={frequency}
                        onChange={(e) => setFrequency(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red font-mono"
                      >
                        <option value="2 Sessions / Week (Form Check & Overhaul)">2 Sessions / Week (Bi-Weekly Check)</option>
                        <option value="3 Sessions / Week (Recommended)">3 Sessions / Week (Gold Standard)</option>
                        <option value="4 Sessions / Week (Advanced Hypertrophy)">4 Sessions / Week (Accelerated)</option>
                        <option value="5 Sessions / Week (Competitive Athlete Wave)">5 Sessions / Week (Intense Athlete)</option>
                      </select>
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Current Lifting Experience
                      </label>
                      <select
                        value={experience}
                        onChange={(e) => setExperience(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                      >
                        <option value="Novice (< 6 Months)">Novice (&lt; 6 Months)</option>
                        <option value="Intermediate (1-3 Years)">Intermediate (1-3 Years)</option>
                        <option value="Advanced (3-6 Years)">Advanced (3-6 Years)</option>
                        <option value="Competitive Lifter / Combat Athlete">Competitive Lifter / Combat Athlete</option>
                      </select>
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Preferred Coaching Window / Shift
                      </label>
                      <select
                        value={preferredShift}
                        onChange={(e) => setPreferredShift(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                      >
                        <option value="Early Morning (06:00 AM - 10:00 AM)">Early Morning (06:00 AM - 10:00 AM)</option>
                        <option value="Afternoon Wave (12:00 PM - 04:00 PM)">Afternoon Wave (12:00 PM - 04:00 PM)</option>
                        <option value="Prime Evening (05:00 PM - 09:00 PM)">Prime Evening (05:00 PM - 09:00 PM)</option>
                      </select>
                    </div>

                    <div className="sm:col-span-2">
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Injuries, Restrictions, or Surgical History (Optional)
                      </label>
                      <input
                        type="text"
                        value={injuries}
                        onChange={(e) => setInjuries(e.target.value)}
                        placeholder="e.g. Minor rotator cuff pinch, lumbar tightness, none"
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                      />
                    </div>

                    <div className="sm:col-span-2">
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        What is your #1 physical milestone for the next 90 days?
                      </label>
                      <textarea
                        rows={2}
                        value={notes}
                        onChange={(e) => setNotes(e.target.value)}
                        placeholder="e.g. Add 20kg to my deadlift without back pain, or drop 5% body fat before summer..."
                        className="w-full bg-black border border-zinc-800 rounded-xl p-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red text-xs resize-none"
                      />
                    </div>
                  </div>

                  <div className="pt-4 border-t border-zinc-900 flex justify-between items-center">
                    <button
                      type="button"
                      onClick={() => setStep(1)}
                      className="px-6 py-3 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-bold transition-colors"
                    >
                      &larr; Back
                    </button>

                    <button
                      type="submit"
                      className="px-8 py-3.5 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
                    >
                      <i className="fa-solid fa-paper-plane text-xs" />
                      <span>Submit Coaching Application</span>
                    </button>
                  </div>
                </div>
              )}
            </form>
          </div>

          {/* Right Sidebar: Selected Coach Spotlight Card (4 Cols) */}
          <div className="lg:col-span-4 space-y-6">
            <div className="sticky top-24 space-y-6">
              <div className="p-6 rounded-3xl bg-zinc-950 border border-brand-red/40 shadow-pass-glow space-y-5">
                <div className="flex items-center justify-between pb-3 border-b border-zinc-900 text-[10px] font-bold">
                  <span className="text-brand-red tracking-wider">Coach Dossier Spotlight</span>
                  <span className="text-emerald-400 flex items-center gap-1.5">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                    Accepting Athletes
                  </span>
                </div>

                <div
                  className="h-48 rounded-2xl bg-cover bg-center relative overflow-hidden border border-zinc-800"
                  style={{ backgroundImage: `url('${activeCoachDetails.image}')` }}
                >
                  <div className="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent" />
                  <div className="absolute bottom-3 left-3 right-3 text-left">
                    <span className="px-2.5 py-0.5 rounded-full bg-brand-red text-white text-[9px] font-bold uppercase tracking-wider">
                      {activeCoachDetails.badge}
                    </span>
                    <h3 className="font-heading text-lg font-bold text-white mt-1">
                      {activeCoachDetails.name}
                    </h3>
                  </div>
                </div>

                <div className="space-y-3 text-xs">
                  <div>
                    <span className="text-[10px] text-zinc-500 font-mono block">Credentials &amp; Certs</span>
                    <span className="text-zinc-200 font-semibold">{activeCoachDetails.experience}</span>
                  </div>

                  <div>
                    <span className="text-[10px] text-zinc-500 font-mono block">Biomechanics Focus</span>
                    <span className="text-zinc-300 text-[11px] leading-relaxed block">
                      {activeCoachDetails.specialty}
                    </span>
                  </div>

                  <p className="text-[11px] text-zinc-400 leading-relaxed border-t border-zinc-900 pt-3">
                    {activeCoachDetails.bio}
                  </p>
                </div>

                <div className="p-3.5 rounded-2xl bg-black border border-zinc-900 space-y-2 text-xs">
                  <div className="flex justify-between">
                    <span className="text-zinc-400">Consultation Assessment:</span>
                    <span className="text-emerald-400 font-bold font-mono">100% Free Initial</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-zinc-400">Custom Nutrition Plan:</span>
                    <span className="text-white font-bold">Included</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-zinc-400">Biometric Form Checks:</span>
                    <span className="text-white font-bold">Every Session</span>
                  </div>
                </div>

                <a
                  href="https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20have%20questions%20about%201-on-1%20Personal%20Coaching."
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full py-3 rounded-2xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white text-xs font-semibold border border-zinc-800 transition-colors flex items-center justify-center gap-2"
                >
                  <i className="fa-brands fa-whatsapp text-emerald-400" />
                  <span>Ask Front Desk on WhatsApp</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default function CoachingApplicationPage() {
  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1">
        {/* Cinematic Header Hero */}
        <section className="relative pt-24 pb-16 px-6 text-center border-b border-zinc-900 overflow-hidden">
          <div
            className="absolute inset-0 bg-cover bg-center filter grayscale contrast-125 opacity-20 scale-105"
            style={{ backgroundImage: `url('/images/download.png')` }}
          />
          <div className="absolute inset-0 bg-gradient-to-b from-black via-black/85 to-black pointer-events-none" />
          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-brand-red/15 rounded-full blur-[160px] pointer-events-none" />

          <div className="max-w-4xl mx-auto relative z-10 space-y-4">
            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-red/10 border border-brand-red/30">
              <span className="w-2 h-2 rounded-full bg-brand-red animate-pulse" />
              <span className="text-[10px] font-bold tracking-wider text-brand-red uppercase">
                1-on-1 Elite Coaching &amp; Mentorship
              </span>
            </div>

            <h1 className="font-heading text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight tracking-tight">
              Apply for 1-on-1 Coaching<span className="text-brand-red">.</span>
            </h1>

            <p className="text-zinc-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
              Work directly with certified coaches for bespoke periodization, biomechanical movement screens,
              and nutrition protocols designed to surpass your genetic baseline.
            </p>
          </div>
        </section>

        {/* Suspense-wrapped form */}
        <Suspense
          fallback={
            <div className="py-20 text-center text-zinc-500 font-mono text-xs">
              <i className="fa-solid fa-spinner fa-spin text-brand-red text-base mb-2 block" />
              Loading Coaching Intake Form...
            </div>
          }
        >
          <CoachingFormContent />
        </Suspense>
      </main>

      <PublicFooter />
    </div>
  );
}

