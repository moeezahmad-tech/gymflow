import Link from 'next/link';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

export default function ProgramsPage() {
  const programs = [
    {
      title: 'Powerlifting & Hypertrophy',
      subtitle: 'Maximal Strength & Muscular Density',
      intensity: 'Intensity: 95% • Heavy Load',
      duration: '75 Min Sessions • 4x Weekly',
      coach: 'Led by Sarah Jenkins',
      description:
        'A periodized compound movement curriculum focused on the squat, bench press, deadlift, and overhead press. Combines heavy mechanical tension sets with high-volume accessory supersets to maximize myofibrillar hypertrophy and neurological strength adaptation.',
      modules: [
        'Kinetic bar path analysis and stance optimization',
        'RPE-based wave loading and deload management',
        'Specialized accessory work targeting weak points',
        'Safe failure protocols and competition prep',
      ],
      image: '/images/5731fdc96ee11e2f0f0eb3ce394ac372.jpg',
      badge: 'Advanced Strength',
    },
    {
      title: 'Boxing & Strike Combatives',
      subtitle: 'Combat Conditioning & Anaerobic Power',
      intensity: 'Intensity: 90% • Fast Paced',
      duration: '60 Min Sessions • 3x Weekly',
      coach: 'Led by Marcus Vance',
      description:
        'Learn real-world striking fundamentals while scorching calories in an authentic combat training environment. Incorporates stance transitions, heavy bag power drills, speed bag rhythm, defensive slip mechanics, and high-intensity interval calisthenics.',
      modules: [
        'Fundamental boxing footwork and rotational power',
        'Heavy bag endurance intervals & pad work',
        'Core rotational torque and neck/jaw conditioning',
        'High-density anaerobic recovery drills',
      ],
      image: '/images/4fc91843bcba0dc0451cadb72bbd2777.jpg',
      badge: 'Combat & Conditioning',
    },
    {
      title: 'Cardio Blast & Core Blitz',
      subtitle: 'Metabolic Conditioning & Mobility',
      intensity: 'Intensity: 85% • Endurance',
      duration: '50 Min Sessions • 5x Weekly',
      coach: 'Led by David Cole',
      description:
        'Engineered to elevate VO2 max, melt body fat, and reinforce core trunk stability. Utilizes Concept2 rowers, SkiErgs, Rogue Echo air bikes, weighted sled sprints, kettlebell swings, and functional multi-planar core stability complexes.',
      modules: [
        'Metabolic interval training for maximum calorie afterburn',
        'Low-impact joint-friendly endurance machines',
        'Deep core anti-rotation stability complexes',
        'Post-workout contrast recovery immersion',
      ],
      image: '/images/006db77edf3f68260cdaab3daf9a5afb.jpg',
      badge: 'Metabolic Stamina',
    },
  ];

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1">
        {/* ================= 1. CINEMATIC HERO BANNER ================= */}
        <section className="relative pt-28 pb-24 px-6 text-center border-b border-zinc-900 overflow-hidden">
          <div
            className="absolute inset-0 bg-cover bg-center filter grayscale contrast-125 opacity-25 scale-105"
            style={{ backgroundImage: `url('/images/download.png')` }}
          />
          <div className="absolute inset-0 bg-fluted-dark opacity-40 mix-blend-multiply pointer-events-none" />
          <div className="absolute inset-0 bg-gradient-to-b from-black via-black/85 to-black pointer-events-none" />
          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-brand-red/15 rounded-full blur-[140px] pointer-events-none" />

          <div className="max-w-4xl mx-auto relative z-10 space-y-4">
            <span className="inline-block text-[10px] font-bold tracking-widest text-brand-red px-3.5 py-1.5 rounded-full bg-brand-red/10 border border-brand-red/30">
              Periodized Scientific Protocols
            </span>
            <h1 className="font-heading text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight tracking-tight">
              Elite Training Disciplines<span className="text-brand-red">.</span>
            </h1>
            <p className="text-zinc-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
              No generic routines or wasted reps. Every curriculum is engineered with progressive overload,
              movement biomechanics, and targeted conditioning to forge elite athletic performance.
            </p>
          </div>
        </section>

        {/* ================= 2. PROGRAM CARDS (LARGE SPLIT TILES) ================= */}
        <section className="py-24 px-6 max-w-7xl mx-auto space-y-20">
          {programs.map((prog, index) => {
            const isReversed = index % 2 === 1;
            return (
              <div
                key={prog.title}
                className={`grid grid-cols-1 lg:grid-cols-2 gap-12 items-center ${
                  isReversed ? 'lg:flex-row-reverse' : ''
                }`}
              >
                {/* Visual Image Container */}
                <div className={`relative group ${isReversed ? 'lg:order-2' : ''}`}>
                  <div className="rounded-3xl overflow-hidden border border-zinc-800 bg-zinc-950 p-2 shadow-2xl relative">
                    <div
                      className="h-[420px] sm:h-[460px] rounded-2xl bg-cover bg-center group-hover:scale-105 transition-transform duration-700 relative"
                      style={{ backgroundImage: `url('${prog.image}')` }}
                    >
                      <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none rounded-2xl" />
                      <div className="absolute top-4 left-4">
                        <span className="px-3 py-1 rounded-full bg-black/80 backdrop-blur-md border border-zinc-700 text-brand-red text-xs font-semibold">
                          {prog.badge}
                        </span>
                      </div>
                      <div className="absolute bottom-4 left-4 right-4 text-xs font-mono text-zinc-300 flex justify-between bg-black/80 backdrop-blur-md px-3.5 py-2 rounded-xl border border-zinc-800">
                        <span>{prog.duration}</span>
                        <span className="text-amber-400 font-bold">{prog.intensity}</span>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Content Box */}
                <div className={`space-y-6 ${isReversed ? 'lg:order-1' : ''}`}>
                  <div>
                    <span className="text-xs font-semibold text-brand-red block mb-1">
                      {prog.subtitle}
                    </span>
                    <h2 className="font-heading text-3xl sm:text-4xl font-extrabold text-white leading-tight tracking-tight">
                      {prog.title}
                    </h2>
                    <span className="text-xs text-zinc-400 font-mono block mt-1">
                      {prog.coach}
                    </span>
                  </div>

                  <p className="text-zinc-400 text-sm leading-relaxed">{prog.description}</p>

                  <div className="space-y-3 pt-2">
                    <span className="text-xs font-semibold text-zinc-400 block">
                      Core Curriculum Modules:
                    </span>
                    {prog.modules.map((mod, idx) => (
                      <div key={idx} className="flex items-start gap-3 text-xs text-zinc-300">
                        <i className="fa-solid fa-circle-check text-brand-red text-xs mt-0.5" />
                        <span>{mod}</span>
                      </div>
                    ))}
                  </div>

                  <div className="pt-4 flex flex-wrap items-center gap-3">
                    <Link
                      href={`/coaching?coach=${encodeURIComponent(prog.coach.replace('Led by ', ''))}`}
                      className="h-11 px-6 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold transition-all shadow-lg shadow-brand-red/30 flex items-center gap-2"
                    >
                      <i className="fa-solid fa-user-ninja text-xs" />
                      <span>Apply for 1-on-1 Coaching</span>
                    </Link>
                    <Link
                      href="/pricing"
                      className="h-11 px-6 rounded-full bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-xs font-semibold transition-all flex items-center gap-2"
                    >
                      <span>View Pricing</span>
                    </Link>
                    <a
                      href={`https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20am%20interested%20in%201-on-1%20coaching%20for%20${encodeURIComponent(prog.title)}.`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="h-11 px-6 rounded-full bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-emerald-400 text-xs font-semibold transition-colors flex items-center gap-2"
                    >
                      <i className="fa-brands fa-whatsapp text-sm" />
                      <span>Chat on WhatsApp</span>
                    </a>
                  </div>
                </div>
              </div>
            );
          })}
        </section>

        {/* ================= 3. BOTTOM BANNER ================= */}
        <section className="py-20 px-6 bg-dark-900 border-t border-zinc-900 text-center">
          <div className="max-w-4xl mx-auto space-y-6">
            <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
              Ready for Dedicated 1-on-1 Coaching?
            </h2>
            <p className="text-xs sm:text-sm text-zinc-400 max-w-xl mx-auto">
              Our head coaches offer bespoke periodization, kinetic bar-path screens, and tailored nutrition blueprints.
            </p>
            <div className="pt-2 flex flex-wrap items-center justify-center gap-4">
              <Link
                href="/coaching"
                className="inline-flex items-center gap-2 h-12 px-8 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold shadow-lg shadow-brand-red/30 transition-all"
              >
                <i className="fa-solid fa-user-ninja text-xs" />
                <span>Apply for 1-on-1 Coaching</span>
              </Link>
              <Link
                href="/contact"
                className="inline-flex items-center gap-2 h-12 px-8 rounded-full bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-sm font-semibold transition-all"
              >
                <span>Book Gym Tour &amp; Screen</span>
              </Link>
            </div>
          </div>
        </section>
      </main>

      <PublicFooter />
    </div>
  );
}
