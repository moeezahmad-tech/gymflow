CREATE TYPE "public"."user_role" AS ENUM('admin', 'staff', 'member');--> statement-breakpoint
CREATE TYPE "public"."user_status" AS ENUM('active', 'inactive', 'suspended');--> statement-breakpoint
CREATE TYPE "public"."plan_status" AS ENUM('active', 'inactive');--> statement-breakpoint
CREATE TYPE "public"."subscription_status" AS ENUM('active', 'expired', 'pending', 'cancelled');--> statement-breakpoint
CREATE TYPE "public"."payment_status" AS ENUM('paid', 'pending', 'overdue', 'failed');--> statement-breakpoint
CREATE TYPE "public"."access_status" AS ENUM('granted', 'denied');--> statement-breakpoint
CREATE TYPE "public"."entry_type" AS ENUM('qr_scanner', 'rfid_keycard', 'manual');--> statement-breakpoint
CREATE TYPE "public"."notification_channel" AS ENUM('sms', 'whatsapp', 'email');--> statement-breakpoint
CREATE TYPE "public"."notification_status" AS ENUM('sent', 'delivered', 'failed', 'queued');--> statement-breakpoint
CREATE TYPE "public"."message_status" AS ENUM('unread', 'read');--> statement-breakpoint
CREATE TYPE "public"."campaign_status" AS ENUM('sent', 'draft', 'failed');--> statement-breakpoint
CREATE TYPE "public"."subscriber_status" AS ENUM('subscribed', 'unsubscribed');--> statement-breakpoint
CREATE TABLE "users" (
	"id" serial PRIMARY KEY NOT NULL,
	"member_code" varchar(30) NOT NULL,
	"full_name" varchar(100) NOT NULL,
	"email" varchar(150) NOT NULL,
	"phone" varchar(30),
	"password" text NOT NULL,
	"role" "user_role" DEFAULT 'member' NOT NULL,
	"avatar" text,
	"status" "user_status" DEFAULT 'active' NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL,
	"updated_at" timestamp DEFAULT now() NOT NULL,
	CONSTRAINT "users_member_code_unique" UNIQUE("member_code"),
	CONSTRAINT "users_email_unique" UNIQUE("email")
);
--> statement-breakpoint
CREATE TABLE "membership_plans" (
	"id" serial PRIMARY KEY NOT NULL,
	"name" varchar(100) NOT NULL,
	"slug" varchar(100) NOT NULL,
	"description" text,
	"price" numeric(10, 2) NOT NULL,
	"duration_days" integer DEFAULT 30 NOT NULL,
	"features" jsonb,
	"is_popular" boolean DEFAULT false NOT NULL,
	"status" "plan_status" DEFAULT 'active' NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL,
	CONSTRAINT "membership_plans_slug_unique" UNIQUE("slug")
);
--> statement-breakpoint
CREATE TABLE "subscriptions" (
	"id" serial PRIMARY KEY NOT NULL,
	"user_id" integer NOT NULL,
	"plan_id" integer NOT NULL,
	"start_date" date NOT NULL,
	"end_date" date NOT NULL,
	"status" "subscription_status" DEFAULT 'active' NOT NULL,
	"auto_renew" boolean DEFAULT true NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL,
	"updated_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "payments" (
	"id" serial PRIMARY KEY NOT NULL,
	"user_id" integer NOT NULL,
	"subscription_id" integer,
	"amount" numeric(10, 2) NOT NULL,
	"payment_method" varchar(50) DEFAULT 'Cash' NOT NULL,
	"transaction_id" varchar(100),
	"status" "payment_status" DEFAULT 'paid' NOT NULL,
	"due_date" date,
	"paid_at" timestamp,
	"notes" text,
	"created_at" timestamp DEFAULT now() NOT NULL,
	CONSTRAINT "payments_transaction_id_unique" UNIQUE("transaction_id")
);
--> statement-breakpoint
CREATE TABLE "attendance" (
	"id" serial PRIMARY KEY NOT NULL,
	"user_id" integer NOT NULL,
	"check_in_time" timestamp DEFAULT now() NOT NULL,
	"check_out_time" timestamp,
	"entry_type" "entry_type" DEFAULT 'qr_scanner' NOT NULL,
	"status" "access_status" DEFAULT 'granted' NOT NULL,
	"notes" text
);
--> statement-breakpoint
CREATE TABLE "notifications_log" (
	"id" serial PRIMARY KEY NOT NULL,
	"user_id" integer NOT NULL,
	"payment_id" integer,
	"channel" "notification_channel" DEFAULT 'whatsapp' NOT NULL,
	"recipient" varchar(100) NOT NULL,
	"message" text NOT NULL,
	"status" "notification_status" DEFAULT 'sent' NOT NULL,
	"sent_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "contact_messages" (
	"id" serial PRIMARY KEY NOT NULL,
	"full_name" varchar(100) NOT NULL,
	"email" varchar(150) NOT NULL,
	"phone" varchar(50),
	"program" varchar(100),
	"message" text NOT NULL,
	"status" "message_status" DEFAULT 'unread' NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL,
	"read_at" timestamp
);
--> statement-breakpoint
CREATE TABLE "email_campaigns" (
	"id" serial PRIMARY KEY NOT NULL,
	"subject" varchar(255) NOT NULL,
	"category" varchar(50) DEFAULT 'Promotion' NOT NULL,
	"target_audience" varchar(100) NOT NULL,
	"recipient_count" integer DEFAULT 0 NOT NULL,
	"message" text NOT NULL,
	"status" "campaign_status" DEFAULT 'sent' NOT NULL,
	"sent_by" varchar(100),
	"sent_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "newsletter_subscribers" (
	"id" serial PRIMARY KEY NOT NULL,
	"email" varchar(255) NOT NULL,
	"ip_address" varchar(45),
	"status" "subscriber_status" DEFAULT 'subscribed' NOT NULL,
	"subscribed_at" timestamp DEFAULT now() NOT NULL,
	CONSTRAINT "newsletter_subscribers_email_unique" UNIQUE("email")
);
--> statement-breakpoint
ALTER TABLE "subscriptions" ADD CONSTRAINT "subscriptions_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "subscriptions" ADD CONSTRAINT "subscriptions_plan_id_membership_plans_id_fk" FOREIGN KEY ("plan_id") REFERENCES "public"."membership_plans"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "payments" ADD CONSTRAINT "payments_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "payments" ADD CONSTRAINT "payments_subscription_id_subscriptions_id_fk" FOREIGN KEY ("subscription_id") REFERENCES "public"."subscriptions"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "attendance" ADD CONSTRAINT "attendance_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "notifications_log" ADD CONSTRAINT "notifications_log_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "notifications_log" ADD CONSTRAINT "notifications_log_payment_id_payments_id_fk" FOREIGN KEY ("payment_id") REFERENCES "public"."payments"("id") ON DELETE set null ON UPDATE no action;