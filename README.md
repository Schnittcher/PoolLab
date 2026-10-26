[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Check Style](https://github.com/Schnittcher/PoolLab/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/PoolLab/actions)
![Version](https://img.shields.io/badge/Symcon%20Version-9.0%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

# PoolLab
Dieses Modul überträgt die Messwerte der PoolLab-Photometer aus der LabCOM-Cloud nach IP-Symcon und bietet den Dosierrechner und den Aktivchlor-Rechner der Cloud an. Die Messwerte werden pro Pool aufgebaut, unabhängig davon, welches Gerät misst.

## Inhaltsverzeichnis
- [PoolLab](#poollab)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Voraussetzungen](#1-voraussetzungen)
  - [2. Funktionsumfang](#2-funktionsumfang)
  - [3. Enthaltene Module](#3-enthaltene-module)
  - [4. Screenshots](#4-screenshots)
  - [5. Installation](#5-installation)
  - [6. Konfiguration in IP-Symcon](#6-konfiguration-in-ip-symcon)
  - [7. Spenden](#7-spenden)
  - [8. Lizenz](#8-lizenz)

## 1. Voraussetzungen

* IP-Symcon ab Version 9.0
* Ein Konto auf [labcom.cloud](https://labcom.cloud) mit einem API-Token (Benutzereinstellungen)
* Ein Gerät, das seine Messungen in die LabCOM-Cloud überträgt:

Gerät | Übertragung | Stand
------------ | ------------ | ----------------
PoolLab 1.0 | Bluetooth zur App, dann Cloud | getestet
PoolLab 2.0 | Bluetooth oder WLAN direkt zur Cloud | Parameter-IDs aus der Herstellerliste, nicht mit einem Gerät getestet

## 2. Funktionsumfang

* Messwerte aller Pools des Cloud-Kontos als Variablen mit Einheit, Zielbereich, Status und Zeitpunkt
* Gerätewechsel und mehrere Geräte pro Pool laufen in denselben Variablen weiter
* Erkennung von Messbereichsüber- und -unterschreitung
* Kacheln für die Kachel-Visualisierung: Messwerte, Dosierungsempfehlung und Aktives Chlor
* Archivierung der Messwerte, auch nachträglich
* Dosierungsempfehlung und Berechnung des aktiven Chlors über die Cloud

Die einzelnen Funktionen stehen in den Modul-Beschreibungen.

## 3. Enthaltene Module

* [PLCloud](PLCloud/README.md)
  * Gateway zur LabCOM-Cloud mit dem API-Token
* [PLConfigurator](PLConfigurator/README.md)
  * Legt für jeden Pool des Kontos die Instanzen an
* [PLAccount](PLAccount/README.md)
  * Splitter pro Pool, liefert die Poolgröße
* [PLMeasurements](PLMeasurements/README.md)
  * Messwerte eines Pools
* [PLDosageRecommendation](PLDosageRecommendation/README.md)
  * Dosierungsempfehlung
* [PLActiveChlor](PLActiveChlor/README.md)
  * Aktives Chlor

## 4. Screenshots

![Messwerte](docs/screenshots/messwerte.png)

![Dosierungsempfehlung](docs/screenshots/dosierempfehlung.png)

![Aktives Chlor](docs/screenshots/aktives_chlor.png)

## 5. Installation
Über den IP-Symcon Module Store.

## 6. Konfiguration in IP-Symcon
1. Auf labcom.cloud unter den Benutzereinstellungen einen API-Token erzeugen.
2. Einen Konfigurator anlegen. Dabei wird das Cloud-Gateway erzeugt, dort den Token eintragen.
3. Im Konfigurator für jeden Pool die gewünschten Instanzen anlegen.

## 7. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 8. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
