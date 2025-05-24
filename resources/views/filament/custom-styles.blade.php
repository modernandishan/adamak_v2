<style>
    /* Aspect Ratio Utilities */
    .aspect-w-16 {
        position: relative;
        padding-bottom: calc(var(--tw-aspect-h) / var(--tw-aspect-w) * 100%);
        --tw-aspect-w: 16;
    }
    .aspect-h-9 {
        --tw-aspect-h: 9;
    }
    .aspect-h-6 {
        --tw-aspect-h: 6;
    }
    .aspect-w-16 > * {
        position: absolute;
        height: 100%;
        width: 100%;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    /* Line Clamp Utilities */
    .line-clamp-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .line-clamp-3 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }
</style>
