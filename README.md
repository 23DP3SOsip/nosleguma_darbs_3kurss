# Iekšējā automašīnu koplietošanas sistēma (josipfamily)

## Par projektu

Šī ir lokāla iekšējā automašīnu koplietošanas sistēma, kas paredzēta uzņēmumiem ar savu autoparku. Tās mērķis ir automatizēt dienesta automašīnu rezervēšanu, braucienu un izmaksu uzskaiti, tehniskās apkopes plānošanu un atskaišu sagatavošanu. Sistēma aizstāj manuālus procesus, piemēram, telefonzvanus, e‑pastus un Excel tabulas, ar vienotu un pārskatāmu tīmekļa risinājumu.

Galvenie ieguvumi:
- samazināts laiks rezervāciju noformēšanai;
- mazāk rezervāciju konfliktu un dubultu pieprasījumu;
- precīzāka nobraukuma, izmaksu un apkopes vēstures uzskaite;
- vienota datu vide grāmatvedībai, administrācijai un vadībai;
- risinājums, kas pielāgots Latvijas uzņēmumu vajadzībām.

## Kāpēc šis projekts ir vajadzīgs

Daudzos uzņēmumos autoparka pārvaldība joprojām notiek manuāli. Tas rada neefektivitāti, necaurspīdīgu izmaksu uzskaiti un problēmas ar auto pieejamību. Šis projekts ir izstrādāts, lai:

- centralizētu rezervācijas un piekļuvi autoparkam;
- uzskaitītu braucienus, izmaksas un tehniskās apkopes;
- nodrošinātu administratoriem un vadībai skaidras atskaites;
- uzlabotu kontroles līmeni pār uzņēmuma transportlīdzekļiem;
- atvieglotu ikdienas darbu darbiniekiem un vadītājiem.

## Galvenās funkcijas

- Lietotāju reģistrācija un autentifikācija ar lomu atbalstu.
- Reāllaika informācija par transportlīdzekļu pieejamību un statusu.
- Rezervāciju izveide un atcelšana.
- Braucienu uzskaite: sākuma un beigu laiks.
- Tehnisko apkopju žurnāls ar vēsturi un piezimem.
- Atskaišu ģenerēšana administrācijai un vadībai.
- Eksporta iespējas datiem PDF formātā.

## Galvenās entītijas

- Lietotāji (`app/Models/User.php`) — ar lomām un piekļuves tiesībām.
- Automašīnas (`app/Models/Car.php`) — transportlīdzekļu dati, statuss un pieejamība.
- Rezervācijas (`app/Models/CarReservation.php`) — periods, lietotājs, statuss un mērķis.
- Apkopju žurnāli (`app/Models/CarMaintenanceLog.php`) — remonta un tehniskās apkopes vēsture.

## Tehnoloģiju steks

- Backend: PHP (Laravel) — projekts atrodas mapē `backend/`.
- Frontend: Vue 3 + Vite — klients atrodas mapē `frontend/`.
- Datubāze: MySQL / MariaDB (konfigurējama ar `.env`).
- Testēšana: PHPUnit backend pusē.

## Projekta struktūra

- `backend/` — Laravel aplikācija, kur atrodas kontrolieri, modeļi, migrācijas un konfigurācija.
- `frontend/` — Vue aplikācija ar skatiem, komponentēm un klienta loģiku.
- `public/` — publiskā piekļuves vieta backend aplikācijai, ja to apkalpo atsevišķs serveris.

## Ātrā palaišana (lokālā izstrāde)

Prasības:
- PHP 8.1+ ar nepieciešamajiem paplašinājumiem;
- Composer;
- Node.js 16+ un npm vai yarn;
- MySQL vai MariaDB datubāze.

1) Instalēt backend atkarības:

```bash
cd backend
composer install
cp .env.example .env
```

2) Konfigurēt `.env` — norādīt datubāzes parametrus, `APP_URL`, ģenerēt atslēgu ar `php artisan key:generate` un pēc vajadzības uzstādīt pasta iestatījumus.

3) Palaist migrācijas un sēšanas skriptus:

```bash
php artisan migrate
php artisan db:seed
```

4) Instalēt frontend atkarības un palaist izstrādes serveri:

```bash
cd frontend
npm install
npm run dev
```

5) Palaist Laravel lokālo serveri:

```bash
cd backend
php artisan serve
```

Parasti frontend (Vite) tiek konfigurēts tā, lai API pieprasījumi tiktu sūtīti uz Laravel serveri vai citu lokālo izstrādes vidi.

## Vides mainīgie

Pārliecinieties, ka `backend/.env` satur:
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`;
- `APP_URL`, `APP_ENV`, `APP_KEY`;
- pēc vajadzības: e‑pasta iestatījumus un citus ārējos integrācijas parametrus.

## Datubāze un migrācijas

Migrācijas atrodas `backend/database/migrations/`. Lai atjauninātu shēmu, izmantojiet `php artisan migrate`.
Ja tiek veiktas izmaiņas modeļos, izveidojiet jaunas migrācijas un palaidiet tās vēlreiz.

## Testēšana

Lai palaistu backend testus, izmantojiet:

```bash
cd backend
./vendor/bin/phpunit
```

Pievienojiet testus mapēs `backend/tests/Feature` un `backend/tests/Unit`.

## Izvietošana (produkcija)

Produkcijas vidē parasti jāveic:
- konfigurēt web serveri (Nginx vai Apache) uz `backend/public`;
- palaist `composer install --no-dev` un frontend būvniecību ar `npm run build`;
- konfigurēt darba rindas, cron uzdevumus un piešķirt tiesības mapēm `storage` un `bootstrap/cache`.

## Drošība un atbilstība

- Piekļuves kontrole tiek organizēta ar lomām.
- Sensitīvie dati tiek glabāti ārpus repozitorija, izmantojot `.env`.
- Produkcijā ieteicams lietot HTTPS, veidot regulāras datu dublējumkopijas un pārbaudīt piekļuves tiesības.

## Pielāgojamība un nākotnes paplašinājumi

Projekts ir veidots tā, lai to varētu paplašināt ar:
- telemātikas integrāciju (GPS) automātiskai nobraukuma reģistrācijai;

## Kontakti

Ja nepieciešama palīdzība, izveidojiet uzdevumu trīskinga sistēmā vai sazinieties ar projekta autoru.