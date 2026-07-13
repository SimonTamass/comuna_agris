# Comuna Agriș – Elementor widget plugin

Közvetlenül a WordPress `wp-content/plugins/` könyvtárába klónozható Elementor plugin a Comuna Agriș weboldal helyben történő, URL-megőrző modernizálásához.

## cPanel Git telepítés és frissítés

1. A cPanel **Git Version Control** felületén a repository pontos célkönyvtára:

   `/home/comagris/dev.comunaagris.ro/wp-content/plugins/comunaagris_plugin`

2. Repository URL: `https://github.com/SimonTamass/comuna_agris.git`
3. Branch: `main`
4. WordPress adminban aktiváld a **Comuna Agriș Elementor Widgets** plugint.
5. Minden későbbi frissítést kizárólag a cPanel Git Version Control felületén végezz.

A plugin nem tartalmaz saját GitHub- vagy WordPress-frissítőt. Ez szándékos: a `comunaagris_plugin` könyvtárnév és a cPanel repository útvonala így stabil marad.

## Tartalom

- 24 külön Elementor widget
- teljes header, footer, kereső és akadálymentesítési panel
- nyitóoldali és belső oldali hero elemek
- szolgáltatás-, hír-, dokumentum-, galéria- és tartalmi widgetek
- vezetői profil, fogadóórák és helyi tanács
- blog-, kategória- és egyedi bejegyzéssablonok
- biztonságos kapcsolatfelvételi űrlap
- szerveroldali Elementor rebuild automatikus mentéssel és visszaállítással
- dinamikusan feloldott meglévő permalinkek; új slug nem készül

## Fontos

A plugin célja a meglévő oldalak átépítése, nem új URL-ek létrehozása. A publikált oldalak ID-ja, nyelve, szülőoldala, slugja és címe minden automatikus rebuild során változatlan marad. A szerveren ne szerkeszd kézzel a Git által kezelt pluginfájlokat.
