Fee Plan documentation
===================================

## Context
A la sauvegarde des clefs api si le merchant ID n'est pas sauvegardé en base on enregistre les fee plan récupéré depuis l'API Alma dans la table ps_configuration de Prestashop.
Tous les plans sont désactivé par défaut sauf le P3X.
Les montants minimum et maximum récupéré depuis l'API Alma est enregistré dans la table ps_configuration de Prestashop avec leur clef respectif.
On ajoute une configuration d'ordre et on incrémente l'ordre des fee plan dans l'ordre défini par le produit (Pay now, PnX, Credit, Pay Later)
Au chargement de la page de configuration, on récupère les donnée fee plans de l'api et on les enrichie avec les données de la DB (enable, min, max, sort).
Tous les fee plans sont sauvegardé dans une clef de configuration unique appelé ALMA_FEE_PLANS dans un json.
