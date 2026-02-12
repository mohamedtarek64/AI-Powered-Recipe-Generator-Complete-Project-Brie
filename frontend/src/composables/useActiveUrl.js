import { usePage } from '@inertiajs/vue3';
import { computed, readonly } from 'vue';
import { toUrl } from '@/lib/utils';
const page = usePage();
const currentUrlReactive = computed(() => new URL(page.url, window?.location.origin).pathname);
export function useActiveUrl() {
    function urlIsActive(urlToCheck, currentUrl) {
        const urlToCompare = currentUrl ?? currentUrlReactive.value;
        return toUrl(urlToCheck) === urlToCompare;
    }
    return {
        currentUrl: readonly(currentUrlReactive),
        urlIsActive,
    };
}
//# sourceMappingURL=useActiveUrl.js.map