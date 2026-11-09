import type { Metadata, Viewport } from 'next';
import './globals.css';
import PwaRegister from '@/components/pwa-register';

export const metadata: Metadata = {
  title: 'GymFlow Intelligence-as-a-Service (IaaS) Gym Platform',
  description:
    'Power your fitness facility with GymFlow autonomous member CRM, dynamic turnstile QR entry, real-time streaks, and serverless intelligence.',
  manifest: '/manifest.json',
  icons: {
    icon: '/favicon.ico',
    apple: '/assets/images/icons/apple-touch-icon.png',
  },
};

export const viewport: Viewport = {
  themeColor: '#000000',
  width: 'device-width',
  initialScale: 1,
  maximumScale: 1,
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="dark scroll-smooth">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link
          href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Inter:wght@300;400;500;600;700;800;900&display=swap"
          rel="stylesheet"
        />
        <link
          rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossOrigin="anonymous"
          referrerPolicy="no-referrer"
        />
      </head>
      <body className="bg-black text-zinc-100 antialiased min-h-screen selection:bg-brand-red selection:text-white">
        {children}
        <PwaRegister />
      </body>
    </html>
  );
}
