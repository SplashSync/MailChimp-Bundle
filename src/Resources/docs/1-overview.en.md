---
lang: en
permalink: overview
title: MailChimp Connector
description: Synchronize your customers with your MailChimp audience, with their merge fields and their subscription state.
updated: 2026-09-24
---

### Overview

MailChimp is an e-mail marketing platform.
The MailChimp connector links your MailChimp account to Splash through the MailChimp API V3: the
customers of your shops and your ERP become **members of a MailChimp audience**, and stay up to
date there.

Nothing needs to be installed on the MailChimp side: the connection is fully managed by Splash,
using your API key.

### Synchronized objects

| Object | Role |
|---|---|
| **Customer** | A member of your MailChimp audience: e-mail, merge fields and subscription state |

### How it works

- A customer written in Splash is **created or updated** in your audience, identified by its
  e-mail address.
- The **merge fields** of your audience (`FNAME`, `LNAME`, and every merge tag you defined) are
  exposed as fields: they map to your other applications like any other field.
- The **subscription state** of a member is readable and writable, as well as its VIP flag.
- MailChimp **notifies Splash** whenever a member changes on its side, through webhooks the
  connector configures for you.

### Good to know

- The connector works on **one audience at a time**: the list chosen in the connection settings
  is the one members are written to.
- Merge fields belong to the audience, not to the account: changing the audience changes the
  available fields.
- Splash never deletes a customer of your shop because a member was removed in MailChimp.

### Getting started

1. **Install the connector** and check the connection (*Getting started* section).
2. Choose your **audience** and adjust the options (*Configuration* section).
3. Read how **members** are synchronized (*Usage* section).
