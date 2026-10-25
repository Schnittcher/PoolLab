# PLActiveChlor
Berechnet mit der LabCOM-Cloud den Anteil an aktivem Chlor (HOCl) aus pH-Wert, Temperatur, Chlor und Cyanursäure. Die Instanz wird über den Konfigurator angelegt.

## Inhaltsverzeichnis
- [PLActiveChlor](#plactivechlor)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Kachel](#3-kachel)
  - [4. Funktionen](#4-funktionen)
  - [5. Spenden](#5-spenden)
  - [6. Lizenz](#6-lizenz)

## 1. Konfiguration
Die Instanz hat keine Einstellungen. Bedient wird sie über ihre Variablen, zum Beispiel im WebFront.

## 2. Variablen
Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
pH | `pH` | Kommazahl | pH-Wert. Steuerbar.
Temperatur | `temperature` | Ganzzahl (°C) | Wassertemperatur. Steuerbar.
Chlor | `chlorine` | Kommazahl (mg/l) | Chlorgehalt. Steuerbar.
CYA | `cya` | Kommazahl (mg/l) | Cyanursäure. Steuerbar.
Aktion | `action` | Text | „Berechnen“ startet die Berechnung. Steuerbar.
Aktives Chlor | `activeChlorine` | Kommazahl (mg/l HOCl) | Ergebnis der Berechnung.

## 3. Kachel
Die Kachel zeigt die vier Eingaben (pH, Temperatur, Chlor, Cyanursäure), die Schaltfläche „Berechnen“ und das Ergebnis. Eingaben werden beim Verlassen des Feldes übernommen. Die Kachel nutzt Schrift und Farben der Visualisierung.

Die maximierte Ansicht der Kachel braucht Symcon 9.1. Unter Symcon 9.0 bleibt sie leer.

## 4. Funktionen
Die Instanz hat keine öffentlichen Funktionen.

## 5. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
