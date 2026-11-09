'use client';

import { useEffect, useState } from 'react';

export default function PwaRegister() {
  const [deferredPrompt, setDeferredPrompt] = useState<any>(null);
  const [showBanner, setShowBanner] = useState(false);

  useEffect(() => {
    // 1. Register Service Worker
    if (typeof window !== 'undefined' && 'serviceWorker' in navigator) {
      navigator.serviceWorker
        .register('/sw.js')
        .then((reg) => {
          console.log('[GymFlow PWA] Service Worker registered with scope:', reg.scope);
        })
        .catch((err) => {
          console.warn('[GymFlow PWA] Service Worker registration failed:', err);
        });
    }

    // 2. Capture beforeinstallprompt for PWA install trigger
    const handleBeforeInstall = (e: Event) => {
      e.preventDefault();
      setDeferredPrompt(e);
      // Only show banner if on mobile or not in standalone mode
      if (!window.matchMedia('(display-mode: standalone)').matches) {
        setShowBanner(true);
      }
    };

    window.addEventListener('beforeinstallprompt', handleBeforeInstall);

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstall);
    };
  }, []);

  const handleInstallClick = async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    console.log('[GymFlow PWA] Install choice outcome:', outcome);
    setDeferredPrompt(null);
    setShowBanner(false);
  };

  if (!showBanner) return null;

  return (
    <div className="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 sm:max-w-sm z-50 p-4 rounded-2xl bg-zinc-950/95 border border-brand-red/60 backdrop-blur-xl shadow-2xl shadow-brand-red/20 text-white flex items-center justify-between gap-3 animate-fadeIn">
      <div className="flex items-center gap-3 overflow-hidden">
        <img
          src="/images/app_logo.png"
          alt="GymFlow"
          className="w-10 h-10 rounded-xl object-contain shrink-0"
        />
        <div className="overflow-hidden">
          <span className="text-xs font-bold text-white block truncate">Install GymFlow PWA</span>
          <span className="text-[10px] text-zinc-400 block truncate">
            Fast turnstile pass on home screen
          </span>
        </div>
      </div>

      <div className="flex items-center gap-2 shrink-0">
        <button
          onClick={handleInstallClick}
          className="px-4 py-1.5 rounded-full bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold shadow-md shadow-brand-red/30 transition-all"
        >
          Install
        </button>
        <button
          onClick={() => setShowBanner(false)}
          className="w-7 h-7 rounded-lg bg-zinc-900 text-zinc-400 hover:text-white flex items-center justify-center text-xs"
        >
          ✕
        </button>
      </div>
    </div>
  );
}

