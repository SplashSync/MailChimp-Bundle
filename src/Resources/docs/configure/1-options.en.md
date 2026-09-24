---
lang: en
permalink: configure/options
title: Connector options
description: API key, audience, and webhooks configuration.
updated: 2026-09-24
---

### API connection

| Option | Role |
|---|---|
| **API Key** | The key Splash uses to reach your MailChimp account |

The key carries the code of your data center, after the last dash. Splash reads it to build the
address of the API: a truncated key is refused by the self-test.

Changing the key reloads your audiences on the next save. Check the selected one afterwards: it
may no longer exist in the new account.

### Audience

| Option | Role |
|---|---|
| **List** | The MailChimp audience members are written to |

The selector offers the audiences read from your account. It only appears once a valid API key
has been saved.

> [!IMPORTANT]
> Merge fields belong to the audience. Changing the audience changes the available fields, and
> may break the mappings built on the previous one.

> [!NOTE]
> Changing the audience does **not** move the members already synchronized. The previous ones stay
> where they are, and only what Splash writes afterwards goes to the new audience.

### Webhooks

The **Update of WebHooks** block reports whether MailChimp can notify Splash. Run the update when
it reports a failure: the connector then creates or repairs the webhooks in your audience.

Webhooks are attached to the audience: run the update again after changing it.

### Self-test

The **MailChimp Connector Configuration** self-test checks, in order, that the API key has a
valid format, that MailChimp answers, and that an audience is selected. Run it after every change
to the connection.
