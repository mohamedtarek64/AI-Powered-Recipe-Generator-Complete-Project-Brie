import { defineComponent, h, PropType } from 'vue';
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

        const onClick = (e: MouseEvent) => {
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
    post: (url: string, options: any) => console.log('Mock router.post to', url),
    patch: (url: string, options: any) => console.log('Mock router.patch to', url),
    delete: (url: string, options: any) => console.log('Mock router.delete to', url),
    get: (url: string, options: any) => console.log('Mock router.get to', url),
    visit: (url: string, options: any) => console.log('Mock router.visit to', url),
    reload: (options: any) => console.log('Mock router.reload'),
    flushAll: () => console.log('Mock router.flushAll'),
};

export function useForm(data: any) {
    // Basic mock of Inertia useForm
    return {
        ...data,
        processing: false,
        wasSuccessful: false,
        recentlySuccessful: false,
        errors: {} as Record<string, string>,
        post: (url: string, options: any) => console.log('Mock POST to', url),
        put: (url: string, options: any) => console.log('Mock PUT to', url),
        patch: (url: string, options: any) => console.log('Mock PATCH to', url),
        delete: (url: string, options: any) => console.log('Mock DELETE to', url),
        submit: (method: string, url: string, options: any) => console.log('Mock useForm.submit', method, url),
        reset: () => { },
        clearErrors: () => { },
        setError: (field: string, value: string) => { },
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
