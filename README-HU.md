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

### Magyar oldalak

Az **Összes magyar oldal átépítése** művelet a meglévő `hu` nyelvű indexet, polgármesteri oldalt és minden publikált magyar belső oldalt Elementor-adattá alakítja. A magyar fejlécet és láblécet, a teljes többszintű WordPress-menüt, a keresés nyelvét, valamint az eredeti tartalmat, képeket, galériákat és dinamikus bejegyzéslistákat is megtartja.

Ha egy céloldalnak van Polylang-fordítása, a rendszer azt használja. Ha nincs magyar párja, csak már létező publikus útvonalra mutat; új vagy feltételezett slugot nem hoz létre.

A kategória-, taxonómia-, keresési, blog- és egyedi bejegyzésnézeteket a plugin automatikusan a közös Elementor widgetrendszerrel jeleníti meg. Ezekhez nem kell külön oldalt létrehozni vagy a WordPress menüben új URL-t beállítani; a megfelelő magyar vagy román header, footer és feliratok az aktuális nyelv alapján töltődnek be.

## Elementor widgetek

A 24 widget az Elementor bal oldali paneljén, a **Comuna Agriș** kategóriában található. A készlet tartalmaz globális headert és footert, keresőt, akadálymentesítést, hero elemeket, tartalmi blokkokat, szolgáltatásokat, vezetői profilt, fogadóórákat, tanácstagokat, kapcsolati elemeket, galériát, adattáblát, híreket, dokumentumtárat, blogarchívumot és egyedi bejegyzéssablont.

Minden widget önálló komponensfájlban található, és saját **Megjelenés** füllel rendelkezik. Itt widgetenként állítható a kiemelő-, cím-, szöveg- és háttérszín, a sarokkerekítés, a belső térköz és a címtipográfia. A widgetazonosítók változatlanok, ezért a korábban felépített Elementor-oldalak kompatibilisek maradnak.

## URL-szabály

Új slug vagy új helyettesítő oldal nem készülhet. A meglévő publikus útvonalakat kell helyben modernizálni, beleértve a történetileg elírt, de már használt URL-eket is.
