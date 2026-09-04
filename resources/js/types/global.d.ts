import type {
    createHeadManager,
    Page,
    PageProps,
    Router,
} from '@inertiajs/core';
import type { Auth } from '@/types/auth';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    // Augment PageProps so $page.props.auth is typed in Vue templates
    interface PageProps {
        auth: Auth;
        name: string;
        sidebarOpen: boolean;
    }

    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page<PageProps>;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
