# NEW WORKFLOW module for [dolibarr erp & crm](https://www.dolibarr.org)

### Problématiques

Comment **simplifier** dolibarr, afin de **facilité** la **compréhension**, l’**appropriation** et l’**utilisation** aux débutants (et aux confirmés) ?

Comment **uniformiser** dolibarr, afin que **tout le monde** suivent un **processus** **clair** et **identique**, et afin d’**éviter les coquilles**.


### En deux mots le projet c’est :


Un module qui propose un réel suivis des affaires commerciales.

Grâce à un (ou plusieurs) véritable flow définis à l’avance, constitué d’étapes accompagné de leurs statuts d’avancement (les deux pouvant être personnaliser). Ainsi qu’un nouvel objet affaire qui unifie les objets dolibarr (propal, facture, …) afin de laisser à l’objet projet actuel seulement la partie production


---

# Valeur ajouté

<span style="color:grey">(concrètement qu’est-ce que ça apporte ?)</span>

**              DE LA SIMPLICITÉ**

**Plus de clarté** : un processus clair et compréhensible et facile à suivre (voir impossible de faire autrement ?)

**Plus d’automatisme** : plus rapide et moins d’erreur/mauvaise manip/oublis, possible d’aller plus loin avec zapier ou make

**Plus de bohneur** : moins de points de friction, moins de tâches ingrates/actions inutiles répétitives

**Un véritable suivis des affaires** : en un coup d’œil on sait à quelle étape du processus on est chaque affaire et à quel statut ;  combien de d’affaire sont au stade de propal, commande, prod ... ; encore plein d’autre statistiques ...

---

# Réalisation

### Solution

<span style="color:grey">(concrètement comment on fait ?)</span>

Ce qui prête à *confusion* selon moi c’est que chaque objet de Dolibarr (propal, facture, projet, commande fournisseur, …) est éparpillé par ci par là, il n’est pas évident de savoir quoi faire ensuite, et comment le faire *(on peut créer n’importe quel objet à n’importe quel moment encore faut-il savoir dans quel onglet ou menu chercher).*
Néanmoins tous ces objets peuvent être *reliés par un projet*. Or c’est problématique lorsque, comme moi, on cherche à créer un flow, un processus clair et suivi par tout le monde, car justement il est *à la fois une affaire commerciale* réunissant toutes les étapes du processus (pouvant aller de l’opportunité jusqu’à la livraison) *et une étape spécifique* de ce processus : l’étape de production (gestions des taches, du temps consommé …). 
Selon moi, il faudrait :

- **un objet Affaire** qui permettrait le suivi d’une affaire commerciale, en déterminant les étapes clés (propal, commande, production …), leur ordre, et leur statuts d’avancement. 
<span style="color:grey">(1 affaire = 1 commande, Ex: nous sommes menuisier, un architecte nous contacte pour un client à lui qui construis sa maison et nous demande des devis pour des fenêtres ainsi que des portes, nous créeront une affaires à laquelle nous lieront plusieurs propal, l’affaire peut se clore ici mais disons que le client final accepte l’une de nos propal, alors l’architecte passe commande, puis on lance la production, puis la livraison… (toutes ces étapes faisant partie de l’affaire).
Mais les artisans endommagent les portes en les installant, alors l’architecte nous rappel pour une réparation : il passe une nouvelle commande : on créer une nouvelle affaire.</span>
- **un objet Production/Développement** qui permettrais le suivi de la phase de production d’un produit, ou de développement d’un service (avec notamment les taches / commande fournisseur / facture fournisseur associés, le temps consommé …)
- **un nouvel objet Projet** qui engloberait les affaires commerciales liées, ou dans le cas d’un projet interne comme un projet R&D, les productions (donc taches, commande fourn, …) liés.
<span style="color:grey">(Ex: dans le cas de notre menuisier, il veut renouveler son catalogue, il lance donc la production de nouveau produit mais sans lien à une quelconque affaire commerciale. 
Ou bien, imaginons qu’il veuille garder une trace du nombre d’heure travaillées par sa comptable, qui s’occupe notamment de l’administratif, alors il créer une production : informatique, avec les tâches associées (compta, admin, …), qu’il lie à la production : Accueil & Commerciale, au sein du même projet : fonctionnement interne. Ainsi il peut faire les bulletins de salaires)</span>

Peut-être est-ce plus clair sous forme de schéma :
https://figma

Pour ce module je me suis concentrer sur l’objet affaire, j’ai conservé les projets actuel pour l’étape de production, j’ai fais ce choix car c’est plus simple pour mon entreprise, c’est comme ça qu’elle fonctionne (mais on aurait pu faire l’inverse, garder les projet pour englober le reste et créer un objet production, de toute façon dans les deux cas ce n’est qu’une étape intermédiaire et l’objectif final est de créer les deux objet).

---

<span style="color:grey">(J’ai pas bien compris une affaire qu’est-ce que c’est ?)</span>

Une affaire est un objet Dolibarr au même titre qu’une propal, un client, une expédition, un projet… d’ailleurs elle est inspiré des projets, comme eux elle à :
une référence, un titre, une description,
un responsable et des contacts liés,
une page vue d’ensemble ou l’on peut voir les bénéfice de l’affaires et tout les objets liés,

Une affaire est tout de même un objet un peu spécial, car c’est le premier objet avec deux barres d’onglets, je m’explique, elle à une première barre d’onglet classique, avec une page avancement, contact, vue d’ensemble, fichier joint, évènement. 

Et si l’on clique sur la page avancement alors une deuxième barre d’onglets avec les étapes apparaît en dessous de la bannière (= barre d’onglets, icon, ref, et statut). Cela permet d’avoir un suivi global depuis un seul et même objet. 

Quand on clique sur une étape cela affiche la page de l’objet en question en dessous (Ex: pour l’étape proposition commerciale on aura accès à la page /propal/card.php depuis l’affaire). Et inversement la bannière de l’affaire apparaît maintenant en haut de chaque page objet. 

Ça c’est s’il s’agit d’un objet, oui car il est possible d’avoir d’autre étapes, (Ex: dans le cas de mon entreprise, j’avais besoin d’une étapes à la toute fin : administratif, quand toutes les autres étapes sont terminé mais que je suis en attente du certificat d’exportation alors mon affaire n’est pas close, elle est à l’étape admin et au statut “att. certificat export.”). Dans ce cas on affiche simplement le statut et les bouton d’action. (il sera possible de mettre son propre code à l’aide de hook).

<span style="color:grey">(C’est quoi ces histoire d’étapes et de statuts ?)</span>

Comme je l’ai dis le but de ce module est d’avoir un flow, un processus clair et suivi par tout le monde. Pour créer ce “workflow”, on va déterminé les différentes parties d’une affaire, ce sont les “étapes”, et leur ordre (Ex: D’abords les propositions commerciales puis la commande puis la production puis la livraison puis la facturation.) 
Bien sur chaque entreprise peut avoir différents types d’affaires, c’est pourquoi il est possible de créer plusieurs workflow <span style="color:grey">(Ex: notre menuisier pourrait avoir un worflow : Prod & Livraison, un autre Prod & Installation (ou l’étape de livraison serait remplacer par une intervention), encore un autreRéparation..)</span>. 
Les étapes ont chacune des “statuts” associés <span style="color:grey">(Ex: pour l’étape propal les statut actuels sont brouillon / validée / signée / non signée / traitée)</span>

Chaque workflow est personnalisable, on choisis les étapes (on peut ajouter ses propres étapes), leur ordres, s’il est possible de passer à l’étape suivant sans avoir avoir terminé la précédante, les statut (là encore on peut créer ses propre statut <span style="color:grey">Ex: pour les propal nous ajoutons le statut à relancer</span>) et les actions (bouttons disponibles <span style="color:grey">Ex: créer facture</span>) à chaque étape. De plus on peut même rajouter un peu d’automatisation <span style="color:grey">(Ex: quand le statut de la facture passe à payée le statut de l’affaire passe cloturée, ou quand le statut d’une propal passe à signé, le statut des autres affaires passe à non signée et une commande est créer, ou que sais-je avec zapier ou make)</span>.

<!--
![Screenshot workflow](img/screenshot_workflow.png?raw=true "Workflow"){imgmd}
-->

---

## Translations

Translations can be completed manually by editing files in the module directories under `langs`.

<!--
This module contains also a sample configuration for Transifex, under the hidden directory [.tx](.tx), so it is possible to manage translation using this service.

For more information, see the [translator's documentation](https://wiki.dolibarr.org/index.php/Translator_documentation).

There is a [Transifex project](https://transifex.com/projects/p/dolibarr-module-template) for this module.
-->


## Installation

Prerequisites: You must have Dolibarr ERP & CRM software installed. You can download it from [Dolistore.org](https://www.dolibarr.org).
You can also get a ready-to-use instance in the cloud from https://saas.dolibarr.org


### From the ZIP file and GUI interface

If the module is a ready-to-deploy zip file, so with a name `module_xxx-version.zip` (e.g., when downloading it from a marketplace like [Dolistore](https://www.dolistore.com)),
go to menu `Home> Setup> Modules> Deploy external module` and upload the zip file.

Note: If this screen tells you that there is no "custom" directory, check that your setup is correct:

<!--

- In your Dolibarr installation directory, edit the `htdocs/conf/conf.php` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading `//`) and assign the proper value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```
-->

<!--

### From a GIT repository

Clone the repository in `$dolibarr_main_document_root_alt/workflow`

```shell
cd ....../custom
git clone git@github.com:gitlogin/workflow.git workflow
```

-->

### Final steps

Using your browser:

  - Log into Dolibarr as a super-administrator
  - Go to "Setup"> "Modules"
  - You should now be able to find and enable the module



## Licenses

### Main code

GPLv3 or (at your option) any later version. See file COPYING for more information.

### Documentation

All texts and readme's are licensed under [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).
