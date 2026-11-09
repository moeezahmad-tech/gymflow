'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import PublicHeader from '@/components/public-header';
import PublicFooter from '@/components/public-footer';

export default function LoginPage() {
  const router = useRouter();
  const [identifier, setIdentifier] = useState('moeezahmad.tech@gmail.com');
  const [password, setPassword] = useState('password123');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    if (!identifier.trim() || !password) {
      setError('Please provide your login credentials.');
      return;
    }

    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      if (identifier.trim().toLowerCase() === 'admin@gymflow.io') {
        router.push('/admin');
      } else {
        // Save current session
        if (typeof window !== 'undefined') {
          localStorage.setItem(
            'gymflow_current_user',
            JSON.stringify({
              fullName: 'Moeez Ahmad',
              email: identifier.includes('@') ? identifier : 'moeezahmad.tech@gmail.com',
              phone: '+92 325 9491349',
              plan: '1m',
              memberId: '2026-4',
              joinedDate: 'Jan 2026',
            })
          );
        }
        router.push('/profile');
      }
    }, 600);
  };

  return (
    <div className="min-h-screen bg-black text-white flex flex-col justify-between selection:bg-brand-red selection:text-white">
      <PublicHeader />

      <main className="flex-1 flex items-center justify-center p-6 py-16">
        <div className="w-full max-w-md bg-zinc-950 border border-zinc-900 rounded-3xl p-8 sm:p-10 space-y-6 shadow-2xl relative overflow-hidden">
          {/* Subtle Crimson Glow */}
          <div className="absolute top-0 left-1/2 -translate-x-1/2 w-72 h-36 bg-brand-red/15 rounded-full blur-3xl pointer-events-none" />

          <div className="text-center space-y-2 relative z-10">
            <span className="inline-block text-[10px] font-bold tracking-widest text-brand-red px-3 py-1 rounded-full bg-brand-red/10 border border-brand-red/30">
              Account Authentication
            </span>
            <h1 className="font-heading text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Welcome Back<span className="text-brand-red">.</span>
            </h1>
            <p className="text-xs text-zinc-400">
              Sign in to manage your athlete profile and training records.
            </p>
          </div>

          {error && (
            <div className="p-3 rounded-xl bg-brand-red/10 border border-brand-red/40 text-brand-red text-xs flex items-center gap-2">
              <i className="fa-solid fa-triangle-exclamation text-xs" />
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleLogin} className="space-y-4 text-xs relative z-10">
            <div>
              <label className="text-xs font-medium text-zinc-400 block mb-1.5">
                Email Address or Member Code
              </label>
              <input
                type="text"
                required
                value={identifier}
                onChange={(e) => setIdentifier(e.target.value)}
                placeholder="athlete@gymflow.io or 2026-4"
                className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red transition-colors"
              />
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="text-xs font-medium text-zinc-400">Password</label>
                <a href="https://wa.me/923259491349?text=Hello%20GymFlow,%20I%20need%20a%20password%20reset%20for%20my%20athlete%20account." target="_blank" rel="noreferrer" className="text-[11px] text-brand-red hover:underline">
                  Forgot?
                </a>
              </div>
              <input
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
                className="w-full bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 focus:outline-none focus:border-brand-red transition-colors"
              />
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full py-3.5 rounded-full bg-brand-red hover:bg-brand-red-hover disabled:opacity-50 text-white font-bold text-xs tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center justify-center gap-2"
            >
              {loading ? (
                <>
                  <i className="fa-solid fa-spinner fa-spin text-xs" />
                  <span>Authenticating Credentials...</span>
                </>
              ) : (
                <>
                  <span>Sign In to Athlete Profile</span>
                  <i className="fa-solid fa-arrow-right text-[10px]" />
                </>
              )}
            </button>
          </form>

          {/* Quick Demo Credentials */}
          <div className="p-3.5 rounded-2xl bg-black border border-zinc-900 text-[11px] text-zinc-500 space-y-1 relative z-10">
            <span className="font-semibold text-zinc-400 block">Test Account (One-Click Ready):</span>
            <div className="flex justify-between font-mono">
              <span>Athlete:</span>
              <span className="text-zinc-300">moeezahmad.tech@gmail.com</span>
            </div>
            <div className="flex justify-between font-mono">
              <span>Password:</span>
              <span className="text-zinc-300">password123</span>
            </div>
          </div>

          <div className="pt-2 text-center text-xs text-zinc-400 relative z-10">
            Don&apos;t have an account yet?{' '}
            <Link href="/admission" className="text-brand-red font-semibold hover:underline ml-1">
              Sign up &amp; get admitted &rarr;
            </Link>
          </div>
        </div>
      </main>

      <PublicFooter />
    </div>
  );
}
