---
lang: fr
permalink: start/install
title: Installer le connecteur MailChimp
description: Clé d'API, création de la connexion dans Splash, choix de l'audience, vérifications et activation des webhooks.
updated: 2026-09-24
---

### Prérequis

- Un compte **MailChimp**, avec au moins une audience.
- Une **clé d'API MailChimp**, créée depuis votre compte sous *Account & billing > Extras > API
  keys*.
- Un compte **Splash Sync Premium** actif.

> [!NOTE]
> Une clé d'API MailChimp se termine par le code de votre centre de données, par exemple `-us14`.
> Copiez-la entièrement : Splash lit ce suffixe pour savoir quel serveur appeler.

> [!CAUTION]
> Une clé d'API donne un **accès complet** à votre compte MailChimp. Ne la partagez pas, et ne
> l'envoyez jamais par e-mail.

### Étape 1 — Créer la connexion MailChimp

Depuis votre compte Splash, ajoutez une nouvelle connexion **MailChimp API V3**.

Le connecteur dialogue directement avec l'API MailChimp : il n'y a rien à installer côté
MailChimp.

### Étape 2 — Saisir votre clé d'API

Renseignez le champ de la connexion :

| Champ | Contenu |
|---|---|
| **API Key** | La clé d'API de votre compte MailChimp, suffixe de centre de données inclus |

Enregistrez. Le connecteur appelle MailChimp, lit votre compte et **charge vos audiences**.

> [!NOTE]
> Le sélecteur de liste n'apparaît qu'une fois une clé valide enregistrée : Splash doit d'abord
> lire vos audiences dans MailChimp pour pouvoir vous les proposer.

### Étape 3 — Choisir l'audience à synchroniser

Éditez à nouveau la connexion. Un sélecteur **Liste** affiche désormais vos audiences MailChimp :
choisissez celle où vos clients doivent être écrits.

### Étape 4 — Vérifier la connexion

Lancez l'auto-test **Configuration du connecteur MailChimp**. Il vérifie que la clé d'API a un
format valide, que MailChimp répond, et qu'une audience est sélectionnée.

Si le test échoue, vérifiez que la clé a été copiée entièrement, avec son suffixe de centre de
données.

### Étape 5 — Activer les webhooks

Les webhooks permettent à MailChimp de **notifier Splash en temps réel** dès qu'un membre change.
Sans eux, les modifications faites dans MailChimp ne remontent pas automatiquement.

Dans le bloc **Mise à jour des WebHooks** de votre connexion, lancez la mise à jour. Le
connecteur crée ou met à jour les webhooks nécessaires dans votre audience.

Le bloc doit alors indiquer que la configuration est faite.

> [!TIP]
> Relancez la mise à jour si le bloc signale un échec, par exemple après un changement de clé ou
> d'audience.

### Et ensuite ?

Votre connexion est prête. Consultez les **Options du connecteur**, puis activez la
synchronisation de vos clients.
