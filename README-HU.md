# Comuna Agriș – Elementor widget plugin

Ez a plugin a mellékelt statikus redesign teljes, moduláris WordPress/Elementor újraépítéséhez készült. Aktiválás után az Elementor bal oldali paneljén megjelenik a **Comuna Agriș** kategória 24 külön widgettel.

## Telepítés

1. WordPress admin → **Bővítmények → Új hozzáadása → Bővítmény feltöltése**.
2. Töltsd fel a `comuna-agris-elementor.zip` fájlt, majd aktiváld.
3. Az **Elementor** és az **Elementor Pro** legyen aktív.
4. Megjelenés → Menük alatt készítsd el a főmenüt; ezt a Header widget legördülőjében válaszd ki.
5. Elementor → Theme Builder alatt hozd létre a globális sablonokat.

Az `1.1.0` verzió első telepítése után a további kiadások a WordPress **Bővítmények** oldalán frissíthetők. Az **Eszközök → Comuna Agriș rebuild** oldal minden automatikus Elementor-átépítés előtt mentést készít, és lehetőséget ad az előző állapot visszaállítására.

## Theme Builder – ajánlott sablonok

| Sablon | Widgetek | Megjelenési feltétel |
|---|---|---|
| Header | `01 · Header complet` | Entire Site |
| Footer | `02 · Footer complet` + `03 · Accesibilitate` + `24 · Kereső / keresési modal` | Entire Site |
| Blog/kategória | `05 · Hero belső oldal` + `22 · Blog / kategória archívum` | All Archives / Post Categories |
| Egyedi bejegyzés | `23 · Egyedi blogbejegyzés` | All Posts |
| Dokumentum archívum | `05 · Hero belső oldal` + `21 · Dinamikus dokumentumtár` | Documents Archive |

A Header és Footer sablonban az Elementor oldalbeállításainál kapcsold ki a téma saját fejlécét/láblécét, vagy használj Elementor-kompatibilis könnyű témát.

## Oldalak újraépítési sorrendje

### Nyitóoldal

1. `04 · Hero nyitóoldal`
2. `06 · Szekció fejléc` + `07 · Szolgáltatások rács`
3. `06 · Szekció fejléc` + `19 · Dinamikus hírek`
4. `06 · Szekció fejléc` + `20 · Kézi dokumentumkártyák` a kiemelt HCL-ekhez
5. `08 · Tartalom + kép` a község bemutatásához
6. `06 · Szekció fejléc` + `07 · Szolgáltatások rács` a Monitorul Oficial kategóriáihoz
7. `15 · CTA / intézményi banner` a SIPOCĂ sávhoz

### Comuna / bemutatkozó belső oldalak

`05 · Hero belső oldal`, majd minden témához külön `08 · Tartalom + kép`. A kapcsolódó aloldalak és dokumentumok oldalsávjához `12 · Linklista / oldalsáv` használható.

### Primăria

`05 · Hero belső oldal` → `09 · Vezetői profil` → `10 · Fogadóórák` → `07 · Szolgáltatások rács` a részlegekhez.

### Consiliul Local

`05 · Hero belső oldal` → `18 · Statisztika / megoszlás` → `11 · Helyi tanács` → `17 · Adattábla / nyilvántartás` → `21 · Dinamikus dokumentumtár` vagy `20 · Kézi dokumentumkártyák`.

### Informații publice / Monitorul Oficial

A hivatalos fájlokat a WordPress **Documente** menüjében add hozzá, rendeld őket dokumentumkategóriához, és töltsd ki a fájl URL-jét. Az oldalon a `21 · Dinamikus dokumentumtár` automatikusan szűrhető kártyákként jeleníti meg őket. A galériás részekhez a `16 · Fotógaléria`, a tételes nyilvántartásokhoz a `17 · Adattábla / nyilvántartás` használható.

### Kapcsolat

`05 · Hero belső oldal` → `13 · Kapcsolati adatok` → `14 · Kapcsolati űrlap`. Az űrlap WordPress `wp_mail()` küldést használ, nonce-védelemmel, honeypottal és percenkénti IP-limitálással. Éles oldalon SMTP plugin beállítása ajánlott.

## Szerkesztési elvek

- Minden listás widget Elementor Repeater mezőkkel szerkeszthető.
- A hírek és archívumok valódi WordPress bejegyzéseket kérdeznek le.
- A dokumentumtár külön tartalomtípust és hierarchikus kategóriákat használ.
- A fejléc valódi WordPress menüt jelenít meg, ezért a navigációt egy helyen kell karbantartani.
- A widgetek mobilon automatikusan egyoszloposak, a header mobilmenüre vált.
- A plugin színrendszere CSS-változókon alapul. Elementor Pro egyedi CSS-ben a `:root` változók felülírhatók: `--agris-brand`, `--agris-ink`, `--agris-bg`, `--agris-radius`, `--agris-max`.
- A képekhez mindig adj értelmes alternatív szöveget a Médiatárban.

## Dokumentum mezők

Új dokumentum létrehozásakor:

1. Adj címet és rövid kivonatot.
2. Válassz egy vagy több dokumentumkategóriát.
3. Töltsd fel a PDF/DOCX fájlt a Médiatárba.
4. Másold a fájl URL-jét a **Fișierul documentului** dobozba.
5. Adj rövid jelölést, például `PDF`, `HCL`, `AN`, `PV` vagy `FIN`.

## Biztonság és karbantartás

- A kimenetek escape-elve vannak, az Elementor gazdag szöveg csak WordPress által engedélyezett HTML-t ad ki.
- A kapcsolatfelvétel WordPress nonce-ot és szerveroldali validációt használ.
- A plugin eltávolításkor nem törli a dokumentumokat; így tartalom nem vész el véletlenül.
- Frissítés előtt készíts adatbázis- és `wp-content` mentést.
