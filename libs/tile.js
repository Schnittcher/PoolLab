// Gemeinsame Hilfsfunktionen der Kacheln dieser Bibliothek.
window.PoolLabTile = {
    // Neues Element, der Text wird als reiner Text gesetzt (nie als HTML)
    element: function (tag, className, text) {
        const node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (text !== undefined && text !== '') {
            node.textContent = text;
        }
        return node;
    },

    // Hoehe: sichtbare Flaeche minus Rand des body (der Rand oben ist fuer Instanzname und Maximieren-Knopf).
    // Manche Anzeigen melden mit innerHeight mehr, als sichtbar ist: der kleinere Wert gilt, abgerundet.
    fit: function (root) {
        const style = getComputedStyle(document.body);
        const visible = Math.min(window.innerHeight, document.documentElement.clientHeight || window.innerHeight);
        const height = Math.floor(visible - parseFloat(style.marginTop) - parseFloat(style.marginBottom));
        root.style.height = Math.max(height, 0) + 'px';
    },

    // Eingabezeile mit Beschriftung links und dem Feld rechts
    row: function (label, input) {
        const row = PoolLabTile.element('label', 'pl-field');
        row.appendChild(PoolLabTile.element('span', '', label));
        row.appendChild(input);
        return row;
    }
};
