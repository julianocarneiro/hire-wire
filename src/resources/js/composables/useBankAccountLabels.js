import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Resolves PT labels from shared Inertia `accountTypeOptions` and movement type codes.
 */
export function useBankAccountLabels() {
    const page = usePage();
    const accountTypeOptions = computed(() => page.props.accountTypeOptions ?? []);

    function typeLabelFor(accountType) {
        if (!accountType) {
            return '';
        }
        return accountTypeOptions.value.find((o) => o.value === accountType)?.label ?? accountType;
    }

    function movementTypeLabel(type) {
        if (type === 'deposit') {
            return 'Depósito';
        }
        if (type === 'monthly_adjustment') {
            return 'Correção monetária';
        }
        return type;
    }

    return { accountTypeOptions, typeLabelFor, movementTypeLabel };
}
