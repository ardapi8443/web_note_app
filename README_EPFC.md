# Projet PRWB 2324 - Groupe g01 - Google Keep

## Notes de version itération 1 

### Liste des utilisateurs et mots de passes

  * boverhaegen@epfc.eu, password "Password1,", utilisateur
  * bepenelle@epfc.eu, password "Password1,", utilisateur
  * xapigeolet@epfc.eu, password "Password1,", utilisateur
  * mamichel@epfc.eu, password "Password1,", utilisateur

### Liste des bugs connus

  * aucun bugs connus

### Liste des fonctionnalités supplémentaires
  * n/a

## Divers

### Notes de version itération 2

  * bugs itération 1 corrigé
    - add_text_note : titre vide = erreurs au lieu du titre par défaut + unicité du titre
    - add_checklist_note : unicité du titre
    - open_note : impossible d'ouvrir une note si l'on en est pas le propriétaire ou si elle nous est partagée (en mode lecture ou écriture)
    - edit_note : unicité du titre + précision temporelle à la seconde
    - edit_checklist_note : on voit si les items sont checké ou non
    - delete_note et confirmation : en php, le bouton de suppression d'une note nous redirige vers une page de confirmation où l'on peut soit revenir en arrière ou accapter la suppression définitive de la note. la modale est acctive uniquement en javascript.
    - view_shared_note : seul le propriétaire de la note peut la pinner, l'archiver ou la supprimer
    - view_shares, add_share : les utilisateurs sont triés par ordre alphabétique
    - les variables et méthodes ont été typés
    - les constantes de longueur de titre et d'item sont présentes dans le fichier de configuration
    - convention de nommage harmonisée

  * bugs iterations 2 
    - dans edit_checklist_note :
      - ajouter un item
      - ecrire quelque chose dans l'input add_item
      - appuyer sur le bouton "save"
      - c'est "add_item" qui est trigger au lieu de "save"

    * liens de deployment
        - web.ardelplanque.ovh
        - http://0707davanrossen.xapigeol-edu.be/
        - http://2310pidramaix.xapigeol-edu.be/

## Notes de version itération 3 

  * bugs itération 1 corrigé
    - edit profile : input "is-invalid" seulement si a une erreur le concernant
    - add checklist note : bouton "+" fonctionne même après des messages d'erreurs
    - check_uncheck : méthode controlleur renommé selon la nouvelle convention de nommage (snake_cake)
    - add item : message d'erreur s'affiche quand on tente d'ajouter un doublon
    - view_shares : les noms de suers sont affichés dans l'ordre alphabétique

  * bugs itération 2 corrigé
  - drag & drop d'une note : 1 seul appel ajax pour l'action complète
  - delete note : 2 modales lors de la suppression d'une note