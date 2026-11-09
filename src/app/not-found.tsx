import Link from 'next/link';

export default function NotFound() {
  return (
    <div className="min-h-screen bg-black text-white flex flex-col items-center justify-center p-6 text-center selection:bg-brand-red selection:text-white">
      <span className="font-heading text-7xl sm:text-8xl font-extrabold text-brand-red mb-2">
        404
      </span>
      <h1 className="font-heading text-2xl sm:text-3xl font-bold tracking-tight mb-3 text-white">
        Page Not Found
      </h1>
      <p className="text-zinc-400 text-xs sm:text-sm max-w-md mb-8 leading-relaxed">
        The requested tactical dossier or operational page does not exist or has been relocated within the GymFlow platform.
      </p>
      <Link
        href="/"
        className="px-6 py-3 rounded-xl bg-brand-red hover:bg-brand-red-hover text-white text-xs font-semibold tracking-wider shadow-lg shadow-brand-red/30 transition-all"
      >
        Return to Homepage
      </Link>
    </div>
  );
}

