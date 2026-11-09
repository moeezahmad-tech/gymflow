import Link from 'next/link';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

export default function FacilitiesPage() {
  const zones = [
    {
      title: 'The Heavy Iron Arena',
      category: 'Zone 01 / Strength & Hypertrophy Floor',
      description:
        'Engineered for serious lifters. Outfitted with calibrated Eleiko competition bars, cast iron & competition bumper plates, 6 full power cages with band pegs, deadlift platforms with noise-dampening flooring, and heavy dumbbell stations scaling up to 150 lbs.',
      perks: [
        '6 Competition Power Racks with Chalk Stations',
        'Olympic Deadlift Platforms with Shock-Absorbent Rubber',
        'Custom Dumbbells up to 150 lbs in 5 lb Increments',
        'Hammer Strength ISO-Lateral Incline, Row & Leg Press Machines',
      ],
      image: '/images/5731fdc96ee11e2f0f0eb3ce394ac372.jpg',
      badge: 'Eleiko & Watson Spec',
    },
    {
      title: 'Calibrated Dumbbell Range & Combat',
      category: 'Zone 02 / Athletic Speed & Striking',
      description:
        'A dedicated 40-meter high-density turf lane designed for sled pushes, sprint intervals, and agility drills, adjoining our regulation combat ring and calibrated Watson solid urethane dumbbell range illuminated under precision crimson lighting.',
      perks: [
        'Watson Solid Urethane Dumbbells Scaling from 2.5kg to 60kg',
        '40m Indoor Sprint Turf Lane for Resistance Sleds',
        'Full Boxing Ring with Leather Floor Padding',
        'SkiErgs, Concept2 Rowers & Rogue Echo Air Bikes',
      ],
      image: '/images/3a0b6f51dcf0a779bafcf757c6943e51.jpg',
      badge: 'Calibrated Dumbbells to 60kg',
    },
    {
      title: 'Hammer Strength Hypertrophy Bay',
      category: 'Zone 03 / Mechanical Tension & Isolation',
      description:
        'Elite training requires elite equipment. Our flagship hypertrophy bay is equipped with competition-grade Hammer Strength plate-loaded machinery, iso-lateral chest presses, linear hack squats, and 45-degree leg press stations.',
      perks: [
        'Hammer Strength ISO-Lateral Incline & Flat Chest Presses',
        '45-Degree High-Capacity Leg Press and Linear Hack Squats',
        'Dual Cable Cross-Stations with Precision Pulley Ratios',
        'Private Luxury Showers with Rainfall Showerheads',
      ],
      image: '/images/5850549eb38c7367d272b6e65219bd0d.jpg',
      badge: 'Hammer Strength Official',
    },
  ];

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1">
        {/* ================= 1. CINEMATIC HERO BANNER ================= */}
        <section className="relative pt-28 pb-24 px-6 text-center border-b border-zinc-900 overflow-hidden">
          <div
            className="absolute inset-0 bg-cover bg-center filter contrast-125 opacity-25 scale-105"
            style={{ backgroundImage: `url('/images/download.png')` }}
          />
          <div className="absolute inset-0 bg-fluted-dark opacity-40 mix-blend-multiply pointer-events-none" />
          <div className="absolute inset-0 bg-gradient-to-b from-black via-black/85 to-black pointer-events-none" />
          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-brand-red/15 rounded-full blur-[140px] pointer-events-none" />

          <div className="max-w-4xl mx-auto relative z-10 space-y-4">
            <span className="inline-block text-[10px] font-bold tracking-widest text-brand-red px-3.5 py-1.5 rounded-full bg-brand-red/10 border border-brand-red/30">
              25,000 SQ FT Elite Facility &bull; Metropolis HQ
            </span>
            <h1 className="font-heading text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight tracking-tight">
              Facilities &amp; Equipment<span className="text-brand-red">.</span>
            </h1>
            <p className="text-zinc-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
              Every square inch is built without compromise. We invest exclusively in competition-grade
              bars, calibrated iron, custom dumbbell runs, and medical-grade thermal recovery suites.
            </p>
          </div>
        </section>

        {/* ================= 2. 3 SPECIFIC TRAINING ZONES (LARGE VISUAL TILES) ================= */}
        <section className="py-24 px-6 max-w-7xl mx-auto space-y-20">
          {zones.map((zone, index) => {
            const isReversed = index % 2 === 1;
            return (
              <div
                key={zone.title}
                className={`grid grid-cols-1 lg:grid-cols-2 gap-12 items-center ${
                  isReversed ? 'lg:flex-row-reverse' : ''
                }`}
              >
                {/* Visual Image Container */}
                <div className={`relative group ${isReversed ? 'lg:order-2' : ''}`}>
                  <div className="rounded-3xl overflow-hidden border border-zinc-800 bg-zinc-950 p-2 shadow-2xl relative">
                    <div
                      className="h-[420px] sm:h-[460px] rounded-2xl bg-cover bg-center group-hover:scale-105 transition-transform duration-700 relative"
                      style={{ backgroundImage: `url('${zone.image}')` }}
                    >
                      <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none rounded-2xl" />
                      <div className="absolute top-4 left-4">
                        <span className="px-3 py-1 rounded-full bg-black/80 backdrop-blur-md border border-zinc-700 text-brand-red text-xs font-semibold">
                          {zone.badge}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Content Box */}
                <div className={`space-y-6 ${isReversed ? 'lg:order-1' : ''}`}>
                  <span className="text-xs font-semibold text-brand-red block">
                    {zone.category}
                  </span>
                  <h2 className="font-heading text-3xl sm:text-4xl font-extrabold text-white leading-tight tracking-tight">
                    {zone.title}
                  </h2>
                  <p className="text-zinc-400 text-sm leading-relaxed">{zone.description}</p>

                  <div className="space-y-3 pt-2">
                    <span className="text-xs font-semibold text-zinc-400 block">
                      Zone Specifications:
                    </span>
                    {zone.perks.map((perk, idx) => (
                      <div key={idx} className="flex items-start gap-3 text-xs text-zinc-300">
                        <i className="fa-solid fa-circle-check text-brand-red text-xs mt-0.5" />
                        <span>{perk}</span>
                      </div>
                    ))}
                  </div>

                  <div className="pt-4">
                    <Link
                      href="/pricing"
                      className="inline-flex items-center gap-2 h-11 px-6 rounded-full bg-zinc-900 hover:bg-brand-red text-white text-xs font-semibold transition-all border border-zinc-800 hover:border-brand-red"
                    >
                      <span>Access This Zone</span>
                      <i className="fa-solid fa-arrow-right text-[10px]" />
                    </Link>
                  </div>
                </div>
              </div>
            );
          })}
        </section>

        {/* ================= 3. CTA STRIP ================= */}
        <section className="py-20 px-6 bg-dark-900 border-t border-zinc-900 text-center">
          <div className="max-w-4xl mx-auto space-y-6">
            <h2 className="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
              See The Facilities In Person
            </h2>
            <p className="text-xs sm:text-sm text-zinc-400 max-w-xl mx-auto">
              Our front desk offers daily guided walkthroughs and 1-day experience passes for prospective athletes.
            </p>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
              <Link
                href="/contact"
                className="h-12 px-8 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold shadow-lg shadow-brand-red/30 transition-all flex items-center justify-center"
              >
                Schedule Facility Tour
              </Link>
              <a
                href="https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20would%20like%20to%20book%20a%20facility%20tour."
                target="_blank"
                rel="noopener noreferrer"
                className="h-12 px-8 rounded-full bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-emerald-400 text-sm font-semibold transition-all flex items-center justify-center gap-2"
              >
                <i className="fa-brands fa-whatsapp text-sm" />
                <span>WhatsApp Front Desk</span>
              </a>
            </div>
          </div>
        </section>
      </main>

      <PublicFooter />
    </div>
  );
}
