# Changelog

Всички значими промени в модула **CreditJet** (ПБ Лични Финанси) се документират в този файл.

Форматът следва [Keep a Changelog](https://keepachangelog.com/bg/1.1.0/),
а версиите следват [Semantic Versioning](https://semver.org/lang/bg/).

## [Unreleased]

## [1.9.0] - 2026-07-20

### Added

- Табове в административната конфигурация: Управление, Функционални, Визуални, Филтри по лихва.
- Визуални настройки на бутона: тип (стандартен / wide), текстове, лого, цветови схеми, ширина, радиус, размер на шрифта.
- Live preview и бутон „Върни стандартните настройки“ във визуалния таб (без да променя типа бутон).
- Wide бутон с отделен CSS и конфигурация през `JetButtonSettings`.
- Локално зареждане на шрифта Roboto Condensed (`fonts/` + `css/creditjet_fonts.css`), без Google Fonts.
- Defaults при нова инсталация и по-ясни help текстове за имейл и минимална цена.

### Changed

- Мобилен layout на калкулатора: колони вместо редове; полетата и сумите (EUR/BGN) са подравнени вдясно.
- Откриване на мобилен изглед чрез CSS media queries вместо user-agent в PHP.
- CSS селекторите са ограничени до `#jet-product-button-container` и `#jet-product-popup-container`, за да няма конфликти с други модули (напр. `.jet_logo`).
- Запазване на активния админ таб чрез `sessionStorage`.

### Fixed

- Хоризонтален скрол на мобилния popup (фиксирана ширина 1100px, overflow, body lock при отворен модал).
- Wide бутонът вече не се ограничава до 155px заради чужд `.jet_logo` CSS.

### Removed

- Отдалечени `@import` към Google Fonts в product/cart/checkout CSS.
- Неизползвани елементи, свързани с `JET_Z1`.

## [1.8.5] - 2026-05-14

### Changed

- Актуализация на Composer autoload и stubs.
- Подобрена санитизация на входни данни в front контролерите и JS.

[Unreleased]: https://github.com/wiley68/jet-ps8/compare/v1.9.0...HEAD
[1.9.0]: https://github.com/wiley68/jet-ps8/releases/tag/v1.9.0
[1.8.5]: https://github.com/wiley68/jet-ps8/releases/tag/v1.8.5
