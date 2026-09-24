---
lang: en
permalink: faq/questions
title: Frequently asked questions
description: Answers to common questions about the MailChimp connector.
updated: 2026-09-24
---

## Frequently asked questions {.faq}

### The self-test refuses my API key

MailChimp keys end with the code of a data center, after the last dash, for example `-us14`.
A key copied without that suffix has an invalid format and is refused before any call is made.

Copy the key whole from your MailChimp account.

### The audience selector is empty, or does not appear

Splash reads your audiences from MailChimp with your API key. If the selector is missing, the key
is either absent or refused: save a valid key first, then edit the connection again.

If the key is valid but your account holds no audience, create one in MailChimp.

### Changes made in MailChimp are not reported to Splash

Check the **Update of WebHooks** block of your connection, and run the update when it reports a
failure.

Webhooks are attached to the audience: run the update again after changing it.

### A customer was not written to MailChimp

Check that the customer carries an **e-mail address**: it is the identifier of a member, and it
is required by MailChimp.

Your Splash logs name the customers that were refused, and why.

### My mappings broke after changing the audience

Merge fields belong to the audience. The fields of the previous one no longer exist, so the
mappings built on them no longer match: rebuild them on the merge tags of the new audience.

### Does the connector delete members?

No. Deleting a customer in your shop never deletes the MailChimp member. Unsubscribing marks the
member as unsubscribed, it does not remove it from the audience.
