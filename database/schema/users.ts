import { pgTable, serial, varchar, text, timestamp, pgEnum } from 'drizzle-orm/pg-core';

export const userRoleEnum = pgEnum('user_role', ['admin', 'staff', 'member']);
export const userStatusEnum = pgEnum('user_status', ['active', 'inactive', 'suspended']);

export const users = pgTable('users', {
  id: serial('id').primaryKey(),
  memberCode: varchar('member_code', { length: 30 }).notNull().unique(), // e.g. 2026-1
  fullName: varchar('full_name', { length: 100 }).notNull(),
  email: varchar('email', { length: 150 }).notNull().unique(),
  phone: varchar('phone', { length: 30 }),
  password: text('password').notNull(), // Bcrypt / Argon2 hash
  role: userRoleEnum('role').default('member').notNull(),
  avatar: text('avatar'),
  status: userStatusEnum('status').default('active').notNull(),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull(),
});

export type User = typeof users.$inferSelect;
export type NewUser = typeof users.$inferInsert;

