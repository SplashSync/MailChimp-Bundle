---
lang: fr
permalink: overview
title: Connecteur MailChimp
description: Synchronisez vos clients avec votre audience MailChimp, avec leurs merge fields et leur état d'abonnement.
updated: 2026-09-24
---

### Présentation

MailChimp est une plateforme d'e-mail marketing.
Le connecteur MailChimp relie votre compte MailChimp à Splash via l'API MailChimp V3 : les
clients de vos boutiques et de votre ERP deviennent des **membres d'une audience MailChimp**, et
y restent à jour.

Rien n'est à installer côté MailChimp : la connexion est entièrement gérée par Splash, à partir
de votre clé d'API.

### Objets synchronisés

| Objet | Rôle |
|---|---|
| **Client** | Un membre de votre audience MailChimp : e-mail, merge fields et état d'abonnement |

### Fonctionnement

- Un client écrit dans Splash est **créé ou mis à jour** dans votre audience, identifié par son
  adresse e-mail.
- Les **merge fields** de votre audience (`FNAME`, `LNAME`, et chacun des merge tags que vous
  avez définis) sont exposés comme des champs : ils se mappent avec vos autres applications comme
  n'importe quel autre champ.
- L'**état d'abonnement** d'un membre est lisible et modifiable, tout comme son indicateur VIP.
- MailChimp **notifie Splash** dès qu'un membre change de son côté, via des webhooks que le
  connecteur configure pour vous.

### Bon à savoir

- Le connecteur travaille sur **une audience à la fois** : celle choisie dans les paramètres de
  la connexion est celle où les membres sont écrits.
- Les merge fields appartiennent à l'audience, pas au compte : changer d'audience change les
  champs disponibles.
- Splash ne supprime jamais un client de votre boutique parce qu'un membre a été retiré dans
  MailChimp.

### Pour démarrer

1. **Installez le connecteur** et vérifiez la connexion (section *Démarrer*).
2. Choisissez votre **audience** et ajustez les options (section *Configuration*).
3. Lisez comment les **membres** sont synchronisés (section *Utilisation*).
