'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';

export default function AdminSidebar() {
  const pathname = usePathname();

  const navItems = [
    { name: 'Dashboard', href: '/admin', icon: 'fa-gauge-high' },
    { name: 'New Admissions', href: '/admin/admissions', icon: 'fa-user-check', badge: '3 New' },
    { name: 'Members Directory', href: '/admin/members', icon: 'fa-users' },
    { name: 'Billing & Ledger', href: '/admin/billing', icon: 'fa-file-invoice-dollar' },
    { name: 'Gate Attendance', href: '/admin/attendance', icon: 'fa-door-open' },
    { name: 'Membership Plans', href: '/admin/plans', icon: 'fa-layer-group' },
    { name: '+ Add Member', href: '/admin/members-add', icon: 'fa-user-plus' },
    { name: 'Client Inquiries', href: '/admin/messages', icon: 'fa-envelope-open-text' },
  ];

  return (
    <aside className="w-full md:w-64 bg-zinc-950 border-r border-zinc-900 p-5 flex flex-col justify-between shrink-0 min-h-screen">
      <div className="space-y-6">
        {/* Brand & Status with Logo Image */}
        <div className="pb-5 border-b border-zinc-900 flex items-center justify-between">
          <Link href="/admin" className="flex items-center gap-2.5 group">
            <img
              src="/images/logo.png"
              alt="GymFlow Logo"
              className="h-8 w-auto object-contain drop-shadow-[0_0_8px_rgba(255,42,42,0.4)] group-hover:scale-105 transition-transform"
              onError={(e) => {
                e.currentTarget.style.display = 'none';
              }}
            />
            <div>
              <span className="font-heading text-xl font-bold tracking-tight text-white leading-none block">
                Gym<span className="text-brand-red">Flow</span>
                <span className="text-brand-red">.</span>
              </span>
              <span className="text-[10px] text-zinc-400 font-medium block mt-0.5">
                Admin Console
              </span>
            </div>
          </Link>
          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] text-emerald-400 font-bold">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
            Live
          </span>
        </div>

        {/* Navigation Items */}
        <nav className="space-y-1.5 text-xs font-medium">
          {navItems.map((item) => {
            const isActive = pathname === item.href;
            return (
              <Link
                key={item.name}
                href={item.href}
                className={`flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all ${
                  isActive
                    ? 'bg-brand-red text-white shadow-lg shadow-brand-red/30'
                    : 'text-zinc-400 hover:text-white hover:bg-zinc-900'
                }`}
              >
                <div className="flex items-center gap-3">
                  <i className={`fa-solid ${item.icon} text-sm w-4 text-center`} />
                  <span>{item.name}</span>
                </div>
                {item.badge && !isActive && (
                  <span className="px-2 py-0.5 rounded-full bg-brand-red/20 text-brand-red border border-brand-red/40 text-[9px] font-bold font-mono">
                    {item.badge}
                  </span>
                )}
                {item.badge && isActive && (
                  <span className="px-2 py-0.5 rounded-full bg-white/20 text-white text-[9px] font-bold font-mono">
                    {item.badge}
                  </span>
                )}
              </Link>
            );
          })}
        </nav>

        {/* System Shortcuts */}
        <div className="pt-4 border-t border-zinc-900 space-y-1">
          <Link
            href="/app"
            target="_blank"
            className="flex items-center gap-3 px-3.5 py-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900 text-xs font-semibold tracking-wide transition-colors"
          >
            <i className="fa-solid fa-mobile-screen text-xs w-4 text-center text-brand-red" />
            <span>Mobile App View</span>
          </Link>

          <Link
            href="/"
            target="_blank"
            className="flex items-center gap-3 px-3.5 py-2 rounded-xl text-zinc-500 hover:text-white hover:bg-zinc-900 text-xs font-semibold tracking-wide transition-colors"
          >
            <i className="fa-solid fa-arrow-up-right-from-square text-xs w-4 text-center" />
            <span>Public Website</span>
          </Link>
        </div>
      </div>

      {/* Admin Profile Bottom Card */}
      <div className="pt-4 border-t border-zinc-900 mt-auto">
        <div className="flex items-center gap-3 p-2.5 rounded-2xl bg-black border border-zinc-900">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-red to-brand-red-dark flex items-center justify-center text-white font-bold text-xs shadow-md shrink-0">
            AD
          </div>
          <div className="overflow-hidden flex-1">
            <span className="text-xs font-bold text-white block truncate">Admin Director</span>
            <span className="text-[10px] text-brand-red font-semibold block tracking-wide">
              Super Administrator
            </span>
          </div>
        </div>
      </div>
    </aside>
  );
}
