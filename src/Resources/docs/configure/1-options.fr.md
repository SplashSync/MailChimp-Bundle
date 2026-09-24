---
lang: fr
permalink: configure/options
title: Options du connecteur
description: Clé d'API, audience, et configuration des webhooks.
updated: 2026-09-24
---

### Connexion à l'API

| Option | Rôle |
|---|---|
| **API Key** | La clé avec laquelle Splash joint votre compte MailChimp |

La clé porte le code de votre centre de données, après le dernier tiret. Splash le lit pour
construire l'adresse de l'API : une clé tronquée est refusée par l'auto-test.

Changer la clé recharge vos audiences au prochain enregistrement. Vérifiez ensuite celle qui est
sélectionnée : elle peut ne plus exister dans le nouveau compte.

### Audience

| Option | Rôle |
|---|---|
| **Liste** | L'audience MailChimp où sont écrits les membres |

Le sélecteur propose les audiences lues dans votre compte. Il n'apparaît qu'une fois une clé
d'API valide enregistrée.

> [!IMPORTANT]
> Les merge fields appartiennent à l'audience. Changer d'audience change les champs disponibles,
> et peut casser les mappings construits sur la précédente.

> [!NOTE]
> Changer d'audience ne **déplace pas** les membres déjà synchronisés. Les précédents restent où
> ils sont, et seul ce que Splash écrit ensuite part dans la nouvelle audience.

### Webhooks

Le bloc **Mise à jour des WebHooks** indique si MailChimp peut notifier Splash. Lancez la mise à
jour lorsqu'il signale un échec : le connecteur crée ou répare alors les webhooks dans votre
audience.

Les webhooks sont attachés à l'audience : relancez la mise à jour après en avoir changé.

### Auto-test

L'auto-test **Configuration du connecteur MailChimp** vérifie, dans l'ordre, que la clé d'API a
un format valide, que MailChimp répond, et qu'une audience est sélectionnée. Lancez-le après
chaque modification de la connexion.
