# Comuna Agriș – Elementor widget plugin

Ez a repository közvetlenül a WordPress `wp-content/plugins/` könyvtárába klónozható Elementor plugin. A cél a Comuna Agriș weboldal meglévő oldalainak és URL-jeinek megtartása melletti modernizálás.

## cPanel Git telepítés

1. A cPanel **Git Version Control** felületén klónozd ezt a repositoryt ide:

   `/home/comagris/dev.comunaagris.ro/wp-content/plugins/comuna-agris-elementor`

2. WordPress adminban nyisd meg a **Bővítmények** oldalt.
3. Aktiváld a **Comuna Agriș Elementor Widgets** plugint.
4. Az Elementor paneljén megjelenik a **Comuna Agriș** kategória 24 külön widgettel.
5. Kövesd a részletes [`README-HU.md`](README-HU.md) útmutatót.

Későbbi frissítéshez normál commit és push után a cPanelben az **Update from Remote** gombot kell használni. A repository előzménye nem írható át force push-sal.

## A repository tartalma

| Útvonal | Tartalom |
|---|---|
| `comuna-agris-elementor.php` | A WordPress által közvetlenül felismerhető plugin főfájl |
| `includes/` | Pluginbetöltés, dokumentumtípus és 24 Elementor widget |
| `assets/` | Frontend és Elementor-szerkesztő CSS/JavaScript |
| `readme.txt`, `uninstall.php` | WordPress metaadatok és biztonságos eltávolítás |
| `README-HU.md` | Részletes magyar oldalépítési útmutató |
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
