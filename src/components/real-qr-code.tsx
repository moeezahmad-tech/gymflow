'use client';

import { useEffect, useState } from 'react';
import QRCode from 'qrcode';

interface RealQrCodeProps {
  value: string;
  size?: number;
  className?: string;
  downloadable?: boolean;
  darkColor?: string;
  lightColor?: string;
  showFormatToggle?: boolean;
}

export default function RealQrCode({
  value,
  size = 200,
  className = '',
  downloadable = false,
  darkColor = '#000000',
  lightColor = '#ffffff',
  showFormatToggle = false,
}: RealQrCodeProps) {
  const [dataUrl, setDataUrl] = useState<string>('');
  const [error, setError] = useState<string | null>(null);
  const [format, setFormat] = useState<'code' | 'url'>('url');
  const [currentOrigin, setCurrentOrigin] = useState<string>('');

  useEffect(() => {
    if (typeof window !== 'undefined') {
      setCurrentOrigin(window.location.origin);
    }
  }, []);

  const encodedPayload =
    format === 'url' && currentOrigin
      ? `${currentOrigin}/scan?code=${encodeURIComponent(value)}`
      : value;

  useEffect(() => {
    if (!encodedPayload) return;

    QRCode.toDataURL(encodedPayload, {
      width: size * 2, // 2x resolution for retina / high-DPI smartphone screens
      margin: 2,
      errorCorrectionLevel: 'H', // High error correction level (30% redundancy)
      color: {
        dark: darkColor,
        light: lightColor,
      },
    })
      .then((url) => {
        setDataUrl(url);
        setError(null);
      })
      .catch((err) => {
        console.error('[RealQRCode] Generation failed:', err);
        setError('Failed to generate QR code');
      });
  }, [encodedPayload, size, darkColor, lightColor]);

  const handleDownload = () => {
    if (!dataUrl) return;
    const a = document.createElement('a');
    a.href = dataUrl;
    a.download = `gymflow-pass-${value.replace(/[^a-zA-Z0-9_-]/g, '_')}.png`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  };

  return (
    <div className={`flex flex-col items-center justify-center ${className}`}>
      {/* High-Contrast Crisp Container */}
      <div
        className="bg-white rounded-2xl p-2.5 shadow-2xl flex items-center justify-center relative overflow-hidden transition-transform duration-200 hover:scale-[1.02]"
        style={{ width: size, height: size }}
      >
        {dataUrl ? (
          <img
            src={dataUrl}
            alt={`Real Scannable QR: ${encodedPayload}`}
            className="w-full h-full object-contain select-none"
          />
        ) : error ? (
          <div className="text-red-500 text-xs text-center p-2">{error}</div>
        ) : (
          <div className="flex flex-col items-center justify-center space-y-2">
            <span className="w-3 h-3 bg-brand-red rounded-full animate-ping" />
            <span className="text-[10px] text-zinc-500 font-mono">Generating QR...</span>
          </div>
        )}
      </div>

      {/* Format Toggle (URL for phone camera vs Raw Code for hardware 2D scanner) */}
      {showFormatToggle && (
        <div className="mt-3 flex items-center bg-zinc-900 border border-zinc-800 rounded-lg p-0.5 text-[10px]">
          <button
            type="button"
            onClick={() => setFormat('url')}
            className={`px-2.5 py-1 rounded font-bold transition-all ${
              format === 'url'
                ? 'bg-brand-red text-white shadow'
                : 'text-zinc-400 hover:text-white'
            }`}
          >
            <i className="fa-solid fa-mobile-screen mr-1 text-[9px]" /> Phone Camera URL
          </button>
          <button
            type="button"
            onClick={() => setFormat('code')}
            className={`px-2.5 py-1 rounded font-bold transition-all ${
              format === 'code'
                ? 'bg-zinc-800 text-white shadow'
                : 'text-zinc-400 hover:text-white'
            }`}
          >
            <i className="fa-solid fa-barcode mr-1 text-[9px]" /> Hardware ID
          </button>
        </div>
      )}

      {/* Action Row */}
      <div className="mt-2 flex items-center gap-3">
        {downloadable && dataUrl && (
          <button
            onClick={handleDownload}
            type="button"
            className="text-xs font-medium text-zinc-400 hover:text-white flex items-center gap-1.5 transition-colors"
          >
            <i className="fa-solid fa-download text-[10px]" />
            <span>Save Pass PNG</span>
          </button>
        )}
      </div>
    </div>
  );
}
