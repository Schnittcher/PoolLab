# PLAccount
Splitter für einen Pool (Account der LabCOM-Cloud). Er reicht die Abfragen der Messwert-, Dosier- und Aktivchlor-Instanzen an die Cloud weiter und liefert die Poolgröße. Er wird über den Konfigurator angelegt.

## Inhaltsverzeichnis
- [PLAccount](#placcount)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Funktionen](#3-funktionen)
  - [4. Spenden](#4-spenden)
  - [5. Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
Account-ID | ID des Pools in der LabCOM-Cloud. Der Konfigurator trägt sie ein.

## 2. Variablen

Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
Poolgröße | `PoolVolume` | Kommazahl (m³) | Wassermenge des Pools laut Cloud-Account.

## 3. Funktionen

`void PL_UpdateAccount(integer $InstanzID);`
Liest die Poolgröße aus der Cloud. Wird beim Übernehmen der Konfiguration aufgerufen.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
