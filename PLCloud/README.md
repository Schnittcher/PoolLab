# PLCloud
Verbindung zur LabCOM-Cloud (PoolLab und PrimeLab). Das Gateway hält den API-Token und führt die Abfragen für die übrigen Instanzen aus. Es wird beim Anlegen des Konfigurators automatisch erzeugt.

## Inhaltsverzeichnis
- [PLCloud](#plcloud)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Funktionen](#3-funktionen)
  - [4. Spenden](#4-spenden)
  - [5. Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
API-Token | Token aus dem Konto auf labcom.cloud (Benutzereinstellungen). Er wird nicht protokolliert.

Die Schaltfläche „Verbindung testen" prüft den Token und nennt die Anzahl der Konten. Der Instanzstatus zeigt, ob der Token fehlt, abgelehnt wurde oder die Cloud nicht erreichbar ist.

## 2. Variablen
Das Gateway legt keine Variablen an.

## 3. Funktionen

`string PL_TestConnection(integer $InstanzID);`
Prüft Token und Verbindung und liefert das Ergebnis als Text.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
