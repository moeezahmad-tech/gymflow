import { pgTable, serial, varchar, text, integer, timestamp, pgEnum } from 'drizzle-orm/pg-core';

export const subscriberStatusEnum = pgEnum('subscriber_status', ['subscribed', 'unsubscribed']);
export const campaignStatusEnum = pgEnum('campaign_status', ['sent', 'draft', 'failed']);

export const newsletterSubscribers = pgTable('newsletter_subscribers', {
  id: serial('id').primaryKey(),
  email: varchar('email', { length: 255 }).notNull().unique(),
  ipAddress: varchar('ip_address', { length: 45 }),
  status: subscriberStatusEnum('status').default('subscribed').notNull(),
  subscribedAt: timestamp('subscribed_at').defaultNow().notNull(),
});

export const emailCampaigns = pgTable('email_campaigns', {
  id: serial('id').primaryKey(),
  subject: varchar('subject', { length: 255 }).notNull(),
  category: varchar('category', { length: 50 }).default('Promotion').notNull(),
  targetAudience: varchar('target_audience', { length: 100 }).notNull(),
  recipientCount: integer('recipient_count').default(0).notNull(),
  message: text('message').notNull(),
  status: campaignStatusEnum('status').default('sent').notNull(),
  sentBy: varchar('sent_by', { length: 100 }),
  sentAt: timestamp('sent_at').defaultNow().notNull(),
});

export type NewsletterSubscriber = typeof newsletterSubscribers.$inferSelect;
export type NewNewsletterSubscriber = typeof newsletterSubscribers.$inferInsert;
export type EmailCampaign = typeof emailCampaigns.$inferSelect;
export type NewEmailCampaign = typeof emailCampaigns.$inferInsert;

