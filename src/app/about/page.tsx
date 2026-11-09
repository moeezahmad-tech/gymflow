import Link from 'next/link';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

export default function AboutPage() {
  const coaches = [
    {
      name: 'Marcus Vance',
      role: 'Head Hypertrophy & Strength',
      bio: 'Former national powerlifter and IFBB coach with 12+ years optimizing progressive overload schemes and biomechanics.',
      image: '/images/a283eef1cb9158de060d30b0c981cecd.jpg',
    },
    {
      name: 'Sarah Jenkins',
      role: 'Olympic Lifting & Combat Lead',
      bio: 'National Olympic weightlifting competitor and CSCS trainer specializing in explosive triple extension, snatch, and clean & jerk mechanics.',
      image: '/images/5731fdc96ee11e2f0f0eb3ce394ac372.jpg',
    },
    {
      name: 'David Cole',
      role: 'Mobility & Tactical Conditioning',
      bio: 'Ex-military physical training lead dedicated to rotational kinetic stability, anaerobic sprint intervals, and joint longevity.',
      image: '/images/006db77edf3f68260cdaab3daf9a5afb.jpg',
    },
  ];

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1">
        {/* ================= 1. CINEMATIC HERO BANNER WITH IMAGE ================= */}
        <section className="relative pt-28 pb-24 px-6 text-center border-b border-zinc-900 overflow-hidden">
          {/* Background Image with Dark Athletic Overlay & Fluted Pleats */}
          <div
            className="absolute inset-0 bg-cover bg-center filter contrast-125 opacity-25 scale-105"
            style={{ backgroundImage: `url('/images/download.png')` }}
          />
          <div className="absolute inset-0 bg-fluted-dark opacity-40 mix-blend-multiply pointer-events-none" />
          <div className="absolute inset-0 bg-gradient-to-b from-black via-black/85 to-black pointer-events-none" />
          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-brand-red/15 rounded-full blur-[140px] pointer-events-none" />

          <div className="max-w-4xl mx-auto relative z-10 space-y-4">
            <span className="inline-block text-[10px] font-bold tracking-widest text-brand-red px-3.5 py-1.5 rounded-full bg-brand-red/10 border border-brand-red/30">
              Our Legacy &bull; Established 2024
            </span>
            <h1 className="font-heading text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight tracking-tight">
              Forged In Discipline &amp; Passion<span className="text-brand-red">.</span>
            </h1>
            <p className="text-zinc-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
              Since our inception, GymFlow has been built around one core premise: creating an elite training sanctuary
              where athletes transform latent potential into undeniable physical dominance.
            </p>
          </div>
        </section>

        {/* ================= 2. NOT JUST A GYM. A HIGH-PERFORMANCE ECOSYSTEM ================= */}
        <section className="py-24 px-6 max-w-7xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {/* Visual Photo Showcase Card */}
            <div className="rounded-3xl overflow-hidden border border-zinc-800 bg-zinc-950 p-2 group shadow-2xl relative">
              <div
                className="h-[480px] rounded-2xl bg-cover bg-center group-hover:scale-105 transition-transform duration-700"
                style={{ backgroundImage: `url('/images/5850549eb38c7367d272b6e65219bd0d.jpg')` }}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none rounded-3xl" />
              <div className="absolute bottom-6 left-6 right-6 z-10">
                <span className="text-xs font-semibold text-brand-red block mb-1">
                  Metropolis Elite Floor
                </span>
                <span className="font-heading text-2xl font-bold text-white block">
                  Raw Power &bull; Scientific Precision
                </span>
              </div>
            </div>

            {/* Information Grid */}
            <div className="space-y-8">
              <div>
                <span className="text-xs font-semibold text-brand-red tracking-wide block mb-2">
                  World-Class Standards
                </span>
                <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
                  Not Just A Gym. <br />
                  A High-Performance Ecosystem<span className="text-brand-red">.</span>
                </h2>
                <p className="text-zinc-400 text-xs sm:text-sm mt-3 leading-relaxed">
                  At GymFlow, we reject the noisy, overcrowded corporate gym model. Whether you are a professional athlete,
                  powerlifter, or embarking on your first workout, we provide the coaching, high-caliber equipment, and automated
                  turnstile intelligence to exceed every fitness goal.
                </p>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900 space-y-2">
                  <div className="w-8 h-8 rounded-lg bg-brand-red/10 text-brand-red flex items-center justify-center text-xs font-bold">
                    <i className="fa-solid fa-dumbbell" />
                  </div>
                  <h3 className="font-heading text-lg font-bold text-white">Calibrated Equipment</h3>
                  <p className="text-xs text-zinc-400 leading-relaxed">
                    Olympic Eleiko bars, competition plates, dumbbells to 150 lbs, and Hammer Strength machinery.
                  </p>
                </div>

                <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900 space-y-2">
                  <div className="w-8 h-8 rounded-lg bg-brand-red/10 text-brand-red flex items-center justify-center text-xs font-bold">
                    <i className="fa-solid fa-chart-line" />
                  </div>
                  <h3 className="font-heading text-lg font-bold text-white">Periodized Systems</h3>
                  <p className="text-xs text-zinc-400 leading-relaxed">
                    Scientifically structured training splits, progressive overload logging, and consistency streak tracking.
                  </p>
                </div>

                <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900 space-y-2">
                  <div className="w-8 h-8 rounded-lg bg-brand-red/10 text-brand-red flex items-center justify-center text-xs font-bold">
                    <i className="fa-solid fa-users" />
                  </div>
                  <h3 className="font-heading text-lg font-bold text-white">Master Mentors</h3>
                  <p className="text-xs text-zinc-400 leading-relaxed">
                    CSCS-certified personal trainers, former combat fighters, and functional rehabilitation specialists.
                  </p>
                </div>

                <div className="p-5 rounded-2xl bg-zinc-950 border border-zinc-900 space-y-2">
                  <div className="w-8 h-8 rounded-lg bg-brand-red/10 text-brand-red flex items-center justify-center text-xs font-bold">
                    <i className="fa-solid fa-temperature-arrow-down" />
                  </div>
                  <h3 className="font-heading text-lg font-bold text-white">Thermal Contrast</h3>
                  <p className="text-xs text-zinc-400 leading-relaxed">
                    Authentic dry Finnish cedar sauna paired with 3°C cold plunge tubs to supercharge recovery.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ================= 3. MEET OUR HEAD COACHES WITH LARGE PHOTOS ================= */}
        <section className="py-24 px-6 bg-dark-900 border-t border-zinc-900">
          <div className="max-w-7xl mx-auto">
            <div className="text-center max-w-2xl mx-auto mb-16">
              <span className="text-xs font-semibold text-brand-red tracking-wide block mb-2">
                Master Instructors
              </span>
              <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
                Meet Our Head Coaches<span className="text-brand-red">.</span>
              </h2>
              <p className="text-xs text-zinc-400 mt-2">
                Experienced mentors dedicated to refining your technique, movement biomechanics, and athletic longevity.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {coaches.map((coach) => (
                <div
                  key={coach.name}
                  className="rounded-3xl bg-zinc-950 border border-zinc-900 overflow-hidden group hover:border-brand-red/40 transition-all shadow-xl"
                >
                  <div
                    className="h-80 bg-cover bg-center group-hover:scale-105 transition-transform duration-500 relative"
                    style={{ backgroundImage: `url('${coach.image}')` }}
                  >
                    <div className="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/20 to-transparent" />
                  </div>
                  <div className="p-6 space-y-3 -mt-6 relative z-10">
                    <span className="text-xs font-semibold text-brand-red block">
                      {coach.role}
                    </span>
                    <h3 className="font-heading text-2xl font-bold text-white">{coach.name}</h3>
                    <p className="text-xs text-zinc-400 leading-relaxed">{coach.bio}</p>
                    <div className="pt-2">
                      <a
                        href={`https://wa.me/923259491349?text=Hello%20Coach%20${encodeURIComponent(coach.name)},%20I%20would%20like%20to%20consult%20you.`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 text-xs font-semibold text-emerald-400 hover:text-emerald-300"
                      >
                        <i className="fa-brands fa-whatsapp text-sm" />
                        <span>Book 1-on-1 WhatsApp Consultation</span>
                      </a>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* ================= 4. READY TO TRAIN BANNER ================= */}
        <section className="py-24 px-6 max-w-5xl mx-auto text-center space-y-6">
          <h2 className="font-heading text-4xl sm:text-5xl md:text-6xl font-extrabold text-white tracking-tight">
            Ready To Train With The Best?
          </h2>
          <p className="text-xs sm:text-sm text-zinc-400 max-w-xl mx-auto">
            Claim your trial session, test our competition equipment, or chat with our master coaches today.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
            <Link
              href="/pricing"
              className="h-12 px-8 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold shadow-lg shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
            >
              <span>View Memberships</span>
              <i className="fa-solid fa-arrow-right text-xs" />
            </Link>
            <Link
              href="/contact"
              className="h-12 px-8 rounded-full bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-sm font-semibold transition-all flex items-center justify-center"
            >
              Contact Reception
            </Link>
          </div>
        </section>
      </main>

      <PublicFooter />
    </div>
  );
}
