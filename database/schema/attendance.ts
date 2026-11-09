import { pgTable, serial, integer, timestamp, pgEnum, text } from 'drizzle-orm/pg-core';
import { users } from './users';

export const entryTypeEnum = pgEnum('entry_type', ['qr_scanner', 'rfid_keycard', 'manual']);
export const accessStatusEnum = pgEnum('access_status', ['granted', 'denied']);

export const attendance = pgTable('attendance', {
  id: serial('id').primaryKey(),
  userId: integer('user_id')
    .notNull()
    .references(() => users.id, { onDelete: 'cascade' }),
  checkInTime: timestamp('check_in_time').defaultNow().notNull(),
  checkOutTime: timestamp('check_out_time'),
  entryType: entryTypeEnum('entry_type').default('qr_scanner').notNull(),
  status: accessStatusEnum('status').default('granted').notNull(),
  notes: text('notes'),
});

export type Attendance = typeof attendance.$inferSelect;
export type NewAttendance = typeof attendance.$inferInsert;

