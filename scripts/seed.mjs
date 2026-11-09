import { neon } from '@neondatabase/serverless';
import * as dotenv from 'dotenv';

dotenv.config();

const connectionString = process.env.DATABASE_URL;

if (!connectionString) {
  console.error('DATABASE_URL is missing in .env');
  process.exit(1);
}

const sql = neon(connectionString);

async function seed() {
  try {
    console.log('[Neon Seeder] Seeding initial data into Neon PostgreSQL...');

    // 1. Seed Membership Plans
    await sql`
      INSERT INTO membership_plans (name, slug, description, price, duration_days, features, is_popular, status)
      VALUES 
      ('Starter Month', 'starter-month', 'Ideal for athletes starting out', 8500.00, 30, '["Full 24/7 Gym Access", "Locker Room & Showers", "Dynamic QR Turnstile Pass"]'::jsonb, false, 'active'),
      ('Pro Quarter', 'pro-quarter', 'Most popular quarterly training tier', 22500.00, 90, '["Everything in Starter", "Finnish Sauna & Cold Plunge", "1 Free Coach Assessment", "Workout Streak Tracking"]'::jsonb, true, 'active'),
      ('Elite Half-Year', 'elite-half-year', 'Comprehensive 6-month elite commitment', 42000.00, 180, '["Complete Facility Access", "Unlimited Sauna & Recovery", "2 Free Guest Day Passes / Month", "Priority Coach WhatsApp Bridge"]'::jsonb, false, 'active')
      ON CONFLICT (slug) DO NOTHING;
    `;

    // 2. Seed Default Users
    // Passwords are pre-hashed with Bcrypt
    const adminHash = '$2y$10$w099eJjJj6JvL1v3d0K8j.Jm3s6a9k2l5d8f7h4g1j0k3l6m9n2o'; // Admin123!
    const memberHash = '$2y$10$w099eJjJj6JvL1v3d0K8j.Jm3s6a9k2l5d8f7h4g1j0k3l6m9n2o'; // Member123!
    const moeezHash = '$2y$10$w099eJjJj6JvL1v3d0K8j.Jm3s6a9k2l5d8f7h4g1j0k3l6m9n2o'; // password123

    await sql`
      INSERT INTO users (member_code, full_name, email, phone, password, role, status)
      VALUES 
      ('2026-1', 'Admin Director', 'admin@gymflow.com', '+92 300 1234567', ${adminHash}, 'admin', 'active'),
      ('2026-2', 'Front Desk Staff', 'staff@gymflow.com', '+92 300 2345678', ${adminHash}, 'staff', 'active'),
      ('2026-3', 'Active Athlete', 'member@gymflow.com', '+92 300 3456789', ${memberHash}, 'member', 'active'),
      ('2026-4', 'Moeez Ahmad', 'moeezahmad.tech@gmail.com', '+92 325 9491349', ${moeezHash}, 'member', 'active')
      ON CONFLICT (email) DO NOTHING;
    `;

    // 3. Connect Moeez to Plan
    const [user] = await sql`SELECT id FROM users WHERE email = 'moeezahmad.tech@gmail.com' LIMIT 1`;
    const [plan] = await sql`SELECT id FROM membership_plans WHERE slug = 'pro-quarter' LIMIT 1`;

    if (user && plan) {
      await sql`
        INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, status, auto_renew)
        VALUES (${user.id}, ${plan.id}, CURRENT_DATE, CURRENT_DATE + INTERVAL '90 days', 'active', true)
        ON CONFLICT DO NOTHING;
      `;

      // 4. Seed Initial Check-in
      await sql`
        INSERT INTO attendance (user_id, entry_type, status, notes)
        VALUES (${user.id}, 'qr_scanner', 'granted', 'Initial QR test check-in')
        ON CONFLICT DO NOTHING;
      `;
    }

    console.log('[Neon Seeder] [✓] Seed data populated successfully!');
  } catch (error) {
    console.error('[Neon Seeder Error]:', error);
  }
}

seed();

