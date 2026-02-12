import { defineComponent, h } from 'vue';
import { useRouter } from 'vue-router';
export const Head = defineComponent({
    props: {
        title: String,
    },
    setup(props) {
        if (props.title) {
            document.title = props.title;
        }
        return () => null;
    },
});
export const Link = defineComponent({
    props: {
        href: {
            type: String,
            required: true,
        },
        method: {
            type: String,
            default: 'get',
        },
        as: {
            type: String,
            default: 'a',
        },
        prefetch: {
            type: Boolean,
            default: false,
        },
    },
    setup(props, { slots }) {
        const router = useRouter();
        const onClick = (e) => {
            if (props.method.toLowerCase() === 'get' && props.as === 'a') {
                e.preventDefault();
                router.push(props.href);
            }
        };
        return () => h(props.as === 'button' ? 'button' : 'a', {
            href: props.href,
            onClick,
            class: 'cursor-pointer'
        }, slots.default?.());
    },
});
export const router = {
    post: (url, options) => console.log('Mock router.post to', url),
    patch: (url, options) => console.log('Mock router.patch to', url),
    delete: (url, options) => console.log('Mock router.delete to', url),
    get: (url, options) => console.log('Mock router.get to', url),
    visit: (url, options) => console.log('Mock router.visit to', url),
    reload: (options) => console.log('Mock router.reload'),
    flushAll: () => console.log('Mock router.flushAll'),
};
export function useForm(data) {
    // Basic mock of Inertia useForm
    return {
        ...data,
        processing: false,
        wasSuccessful: false,
        recentlySuccessful: false,
        errors: {},
        post: (url, options) => console.log('Mock POST to', url),
        put: (url, options) => console.log('Mock PUT to', url),
        patch: (url, options) => console.log('Mock PATCH to', url),
        delete: (url, options) => console.log('Mock DELETE to', url),
        submit: (method, url, options) => console.log('Mock useForm.submit', method, url),
        reset: () => { },
        clearErrors: () => { },
        setError: (field, value) => { },
    };
}
export function usePage() {
    return {
        props: {
            auth: {
                user: { name: 'Guest', email: 'guest@example.com' }
            },
            ziggy: {},
        }
    };
}
//# sourceMappingURL=inertia.js.map