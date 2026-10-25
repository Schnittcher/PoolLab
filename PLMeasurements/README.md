# PLMeasurements
Legt die Messwerte eines Pools aus der LabCOM-Cloud als Variablen an. Eine Instanz gehört zu genau einem Pool (Account der Cloud) und nimmt die Messungen aller Geräte entgegen, die in diesen Pool messen. Das Gerät spielt für die Variablen keine Rolle: Wird das Gerät getauscht oder ein Gerät für mehrere Pools genutzt, laufen die Werte pro Pool in derselben Variable weiter. Die Instanz wird normalerweise über den Konfigurator angelegt. Sie hat eine Kachel für die Kachel-Visualisierung.

## Inhaltsverzeichnis
- [PLMeasurements](#plmeasurements)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Kachel](#3-kachel)
  - [4. Funktionen](#4-funktionen)
  - [5. Spenden](#5-spenden)
  - [6. Lizenz](#6-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
Aktiv | Schaltet die zyklische Abfrage ein oder aus. Standard: an.
Aktualisierungsintervall | Abstand der Abfragen in Sekunden, mindestens 60. Standard: 600. Es werden nur neue Messungen verarbeitet.
Nur dieses Gerät | Seriennummer eines Geräts. Leer: Messungen aller Geräte des Pools. Eine Änderung liest die Messungen neu ein.

Die Schaltfläche „Daten aktualisieren" ruft die Cloud sofort ab. „Fehlende Werte ins Archiv ergänzen" schreibt alle Messungen in das Archiv, die dort noch nicht stehen. Vorhandene Archivwerte bleiben unverändert, die Protokollierung der Variablen wird dabei eingeschaltet.

## 2. Variablen
Die Variablen entstehen erst, wenn die erste Messung der jeweiligen Messgröße eintrifft. Der Ident der Messgröße steht in der Datei `parameters.json` (Zuordnung der Parameter-IDs der Cloud, sowohl PoolLab 1.0 als auch 2.0). Messgrößen, die dort fehlen, werden mit einer Warnung im Meldungsfenster ignoriert.

Im Debug-Fenster der Instanz steht zu jeder ignorierten Messung eine Zeile `UNKNOWN PARAMETER` mit allen Angaben, die zum Ergänzen nötig sind, zum Beispiel:

```
parameter_id=241 parameter="Urea" scenario_id=741 scenario="458-Urea" unit="mg/l" value=1.2 device=AB-12-CD time=2026-10-25 12:00:00
```

Die Zeile enthält die `parameter_id`, den Namen der Messgröße in der Cloud (`parameter`), die Messmethode (`scenario` und `scenario_id`), die Einheit, den Wert, die Seriennummer des Geräts und den Zeitpunkt. Zusätzlich fasst das Debug jeden Abruf zusammen (abgerufene und neue Messungen, aktualisierte Messgrößen, ignorierte Messungen) und meldet Messbereichsüber- und -unterschreitung und zurückgestellte ältere Messungen.

Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
Letztes Gerät | `LastDevice` | Text | Seriennummer des Geräts mit der neuesten Messung.
Gerätetyp | `DeviceType` | Text | PoolLab 1.0, PoolLab 2.0 oder unbekannt, abgeleitet aus der Messmethode.
Messgröße (zum Beispiel pH, Freies Chlor) | `<Messgröße>` | Kommazahl | Letzter Messwert mit Einheit und Nachkommastellen aus der Cloud. Bei Messbereichsüber- oder -unterschreitung bleibt der Wert unverändert.
… Status | `<Messgröße>_State` | Text | zu niedrig, in Ordnung, zu hoch, über oder unter dem Messbereich.
… Grenzwert (gering) / (hoch) | `<Messgröße>_IdealLow` / `_IdealHigh` | Kommazahl | Zielbereich der Messgröße.
… Kommentar | `<Messgröße>_Comment` | Text | Kommentar zur Messung.
… letzte Messung | `<Messgröße>_Timestamp` | Datum/Uhrzeit | Zeitpunkt der Messung.

Beispiele für Idents: `pH`, `ChlorineFree`, `ChlorineTotal`, `Alkalinity`, `CyanuricAcid`.

## 3. Kachel
Die Kachel zeigt je Messgröße eine Karte mit dem letzten Wert, einer Skala mit dem Zielbereich und dem Messpunkt, dem Status, dem Zeitpunkt und einem eventuellen Kommentar. Oben stehen Gerätetyp und Seriennummer des letzten Geräts. Sie aktualisiert sich mit jeder neuen Messung und wird nicht bedient. Farben, Schrift und Schriftgröße übernimmt sie von der Visualisierung.

Die maximierte Ansicht der Kachel braucht Symcon 9.1. Unter Symcon 9.0 bleibt sie leer.

## 4. Funktionen

`void PL_UpdateData(integer $InstanzID);`
Ruft neue Messungen aus der Cloud ab und aktualisiert die Variablen. Läuft automatisch im eingestellten Intervall.

`void PL_FillArchive(integer $InstanzID);`
Ergänzt das Archiv um alle Messungen, die dort noch fehlen.

## 5. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
