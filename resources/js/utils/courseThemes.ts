/**
 * Course-specific theme configuration for the Student Dashboard.
 *
 * Each entry maps a course code (e.g. 'BSIS') to a set of Tailwind class
 * strings. This file is the single source of truth for theming — add new
 * courses here and the dashboard will pick them up automatically.
 *
 * If a student's course code is not found, the dashboard falls back to the
 * 'default' theme (neutral gray) instead of incorrectly assuming BSIS.
 */

export type CourseTheme = {
    label: string;
    bannerClass: string;
    accentTextClass: string;
    softTextClass: string;
    headingTextClass: string;
    iconBgClass: string;
    progressBarClass: string;
    statusBoxClass: string;
    primaryButtonClass: string;
    primaryButtonHoverClass: string;
    selectedOfficeClass: string;
    selectedCheckClass: string;
    mobileNavClass: string;
    mobileNavActiveClass: string;
};

export const courseThemes: Record<string, CourseTheme> = {
    // ── Default / fallback theme (neutral) ─────────────────────────────────
    default: {
        label: 'Default Theme',
        bannerClass:
            'border border-slate-200 bg-gradient-to-r from-slate-100 via-slate-50 to-white',
        accentTextClass: 'text-slate-600',
        softTextClass: 'text-slate-700/80',
        headingTextClass: 'text-slate-900',
        iconBgClass: 'bg-slate-200',
        progressBarClass: 'bg-slate-500',
        statusBoxClass: 'border-slate-200 bg-slate-50 text-slate-700',
        primaryButtonClass: 'bg-slate-600',
        primaryButtonHoverClass: 'hover:bg-slate-700',
        selectedOfficeClass: 'border-slate-500 bg-slate-50 text-slate-900',
        selectedCheckClass: 'border-slate-600 bg-slate-600 text-white',
        mobileNavClass: 'border-slate-300 bg-slate-900/95 shadow-slate-950/20',
        mobileNavActiveClass: 'bg-white/15 text-white ring-1 ring-slate-100/40',
    },

    // ── Course themes ───────────────────────────────────────────────────────
    BSIS: {
        label: 'BSIS Theme',
        bannerClass:
            'border border-blue-100 bg-gradient-to-r from-blue-100 via-blue-50 to-white',
        accentTextClass: 'text-blue-700',
        softTextClass: 'text-blue-900/80',
        headingTextClass: 'text-blue-950',
        iconBgClass: 'bg-blue-200',
        progressBarClass: 'bg-blue-600',
        statusBoxClass: 'border-blue-200 bg-blue-50 text-blue-800',
        primaryButtonClass: 'bg-blue-600',
        primaryButtonHoverClass: 'hover:bg-blue-700',
        selectedOfficeClass: 'border-blue-600 bg-blue-50 text-blue-900',
        selectedCheckClass: 'border-blue-600 bg-blue-600 text-white',
        mobileNavClass: 'border-blue-200 bg-blue-950/95 shadow-blue-950/25',
        mobileNavActiveClass: 'bg-white/15 text-white ring-1 ring-blue-200/40',
    },
    BAEL: {
        label: 'BAEL Theme',
        bannerClass:
            'border border-yellow-100 bg-gradient-to-r from-yellow-100 via-yellow-50 to-white',
        accentTextClass: 'text-yellow-700',
        softTextClass: 'text-yellow-900/80',
        headingTextClass: 'text-yellow-950',
        iconBgClass: 'bg-yellow-200',
        progressBarClass: 'bg-yellow-500',
        statusBoxClass: 'border-yellow-200 bg-yellow-50 text-yellow-800',
        primaryButtonClass: 'bg-yellow-500',
        primaryButtonHoverClass: 'hover:bg-yellow-600',
        selectedOfficeClass: 'border-yellow-500 bg-yellow-50 text-yellow-900',
        selectedCheckClass: 'border-yellow-500 bg-yellow-500 text-white',
        mobileNavClass:
            'border-yellow-200 bg-yellow-600/95 shadow-yellow-900/20',
        mobileNavActiveClass:
            'bg-white/20 text-white ring-1 ring-yellow-100/50',
    },
    BAPS: {
        label: 'BAPS Theme',
        bannerClass:
            'border border-red-100 bg-gradient-to-r from-red-100 via-red-50 to-white',
        accentTextClass: 'text-red-700',
        softTextClass: 'text-red-900/80',
        headingTextClass: 'text-red-950',
        iconBgClass: 'bg-red-200',
        progressBarClass: 'bg-red-600',
        statusBoxClass: 'border-red-200 bg-red-50 text-red-800',
        primaryButtonClass: 'bg-red-600',
        primaryButtonHoverClass: 'hover:bg-red-700',
        selectedOfficeClass: 'border-red-600 bg-red-50 text-red-900',
        selectedCheckClass: 'border-red-600 bg-red-600 text-white',
        mobileNavClass: 'border-red-200 bg-red-800/95 shadow-red-950/20',
        mobileNavActiveClass: 'bg-white/15 text-white ring-1 ring-red-100/40',
    },
    BSA: {
        label: 'BSA Theme',
        bannerClass:
            'border border-green-100 bg-gradient-to-r from-green-100 via-green-50 to-white',
        accentTextClass: 'text-green-700',
        softTextClass: 'text-green-900/80',
        headingTextClass: 'text-green-950',
        iconBgClass: 'bg-green-200',
        progressBarClass: 'bg-green-600',
        statusBoxClass: 'border-green-200 bg-green-50 text-green-800',
        primaryButtonClass: 'bg-green-600',
        primaryButtonHoverClass: 'hover:bg-green-700',
        selectedOfficeClass: 'border-green-600 bg-green-50 text-green-900',
        selectedCheckClass: 'border-green-600 bg-green-600 text-white',
        mobileNavClass: 'border-green-200 bg-green-800/95 shadow-green-950/20',
        mobileNavActiveClass: 'bg-white/15 text-white ring-1 ring-green-100/40',
    },
    BSAIS: {
        label: 'BSAIS Theme',
        bannerClass:
            'border border-gray-200 bg-gradient-to-r from-gray-200 via-gray-100 to-white',
        accentTextClass: 'text-gray-700',
        softTextClass: 'text-gray-800/80',
        headingTextClass: 'text-gray-950',
        iconBgClass: 'bg-gray-300',
        progressBarClass: 'bg-gray-700',
        statusBoxClass: 'border-gray-300 bg-gray-100 text-gray-800',
        primaryButtonClass: 'bg-gray-700',
        primaryButtonHoverClass: 'hover:bg-gray-800',
        selectedOfficeClass: 'border-gray-700 bg-gray-100 text-gray-900',
        selectedCheckClass: 'border-gray-700 bg-gray-700 text-white',
        mobileNavClass: 'border-gray-300 bg-gray-900/95 shadow-gray-950/20',
        mobileNavActiveClass: 'bg-white/15 text-white ring-1 ring-gray-100/40',
    },
    BECED: {
        label: 'BECED Theme',
        bannerClass:
            'border border-sky-100 bg-gradient-to-r from-sky-100 via-sky-50 to-white',
        accentTextClass: 'text-sky-700',
        softTextClass: 'text-sky-900/80',
        headingTextClass: 'text-sky-950',
        iconBgClass: 'bg-sky-200',
        progressBarClass: 'bg-sky-500',
        statusBoxClass: 'border-sky-200 bg-sky-50 text-sky-800',
        primaryButtonClass: 'bg-sky-500',
        primaryButtonHoverClass: 'hover:bg-sky-600',
        selectedOfficeClass: 'border-sky-500 bg-sky-50 text-sky-900',
        selectedCheckClass: 'border-sky-500 bg-sky-500 text-white',
        mobileNavClass: 'border-sky-200 bg-sky-800/95 shadow-sky-950/20',
        mobileNavActiveClass: 'bg-white/15 text-white ring-1 ring-sky-100/40',
    },
    BSCRIM: {
        label: 'BSCRIM Theme',
        bannerClass:
            'border border-orange-100 bg-gradient-to-r from-orange-100 via-orange-50 to-white',
        accentTextClass: 'text-orange-700',
        softTextClass: 'text-orange-900/80',
        headingTextClass: 'text-orange-950',
        iconBgClass: 'bg-orange-200',
        progressBarClass: 'bg-orange-600',
        statusBoxClass: 'border-orange-200 bg-orange-50 text-orange-800',
        primaryButtonClass: 'bg-orange-600',
        primaryButtonHoverClass: 'hover:bg-orange-700',
        selectedOfficeClass: 'border-orange-600 bg-orange-50 text-orange-900',
        selectedCheckClass: 'border-orange-600 bg-orange-600 text-white',
        mobileNavClass:
            'border-orange-200 bg-orange-800/95 shadow-orange-950/20',
        mobileNavActiveClass:
            'bg-white/15 text-white ring-1 ring-orange-100/40',
    },
};

/**
 * Resolve a CourseTheme by course code.
 * Falls back to the neutral 'default' theme if the code is unknown,
 * rather than incorrectly assuming BSIS.
 */
export function resolveCourseTheme(
    courseCode: string | null | undefined,
): CourseTheme {
    if (courseCode && courseCode in courseThemes) {
        return courseThemes[courseCode];
    }

    return courseThemes.default;
}
