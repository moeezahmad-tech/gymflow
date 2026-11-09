'use client';

import { useState } from 'react';
import Link from 'next/link';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';
import RealQrCode from '@/components/real-qr-code';

export default function AdmissionFormPage() {
  // Step navigation (1 to 4)
  const [step, setStep] = useState(1);
  const [isSubmitted, setIsSubmitted] = useState(false);

  // Form Fields State
  // 1. Personal & Identity
  const [fullName, setFullName] = useState('');
  const [fatherName, setFatherName] = useState('');
  const [cnic, setCnic] = useState('');
  const [dob, setDob] = useState('2000-01-01');
  const [gender, setGender] = useState('Male');
  const [bloodGroup, setBloodGroup] = useState('B+');
  const [phone, setPhone] = useState('+92 ');
  const [alternatePhone, setAlternatePhone] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('password123');
  const [showPassword, setShowPassword] = useState(false);
  const [address, setAddress] = useState('');
  const [city, setCity] = useState('Islamabad');
  const [emergencyName, setEmergencyName] = useState('');
  const [emergencyRelation, setEmergencyRelation] = useState('Brother');
  const [emergencyPhone, setEmergencyPhone] = useState('+92 ');

  // 2. Physical & Health Screening
  const [heightFeet, setHeightFeet] = useState('5');
  const [heightInches, setHeightInches] = useState('10');
  const [weightKg, setWeightKg] = useState('75');
  const [fitnessExperience, setFitnessExperience] = useState('Intermediate (1-3 Years)');
  const [primaryGoal, setPrimaryGoal] = useState('Muscle Hypertrophy & Bulking');
  const [medicalConditions, setMedicalConditions] = useState({
    heartCondition: false,
    highBp: false,
    asthma: false,
    jointInjuries: false,
    recentSurgery: false,
  });
  const [medicalNotes, setMedicalNotes] = useState('');

  // 3. Package & Shift
  const [selectedPlan, setSelectedPlan] = useState<'1m' | '3m' | '6m'>('3m');
  const [preferredShift, setPreferredShift] = useState('Prime Evening (05:00 PM - 11:00 PM)');
  const [lockerAddon, setLockerAddon] = useState(false);
  const [trainerAssigned, setTrainerAssigned] = useState('Coach Marcus Vance (Hypertrophy)');

  // 4. Payment & Dues
  const [paymentChannel, setPaymentChannel] = useState<'EASYPAISA' | 'JAZZCASH' | 'CASH' | 'BANK'>('EASYPAISA');
  const [transactionRef, setTransactionRef] = useState('');
  const [agreedTerms, setAgreedTerms] = useState(true);
  const [agreedCutoff, setAgreedCutoff] = useState(true);
  const [agreedWaiver, setAgreedWaiver] = useState(true);

  // Generated Member Code
  const [allocatedMemberId] = useState(`2026-${Math.floor(100 + Math.random() * 899)}`);

  // Pricing calculations
  const planDetails = {
    '1m': { name: '1 Month Package', price: 8500, duration: '30 Days', admissionFee: 1500 },
    '3m': { name: '3 Month Package', price: 22500, duration: '90 Days', admissionFee: 0 },
    '6m': { name: '6 Month Package', price: 42000, duration: '180 Days', admissionFee: 0 },
  };

  const currentPlan = planDetails[selectedPlan];
  const lockerFee = lockerAddon ? (selectedPlan === '6m' ? 0 : 1500) : 0;
  const totalAmount = currentPlan.price + currentPlan.admissionFee + lockerFee;

  // Step 1 Validation State
  const [step1Errors, setStep1Errors] = useState<Record<string, string>>({});
  const [step1ErrorBanner, setStep1ErrorBanner] = useState<string | null>(null);

  const validateStep1 = () => {
    const errors: Record<string, string> = {};

    if (!fullName.trim() || fullName.trim().length < 2) {
      errors.fullName = 'Please enter your full legal name.';
    }
    if (!fatherName.trim() || fatherName.trim().length < 2) {
      errors.fatherName = 'Please enter father or guardian name.';
    }
    if (!cnic.trim() || cnic.trim().length < 5) {
      errors.cnic = 'Please enter a valid CNIC / B-Form number.';
    }
    if (!dob.trim()) {
      errors.dob = 'Please select your date of birth.';
    }
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    if (!cleanPhone || cleanPhone.length < 9) {
      errors.phone = 'Please enter a valid primary WhatsApp phone number.';
    }
    if (!email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
      errors.email = 'Please enter a valid email address.';
    }
    if (!password || password.length < 6) {
      errors.password = 'Please create a login password with at least 6 characters.';
    }
    if (!address.trim() || address.trim().length < 4) {
      errors.address = 'Please enter your residential address & sector.';
    }
    if (!emergencyName.trim() || emergencyName.trim().length < 2) {
      errors.emergencyName = 'Please enter emergency contact person name.';
    }
    const cleanEmergencyPhone = emergencyPhone.replace(/[^0-9]/g, '');
    if (!cleanEmergencyPhone || cleanEmergencyPhone.length < 9) {
      errors.emergencyPhone = 'Please enter emergency contact phone number.';
    }

    setStep1Errors(errors);

    if (Object.keys(errors).length > 0) {
      setStep1ErrorBanner(
        'Step 1 Incomplete: Please fill in all required fields (Name, CNIC, Phone, Email, Password, Address, Emergency Contact) before moving to subsequent steps.'
      );
      return false;
    }

    setStep1ErrorBanner(null);
    return true;
  };

  const handleStepClick = (targetStep: number) => {
    if (targetStep > 1) {
      const isValid = validateStep1();
      if (!isValid) {
        setStep(1);
        return;
      }
    }
    setStep(targetStep);
  };

  const handleNext = () => {
    if (step === 1) {
      const isValid = validateStep1();
      if (!isValid) return;
    }
    if (step < 4) setStep(step + 1);
  };

  const handlePrev = () => {
    if (step > 1) setStep(step - 1);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!validateStep1()) {
      setStep(1);
      return;
    }

    // Persist full athlete registration session
    if (typeof window !== 'undefined') {
      localStorage.setItem(
        'gymflow_current_user',
        JSON.stringify({
          fullName,
          fatherName,
          cnic,
          dob,
          gender,
          bloodGroup,
          email,
          phone,
          address,
          city,
          emergencyName,
          emergencyPhone,
          plan: selectedPlan,
          memberId: allocatedMemberId,
          shift: preferredShift,
          joinedDate: new Date().toLocaleDateString('en-US', { month: 'short', year: 'numeric' }),
        })
      );
    }

    setIsSubmitted(true);
  };

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1 py-12 px-4 sm:px-6 max-w-7xl mx-auto w-full">
        {/* Title Header */}
        <div className="text-center max-w-3xl mx-auto mb-10 space-y-3">
          <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-red/10 border border-brand-red/30">
            <span className="w-2 h-2 rounded-full bg-brand-red animate-pulse" />
            <span className="text-[10px] font-bold tracking-wider text-brand-red">
              Official Athlete Sign-Up &amp; Admission
            </span>
          </div>

          <h1 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
            Athlete Sign Up &amp; Admission<span className="text-brand-red">.</span>
          </h1>

          <p className="text-xs sm:text-sm text-zinc-400 max-w-xl mx-auto">
            Create your athlete profile, configure biometric turnstile privileges,
            and select your training package &amp; shift for Metropolis Headquarters.
          </p>

          <div className="pt-1 text-xs text-zinc-400">
            Already an admitted member?{' '}
            <Link href="/login" className="text-brand-red font-semibold hover:underline">
              Log in to your profile &rarr;
            </Link>
          </div>
        </div>

        {/* Progress Tracker (4 Steps) */}
        {!isSubmitted && (
          <div className="max-w-4xl mx-auto mb-10">
            <div className="grid grid-cols-4 gap-2 text-center text-xs">
              {[
                { s: 1, label: 'Identity', icon: 'fa-id-card' },
                { s: 2, label: 'Health Profile', icon: 'fa-heart-pulse' },
                { s: 3, label: 'Package & Shift', icon: 'fa-dumbbell' },
                { s: 4, label: 'Payment & Slip', icon: 'fa-receipt' },
              ].map((item) => {
                const isCurrent = step === item.s;
                const isDone = step > item.s;
                return (
                  <button
                    key={item.s}
                    type="button"
                    onClick={() => handleStepClick(item.s)}
                    className={`p-3 rounded-2xl border transition-all text-left flex flex-col sm:flex-row items-center gap-2.5 ${
                      isCurrent
                        ? 'bg-zinc-950 border-brand-red shadow-pass-glow text-white'
                        : isDone
                        ? 'bg-zinc-950/70 border-emerald-500/40 text-emerald-400'
                        : 'bg-zinc-950/40 border-zinc-900 text-zinc-500'
                    }`}
                  >
                    <div
                      className={`w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 ${
                        isCurrent
                          ? 'bg-brand-red text-white'
                          : isDone
                          ? 'bg-emerald-500/20 text-emerald-400'
                          : 'bg-zinc-900 text-zinc-500'
                      }`}
                    >
                      {isDone ? <i className="fa-solid fa-check text-[10px]" /> : item.s}
                    </div>
                    <div className="hidden sm:block overflow-hidden">
                      <span className="text-[10px] font-semibold tracking-wider block">
                        Step 0{item.s}
                      </span>
                      <span className="text-xs font-bold text-white truncate block">
                        {item.label}
                      </span>
                    </div>
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* ================= SUCCESS STATE: ADMISSION DOSSIER SUMMARY ================= */}
        {isSubmitted ? (
          <div className="max-w-3xl mx-auto space-y-8 animate-fadeIn">
            {/* Success Celebration Card */}
            <div className="p-8 sm:p-10 rounded-3xl bg-zinc-950 border border-brand-red/50 shadow-pass-glow text-center space-y-6">
              <div className="w-20 h-20 rounded-full bg-emerald-500/10 border-2 border-emerald-500 text-emerald-400 mx-auto flex items-center justify-center text-3xl">
                <i className="fa-solid fa-check" />
              </div>

              <div>
                <span className="text-[10px] font-semibold text-emerald-400 tracking-widest block uppercase">
                  Admission Dossier Verified &bull; Athlete Registered
                </span>
                <h2 className="font-heading text-4xl sm:text-5xl font-bold text-white mt-1">
                  Welcome to GymFlow, {fullName || 'Athlete'}!
                </h2>
                <p className="text-xs sm:text-sm text-zinc-400 mt-2 max-w-lg mx-auto">
                  Your admission has been processed successfully. Your assigned permanent athlete ID is{' '}
                  <span className="font-mono text-brand-red font-bold">{allocatedMemberId}</span>.
                </p>
              </div>

              {/* Official Athlete Profile & Dossier Summary */}
              <div className="p-6 sm:p-8 rounded-3xl bg-black border border-zinc-800 text-left space-y-6 relative overflow-hidden">
                <div className="flex items-center justify-between pb-4 border-b border-zinc-800">
                  <div className="flex items-center gap-3">
                    <img src="/images/logo.png" alt="GymFlow" className="h-8 w-auto object-contain" />
                    <div>
                      <span className="font-heading text-2xl font-bold text-white tracking-wider">
                        GYM<span className="text-brand-red">FLOW</span>
                      </span>
                      <span className="text-[9px] tracking-widest text-zinc-500 block uppercase">
                        Official Athlete Dossier
                      </span>
                    </div>
                  </div>
                  <div className="text-right">
                    <span className="text-[9px] font-semibold text-zinc-500 block">
                      Athlete ID
                    </span>
                    <span className="font-mono text-lg font-bold text-brand-red">
                      {allocatedMemberId}
                    </span>
                  </div>
                </div>

                {/* Athlete Details Grid */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
                  <div>
                    <span className="text-[9px] text-zinc-500 block">Athlete Name</span>
                    <span className="text-white font-bold">{fullName}</span>
                  </div>
                  <div>
                    <span className="text-[9px] text-zinc-500 block">Father/Guardian</span>
                    <span className="text-zinc-300">{fatherName || 'N/A'}</span>
                  </div>
                  <div>
                    <span className="text-[9px] text-zinc-500 block">CNIC / ID</span>
                    <span className="text-zinc-300">{cnic || 'N/A'}</span>
                  </div>
                  <div>
                    <span className="text-[9px] text-zinc-500 block">Blood Group</span>
                    <span className="text-brand-red font-bold">{bloodGroup}</span>
                  </div>

                  <div>
                    <span className="text-[9px] text-zinc-500 block">Selected Tier</span>
                    <span className="text-emerald-400 font-bold">{currentPlan.name}</span>
                  </div>
                  <div>
                    <span className="text-[9px] text-zinc-500 block">Training Shift</span>
                    <span className="text-zinc-300 truncate block">{preferredShift}</span>
                  </div>
                  <div>
                    <span className="text-[9px] text-zinc-500 block">Payment Mode</span>
                    <span className="text-zinc-300">{paymentChannel}</span>
                  </div>
                  <div>
                    <span className="text-[9px] text-zinc-500 block">Total Dues</span>
                    <span className="text-white font-bold font-mono">PKR {totalAmount.toLocaleString()}</span>
                  </div>
                </div>

                {/* Status Notice */}
                <div className="pt-4 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-brand-red/10 border border-brand-red/30 flex items-center justify-center text-brand-red">
                      <i className="fa-solid fa-user-check text-base" />
                    </div>
                    <div>
                      <span className="text-xs font-bold text-white block">
                        Athlete Profile Synchronized
                      </span>
                      <span className="text-[10px] text-zinc-500 block">
                        Strict 10th-of-the-month cutoff billing applies. Access your profile anytime.
                      </span>
                    </div>
                  </div>

                  <span className="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold">
                    Registration Confirmed
                  </span>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <Link
                  href="/profile"
                  className="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-brand-red/30 transition-all"
                >
                  <i className="fa-solid fa-user text-xs" />
                  <span>View Athlete Profile</span>
                </Link>

                <a
                  href={`https://wa.me/923259491349?text=Hello%20GymFlow%20Metropolis,%20I%20have%20completed%20my%20admission%20form!%20My%20Athlete%20ID%20is%20${allocatedMemberId},%20Name:%20${encodeURIComponent(fullName)},%20Plan:%20${encodeURIComponent(currentPlan.name)}.`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition-all"
                >
                  <i className="fa-brands fa-whatsapp text-sm" />
                  <span>Send Confirmation on WhatsApp</span>
                </a>

                <button
                  type="button"
                  onClick={() => window.print()}
                  className="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-bold tracking-wider border border-zinc-800 transition-colors flex items-center justify-center gap-2"
                >
                  <i className="fa-solid fa-print text-xs" />
                  <span>Print Dossier</span>
                </button>
              </div>
            </div>
          </div>
        ) : (
          /* ================= MAIN 4-STEP ADMISSION FORM ================= */
          <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {/* Left 8 Cols: Step Forms */}
            <div className="lg:col-span-8 space-y-6">
              {/* STEP 1: ATHLETE PERSONAL & IDENTITY */}
              {step === 1 && (
                <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6 animate-fadeIn">
                  <div className="border-b border-zinc-900 pb-4">
                    <span className="text-[10px] font-semibold text-brand-red tracking-widest block mb-1">
                      Section 01 of 04
                    </span>
                    <h2 className="font-heading text-2xl sm:text-3xl font-bold text-white">
                      Athlete Bio &amp; Identity Verification
                    </h2>
                    <p className="text-xs text-zinc-400 mt-1">
                      Legal identity for official registration, turnstile pass issuing, and emergency notification.
                    </p>
                  </div>

                  {/* Validation Error Banner */}
                  {step1ErrorBanner && (
                    <div className="p-4 rounded-2xl bg-brand-red/10 border border-brand-red/40 text-brand-red text-xs flex items-center justify-between animate-fadeIn">
                      <div className="flex items-center gap-3">
                        <i className="fa-solid fa-triangle-exclamation text-base shrink-0" />
                        <span>{step1ErrorBanner}</span>
                      </div>
                      <button
                        type="button"
                        onClick={() => setStep1ErrorBanner(null)}
                        className="text-zinc-400 hover:text-white px-2 py-1 text-xs"
                      >
                        &times;
                      </button>
                    </div>
                  )}

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
                          if (step1Errors.fullName) {
                            setStep1Errors((prev) => ({ ...prev, fullName: '' }));
                          }
                        }}
                        placeholder="e.g. Tariq Mehmood"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                          step1Errors.fullName
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.fullName && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.fullName}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Father / Guardian Name *
                      </label>
                      <input
                        type="text"
                        required
                        value={fatherName}
                        onChange={(e) => {
                          setFatherName(e.target.value);
                          if (step1Errors.fatherName) {
                            setStep1Errors((prev) => ({ ...prev, fatherName: '' }));
                          }
                        }}
                        placeholder="Father or Guardian Full Name"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                          step1Errors.fatherName
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.fatherName && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.fatherName}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        CNIC / B-Form Number *
                      </label>
                      <input
                        type="text"
                        required
                        value={cnic}
                        onChange={(e) => {
                          setCnic(e.target.value);
                          if (step1Errors.cnic) {
                            setStep1Errors((prev) => ({ ...prev, cnic: '' }));
                          }
                        }}
                        placeholder="61101-1234567-1"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none font-mono transition-colors ${
                          step1Errors.cnic
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.cnic && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.cnic}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Date of Birth *
                      </label>
                      <input
                        type="date"
                        required
                        value={dob}
                        onChange={(e) => {
                          setDob(e.target.value);
                          if (step1Errors.dob) {
                            setStep1Errors((prev) => ({ ...prev, dob: '' }));
                          }
                        }}
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white focus:outline-none font-mono transition-colors ${
                          step1Errors.dob
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.dob && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.dob}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Gender
                      </label>
                      <select
                        value={gender}
                        onChange={(e) => setGender(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                      >
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Blood Group
                      </label>
                      <select
                        value={bloodGroup}
                        onChange={(e) => setBloodGroup(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white font-mono focus:outline-none focus:border-brand-red"
                      >
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                      </select>
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Primary WhatsApp Mobile *
                      </label>
                      <input
                        type="tel"
                        required
                        value={phone}
                        onChange={(e) => {
                          setPhone(e.target.value);
                          if (step1Errors.phone) {
                            setStep1Errors((prev) => ({ ...prev, phone: '' }));
                          }
                        }}
                        placeholder="+92 300 1234567"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none font-mono transition-colors ${
                          step1Errors.phone
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.phone && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.phone}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Alternate Phone / PTCL
                      </label>
                      <input
                        type="tel"
                        value={alternatePhone}
                        onChange={(e) => setAlternatePhone(e.target.value)}
                        placeholder="+92 51..."
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red font-mono"
                      />
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Email Address (Login ID) *
                      </label>
                      <input
                        type="email"
                        required
                        value={email}
                        onChange={(e) => {
                          setEmail(e.target.value);
                          if (step1Errors.email) {
                            setStep1Errors((prev) => ({ ...prev, email: '' }));
                          }
                        }}
                        placeholder="athlete@gymflow.io"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                          step1Errors.email
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.email && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.email}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Create Login Password *
                      </label>
                      <div className="relative">
                        <input
                          type={showPassword ? 'text' : 'password'}
                          required
                          value={password}
                          onChange={(e) => {
                            setPassword(e.target.value);
                            if (step1Errors.password) {
                              setStep1Errors((prev) => ({ ...prev, password: '' }));
                            }
                          }}
                          placeholder="At least 6 characters"
                          className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                            step1Errors.password
                              ? 'border-brand-red bg-brand-red/5'
                              : 'border-zinc-800 focus:border-brand-red'
                          }`}
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword(!showPassword)}
                          className="absolute right-3.5 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white text-xs transition-colors"
                        >
                          <i className={`fa-solid ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`} />
                        </button>
                      </div>
                      {step1Errors.password && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.password}
                        </span>
                      )}
                    </div>

                    <div className="sm:col-span-2">
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Residential Address &amp; Sector *
                      </label>
                      <input
                        type="text"
                        required
                        value={address}
                        onChange={(e) => {
                          setAddress(e.target.value);
                          if (step1Errors.address) {
                            setStep1Errors((prev) => ({ ...prev, address: '' }));
                          }
                        }}
                        placeholder="House #, Street #, Sector / Phase"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                          step1Errors.address
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.address && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.address}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        City
                      </label>
                      <input
                        type="text"
                        value={city}
                        onChange={(e) => setCity(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                      />
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Emergency Contact Person *
                      </label>
                      <input
                        type="text"
                        required
                        value={emergencyName}
                        onChange={(e) => {
                          setEmergencyName(e.target.value);
                          if (step1Errors.emergencyName) {
                            setStep1Errors((prev) => ({ ...prev, emergencyName: '' }));
                          }
                        }}
                        placeholder="Emergency Contact Name"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none transition-colors ${
                          step1Errors.emergencyName
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.emergencyName && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.emergencyName}
                        </span>
                      )}
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Relationship
                      </label>
                      <input
                        type="text"
                        value={emergencyRelation}
                        onChange={(e) => setEmergencyRelation(e.target.value)}
                        placeholder="Brother / Father / Friend"
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red"
                      />
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Emergency Phone Number *
                      </label>
                      <input
                        type="tel"
                        required
                        value={emergencyPhone}
                        onChange={(e) => {
                          setEmergencyPhone(e.target.value);
                          if (step1Errors.emergencyPhone) {
                            setStep1Errors((prev) => ({ ...prev, emergencyPhone: '' }));
                          }
                        }}
                        placeholder="+92 300 0000000"
                        className={`w-full bg-black border rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none font-mono transition-colors ${
                          step1Errors.emergencyPhone
                            ? 'border-brand-red bg-brand-red/5'
                            : 'border-zinc-800 focus:border-brand-red'
                        }`}
                      />
                      {step1Errors.emergencyPhone && (
                        <span className="text-[10px] text-brand-red mt-1 block font-semibold">
                          {step1Errors.emergencyPhone}
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="pt-4 border-t border-zinc-900 flex justify-end">
                    <button
                      type="button"
                      onClick={handleNext}
                      className="px-8 py-3.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
                    >
                      <span>Proceed to Health Screening</span>
                      <i className="fa-solid fa-arrow-right text-[10px]" />
                    </button>
                  </div>
                </div>
              )}

              {/* STEP 2: PHYSICAL METRICS & HEALTH SCREENING */}
              {step === 2 && (
                <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6 animate-fadeIn">
                  <div className="border-b border-zinc-900 pb-4">
                    <span className="text-[10px] font-semibold text-brand-red tracking-widest block mb-1">
                      Section 02 of 04
                    </span>
                    <h2 className="font-heading text-2xl sm:text-3xl font-bold text-white">
                      Physical Metrics &amp; Medical Clearance
                    </h2>
                    <p className="text-xs text-zinc-400 mt-1">
                      Ensure safe exercise prescription, tailored coaching, and liability clearance.
                    </p>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Height (Feet &amp; Inches)
                      </label>
                      <div className="grid grid-cols-2 gap-2">
                        <select
                          value={heightFeet}
                          onChange={(e) => setHeightFeet(e.target.value)}
                          className="w-full bg-black border border-zinc-800 rounded-xl px-3 py-3 text-white focus:outline-none focus:border-brand-red"
                        >
                          {['4', '5', '6', '7'].map((f) => (
                            <option key={f} value={f}>{f} ft</option>
                          ))}
                        </select>
                        <select
                          value={heightInches}
                          onChange={(e) => setHeightInches(e.target.value)}
                          className="w-full bg-black border border-zinc-800 rounded-xl px-3 py-3 text-white focus:outline-none focus:border-brand-red"
                        >
                          {Array.from({ length: 12 }, (_, i) => (
                            <option key={i} value={i.toString()}>{i} in</option>
                          ))}
                        </select>
                      </div>
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Current Weight (kg)
                      </label>
                      <input
                        type="number"
                        value={weightKg}
                        onChange={(e) => setWeightKg(e.target.value)}
                        placeholder="75"
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white font-mono focus:outline-none focus:border-brand-red"
                      />
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Experience Level
                      </label>
                      <select
                        value={fitnessExperience}
                        onChange={(e) => setFitnessExperience(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red"
                      >
                        <option value="Complete Beginner (0-6 Months)">Complete Beginner (0-6 Months)</option>
                        <option value="Intermediate (1-3 Years)">Intermediate (1-3 Years)</option>
                        <option value="Advanced Lifter (3+ Years)">Advanced Lifter (3+ Years)</option>
                        <option value="Competitive Athlete">Competitive Athlete</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Primary Fitness Goal *
                    </label>
                    <select
                      value={primaryGoal}
                      onChange={(e) => setPrimaryGoal(e.target.value)}
                      className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red text-xs"
                    >
                      <option value="Muscle Hypertrophy & Bulking">Muscle Hypertrophy &amp; Bulking</option>
                      <option value="Fat Loss & Body Recomposition">Fat Loss &amp; Body Recomposition</option>
                      <option value="Powerlifting & Compound Strength">Powerlifting &amp; Compound Strength</option>
                      <option value="Combat Strike & Boxing Conditioning">Combat Strike &amp; Boxing Conditioning</option>
                      <option value="General Cardiovascular Health & Posture">General Cardiovascular Health &amp; Posture</option>
                    </select>
                  </div>

                  {/* PAR-Q Medical Conditions Checklist */}
                  <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-3">
                    <span className="text-[10px] font-semibold text-brand-red tracking-wider block">
                      Physical Activity Readiness Declaration (Check all that apply):
                    </span>

                    <div className="space-y-2 text-xs">
                      <label className="flex items-center gap-3 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={medicalConditions.heartCondition}
                          onChange={(e) =>
                            setMedicalConditions({ ...medicalConditions, heartCondition: e.target.checked })
                          }
                          className="accent-brand-red w-4 h-4 cursor-pointer"
                        />
                        <span className="text-zinc-300">History of heart disease, chest pain, or fainting</span>
                      </label>

                      <label className="flex items-center gap-3 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={medicalConditions.highBp}
                          onChange={(e) =>
                            setMedicalConditions({ ...medicalConditions, highBp: e.target.checked })
                          }
                          className="accent-brand-red w-4 h-4 cursor-pointer"
                        />
                        <span className="text-zinc-300">Diagnosed chronic high blood pressure or taking BP medication</span>
                      </label>

                      <label className="flex items-center gap-3 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={medicalConditions.asthma}
                          onChange={(e) =>
                            setMedicalConditions({ ...medicalConditions, asthma: e.target.checked })
                          }
                          className="accent-brand-red w-4 h-4 cursor-pointer"
                        />
                        <span className="text-zinc-300">Asthma, breathing difficulties, or respiratory allergy</span>
                      </label>

                      <label className="flex items-center gap-3 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={medicalConditions.jointInjuries}
                          onChange={(e) =>
                            setMedicalConditions({ ...medicalConditions, jointInjuries: e.target.checked })
                          }
                          className="accent-brand-red w-4 h-4 cursor-pointer"
                        />
                        <span className="text-zinc-300">Chronic back, shoulder, or knee joint injury</span>
                      </label>

                      <label className="flex items-center gap-3 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={medicalConditions.recentSurgery}
                          onChange={(e) =>
                            setMedicalConditions({ ...medicalConditions, recentSurgery: e.target.checked })
                          }
                          className="accent-brand-red w-4 h-4 cursor-pointer"
                        />
                        <span className="text-zinc-300">Major surgery or hospitalization in past 12 months</span>
                      </label>
                    </div>
                  </div>

                  <div>
                    <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                      Specific Medical or Dietary Notes (Optional)
                    </label>
                    <textarea
                      rows={2}
                      value={medicalNotes}
                      onChange={(e) => setMedicalNotes(e.target.value)}
                      placeholder="List any ongoing medications, dietary restrictions, or notes for coaches..."
                      className="w-full bg-black border border-zinc-800 rounded-xl p-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red text-xs resize-none"
                    />
                  </div>

                  <div className="pt-4 border-t border-zinc-900 flex justify-between">
                    <button
                      type="button"
                      onClick={handlePrev}
                      className="px-6 py-3.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-bold tracking-wider"
                    >
                      &larr; Back
                    </button>
                    <button
                      type="button"
                      onClick={handleNext}
                      className="px-8 py-3.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
                    >
                      <span>Choose Package &amp; Shift</span>
                      <i className="fa-solid fa-arrow-right text-[10px]" />
                    </button>
                  </div>
                </div>
              )}

              {/* STEP 3: PACKAGE SELECTION & TRAINING SHIFT */}
              {step === 3 && (
                <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6 animate-fadeIn">
                  <div className="border-b border-zinc-900 pb-4">
                    <span className="text-[10px] font-semibold text-brand-red tracking-widest block mb-1">
                      Section 03 of 04
                    </span>
                    <h2 className="font-heading text-2xl sm:text-3xl font-bold text-white">
                      Select Package, Shift &amp; Add-ons
                    </h2>
                    <p className="text-xs text-zinc-400 mt-1">
                      Choose your subscription tier, daily floor access window, and coach assignment.
                    </p>
                  </div>

                  {/* Package Cards */}
                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {(['1m', '3m', '6m'] as const).map((planKey) => {
                      const p = planDetails[planKey];
                      const isSelected = selectedPlan === planKey;
                      return (
                        <div
                          key={planKey}
                          onClick={() => setSelectedPlan(planKey)}
                          className={`p-5 rounded-2xl cursor-pointer transition-all border relative flex flex-col justify-between ${
                            isSelected
                              ? 'bg-zinc-900/90 border-2 border-brand-red shadow-pass-glow'
                              : 'bg-black border-zinc-800 hover:border-zinc-700'
                          }`}
                        >
                          {planKey === '3m' && (
                            <span className="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full bg-brand-red text-white text-[9px] font-bold tracking-wider">
                              Most Popular
                            </span>
                          )}

                          <div className="space-y-1">
                            <span className="text-xs font-medium text-zinc-400 block font-mono">
                              {p.duration}
                            </span>
                            <h3 className="font-heading text-xl font-bold text-white">{p.name}</h3>
                            <div className="font-heading text-2xl font-bold text-emerald-400 font-mono">
                              ₨ {p.price.toLocaleString()}
                            </div>
                          </div>

                          <div className="pt-3 border-t border-zinc-800/80 mt-3 text-[11px] text-zinc-400 space-y-1">
                            {p.admissionFee === 0 ? (
                              <span className="text-emerald-400 font-bold block">✓ ₨ 1,000 Admission Waived</span>
                            ) : (
                              <span>+ ₨ 1,000 One-time Reg Fee</span>
                            )}
                            <span>{planKey === '1m' ? 'Standard Floor Access' : 'Sauna & Plunge Included'}</span>
                          </div>
                        </div>
                      );
                    })}
                  </div>

                  {/* Shift Selection */}
                  <div className="space-y-2">
                    <label className="text-xs font-medium text-zinc-400 block">
                      Preferred Training Shift *
                    </label>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                      {[
                        { shift: 'Morning Shift (05:00 AM - 11:00 AM)', tag: 'Quiet & Focused' },
                        { shift: 'Afternoon Shift (12:00 PM - 05:00 PM)', tag: 'Open Racks' },
                        { shift: 'Prime Evening (05:00 PM - 11:00 PM)', tag: 'High Energy' },
                      ].map((s) => (
                        <div
                          key={s.shift}
                          onClick={() => setPreferredShift(s.shift)}
                          className={`p-3.5 rounded-xl border cursor-pointer transition-all ${
                            preferredShift === s.shift
                              ? 'bg-brand-red/10 border-brand-red text-white'
                              : 'bg-black border-zinc-800 text-zinc-400 hover:border-zinc-700'
                          }`}
                        >
                          <span className="font-bold block text-white">{s.shift}</span>
                          <span className="text-[10px] text-zinc-500 font-mono">{s.tag}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  {/* Add-on: Locker & Coach Request */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                    <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-2">
                      <label className="flex items-center gap-3 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={lockerAddon}
                          onChange={(e) => setLockerAddon(e.target.checked)}
                          className="accent-brand-red w-4 h-4 cursor-pointer"
                        />
                        <div>
                          <span className="font-bold text-white block">Reserve Dedicated Locker</span>
                          <span className="text-[10px] text-zinc-400">
                            {selectedPlan === '6m' ? 'Free on 6-Month Pass' : '+ ₨ 1,500 / month'}
                          </span>
                        </div>
                      </label>
                    </div>

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                        Coach Orientation Request
                      </label>
                      <select
                        value={trainerAssigned}
                        onChange={(e) => setTrainerAssigned(e.target.value)}
                        className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-red text-xs"
                      >
                        <option value="Coach Marcus Vance (Hypertrophy)">Coach Marcus Vance (Hypertrophy)</option>
                        <option value="Coach Sarah Jenkins (Olympic & Combat)">Coach Sarah Jenkins (Olympic &amp; Combat)</option>
                        <option value="Coach David Cole (Conditioning)">Coach David Cole (Conditioning)</option>
                        <option value="No Trainer Needed (Self Directed)">No Trainer Needed (Self Directed)</option>
                      </select>
                    </div>
                  </div>

                  <div className="pt-4 border-t border-zinc-900 flex justify-between">
                    <button
                      type="button"
                      onClick={handlePrev}
                      className="px-6 py-3.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-bold tracking-wider"
                    >
                      &larr; Back
                    </button>
                    <button
                      type="button"
                      onClick={handleNext}
                      className="px-8 py-3.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
                    >
                      <span>Proceed to Payment</span>
                      <i className="fa-solid fa-arrow-right text-[10px]" />
                    </button>
                  </div>
                </div>
              )}

              {/* STEP 4: PAYMENT METHOD & SUBMISSION */}
              {step === 4 && (
                <div className="p-6 sm:p-8 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-6 animate-fadeIn">
                  <div className="border-b border-zinc-900 pb-4">
                    <span className="text-[10px] font-semibold text-brand-red tracking-widest block mb-1">
                      Section 04 of 04
                    </span>
                    <h2 className="font-heading text-2xl sm:text-3xl font-bold text-white">
                      Payment Verification &amp; Waiver
                    </h2>
                    <p className="text-xs text-zinc-400 mt-1">
                      Select your payment channel, review the 10th cutoff terms, and generate your admission pass.
                    </p>
                  </div>

                  {/* Payment Channel Selection */}
                  <div className="space-y-3">
                    <label className="text-xs font-medium text-zinc-400 block">
                      Select Payment Channel *
                    </label>
                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                      {[
                        { id: 'EASYPAISA', name: 'EasyPaisa', desc: '0325 9491349' },
                        { id: 'JAZZCASH', name: 'JazzCash', desc: '0300 1234567' },
                        { id: 'CASH', name: 'Cash at Desk', desc: 'Front Counter' },
                        { id: 'BANK', name: 'Bank Transfer', desc: 'Meezan Bank' },
                      ].map((ch) => (
                        <div
                          key={ch.id}
                          onClick={() => setPaymentChannel(ch.id as any)}
                          className={`p-3.5 rounded-2xl border cursor-pointer transition-all ${
                            paymentChannel === ch.id
                              ? 'bg-brand-red/10 border-brand-red shadow-pass-glow text-white'
                              : 'bg-black border-zinc-800 text-zinc-400 hover:border-zinc-700'
                          }`}
                        >
                          <span className="font-bold text-white block">{ch.name}</span>
                          <span className="text-[10px] text-zinc-500 font-mono block mt-0.5">{ch.desc}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  {/* Payment Details Card */}
                  <div className="p-4 rounded-2xl bg-black border border-zinc-900 space-y-3 text-xs">
                    <div className="flex items-center justify-between pb-2 border-b border-zinc-900 font-mono">
                      <span className="text-zinc-400">Total Admission &amp; Dues:</span>
                      <span className="font-heading text-2xl font-bold text-emerald-400">
                        PKR {totalAmount.toLocaleString()}
                      </span>
                    </div>

                    {paymentChannel === 'EASYPAISA' && (
                      <div className="text-[11px] text-zinc-300 space-y-1">
                        <p>
                          Send <span className="font-mono font-bold text-emerald-400">₨ {totalAmount.toLocaleString()}</span> to EasyPaisa Title: <span className="font-bold text-white">GYMFLOW METROPOLIS</span> (Number: <span className="font-mono text-brand-red font-bold">0325-9491349</span>).
                        </p>
                      </div>
                    )}

                    {paymentChannel === 'JAZZCASH' && (
                      <div className="text-[11px] text-zinc-300 space-y-1">
                        <p>
                          Send <span className="font-mono font-bold text-emerald-400">₨ {totalAmount.toLocaleString()}</span> to JazzCash Account: <span className="font-bold text-white">GYMFLOW METROPOLIS</span> (Number: <span className="font-mono text-brand-red font-bold">0300-1234567</span>).
                        </p>
                      </div>
                    )}

                    {paymentChannel === 'CASH' && (
                      <div className="text-[11px] text-zinc-300">
                        <p>
                          Pay <span className="font-mono font-bold text-emerald-400">₨ {totalAmount.toLocaleString()}</span> at the front desk reception upon your first check-in. Your turnstile pass will activate immediately.
                        </p>
                      </div>
                    )}

                    {paymentChannel === 'BANK' && (
                      <div className="text-[11px] text-zinc-300 space-y-0.5 font-mono">
                        <p className="text-white font-bold">Meezan Bank Ltd &bull; IBAN: PK68MEZN0001234567890123</p>
                        <p className="text-zinc-400">Account Title: GymFlow Fitness Technologies</p>
                      </div>
                    )}

                    <div>
                      <label className="text-xs font-medium text-zinc-400 block mb-1">
                        Transaction Reference / Trx ID (Optional if paying cash):
                      </label>
                      <input
                        type="text"
                        value={transactionRef}
                        onChange={(e) => setTransactionRef(e.target.value)}
                        placeholder="e.g. EP-9842109843 or Cash at Desk"
                        className="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red font-mono text-xs"
                      />
                    </div>
                  </div>

                  {/* Mandatory Agreements */}
                  <div className="space-y-2.5 text-xs text-zinc-300 pt-2">
                    <label className="flex items-start gap-3 cursor-pointer">
                      <input
                        type="checkbox"
                        required
                        checked={agreedCutoff}
                        onChange={(e) => setAgreedCutoff(e.target.checked)}
                        className="accent-brand-red w-4 h-4 rounded mt-0.5 cursor-pointer"
                      />
                      <span>
                        I understand the <strong className="text-white">strict 10th-of-the-month cutoff policy</strong>. Dues must be settled before or on the 10th to prevent automated turnstile lockout.
                      </span>
                    </label>

                    <label className="flex items-start gap-3 cursor-pointer">
                      <input
                        type="checkbox"
                        required
                        checked={agreedTerms}
                        onChange={(e) => setAgreedTerms(e.target.checked)}
                        className="accent-brand-red w-4 h-4 rounded mt-0.5 cursor-pointer"
                      />
                      <span>
                        I agree to the GymFlow code of conduct, weights reracking discipline, and turnstile pass rules.
                      </span>
                    </label>

                    <label className="flex items-start gap-3 cursor-pointer">
                      <input
                        type="checkbox"
                        required
                        checked={agreedWaiver}
                        onChange={(e) => setAgreedWaiver(e.target.checked)}
                        className="accent-brand-red w-4 h-4 rounded mt-0.5 cursor-pointer"
                      />
                      <span>
                        I declare that I am physically fit for strenuous physical training and release GymFlow from liability for injuries arising from self-conducted exercise.
                      </span>
                    </label>
                  </div>

                  <div className="pt-4 border-t border-zinc-900 flex justify-between">
                    <button
                      type="button"
                      onClick={handlePrev}
                      className="px-6 py-3.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-bold tracking-wider"
                    >
                      &larr; Back
                    </button>
                    <button
                      type="submit"
                      className="px-8 py-4 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2"
                    >
                      <i className="fa-solid fa-id-card text-xs" />
                      <span>Submit Admission &amp; Generate Pass</span>
                    </button>
                  </div>
                </div>
              )}
            </div>

            {/* Right 4 Cols: Live Digital Pass Preview & Summary */}
            <div className="lg:col-span-4 space-y-6">
              <div className="sticky top-24 space-y-6">
                {/* Glowing Digital Pass Preview */}
                <div className="p-6 rounded-3xl bg-zinc-950 border border-brand-red/40 shadow-pass-glow space-y-5 text-center relative overflow-hidden">
                  <div className="flex items-center justify-between pb-3 border-b border-zinc-900 text-[10px] font-bold">
                    <span className="text-brand-red">Turnstile Pass Preview</span>
                    <span className="text-emerald-400 flex items-center gap-1.5">
                      <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                      Allocated
                    </span>
                  </div>

                  {/* Real Scannable 2D QR Matrix */}
                  <RealQrCode
                    value={allocatedMemberId || '2026-NEW'}
                    size={180}
                    downloadable={true}
                    showFormatToggle={true}
                  />

                  <div>
                    <h3 className="font-heading text-2xl font-bold text-white truncate">
                      {fullName || 'New Athlete'}
                    </h3>
                    <span className="font-mono text-lg font-bold text-brand-red block">
                      {allocatedMemberId}
                    </span>
                    <span className="text-[10px] text-zinc-500 font-semibold tracking-wider block mt-0.5">
                      {currentPlan.name} &bull; {preferredShift.split('(')[0]}
                    </span>
                  </div>

                  {/* Financial Breakdown Card */}
                  <div className="p-3.5 rounded-2xl bg-black border border-zinc-900 text-left space-y-2 text-xs">
                    <div className="flex justify-between text-zinc-400">
                      <span>Package Term:</span>
                      <span className="text-white font-mono font-bold">{currentPlan.duration}</span>
                    </div>
                    <div className="flex justify-between text-zinc-400">
                      <span>Package Fee:</span>
                      <span className="text-white font-mono">₨ {currentPlan.price.toLocaleString()}</span>
                    </div>
                    {currentPlan.admissionFee > 0 && (
                      <div className="flex justify-between text-zinc-400">
                        <span>Admission Fee:</span>
                        <span className="text-white font-mono">₨ {currentPlan.admissionFee.toLocaleString()}</span>
                      </div>
                    )}
                    {lockerAddon && (
                      <div className="flex justify-between text-zinc-400">
                        <span>Locker Add-on:</span>
                        <span className="text-white font-mono">₨ {lockerFee.toLocaleString()}</span>
                      </div>
                    )}
                    <div className="pt-2 border-t border-zinc-800 flex justify-between font-bold text-sm">
                      <span className="text-white">Total Payable:</span>
                      <span className="text-emerald-400 font-mono">₨ {totalAmount.toLocaleString()}</span>
                    </div>
                  </div>

                  {/* Help Support */}
                  <div className="pt-2 text-left">
                    <a
                      href="https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20need%20help%20with%20my%20admission%20form."
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-[11px] text-zinc-400 hover:text-emerald-400 flex items-center gap-2 transition-colors"
                    >
                      <i className="fa-brands fa-whatsapp text-emerald-400" />
                      <span>Need help? Chat with Reception</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </form>
        )}
      </main>

      <PublicFooter />
    </div>
  );
}

