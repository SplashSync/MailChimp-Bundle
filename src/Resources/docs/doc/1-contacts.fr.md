---
lang: fr
permalink: doc/contacts
title: Membres
description: Comment les clients deviennent des membres MailChimp, quels merge fields sont exposés, et comment l'abonnement est géré.
updated: 2026-09-24
---

### Identification

Un membre est identifié par son **adresse e-mail**. Écrire un client dont l'e-mail existe déjà
dans l'audience met à jour ce membre, cela ne crée jamais de doublon.

Changer l'adresse e-mail d'un client **déplace** donc le membre : MailChimp y voit une nouvelle
identité.

### Champs exposés

| Champ | Sens | Remarques |
|---|---|---|
| **E-mail** | lecture & écriture | L'identifiant du membre, obligatoire |
| **Abonné** | lecture & écriture | État d'abonnement dans l'audience |
| **VIP** | lecture & écriture | L'indicateur VIP de MailChimp |
| **Merge fields** | lecture & écriture | Chaque merge tag défini dans l'audience |
| **Date de création** | lecture seule | Date d'inscription du membre |
| **Dernière modification** | lecture seule | Dernier changement du membre côté MailChimp |

### Merge fields

Les merge fields MailChimp ne sont pas une liste figée : ce sont ceux définis **dans votre
audience**, comme `FNAME` et `LNAME`, plus chacun des merge tags que vous avez ajoutés.

Le connecteur les lit et expose chacun comme un champ, sous son merge tag. Ils apparaissent dans
vos mappings comme n'importe quel autre champ.

> [!TIP]
> Ajoutez un merge field dans MailChimp, puis rechargez votre connexion : le nouveau champ est
> immédiatement disponible pour le mapping.

> [!IMPORTANT]
> Les merge fields appartiennent à l'audience, pas au compte. Changer d'audience change la liste
> des champs, et les mappings qui utilisaient les précédents ne correspondent plus.

### Abonnement

**Abonné** porte l'état du membre dans l'audience. Désabonner depuis Splash marque le membre
comme désabonné dans MailChimp ; cela ne le retire pas de l'audience.

Un membre qui se désabonne depuis un e-mail MailChimp est signalé à Splash par les webhooks :
l'état circule dans les deux sens.

### Ce qui n'est jamais fait

- Splash ne **supprime jamais** un membre MailChimp quand un client est supprimé dans votre
  boutique.
- Les **statistiques** MailChimp — ouvertures, clics, campagnes — ne sont pas synchronisées.
- Les membres ne sont pas importés en masse : ils arrivent dans MailChimp au fur et à mesure que
  vos autres applications les écrivent.
