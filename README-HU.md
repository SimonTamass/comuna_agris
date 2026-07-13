# Comuna Agriș – használati útmutató

## Telepítés

1. A cPanel **Git Version Control** felületén klónozd a `https://github.com/SimonTamass/comuna_agris.git` repository `main` ágát ide:

   `/home/comagris/dev.comunaagris.ro/wp-content/plugins/comunaagris_plugin`

2. WordPress admin → **Bővítmények** alatt aktiváld a **Comuna Agriș Elementor Widgets** plugint.
3. Az Elementor és az Elementor Pro legyen aktív.

## Frissítés

A plugint kizárólag a cPanel **Git Version Control** felületéről frissítsd. A plugin nem használ saját GitHub/WordPress frissítőt, ezért nem nevezi át és nem cseréli le a `comunaagris_plugin` könyvtárat.

## Oldalak biztonságos átépítése

Az **Eszközök → Comuna Agriș rebuild** oldal szerveroldalon alkalmazza az Elementor-sablonokat. Minden művelet előtt automatikus mentés készül. A rendszer ellenőrzi, hogy az oldal permalinkje változatlan maradt-e; eltérés esetén automatikusan visszaállítja az előző állapotot.

Az átépítés nem hoz létre új oldalt, és nem módosítja a meglévő oldal ID-ját, nyelvét, szülőjét vagy slugját.

## Elementor widgetek

A 24 widget az Elementor bal oldali paneljén, a **Comuna Agriș** kategóriában található. A készlet tartalmaz globális headert és footert, keresőt, akadálymentesítést, hero elemeket, tartalmi blokkokat, szolgáltatásokat, vezetői profilt, fogadóórákat, tanácstagokat, kapcsolati elemeket, galériát, adattáblát, híreket, dokumentumtárat, blogarchívumot és egyedi bejegyzéssablont.

## URL-szabály

Új slug vagy új helyettesítő oldal nem készülhet. A meglévő publikus útvonalakat kell helyben modernizálni, beleértve a történetileg elírt, de már használt URL-eket is.
