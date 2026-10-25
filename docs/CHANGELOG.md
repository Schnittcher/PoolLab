# Changelog

## 25.10.2026 - Version 2.0

### Neu
- Unterstützung für PoolLab 1.0 und PoolLab 2.0: Die Messwerte werden pro Pool aufgebaut, egal welches Gerät misst. Ein Gerätewechsel unterbricht die Variablen nicht.
- Alle Messgrößen der Geräte werden unterstützt, die Variablen entstehen mit der ersten Messung.
- Neue Variablen "Letztes Gerät" und "Gerätetyp" je Pool.
- Erkennung von Messbereichsüber- und -unterschreitung im Status.
- Optionaler Filter auf ein einzelnes Gerät (Seriennummer).
- Abfrage holt nur noch neue Messungen. Nachträglich vom Gerät synchronisierte Messungen werden erkannt.
- "Fehlende Werte ins Archiv ergänzen" fügt nur Werte hinzu, die im Archiv fehlen, und löscht nichts.
- Klare Statusmeldungen im Cloud-Gateway (Token fehlt, abgelehnt, Cloud nicht erreichbar) und Schaltfläche "Verbindung testen".
- Variablen nutzen die neuen Darstellungen von Symcon 9.
- Kacheln für die Kachel-Visualisierung: Messwerte mit Zielbereich, Status und Zeitpunkt je Messgröße sowie Dosierungsempfehlung und Aktives Chlor mit Eingabe und Ergebnis.

### Fixes
- Zuordnung der Chemischen Gruppen im Dosierungsrechner korrigiert (QAC, Schwebende Feststoffe, Nickel) und die Übersetzung von "Öl" und "Gelöster Sauerstoff".
- Der Konfigurator zeigt nur noch Name, Ort und Wassermenge der Pools.

Achtung: Das Modul wurde komplett neu aufgebaut und benötigt Symcon 9.0. Die Messwert-Instanzen müssen neu angelegt werden, die Variablen heißen anders (zum Beispiel `pH`, `ChlorineFree`). Skripte und Visualisierungen müssen angepasst werden, alte Archivdaten bleiben in den alten Variablen.
