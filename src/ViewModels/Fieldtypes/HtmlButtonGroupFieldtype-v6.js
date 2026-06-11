document.addEventListener('alpine:init', function () {
    Statamic.$components.register('html_button_group-fieldtype', {
        template: `
            <ui-button-group>
                <ui-button
                    v-for="(option, $index) in options"
                    ref="button"
                    :disabled="config.disabled"
                    :key="$index"
                    :name="name"
                    :read-only="isReadOnly"
                    :text="option.label || option.value"
                    :value="option.value"
                    :variant="value == option.value ? 'pressed' : 'default'"
                    @click="update($event.target.closest('button').value)"
                    v-html="option.label || option.value"
                />
            </ui-button-group>
        `,
        name: "HTMLButtonGroupFieldtype",
        props: {
            value: String,
            config: Object,
            name: String,
        },
        computed: {
            options() {
                if (!this.config?.options) return [];

                return Object.entries(this.config.options).map(([key, value]) => {
                    return {
                        'value': typeof value === 'string' ? key : (value.key || key),
                        'label': value.value || value
                    };
                });
            },
        },

        methods: {
            update(value) {
                this.$emit('update:value', value);
            },
        },
    });
});
