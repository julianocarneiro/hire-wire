import { nextTick, ref } from 'vue';

/**
 * Accessible three-tab pattern (movements page): roving tabindex + arrow/home/end.
 */
export function useAccountTabsThree() {
    const activeTab = ref(0);
    const tabDepositRef = ref(null);
    const tabListRef = ref(null);
    const tabCorrecaoRef = ref(null);
    const tabRefs = [tabDepositRef, tabListRef, tabCorrecaoRef];
    const len = 3;

    function onTabKeydown(e) {
        const key = e.key;
        if (key !== 'ArrowRight' && key !== 'ArrowLeft' && key !== 'Home' && key !== 'End') {
            return;
        }
        e.preventDefault();
        let next = activeTab.value;
        if (key === 'ArrowRight') {
            next = (activeTab.value + 1) % len;
        } else if (key === 'ArrowLeft') {
            next = (activeTab.value + len - 1) % len;
        } else if (key === 'Home') {
            next = 0;
        } else {
            next = len - 1;
        }
        activeTab.value = next;
        nextTick(() => {
            tabRefs[next]?.value?.focus();
        });
    }

    return {
        activeTab,
        tabDepositRef,
        tabListRef,
        tabCorrecaoRef,
        onTabKeydown,
    };
}
