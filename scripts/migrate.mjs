import { neon } from '@neondatabase/serverless';
import * as dotenv from 'dotenv';
import fs from 'fs';

dotenv.config();

const connectionString = process.env.DATABASE_URL;

if (!connectionString) {
  console.error('DATABASE_URL is missing in .env');
  process.exit(1);
}

const sql = neon(connectionString);

async function runMigration() {
  try {
    console.log('[Neon Migration] Reading generated SQL migration...');
    const sqlContent = fs.readFileSync('./drizzle/0000_perfect_centennial.sql', 'utf8');

    // Split statements by semicolon and run each
    const statements = sqlContent
      .split('--> statement-breakpoint')
      .map((s) => s.trim())
      .filter((s) => s.length > 0);

    console.log(`[Neon Migration] Executing ${statements.length} statements on Neon PostgreSQL...`);

    for (let i = 0; i < statements.length; i++) {
      const stmt = statements[i];
      await sql(stmt);
      process.stdout.write(`.` );
    }

    console.log('\n[Neon Migration] [✓] All tables and enums successfully created in Neon PostgreSQL!');
  } catch (error) {
    console.error('\n[Neon Migration Error]:', error);
  }
}

runMigration();

