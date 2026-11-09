import { pgTable, serial, varchar, text, timestamp, pgEnum } from 'drizzle-orm/pg-core';

export const messageStatusEnum = pgEnum('message_status', ['unread', 'read']);

export const contactMessages = pgTable('contact_messages', {
  id: serial('id').primaryKey(),
  fullName: varchar('full_name', { length: 100 }).notNull(),
  email: varchar('email', { length: 150 }).notNull(),
  phone: varchar('phone', { length: 50 }),
  program: varchar('program', { length: 100 }),
  message: text('message').notNull(),
  status: messageStatusEnum('status').default('unread').notNull(),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  readAt: timestamp('read_at'),
});

export type ContactMessage = typeof contactMessages.$inferSelect;
export type NewContactMessage = typeof contactMessages.$inferInsert;

