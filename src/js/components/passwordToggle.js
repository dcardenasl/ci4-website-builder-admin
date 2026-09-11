/**
 * Alpine component for password fields with an accessible visibility toggle.
 */
export const passwordToggle = () => ({
    visible: false,

    toggle() {
        this.visible = !this.visible;
    },
});
