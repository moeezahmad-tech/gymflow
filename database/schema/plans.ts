import { pgTable, serial, varchar, text, numeric, integer, boolean, timestamp, pgEnum, jsonb } from 'drizzle-orm/pg-core';

export const planStatusEnum = pgEnum('plan_status', ['active', 'inactive']);

export const membershipPlans = pgTable('membership_plans', {
  id: serial('id').primaryKey(),
  name: varchar('name', { length: 100 }).notNull(),
  slug: varchar('slug', { length: 100 }).notNull().unique(),
  description: text('description'),
  price: numeric('price', { precision: 10, scale: 2 }).notNull(), // Price in PKR
  durationDays: integer('duration_days').default(30).notNull(),
  features: jsonb('features'), // Array of feature bullets e.g. ["Gym Access", "Sauna"]
  isPopular: boolean('is_popular').default(false).notNull(),
  status: planStatusEnum('status').default('active').notNull(),
  createdAt: timestamp('created_at').defaultNow().notNull(),
});

export type MembershipPlan = typeof membershipPlans.$inferSelect;
export type NewMembershipPlan = typeof membershipPlans.$inferInsert;

