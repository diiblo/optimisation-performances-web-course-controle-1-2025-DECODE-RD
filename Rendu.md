# CC Optimisation et Performance
## Intro
Les performances sont importante, car elles permettes :
- d'augementer le taux de conversion
- Diminuition du taux de rebonds
- Bonne indexation et classement du site par les moteurs de recherches comme google.

## Hypothèse
En inspectant le profiler symfony et l'inspecteur, sur différentes vues, desktop et mobiles, voici donc mes hypothèses :
- Images
  - Format de l'image pas idéal, il est lourd.
  - Pas de lasy loading, le navigateur dois téléchargement toutes les images à même temps
  - Utilisation d'une même image pour en faire des miniatures (à partir du css).

- Base de donnée 
  - Les requêtes en bases de données sont énormes pour une simple page.

## Test et mésures
Voici  ce qui m'a permit de vérifier les hypothèses :

- Les Metrics :
  - Filtre image, dans réseau : 
    - Ressources : 736 831 ko/738 864 ko
    - 121 requête(s) sur 13
  - LCP : 
    - Desktop 27,4s
    - Mobile 137,8 s
  - Total Blocking Time
    - Desktop 190 ms
    - Mobile 8470 ms
  - Speed index
    - Desktop 1,3 s
    - Mobile 3,9 s
  - Database queries : 164
  - Query time : 55,07s
  - Different statement : 4

- Les outils :
  - Profiler Symfony
  - Chrome devtool / réseau
  - Chrome devtool / Lighthouse

## Solution
- optimisation du code du controller pour les requêtes en BD qui causais un soucis N+1
- Optimisation des images en Webp et utiliation des lazy loading

## Conclusion
- Nouvelle mesure :
  - Database avant : ![non optimisé](/home/diiblo/Projets/optimisation-performances-web-course-controle-1-2025-DECODE-RD/assets/database1.png)
  - Database après : ![optimisé](/home/diiblo/Projets/optimisation-performances-web-course-controle-1-2025-DECODE-RD/assets/database2.png)

On pourrais à l'avenir optimisé le SEO qui est pour ma part mauvais, comme l'accessibilité
