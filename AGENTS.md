# cumayanekadarkaldi.com

## Proje Özeti
Kullanıcının konumuna göre cuma namazı saatini (Dhuhr) gösteren, SEO odaklı, ultra hızlı bir Laravel uygulaması.

## Teknik Stack
- **Framework:** Laravel 13 (PHP 8.3)
- **DB:** SQLite (cache + migrations)
- **Rendering:** Blade SSR (minimal JS)
- **API:** AlAdhan API (method 13 = Diyanet)
- **Cache:** Laravel Cache (24 saat TTL)
- **i18n:** Laravel Localization (TR / EN)

## Dosya Yapısı
```
app/
  Http/
    Middleware/SetLocale.php   # Locale middleware
    Controllers/
      FridayController.php     # Ana controller
  Services/
    PrayerTimeService.php      # AlAdhan API entegrasyonu + cache + haftalık vakitler
    LocationService.php        # IP bazlı konum tespiti
lang/
  tr/messages.php              # Türkçe metinler
  en/messages.php              # İngilizce metinler
resources/views/
  layouts/app.blade.php        # Ana layout + CSS + dil switcher
  home.blade.php               # Ana sayfa
  city.blade.php               # Şehir bazlı SEO sayfası
  widget.blade.php             # Embed widget
routes/web.php                 # Route tanımları
tests/Feature/FridayTimeTest.php
```

## Önemli Route'lar
| Route | Açıklama |
|-------|----------|
| `GET /` | Ana sayfa (TR, IP'den şehir tahmini) |
| `GET /{city}-cuma-saati` | Şehir sayfası (TR) |
| `GET /en` | Ana sayfa (EN) |
| `GET /en/{city}-friday-time` | Şehir sayfası (EN) |
| `GET /api/friday-time?city={city}` | JSON API |
| `GET /widget/{city}` | Embed widget (TR) |
| `GET /en/widget/{city}` | Embed widget (EN) |

## Cache Stratejisi
- API yanıtları `friday_time_{city}` anahtarıyla 24 saat cache'lenir.
- Haftalık vakitler `weekly_times_{city}` anahtarıyla cache'lenir.

## Konum Tespiti
1. Kullanıcı manuel seçim
2. IP fallback (ip-api.com → ipinfo.io)
3. Varsayılan: İstanbul

## SEO Özellikleri
- Şehir bazlı canonical URL'ler
- Schema.org Event structured data (JSON-LD)
- Optimize edilmiş `<h1>` ve meta description
- SSR ile anlık yükleme
- Çok dilli şehir sayfaları

## Widget Kullanımı
```html
<iframe src="https://cumayanekadarkaldi.com/widget/bursa" width="100%" height="120" style="border:none; border-radius:0.5rem;"></iframe>
```
Şehir sayfalarında otomatik embed kodu ve önizleme bulunur.

## Haftalık Vakitler
Her şehir sayfasında o ayın tüm günlerine ait namaz vakitleri tablo halinde gösterilir. Bugünün satırı sarıyla vurgulanır.

## Geliştirme Komutları
```bash
php artisan serve
php artisan test
php artisan view:clear
php artisan route:clear
```

## Forge Deployment Notu
- `APP_URL` domain'e göre ayarlanmalı
- `php artisan optimize` çalıştırılabilir
- SQLite dosyası (`database/database.sqlite`) ve `storage/` dizini yazılabilir olmalı
