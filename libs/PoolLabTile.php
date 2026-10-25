<?php

declare(strict_types=1);

/**
 * Gemeinsame Teile der Kacheln dieser Bibliothek: Stylesheet (tile.css), HTML des Moduls und Startdaten.
 */
trait PoolLabTile
{
    private function buildTile(string $HtmlFile, array $Data): string
    {
        $style = strval(file_get_contents(__DIR__ . '/tile.css'));
        $script = strval(file_get_contents(__DIR__ . '/tile.js'));
        $html = strval(file_get_contents($HtmlFile));
        return '<style>' . $style . '</style><script>' . $script . '</script>' . $html . '<script>handleMessage(' . json_encode(json_encode($Data)) . ');</script>';
    }

    private function updateTile(array $Data): void
    {
        $this->UpdateVisualizationValue(json_encode($Data));
    }

    private function setTileType(): void
    {
        //Vollbild ab Symcon 9.1, davor die normale HTML-Kachel (1)
        $this->SetVisualizationType(defined('INSTANCE_VISUALIZATION_TYPE_HTML_FULLSCREEN') ? INSTANCE_VISUALIZATION_TYPE_HTML_FULLSCREEN : 1);
    }
}
