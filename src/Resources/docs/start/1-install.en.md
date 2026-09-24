---
lang: en
permalink: start/install
title: Install the MailChimp connector
description: API key, connection setup in Splash, audience selection, checks and webhooks activation.
updated: 2026-09-24
---

### Requirements

- A **MailChimp** account, with at least one audience.
- A **MailChimp API key**, created from your account under *Account & billing > Extras > API
  keys*.
- An active **Splash Sync Premium** account.

> [!NOTE]
> A MailChimp API key ends with the code of your data center, for example `-us14`. Copy it whole:
> Splash reads that suffix to know which server to call.

> [!CAUTION]
> An API key grants **full access** to your MailChimp account. Do not share it, and never send it
> by e-mail.

### Step 1 — Create the MailChimp connection

From your Splash account, add a new **MailChimp API V3** connection.

The connector talks directly to the MailChimp API: there is nothing to install on the MailChimp
side.

### Step 2 — Enter your API key

Fill in the connection field:

| Field | Content |
|---|---|
| **API Key** | The API key of your MailChimp account, data center suffix included |

Save. The connector calls MailChimp, reads your account and **loads your audiences**.

> [!NOTE]
> The list selector only appears once a valid key has been saved: Splash has to read your
> audiences from MailChimp before it can offer them.

### Step 3 — Choose the audience to synchronize

Edit the connection again. A **List** selector now shows your MailChimp audiences: pick the one
your customers must be written to.

### Step 4 — Check the connection

Run the **MailChimp Connector Configuration** self-test. It checks that the API key has a valid
format, that MailChimp answers, and that an audience is selected.

If the test fails, check that the key was copied whole, with its data center suffix.

### Step 5 — Enable webhooks

Webhooks let MailChimp **notify Splash in real time** whenever a member changes. Without them,
changes made in MailChimp are not automatically reported.

In the **Update of WebHooks** block of your connection, run the update. The connector creates or
updates the required webhooks in your audience.

The block should then display that the configuration is done.

> [!TIP]
> Run the update again if the block reports a failure, for example after changing your key or
> your audience.

### What's next?

Your connection is ready. Read the **Connector options**, then enable synchronization for your
customers.
