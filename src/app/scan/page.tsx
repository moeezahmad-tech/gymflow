'use client';

import { useEffect, useRef, useState, useCallback, Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import jsQR from 'jsqr';

interface VerifiedMember {
  id: number;
  fullName: string;
  memberCode: string;
  email?: string;
  phone?: string;
  status: string;
  avatar?: string | null;
  planName: string;
  subscriptionStatus?: string;
  expiryDate?: string;
}

interface ScanResult {
  access: 'granted' | 'denied';
  message: string;
  member?: VerifiedMember;
  timestamp: string;
  rawCode: string;
}

/**
 * Web Audio API synthesizer for turnstile sounds (offline, 0 external asset dependency)
 */
function playChime(type: 'success' | 'error') {
  try {
    const ctx = new (window.AudioContext || (window as any).webkitAudioContext)();
    if (type === 'success') {
      // Pleasant high two-tone gate chime
      const osc1 = ctx.createOscillator();
      const osc2 = ctx.createOscillator();
      const gain = ctx.createGain();

      osc1.type = 'sine';
      osc2.type = 'triangle';
      osc1.frequency.setValueAtTime(880, ctx.currentTime); // A5
      osc1.frequency.setValueAtTime(1320, ctx.currentTime + 0.12); // E6
      osc2.frequency.setValueAtTime(440, ctx.currentTime);

      gain.gain.setValueAtTime(0.3, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);

      osc1.connect(gain);
      osc2.connect(gain);
      gain.connect(ctx.destination);

      osc1.start();
      osc2.start();
      osc1.stop(ctx.currentTime + 0.5);
      osc2.stop(ctx.currentTime + 0.5);
    } else {
      // Error warning buzzer
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(150, ctx.currentTime);
      osc.frequency.setValueAtTime(110, ctx.currentTime + 0.15);

      gain.gain.setValueAtTime(0.4, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start();
      osc.stop(ctx.currentTime + 0.4);
    }
  } catch (e) {
    console.warn('[Audio] Sound playback failed:', e);
  }
}

function ScannerContent() {
  const searchParams = useSearchParams();
  const initialCode = searchParams.get('code');

  const videoRef = useRef<HTMLVideoElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);

  const [cameraActive, setCameraActive] = useState(false);
  const [cameraError, setCameraError] = useState<string | null>(null);
  const [facingMode, setFacingMode] = useState<'environment' | 'user'>('environment');

  const [isProcessing, setIsProcessing] = useState(false);
  const [lastScannedCode, setLastScannedCode] = useState<string | null>(null);
  const [scanResult, setScanResult] = useState<ScanResult | null>(null);
  const [gateOpen, setGateOpen] = useState(false);
  const [manualCode, setManualCode] = useState('');
  const [recentLogs, setRecentLogs] = useState<ScanResult[]>([]);

  // Turnstile verification function
  const verifyPass = useCallback(
    async (codeToVerify: string) => {
      if (!codeToVerify.trim() || isProcessing) return;

      setIsProcessing(true);
      setLastScannedCode(codeToVerify);

      try {
        const res = await fetch(
          `/api/attendance?code=${encodeURIComponent(codeToVerify)}&log=true`
        );
        const data = await res.json();

        const result: ScanResult = {
          access: data.access === 'granted' ? 'granted' : 'denied',
          message: data.message || (data.access === 'granted' ? 'Access Granted' : 'Access Denied'),
          member: data.member,
          timestamp: new Date().toLocaleTimeString(),
          rawCode: codeToVerify,
        };

        setScanResult(result);
        setRecentLogs((prev) => [result, ...prev.slice(0, 9)]);

        if (result.access === 'granted') {
          playChime('success');
          setGateOpen(true);
          setTimeout(() => setGateOpen(false), 4500);
        } else {
          playChime('error');
          setGateOpen(false);
        }
      } catch (err) {
        console.error('[Scan Verification Error]:', err);
        const errResult: ScanResult = {
          access: 'denied',
          message: 'Network verification failed or turnstile offline.',
          timestamp: new Date().toLocaleTimeString(),
          rawCode: codeToVerify,
        };
        setScanResult(errResult);
        playChime('error');
      } finally {
        // Cooldown before scanning another QR code
        setTimeout(() => {
          setIsProcessing(false);
        }, 2000);
      }
    },
    [isProcessing]
  );

  // Auto-verify if ?code= is present in URL (e.g. from iPhone Camera tap)
  useEffect(() => {
    if (initialCode) {
      verifyPass(initialCode);
    }
  }, [initialCode, verifyPass]);

  // Keyboard wedge listener for physical USB barcode scanners
  useEffect(() => {
    let buffer = '';
    let lastKeyTime = Date.now();

    const handleKeyDown = (e: KeyboardEvent) => {
      // Don't intercept if user is typing in the manual input box
      if (document.activeElement?.tagName === 'INPUT') return;

      const currentTime = Date.now();
      if (currentTime - lastKeyTime > 150) {
        buffer = ''; // Reset buffer if typing was slow (human)
      }
      lastKeyTime = currentTime;

      if (e.key === 'Enter') {
        if (buffer.length >= 2) {
          verifyPass(buffer);
          buffer = '';
        }
      } else if (e.key.length === 1) {
        buffer += e.key;
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [verifyPass]);

  // Camera start / stop logic
  const startCamera = async () => {
    setCameraError(null);
    try {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        throw new Error('Camera API is not supported on this browser or context (requires HTTPS).');
      }

      const stream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: facingMode,
          width: { ideal: 1280 },
          height: { ideal: 720 },
        },
      });

      if (videoRef.current) {
        videoRef.current.srcObject = stream;
        videoRef.current.setAttribute('playsinline', 'true');
        await videoRef.current.play();
        setCameraActive(true);
      }
    } catch (err: any) {
      console.error('[Camera Access Error]:', err);
      setCameraError(err.message || 'Unable to access camera.');
      setCameraActive(false);
    }
  };

  const stopCamera = () => {
    if (videoRef.current && videoRef.current.srcObject) {
      const stream = videoRef.current.srcObject as MediaStream;
      stream.getTracks().forEach((track) => track.stop());
      videoRef.current.srcObject = null;
    }
    setCameraActive(false);
  };

  // Continuous frame scanning loop with jsQR
  useEffect(() => {
    if (!cameraActive) return;

    let animationFrameId: number;

    const scanFrame = () => {
      if (videoRef.current && videoRef.current.readyState === videoRef.current.HAVE_ENOUGH_DATA) {
        const video = videoRef.current;
        const canvas = canvasRef.current;

        if (canvas) {
          const ctx = canvas.getContext('2d', { willReadFrequently: true });
          if (ctx) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const decoded = jsQR(imageData.data, imageData.width, imageData.height, {
              inversionAttempts: 'dontInvert',
            });

            if (decoded && decoded.data) {
              const scannedText = decoded.data.trim();
              if (scannedText && scannedText !== lastScannedCode && !isProcessing) {
                verifyPass(scannedText);
              }
            }
          }
        }
      }

      animationFrameId = requestAnimationFrame(scanFrame);
    };

    animationFrameId = requestAnimationFrame(scanFrame);

    return () => {
      cancelAnimationFrame(animationFrameId);
    };
  }, [cameraActive, isProcessing, lastScannedCode, verifyPass]);

  // Clean up camera on unmount
  useEffect(() => {
    return () => {
      stopCamera();
    };
  }, []);

  const handleManualSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (manualCode.trim()) {
      verifyPass(manualCode.trim());
      setManualCode('');
    }
  };

  return (
    <div className="min-h-screen w-full bg-black text-white p-4 sm:p-8 flex flex-col items-center selection:bg-brand-red selection:text-white">
      {/* Top Navbar */}
      <div className="w-full flex items-center justify-between pb-6 border-b border-zinc-900">
        <div className="flex items-center gap-3">
          <img
            src="/images/logo.png"
            alt="GymFlow Logo"
            className="h-10 w-auto object-contain drop-shadow-[0_0_8px_rgba(255,42,42,0.4)]"
            onError={(e) => {
              e.currentTarget.style.display = 'none';
            }}
          />
          <div>
            <Link href="/" className="font-heading text-xl font-extrabold tracking-tight text-white">
              Gym<span className="text-brand-red">Flow</span>
              <span className="text-brand-red">.</span>
            </Link>
            <span className="block text-[10px] text-zinc-400 font-mono">
              Turnstile Hardware Gate Verifier
            </span>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <Link
            href="/admin/attendance"
            className="px-3.5 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-xs font-bold text-zinc-300 transition-colors"
          >
            Admin Feed
          </Link>
          <Link
            href="/app"
            className="px-3.5 py-1.5 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-bold transition-colors"
          >
            Member App
          </Link>
        </div>
      </div>

      {/* Main Scanner Section */}
      <div className="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
        {/* Left Column: Live Camera & Scanner HUD (7 cols) */}
        <div className="lg:col-span-7 space-y-4">
          <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900 shadow-2xl relative overflow-hidden">
            {/* Header / Mode Indicator */}
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-2">
                <span
                  className={`w-3 h-3 rounded-full ${
                    cameraActive ? 'bg-emerald-500 animate-pulse' : 'bg-zinc-700'
                  }`}
                />
                <span className="text-xs font-semibold tracking-wider text-zinc-300">
                  {cameraActive ? 'Live Camera Feed' : 'Optical Scanner Standby'}
                </span>
              </div>

              <div className="flex items-center gap-2">
                {cameraActive && (
                  <button
                    onClick={() => {
                      stopCamera();
                      setFacingMode((prev) => (prev === 'environment' ? 'user' : 'environment'));
                      setTimeout(startCamera, 200);
                    }}
                    type="button"
                    className="text-[11px] px-2.5 py-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-400 font-bold"
                  >
                    <i className="fa-solid fa-camera-rotate mr-1" /> Flip Camera
                  </button>
                )}
                {!cameraActive ? (
                  <button
                    onClick={startCamera}
                    type="button"
                    className="text-xs px-4 py-2 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all flex items-center gap-1.5"
                  >
                    <i className="fa-solid fa-camera text-xs" /> Start Camera
                  </button>
                ) : (
                  <button
                    onClick={stopCamera}
                    type="button"
                    className="text-xs px-4 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 font-bold transition-all"
                  >
                    Stop Camera
                  </button>
                )}
              </div>
            </div>

            {/* Video Viewfinder Container */}
            <div className="relative aspect-video sm:aspect-[4/3] rounded-2xl bg-black border border-zinc-800 overflow-hidden flex items-center justify-center">
              <video
                ref={videoRef}
                className={`w-full h-full object-cover ${cameraActive ? 'block' : 'hidden'}`}
                autoPlay
                muted
                playsInline
              />
              <canvas ref={canvasRef} className="hidden" />

              {!cameraActive && (
                <div className="text-center p-6 space-y-3">
                  <div className="w-16 h-16 mx-auto rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-600">
                    <i className="fa-solid fa-qrcode text-3xl" />
                  </div>
                  <h4 className="font-heading text-lg font-bold text-white">
                    Ready to Scan QR Pass
                  </h4>
                  <p className="text-xs text-zinc-500 max-w-xs mx-auto">
                    Click &ldquo;Start Camera&rdquo; to use your webcam or phone camera, or plug in
                    any 2D USB/Bluetooth barcode scanner.
                  </p>
                </div>
              )}

              {/* Viewfinder Target Reticle & Laser Sweep (Active when camera is running) */}
              {cameraActive && (
                <div className="absolute inset-0 pointer-events-none flex items-center justify-center">
                  {/* Targeting Reticle */}
                  <div className="w-56 h-56 sm:w-64 sm:h-64 border-2 border-brand-red/60 rounded-3xl relative shadow-[0_0_20px_rgba(255,42,42,0.3)]">
                    {/* Corner accents */}
                    <div className="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-brand-red rounded-tl-xl" />
                    <div className="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-brand-red rounded-tr-xl" />
                    <div className="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-brand-red rounded-bl-xl" />
                    <div className="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-brand-red rounded-br-xl" />

                    {/* Animated Sweeping Red Laser */}
                    <div className="absolute left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent shadow-[0_0_12px_#ff2a2a] animate-[scanLaser_2.2s_ease-in-out_infinite]" />
                  </div>

                  <span className="absolute bottom-4 px-3 py-1 rounded-full bg-black/80 backdrop-blur border border-zinc-800 text-[10px] text-zinc-400 font-mono">
                    Align QR inside reticle
                  </span>
                </div>
              )}

              {/* Processing Loader Overlay */}
              {isProcessing && (
                <div className="absolute inset-0 bg-black/70 backdrop-blur-sm flex flex-col items-center justify-center space-y-3 z-10 animate-fadeIn">
                  <div className="w-10 h-10 border-4 border-brand-red border-t-transparent rounded-full animate-spin" />
                  <span className="text-xs font-mono font-bold text-white tracking-wider">
                    Verifying Credential with Neon DB...
                  </span>
                </div>
              )}
            </div>

            {cameraError && (
              <div className="mt-4 p-3 rounded-xl bg-red-950/60 border border-red-800 text-red-300 text-xs flex items-center gap-2">
                <i className="fa-solid fa-triangle-exclamation" />
                <span>{cameraError}</span>
              </div>
            )}
          </div>

          {/* Quick Hardware Wedge & Manual Code Input */}
          <div className="p-6 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-4">
            <h4 className="text-xs font-semibold tracking-wider text-zinc-400 flex items-center gap-2">
              <i className="fa-solid fa-keyboard text-brand-red" />
              <span>Barcode Wedge / Manual Member Code Input</span>
            </h4>

            <form onSubmit={handleManualSubmit} className="flex gap-2">
              <input
                type="text"
                value={manualCode}
                onChange={(e) => setManualCode(e.target.value)}
                placeholder="Scan with gun or type code (e.g. 2026-4)..."
                className="flex-1 bg-black border border-zinc-800 rounded-xl px-4 py-3 text-white placeholder-zinc-600 font-mono text-sm focus:outline-none focus:border-brand-red transition-all"
              />
              <button
                type="submit"
                className="px-6 py-3 rounded-xl bg-zinc-900 hover:bg-brand-red border border-zinc-800 hover:border-brand-red text-white text-xs font-semibold tracking-wider transition-all"
              >
                Verify
              </button>
            </form>

            {/* Quick Test Pill Buttons */}
            <div className="flex flex-wrap items-center gap-2 pt-2">
              <span className="text-[10px] font-semibold text-zinc-500">Fast Test IDs:</span>
              {[
                { code: '2026-4', label: '2026-4 (Moeez Ahmad - Active)' },
                { code: '2026-1', label: '2026-1 (Admin Director)' },
                { code: '2026-2', label: '2026-2 (Staff)' },
                { code: '2026-3', label: '2026-3 (Active Athlete)' },
                { code: 'INVALID-99', label: 'INVALID-99 (Denied Test)' },
              ].map((t) => (
                <button
                  key={t.code}
                  type="button"
                  onClick={() => verifyPass(t.code)}
                  className="px-2.5 py-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-[10px] font-mono text-zinc-400 hover:text-white transition-colors"
                >
                  {t.label}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Right Column: Turnstile Status HUD & Athlete Profile Card (5 cols) */}
        <div className="lg:col-span-5 space-y-4">
          {/* Hardware Gate Clearance Indicator */}
          <div
            className={`p-6 rounded-3xl border transition-all duration-300 relative overflow-hidden ${
              gateOpen
                ? 'bg-emerald-950/40 border-emerald-500/80 shadow-[0_0_30px_rgba(16,185,129,0.3)]'
                : scanResult?.access === 'denied'
                ? 'bg-red-950/40 border-red-500/80 shadow-[0_0_30px_rgba(239,68,68,0.3)]'
                : 'bg-zinc-950 border-zinc-900'
            }`}
          >
            {/* Visual Gate Arms / Turnstile Simulation */}
            <div className="flex items-center justify-between pb-4 border-b border-zinc-900">
              <div className="flex items-center gap-2">
                <span
                  className={`w-3.5 h-3.5 rounded-full ${
                    gateOpen
                      ? 'bg-emerald-400 animate-ping'
                      : scanResult?.access === 'denied'
                      ? 'bg-red-500 animate-pulse'
                      : 'bg-zinc-700'
                  }`}
                />
                <span className="font-mono text-xs font-semibold tracking-wider text-white">
                  Turnstile Status: {gateOpen ? 'GATE OPENED' : scanResult?.access === 'denied' ? 'LOCKED / DENIED' : 'SECURED / READY'}
                </span>
              </div>
              <span className="text-[10px] font-mono text-zinc-500">GATE #01</span>
            </div>

            {/* Turnstile Graphic */}
            <div className="py-6 text-center">
              <div
                className={`w-24 h-24 mx-auto rounded-3xl flex items-center justify-center text-4xl transition-all duration-300 ${
                  gateOpen
                    ? 'bg-emerald-500 text-black scale-110 shadow-lg shadow-emerald-500/40'
                    : scanResult?.access === 'denied'
                    ? 'bg-red-600 text-white scale-105 shadow-lg shadow-red-600/40'
                    : 'bg-zinc-900 text-zinc-600 border border-zinc-800'
                }`}
              >
                <i
                  className={`fa-solid ${
                    gateOpen
                      ? 'fa-lock-open'
                      : scanResult?.access === 'denied'
                      ? 'fa-ban'
                      : 'fa-shield-halved'
                  }`}
                />
              </div>

              <h3 className="font-heading text-2xl font-semibold mt-4">
                {gateOpen ? (
                  <span className="text-emerald-400">ACCESS GRANTED</span>
                ) : scanResult?.access === 'denied' ? (
                  <span className="text-red-400">ACCESS REJECTED</span>
                ) : (
                  <span className="text-zinc-500">AWAITING CREDENTIAL</span>
                )}
              </h3>

              <p className="text-xs text-zinc-400 mt-1">
                {scanResult ? scanResult.message : 'Present QR pass to camera or scanner gun.'}
              </p>
            </div>

            {/* Verified Athlete Dossier */}
            {scanResult?.member && (
              <div className="mt-2 pt-4 border-t border-zinc-900 space-y-3 bg-black/40 rounded-2xl p-4">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 rounded-xl bg-brand-red flex items-center justify-center font-heading text-xl font-bold text-white shrink-0">
                    {scanResult.member.fullName.charAt(0)}
                  </div>
                  <div className="overflow-hidden">
                    <h5 className="font-heading text-lg font-bold text-white truncate">
                      {scanResult.member.fullName}
                    </h5>
                    <span className="font-mono text-xs text-brand-red font-bold block">
                      ID: {scanResult.member.memberCode}
                    </span>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-2 pt-2 text-[11px] border-t border-zinc-800/80">
                  <div>
                    <span className="text-zinc-500 block text-[9px] font-semibold">Tier Plan</span>
                    <span className="text-zinc-200 font-semibold truncate block">
                      {scanResult.member.planName}
                    </span>
                  </div>
                  <div>
                    <span className="text-zinc-500 block text-[9px] font-semibold">Membership</span>
                    <span className="text-emerald-400 font-semibold">
                      {scanResult.member.status}
                    </span>
                  </div>
                </div>

                <div className="pt-1 text-[10px] text-zinc-400 font-mono flex items-center justify-between">
                  <span>Verified: {scanResult.timestamp}</span>
                  <span className="text-emerald-400 font-bold">✓ Logged to Neon DB</span>
                </div>
              </div>
            )}
          </div>

          {/* Live Recent Gate Logs */}
          <div className="p-5 rounded-3xl bg-zinc-950 border border-zinc-900 space-y-3">
            <div className="flex items-center justify-between">
              <h4 className="text-xs font-semibold tracking-wider text-zinc-400">
                Recent Scans This Session
              </h4>
              <span className="text-[10px] font-mono text-zinc-500">{recentLogs.length} scans</span>
            </div>

            <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
              {recentLogs.length === 0 ? (
                <div className="text-center py-6 text-xs text-zinc-600 font-mono">
                  No passes scanned yet.
                </div>
              ) : (
                recentLogs.map((log, idx) => (
                  <div
                    key={idx}
                    className="p-2.5 rounded-xl bg-black border border-zinc-900 flex items-center justify-between text-xs"
                  >
                    <div className="flex items-center gap-2">
                      <span
                        className={`w-2 h-2 rounded-full ${
                          log.access === 'granted' ? 'bg-emerald-400' : 'bg-red-500'
                        }`}
                      />
                      <span className="font-mono font-bold text-white">
                        {log.member?.memberCode || log.rawCode}
                      </span>
                      <span className="text-zinc-400 text-[11px] truncate max-w-[120px]">
                        {log.member?.fullName || 'Unknown'}
                      </span>
                    </div>
                    <span className="text-[10px] text-zinc-500 font-mono">{log.timestamp}</span>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function TurnstileScannerPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-black text-white flex items-center justify-center">
          <div className="w-8 h-8 border-4 border-brand-red border-t-transparent rounded-full animate-spin" />
        </div>
      }
    >
      <ScannerContent />
    </Suspense>
  );
}


