import { relations } from 'drizzle-orm';
import { users } from './users';
import { membershipPlans } from './plans';
import { subscriptions } from './subscriptions';
import { payments } from './payments';
import { attendance } from './attendance';
import { notificationsLog } from './notifications';
import { contactMessages } from './messages';
import { newsletterSubscribers, emailCampaigns } from './newsletter';

// Re-export all schema tables and enums
export * from './users';
export * from './plans';
export * from './subscriptions';
export * from './payments';
export * from './attendance';
export * from './notifications';
export * from './messages';
export * from './newsletter';

// -----------------------------------------------------------------------------
// Drizzle Entity Relationships (Relations)
// -----------------------------------------------------------------------------

export const usersRelations = relations(users, ({ many }) => ({
  subscriptions: many(subscriptions),
  payments: many(payments),
  attendance: many(attendance),
  notifications: many(notificationsLog),
}));

export const membershipPlansRelations = relations(membershipPlans, ({ many }) => ({
  subscriptions: many(subscriptions),
}));

export const subscriptionsRelations = relations(subscriptions, ({ one, many }) => ({
  user: one(users, {
    fields: [subscriptions.userId],
    references: [users.id],
  }),
  plan: one(membershipPlans, {
    fields: [subscriptions.planId],
    references: [membershipPlans.id],
  }),
  payments: many(payments),
}));

export const paymentsRelations = relations(payments, ({ one, many }) => ({
  user: one(users, {
    fields: [payments.userId],
    references: [users.id],
  }),
  subscription: one(subscriptions, {
    fields: [payments.subscriptionId],
    references: [subscriptions.id],
  }),
  notifications: many(notificationsLog),
}));

export const attendanceRelations = relations(attendance, ({ one }) => ({
  user: one(users, {
    fields: [attendance.userId],
    references: [users.id],
  }),
}));

export const notificationsLogRelations = relations(notificationsLog, ({ one }) => ({
  user: one(users, {
    fields: [notificationsLog.userId],
    references: [users.id],
  }),
  payment: one(payments, {
    fields: [notificationsLog.paymentId],
    references: [payments.id],
  }),
}));

