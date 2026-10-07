# SPEEDSHOT-3 – wdrożenie podstron „Imprezy firmowe [Miasto]”

Katalogi odpowiadają katalogowi głównemu WordPressa na serwerze.

1. **Kopia zapasowa** bazy (panel hostingu lub Duplicator).
2. Wgraj przez FTP pliki motywu:
   - `wp-content/themes/speedshot/views/partials/footer.twig`
   - `wp-content/themes/speedshot/public/style.css` – to jest plik z produkcji + tylko nowe reguły stopki (nie wgrywaj lokalnego `public/style.css`, zawiera niewdrożone zmiany)
   - `wp-content/themes/speedshot/scripts/city-pages/` (`lib.php`, `cities.php`, `seed.php`)
3. Wgraj `speedshot-city-pages.php` do katalogu głównego WordPressa (obok `wp-config.php`).
4. Otwórz `https://centrumrozrywki-speedshot.pl/speedshot-city-pages.php?token=bd658c8e213034c11c7d3e9e5f860503`
   – podgląd + walidacja, nic się jeszcze nie zapisuje. Powinno być 7× `NOWA` i 1× `AKTUALIZACJA`.
5. Kliknij **Uruchom**. Skrypt usuwa się sam; jeśli zobaczysz komunikat, że się nie udało – usuń go przez FTP.
   Po 2026-10-13 skrypt przestaje działać.
6. **Wyczyść cache serwera** (nginx zwraca `X-Cache-Status: HIT`) – inaczej stare strony pokażą stopkę bez miast.
7. Sprawdź np. `/imprezy-firmowe-wroclaw/`, stopkę i sekcję na `/imprezy-integracyjne-dla-firm/`.

Wycofanie sekcji na stronie „Imprezy integracyjne”: oryginalna treść jest zapisana w meta
`_speedshot_content_before_city_links` strony #25 (oraz w rewizjach WP).
