---
lang: en
permalink: doc/contacts
title: Members
description: How customers become MailChimp members, which merge fields are exposed, and how subscription is handled.
updated: 2026-09-24
---

### Identification

A member is identified by its **e-mail address**. Writing a customer whose e-mail already exists
in the audience updates that member, it never creates a duplicate.

Changing the e-mail address of a customer therefore **moves** the member: MailChimp sees a new
identity.

### Exposed fields

| Field | Direction | Notes |
|---|---|---|
| **Email** | read & write | The identifier of the member, required |
| **Is Subscribed** | read & write | Subscription state in the audience |
| **Is VIP** | read & write | The VIP flag of MailChimp |
| **Merge fields** | read & write | Every merge tag defined in the audience |
| **Date Created** | read only | When the member signed up |
| **Last modification** | read only | When MailChimp last changed the member |

### Merge fields

MailChimp merge fields are not a fixed list: they are the ones defined **in your audience**, such
as `FNAME` and `LNAME`, plus every merge tag you added yourself.

The connector reads them and exposes each one as a field, under its merge tag. They appear in
your mappings like any other field.

> [!TIP]
> Add a merge field in MailChimp, then reload your connection: the new field is available for
> mapping right away.

> [!IMPORTANT]
> Merge fields belong to the audience, not to the account. Switching audience changes the list of
> fields, and the mappings that used the previous ones stop matching.

### Subscription

**Is Subscribed** carries the state of the member in the audience. Unsubscribing from Splash
marks the member as unsubscribed in MailChimp; it does not remove it from the audience.

A member who unsubscribed from a MailChimp e-mail is reported back to Splash by the webhooks: the
state travels both ways.

### What is never done

- Splash **never deletes** a MailChimp member when a customer is deleted in your shop.
- MailChimp **statistics** — opens, clicks, campaigns — are not synchronized.
- Members are not imported in bulk: they reach MailChimp as your other applications write them.
