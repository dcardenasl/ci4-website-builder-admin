import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        // Canvas modules exercise the same browser contracts as the panel:
        // window/document, focus management and ResizeObserver boundaries.
        environment: 'happy-dom',
        include: ['src/js/**/*.test.js'],
    },
});
