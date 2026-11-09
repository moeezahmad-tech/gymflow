import { pgTable, serial, integer, date, boolean, timestamp, pgEnum } from 'drizzle-orm/pg-core';
import { users } from './users';
import { membershipPlans } from './plans';

export const subscriptionStatusEnum = pgEnum('subscription_status', [
  'active',
  'expired',
  'pending',
  'cancelled',
]);

export const subscriptions = pgTable('subscriptions', {
  id: serial('id').primaryKey(),
  userId: integer('user_id')
    .notNull()
    .references(() => users.id, { onDelete: 'cascade' }),
  planId: integer('plan_id')
    .notNull()
    .references(() => membershipPlans.id, { onDelete: 'cascade' }),
  startDate: date('start_date').notNull(),
  endDate: date('end_date').notNull(),
  status: subscriptionStatusEnum('status').default('active').notNull(),
  autoRenew: boolean('auto_renew').default(true).notNull(),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull(),
});

export type Subscription = typeof subscriptions.$inferSelect;
export type NewSubscription = typeof subscriptions.$inferInsert;

