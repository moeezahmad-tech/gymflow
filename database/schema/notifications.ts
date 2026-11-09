import { pgTable, serial, integer, varchar, text, timestamp, pgEnum } from 'drizzle-orm/pg-core';
import { users } from './users';
import { payments } from './payments';

export const notificationChannelEnum = pgEnum('notification_channel', ['sms', 'whatsapp', 'email']);
export const notificationStatusEnum = pgEnum('notification_status', [
  'sent',
  'delivered',
  'failed',
  'queued',
]);

export const notificationsLog = pgTable('notifications_log', {
  id: serial('id').primaryKey(),
  userId: integer('user_id')
    .notNull()
    .references(() => users.id, { onDelete: 'cascade' }),
  paymentId: integer('payment_id')
    .references(() => payments.id, { onDelete: 'set null' }),
  channel: notificationChannelEnum('channel').default('whatsapp').notNull(),
  recipient: varchar('recipient', { length: 100 }).notNull(),
  message: text('message').notNull(),
  status: notificationStatusEnum('status').default('sent').notNull(),
  sentAt: timestamp('sent_at').defaultNow().notNull(),
});

export type NotificationLog = typeof notificationsLog.$inferSelect;
export type NewNotificationLog = typeof notificationsLog.$inferInsert;

