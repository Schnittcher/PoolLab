# PLActiveChlor
Anhand dieser Instanz kann das aktive Chlor berechnet werden..

   ## Inhaltverzeichnis
- [PLActiveChlor](#plactivechlor)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
  - [3. Webfront](#3-webfront)

## 1. Konfiguration
   
Keine Konfiguration nötig.

## 2. Funktionen

```
PL_calculateActiveChlor(int $InstanceID,int $temperature, float $pH, float $chlorine, float $cya)
```

Mit dieser Funktion kann das aktive Chlor berechnet werden.

**Beispiel**
```
$result = PL_calculateActiveChlor(12345, 20, 7, 1.5, 10);
```

## 3. Webfront

Im Webfront müssen die Variablen "pH", "Temperatur", "Chlor" und "CYA" angegeben werden, mit einem klick auf Berechnen lässt sich das aktive Chlor berechnen.