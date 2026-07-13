# Comuna Agriș – Elementor widget plugin

Ez a repository kizárólag a Comuna Agriș weboldal meglévő URL-jeinek megtartása mellett végzett Elementor Pro modernizáláshoz szükséges plugint és widgeteket tartalmazza.

## Gyors telepítés

1. Töltsd le a [`comuna-agris-elementor.zip`](comuna-agris-elementor.zip) fájlt.
2. WordPress adminban nyisd meg: **Bővítmények → Új hozzáadása → Bővítmény feltöltése**.
3. Telepítsd és aktiváld az Elementor, Elementor Pro és Comuna Agriș Elementor Widgets bővítményeket.
4. Az Elementor bal oldali paneljén megjelenik a **Comuna Agriș** kategória 24 külön widgettel.
5. Kövesd a részletes [`comuna-agris-elementor/README-HU.md`](comuna-agris-elementor/README-HU.md) útmutatót.

## A repository tartalma

| Útvonal | Tartalom |
|---|---|
| `comuna-agris-elementor/` | A WordPress plugin teljes, szerkeszthető forráskódja |
| `comuna-agris-elementor.zip` | Közvetlenül feltölthető WordPress plugin |
| `README.md` | Rövid telepítési és tartalmi összefoglaló |
| `.gitignore` | A helyi munkafájlok kizárási szabályai |

## A plugin fő képességei

- 24 külön Elementor widget
- Elementor Theme Builder header, footer, blog-, kategória- és egyedi bejegyzéssablonok
- nyitóoldali és belső oldali hero elemek
- szolgáltatások, hírek, dokumentumok, galériák és tartalmi blokkok
- vezetői profilok, fogadóórák és helyi tanács
- reszponzív adattáblák és statisztikai blokkok
- dinamikus, kategorizálható WordPress dokumentumtár
- AJAX kapcsolatfelvételi űrlap nonce-, honeypot- és rate-limit védelemmel
- keresési modal és akadálymentesítési panel
- mobil, tablet és desktop megjelenés

## Fejlesztői ellenőrzések

A kiadás előtt minden PHP-fájl `php -l` ellenőrzést kapott, a frontend JavaScript `node --check` teszten ment át, a 24 widget regisztrációja és a ZIP belső útvonalai automatikusan ellenőrizve lettek.

## Fontos

A plugin célja a meglévő WordPress-oldalak helyben történő átépítése. A jelenlegi oldalak, bejegyzések, kategóriák, dokumentumok, permalinkek és belső elérési utak nem változtathatók meg. A hivatalos dokumentumok WordPressben külön **Documente** tartalomtípusként kezelhetők; a plugin eltávolítása szándékosan nem törli ezeket a bejegyzéseket vagy kategóriákat.
