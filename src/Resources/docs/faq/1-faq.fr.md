---
lang: fr
permalink: faq/questions
title: Questions fréquentes
description: Réponses aux questions courantes sur le connecteur MailChimp.
updated: 2026-09-24
---

## Questions fréquentes {.faq}

### L'auto-test refuse ma clé d'API

Les clés MailChimp se terminent par le code d'un centre de données, après le dernier tiret, par
exemple `-us14`. Une clé copiée sans ce suffixe a un format invalide et est refusée avant même
tout appel.

Copiez la clé entièrement depuis votre compte MailChimp.

### Le sélecteur d'audience est vide, ou n'apparaît pas

Splash lit vos audiences dans MailChimp avec votre clé d'API. Si le sélecteur manque, la clé est
absente ou refusée : enregistrez d'abord une clé valide, puis éditez à nouveau la connexion.

Si la clé est valide mais que votre compte ne contient aucune audience, créez-en une dans
MailChimp.

### Les modifications faites dans MailChimp ne remontent pas dans Splash

Vérifiez le bloc **Mise à jour des WebHooks** de votre connexion, et lancez la mise à jour
lorsqu'il signale un échec.

Les webhooks sont attachés à l'audience : relancez la mise à jour après en avoir changé.

### Un client n'a pas été écrit dans MailChimp

Vérifiez que le client porte une **adresse e-mail** : c'est l'identifiant d'un membre, et elle
est obligatoire pour MailChimp.

Vos journaux Splash nomment les clients refusés, et pourquoi.

### Mes mappings ont cassé après un changement d'audience

Les merge fields appartiennent à l'audience. Ceux de la précédente n'existent plus, les mappings
construits dessus ne correspondent donc plus : reconstruisez-les sur les merge tags de la
nouvelle audience.

### Le connecteur supprime-t-il des membres ?

Non. Supprimer un client dans votre boutique ne supprime jamais le membre MailChimp. Le
désabonnement marque le membre comme désabonné, il ne le retire pas de l'audience.
