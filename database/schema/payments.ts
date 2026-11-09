import { pgTable, serial, integer, numeric, varchar, timestamp, date, text, pgEnum } from 'drizzle-orm/pg-core';
import { users } from './users';
import { subscriptions } from './subscriptions';

export const paymentStatusEnum = pgEnum('payment_status', [
  'paid',
  'pending',
  'overdue',
  'failed',
]);

export const payments = pgTable('payments', {
  id: serial('id').primaryKey(),
  userId: integer('user_id')
    .notNull()
    .references(() => users.id, { onDelete: 'cascade' }),
  subscriptionId: integer('subscription_id')
    .references(() => subscriptions.id, { onDelete: 'set null' }),
  amount: numeric('amount', { precision: 10, scale: 2 }).notNull(), // Amount in PKR
  paymentMethod: varchar('payment_method', { length: 50 }).default('Cash').notNull(), // Cash, Card, JazzCash, EasyPaisa
  transactionId: varchar('transaction_id', { length: 100 }).unique(), // e.g. TXN-87412-A101
  status: paymentStatusEnum('status').default('paid').notNull(),
  dueDate: date('due_date'),
  paidAt: timestamp('paid_at'),
  notes: text('notes'),
  createdAt: timestamp('created_at').defaultNow().notNull(),
});

export type Payment = typeof payments.$inferSelect;
export type NewPayment = typeof payments.$inferInsert;

