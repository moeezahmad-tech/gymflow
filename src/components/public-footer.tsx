'use client';

import Link from 'next/link';

export default function PublicFooter() {
  return (
    <footer className="border-t border-zinc-900/90 bg-black text-zinc-400 text-xs mt-20">
      {/* Upper Footer: Brand, Links, Contact with Generous Gaps and Margins */}
      <div className="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 pt-24 pb-20">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-12 sm:gap-14 lg:gap-16 xl:gap-20">
          {/* Brand & Mission (4 cols on desktop) */}
          <div className="md:col-span-12 lg:col-span-4 space-y-6">
            <Link href="/" className="inline-flex items-center gap-3 group">
              <img
                src="/images/logo.png"
                alt="GymFlow Logo"
                className="h-9 w-auto object-contain drop-shadow-[0_0_12px_rgba(255,42,42,0.45)] group-hover:scale-105 transition-transform"
                onError={(e) => {
                  e.currentTarget.style.display = 'none';
                }}
              />
              <span className="font-heading text-2xl font-extrabold tracking-tight text-white">
                Gym<span className="text-brand-red">Flow</span>
                <span className="text-brand-red">.</span>
              </span>
            </Link>

            <p className="text-xs sm:text-sm leading-relaxed max-w-sm text-zinc-400">
              Metropolis premier fitness facility engineered for raw power, athletic conditioning,
              and complete physical transformation. Powered by serverless intelligence and automated
              turnstile biometric technology.
            </p>

            <div className="flex items-center gap-3 pt-2">
              <a
                href="https://wa.me/923259491349"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="WhatsApp"
                className="w-10 h-10 rounded-full bg-zinc-900/90 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-emerald-400 hover:border-emerald-500/40 hover:scale-105 transition-all"
              >
                <i className="fa-brands fa-whatsapp text-sm" />
              </a>
              <a
                href="https://instagram.com"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Instagram"
                className="w-10 h-10 rounded-full bg-zinc-900/90 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-pink-400 hover:border-pink-500/40 hover:scale-105 transition-all"
              >
                <i className="fa-brands fa-instagram text-sm" />
              </a>
              <a
                href="https://facebook.com"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Facebook"
                className="w-10 h-10 rounded-full bg-zinc-900/90 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-blue-400 hover:border-blue-500/40 hover:scale-105 transition-all"
              >
                <i className="fa-brands fa-facebook-f text-sm" />
              </a>
              <a
                href="https://youtube.com"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="YouTube"
                className="w-10 h-10 rounded-full bg-zinc-900/90 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-red-500 hover:border-red-500/40 hover:scale-105 transition-all"
              >
                <i className="fa-brands fa-youtube text-sm" />
              </a>
            </div>
          </div>

          {/* Quick Links (2.5 cols) */}
          <div className="md:col-span-4 lg:col-span-2 lg:col-start-6 space-y-5">
            <h4 className="font-heading text-sm font-semibold tracking-wide text-white">
              Explore
            </h4>
            <ul className="space-y-3.5 text-xs">
              <li>
                <Link href="/" className="hover:text-white transition-colors py-0.5 inline-block">
                  Home
                </Link>
              </li>
              <li>
                <Link href="/about" className="hover:text-white transition-colors py-0.5 inline-block">
                  About &amp; Legacy
                </Link>
              </li>
              <li>
                <Link href="/programs" className="hover:text-white transition-colors py-0.5 inline-block">
                  Training Programs
                </Link>
              </li>
              <li>
                <Link href="/coaching" className="hover:text-white transition-colors py-0.5 inline-block">
                  Apply for Coaching
                </Link>
              </li>
              <li>
                <Link href="/facilities" className="hover:text-white transition-colors py-0.5 inline-block">
                  3 Training Zones
                </Link>
              </li>
              <li>
                <Link href="/pricing" className="hover:text-white transition-colors py-0.5 inline-block">
                  Membership Tiers
                </Link>
              </li>
              <li>
                <Link href="/admission" className="text-brand-red font-medium hover:underline py-0.5 inline-block">
                  Online Admission
                </Link>
              </li>
            </ul>
          </div>

          {/* Portals & Apps (2.5 cols) */}
          <div className="md:col-span-4 lg:col-span-2 space-y-5">
            <h4 className="font-heading text-sm font-semibold tracking-wide text-white">
              Portals
            </h4>
            <ul className="space-y-3.5 text-xs">
              <li>
                <Link href="/login" className="hover:text-brand-red transition-colors flex items-center gap-2 py-0.5">
                  <i className="fa-solid fa-right-to-bracket text-[11px] text-zinc-500" />
                  <span>Account Login</span>
                </Link>
              </li>
              <li>
                <Link href="/app" className="hover:text-brand-red transition-colors flex items-center gap-2 py-0.5">
                  <i className="fa-solid fa-mobile-screen text-[11px] text-zinc-500" />
                  <span>Mobile PWA App</span>
                </Link>
              </li>
              <li>
                <Link href="/scan" className="hover:text-emerald-400 transition-colors flex items-center gap-2 py-0.5">
                  <i className="fa-solid fa-camera text-[11px] text-zinc-500" />
                  <span>Turnstile Scanner</span>
                </Link>
              </li>
              <li>
                <Link href="/admin" className="hover:text-white transition-colors flex items-center gap-2 py-0.5">
                  <i className="fa-solid fa-lock text-[11px] text-zinc-500" />
                  <span>Admin Console</span>
                </Link>
              </li>
              <li>
                <Link href="/contact" className="hover:text-white transition-colors flex items-center gap-2 py-0.5">
                  <i className="fa-regular fa-clock text-[11px] text-zinc-500" />
                  <span>Front Desk &amp; Hours</span>
                </Link>
              </li>
            </ul>
          </div>

          {/* Headquarters (3 cols) */}
          <div className="md:col-span-4 lg:col-span-3 space-y-5">
            <h4 className="font-heading text-sm font-semibold tracking-wide text-white">
              Metropolis HQ
            </h4>
            <div className="space-y-3.5 text-xs text-zinc-400">
              <p className="leading-relaxed">
                Plot 42-C, Commercial Sector 4, Metropolis Boulevard, Islamabad, Pakistan.
              </p>
              <div className="flex items-center gap-2 pt-1">
                <i className="fa-solid fa-phone text-xs text-brand-red" />
                <span className="text-white font-mono font-semibold">+92 325 9491349</span>
              </div>
              <div className="pt-2">
                <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-zinc-900 border border-zinc-800 text-[11px] text-zinc-300">
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                  <span>24/7 Turnstile Gate Pass Active</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom Legal / Copyright Strip with Ample Padding */}
      <div className="border-t border-zinc-900 bg-black/90 py-8 px-6 sm:px-8 lg:px-12">
        <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-5 text-xs text-zinc-500">
          <div>
            &copy; {new Date().getFullYear()} GymFlow Technologies. All rights reserved.
          </div>
          <div className="flex flex-wrap items-center gap-4 sm:gap-6 text-xs">
            <span className="text-brand-red font-medium">10th Monthly Cutoff Policy</span>
            <span className="text-zinc-700">&bull;</span>
            <span className="text-zinc-400">Turnstile Biometric Sync</span>
            <span className="text-zinc-700">&bull;</span>
            <Link href="/contact" className="hover:text-white transition-colors">
              Support Desk
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
