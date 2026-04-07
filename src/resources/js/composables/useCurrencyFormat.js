/**
 * BRL display helpers for bank UI (aligned with pt-BR).
 */
export function useCurrencyFormat() {
    function formatBalance(value) {
        const n = Number.parseFloat(value);
        if (Number.isNaN(n)) {
            return value;
        }
        return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function formatDate(iso) {
        if (!iso) {
            return '';
        }
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) {
            return iso;
        }
        return d.toLocaleString('pt-BR', {
            dateStyle: 'short',
            timeStyle: 'short',
        });
    }

    return { formatBalance, formatDate };
}
