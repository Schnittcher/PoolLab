# PLMeasurements
Dieses Instanz spiegelt die Messwerte in IP-Symcon wieder.

   ## Inhaltverzeichnis
- [PLMeasurements](#plmeasurements)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   Aktiv | Über diese CheckBox kann die Instanz aktiv bzw. inaktiv geschaltet werden.
   Aktualisierungsintervall | Hier kann in Sekunden die Zeit angegeben werden, wie oft die Werte von der Cloud abgerufen werden sollen.
   Variablen | Anhand dieser Liste kann ausgewählt werden, welche Messwerte in Symcon übertragen werden sollen.
   Aktualisiere Werte | Ruft die aktuellen Werte ab.
   Archivdaten aktualisieren | Mit diesem Button lassen sich alle Messwerte der Cloud in das Symcon Archiv übertragen.
   
   ## 2. Funktionen

   ```php
   PL_updateData(bool $archive)
   ```
   Mit dieser Funktion lassen sich die Werte aktualisieren, wenn die Variable $archiv auf true gesetzt wird, werden die Daten ebenso in das Archiv übertragen.

   **Beispiel:**
   
   ```php
   PL_updateData(true) //Werte werden ebenfalls in das Archiv übertragen.
   PL_updateData(false) //Werte werden nicht in das Archiv übertragen.
   ```