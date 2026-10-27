# Authenticatie – backend

Een Laravel-backend die gebruikers laat registreren, inloggen en uitloggen via een REST API. De frontend van dit project staat in een aparte repository: [authenticatie-FE](https://github.com/Shedion/authenticatie-FE).

Schoolproject voor de opleiding Software Developer (mbo 4) aan het Deltion College.

## Functies

- **Registreren**: een nieuwe gebruiker aanmaken met naam, e-mailadres en wachtwoord
- **Inloggen**: na het inloggen krijgt de gebruiker een token waarmee hij beveiligde routes kan gebruiken **[controleer: Laravel Sanctum of iets anders?]**
- **Uitloggen**: het token wordt ongeldig gemaakt
- **Beveiligde routes**: alleen ingelogde gebruikers kunnen hun eigen gegevens opvragen
- **Validatie**: foute of ontbrekende invoer geeft een duidelijke foutmelding terug

## API-endpoints

| Methode | Endpoint | Beschrijving | Ingelogd nodig |
|---|---|---|---|
| POST | `/api/register` | Nieuwe gebruiker registreren | Nee |
| POST | `/api/login` | Inloggen en token ontvangen | Nee |
| POST | `/api/logout` | Uitloggen | Ja |
| GET | `/api/user` | Gegevens van de ingelogde gebruiker | Ja |

## Gebruikte technieken

- PHP
- Laravel
- MySQL

## Zelf draaien

Je hebt PHP, Composer en MySQL nodig (bijvoorbeeld via XAMPP).

```bash
git clone https://github.com/Shedion/authenticatie.git
cd authenticatie
composer install
cp .env.example .env
php artisan key:generate
```

Vul in `.env` de gegevens van je database in, en draai daarna:

```bash
php artisan migrate
php artisan serve
```
