'use client';

import Link from 'next/link';
import Image from 'next/image';
import { usePathname } from 'next/navigation';
import { useState } from 'react';

export default function PublicHeader() {
  const pathname = usePathname();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const navLinks = [
    { name: 'Home', href: '/' },
    { name: 'About', href: '/about' },
    { name: 'Programs', href: '/programs' },
    { name: 'Coaching', href: '/coaching' },
    { name: 'Facilities', href: '/facilities' },
    { name: 'Memberships', href: '/pricing' },
    { name: 'Admission', href: '/admission' },
    { name: 'Contact', href: '/contact' },
  ];

  return (
    <header className="sticky top-0 z-50 bg-black/95 backdrop-blur-2xl border-b border-zinc-900 px-6 py-4 transition-all">
      <div className="max-w-7xl mx-auto flex items-center justify-between">
        {/* Brand Logo */}
        <Link href="/" className="flex items-center gap-2.5 group shrink-0">
          <img
            src="/images/logo.png"
            alt="GymFlow"
            className="h-8 sm:h-9 w-auto object-contain drop-shadow-[0_0_12px_rgba(255,42,42,0.4)] group-hover:scale-105 transition-transform"
            onError={(e) => {
              e.currentTarget.style.display = 'none';
            }}
          />
          <span className="font-heading text-xl sm:text-2xl font-extrabold tracking-tight text-white">
            Gym<span className="text-brand-red">Flow</span>
            <span className="text-brand-red">.</span>
          </span>
        </Link>

        {/* Desktop Navigation Links */}
        <nav className="hidden lg:flex items-center gap-7 text-sm font-medium text-zinc-400">
          {navLinks.map((link) => {
            const isActive = pathname === link.href;
            return (
              <Link
                key={link.name}
                href={link.href}
                className={`transition-colors py-1 ${
                  isActive ? 'text-white font-semibold' : 'hover:text-white'
                }`}
              >
                <span>{link.name}</span>
              </Link>
            );
          })}
        </nav>

        {/* Right Auth & Trial Actions */}
        <div className="hidden sm:flex items-center gap-3">
          <Link
            href="/login"
            className="text-xs sm:text-sm font-medium text-zinc-300 hover:text-white transition-colors px-3 py-1.5 rounded-full hover:bg-zinc-900"
          >
            Log In
          </Link>

          <Link
            href="/admission"
            className="text-xs sm:text-sm font-semibold text-white bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 px-4 py-2 rounded-full transition-all"
          >
            Sign Up
          </Link>

          <Link
            href="/profile"
            className="w-9 h-9 rounded-full bg-brand-red/10 border border-brand-red/30 hover:border-brand-red text-brand-red hover:text-white flex items-center justify-center text-xs font-bold transition-all"
            title="Athlete Profile"
          >
            <i className="fa-solid fa-user text-xs" />
          </Link>
        </div>

        {/* Mobile Hamburger Toggle */}
        <button
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          className="lg:hidden w-10 h-10 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-300 hover:text-white"
          aria-label="Toggle Navigation Menu"
        >
          <i className={`fa-solid ${mobileMenuOpen ? 'fa-xmark' : 'fa-bars'} text-lg`} />
        </button>
      </div>

      {/* Mobile Drawer Menu */}
      {mobileMenuOpen && (
        <div className="lg:hidden border-t border-zinc-900 mt-3 pt-4 pb-6 space-y-3 animate-fadeIn">
          <nav className="flex flex-col space-y-2 text-sm font-medium">
            {navLinks.map((link) => {
              const isActive = pathname === link.href;
              return (
                <Link
                  key={link.name}
                  href={link.href}
                  onClick={() => setMobileMenuOpen(false)}
                  className={`px-3.5 py-2 rounded-xl transition-colors ${
                    isActive
                      ? 'bg-brand-red/10 text-brand-red border border-brand-red/30 font-semibold'
                      : 'text-zinc-400 hover:text-white hover:bg-zinc-900'
                  }`}
                >
                  {link.name}
                </Link>
              );
            })}
          </nav>

          <div className="pt-4 border-t border-zinc-900 flex flex-col gap-2">
            <div className="grid grid-cols-2 gap-2">
              <Link
                href="/login"
                onClick={() => setMobileMenuOpen(false)}
                className="py-2.5 rounded-full bg-zinc-900 text-center text-sm font-medium text-zinc-300 border border-zinc-800"
              >
                Log In
              </Link>
              <Link
                href="/admission"
                onClick={() => setMobileMenuOpen(false)}
                className="py-2.5 rounded-full bg-brand-red text-center text-sm font-semibold text-white shadow-md shadow-brand-red/30"
              >
                Sign Up
              </Link>
            </div>
            <Link
              href="/profile"
              onClick={() => setMobileMenuOpen(false)}
              className="py-2.5 rounded-full bg-zinc-950 text-center text-sm font-medium text-zinc-300 border border-zinc-800 flex items-center justify-center gap-2"
            >
              <i className="fa-solid fa-user text-brand-red text-xs" />
              <span>Athlete Profile</span>
            </Link>
          </div>
        </div>
      )}
    </header>
  );
}
